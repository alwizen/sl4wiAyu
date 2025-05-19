<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Delivery;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DeliveryToday extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.delivery-today';

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    protected static ?string $title = 'Pengiriman Hari Ini';

    protected static ?string $navigationLabel = 'Pengiriman Hari Ini';

    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Delivery::query()
                    ->where('delivery_date', '=', now()->toDateString())
            )
            ->heading('Daftar Pengiriman Hari Ini')
            ->description(fn() => 'Tanggal: ' . now()->format('d F Y'))
            ->columns([
                TextColumn::make('delivery_number')
                    ->searchable()
                    ->label('No. Pengiriman'),
//                TextColumn::make('delivery_date')
//                    ->date()
//                    ->sortable()
//                    ->label('Tanggal Pengiriman'),
                TextColumn::make('recipient.name')
                    ->sortable()
                    ->label('Penerima'),
                TextColumn::make('qty')
                    ->label('Jumlah'),
                TextColumn::make('received_qty')
                    ->label('Jumlah Diterima'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'dikemas' => 'secondary',
                        'dalam_perjalanan' => 'gray',
                        'terkirim' => 'warning',
                        'selesai' => 'info',
                        'kembali' => 'success',
                        default => 'gray',
                    })
                    ->label('Status'),
                TextColumn::make('prepared_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Disiapkan Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipped_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dalam Perjalanan Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Diterima Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Selesai Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('delivery_date')
                            ->default(now())
                            ->label('Tanggal Pengiriman'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['delivery_date'],
                            fn(Builder $query, $date): Builder => $query->whereDate('delivery_date', $date)
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['delivery_date']) {
                            return null;
                        }

                        return 'Tanggal: ' . Carbon::parse($data['delivery_date'])->format('d/m/Y');
                    }),
                SelectFilter::make('status')
                    ->options([
                        'dikemas' => 'Dikemas',
                        'dalam_perjalanan' => 'Dalam Perjalanan',
                        'terkirim' => 'Terkirim',
                        'selesai' => 'Selesai',
                        'kembali' => 'Kembali',
                    ])
                    ->label('Status Pengiriman'),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Action::make('inputReceivedQty')
                    ->label('Input Jumlah Diterima')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn(Delivery $record) => $record->status === 'terkirim' && is_null($record->received_qty))
                    ->form([
                        TextInput::make('received_qty')
                            ->label('Jumlah Diterima')
                            ->numeric()
                            ->required()
                            ->suffix('Box')
                            ->helperText('Masukkan jumlah barang yang diterima')
                    ])
                    ->action(function (Delivery $record, array $data) {
                        $record->received_qty = $data['received_qty'];
                        $record->save();

                        Notification::make()
                            ->title('Jumlah diterima berhasil disimpan')
                            ->success()
                            ->send();
                    }),
                Action::make('setPrepared')
                    ->label('Disiapkan')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check')
                    ->color('danger')
                    ->visible(fn(Delivery $record) => $record->status === 'dikemas')
                    ->action(function (Delivery $record) {
                        $record->status = 'disiapkan';
                        $record->prepared_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Pengiriman berhasil ditandai sebagai Disiapkan')
                            ->success()
                            ->send();
                    }),
                Action::make('setShipped')
                    ->label('Dalam Perjalanan')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn(Delivery $record) => $record->status === 'disiapkan')
                    ->action(function (Delivery $record) {
                        $record->status = 'dalam_perjalanan';
                        $record->shipped_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Status berhasil diperbarui ke Dalam Perjalanan')
                            ->success()
                            ->send();
                    }),

               Action::make('setDelivered')
                    ->label('Terkirim')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('info')
                    ->visible(fn(Delivery $record) => $record->status === 'dalam_perjalanan')
                    ->action(function (Delivery $record) {
                        $record->status = 'terkirim';
                        $record->received_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Status berhasil diperbarui ke Terkirim')
                            ->success()
                            ->send();
                    }),


                Action::make('setCompleted')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(Delivery $record) => $record->status === 'terkirim' && !is_null($record->received_qty))
                    ->action(function (Delivery $record) {
                        $record->status = 'selesai';
                        $record->returned_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Status berhasil diperbarui ke Selesai')
                            ->success()
                            ->send();
                    }),

               Action::make('kirimWhatsApp')
                    ->label('')
                    ->tooltip('Kirim pesan WhatsApp ke penerima pengiriman')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (Delivery $record) {
                        // Format tanggal untuk pesan
                        $formattedDate = Carbon::parse($record->delivery_date)->format('d/m/Y');

                        // Format status dalam bahasa Indonesia
                        $statusIndonesia = match ($record->status) {
                            'dikemas' => 'Dikemas',
                            'dalam_perjalanan' => 'Dalam Perjalanan',
                            'terkirim' => 'Terkirim',
                            'selesai' => 'Selesai',
                            'kembali' => 'Kembali',
                            default => $record->status,
                        };

                        // Format pesan WhatsApp
                        $message = "Informasi Pengiriman:\n"
                            . "Tanggal: {$formattedDate}\n"
                            . "No. Order: {$record->delivery_number}\n"
                            . "Jumlah: {$record->qty}\n";

                        // Tambahkan jumlah diterima jika ada
                        if (!is_null($record->received_qty)) {
                            $message .= "Jumlah Diterima: {$record->received_qty}\n";
                        }

                        $message .= "Nama Sekolah: {$record->recipient->name}\n"
                            . "Status: {$statusIndonesia}";

                        // Encode pesan untuk URL WhatsApp
                        $encodedMessage = urlencode($message);

                        // Ambil nomor WhatsApp penerima
                        $phoneNumber = $record->recipient->phone ?? '';

                        // Format nomor telepon ke format internasional
                        // Jika nomor dimulai dengan '0', ganti dengan kode negara Indonesia (62)
                        if (strlen($phoneNumber) > 0) {
                            if (substr($phoneNumber, 0, 1) === '0') {
                                $phoneNumber = '62' . substr($phoneNumber, 1);
                            } // Jika nomor tidak dimulai dengan '+' atau '62', tambahkan '62'
                            elseif (substr($phoneNumber, 0, 1) !== '+' && substr($phoneNumber, 0, 2) !== '62') {
                                $phoneNumber = '62' . $phoneNumber;
                            }

                            // Hapus karakter '+' jika ada
                            $phoneNumber = str_replace('+', '', $phoneNumber);
                        }

                        // Redirect ke WhatsApp dengan pesan yang sudah disiapkan
                        return redirect()->away("https://wa.me/{$phoneNumber}?text={$encodedMessage}");
                    })
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('bulkSetReturned')
                    ->label('Selesai')
                    ->tooltip('Tandai beberapa pengiriman sebagai Selesai (Kembali)')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $success = 0;
                        $skipped = 0;

                        foreach ($records as $record) {
                            // Hanya proses record dengan status 'dalam_perjalanan' atau 'terkirim' dan belum memiliki returned_at
                            if (in_array($record->status, ['dalam_perjalanan', 'terkirim']) && is_null($record->returned_at)) {
                                $record->status = 'selesai';
                                $record->returned_at = now();
                                $record->save();
                                $success++;
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title("$success pengiriman berhasil diubah ke status Selesai" . ($skipped > 0 ? " ($skipped dilewati)" : ""))
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-check-badge')
                    ->color('danger')
                    ->deselectRecordsAfterCompletion()
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->emptyStateHeading('Tidak ada pengiriman hari ini')
            ->emptyStateDescription('Pengiriman hari ini akan muncul di sini ketika dibuat.')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
