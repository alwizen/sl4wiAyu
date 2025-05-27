<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Helpers\BitlyHelper;
use App\Models\Delivery;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\ActionsPosition;


class DeliveryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Pengiriman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delivery_number')
                    ->label('No. Pengiriman')
                    ->default(function () {
                        $date = Carbon::now();
                        $randomStr = Str::random(3);
                        return 'SPPG-MGS/' . $date->format('dmy') . '/' . strtoupper($randomStr);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\DatePicker::make('delivery_date')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('recipient_id')
                    ->relationship('recipient', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->suffix('Box')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('received_qty')
                    ->suffix('Box')
                    ->numeric()
                    ->label('Jumlah Diterima')
                    ->visible(function ($record) {
                        return $record && in_array($record->status, ['terkirim', 'selesai']);
                    }),

                Forms\Components\Select::make('car_id')
                    ->label('Mobil')
                    ->relationship('car', 'car_number')
                    ->searchable()
                    ->preload()
                    ->required(),

                    Forms\Components\Select::make('user_id')
    ->label('Supir')
    ->options(function () {
        return \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'driver');
        })->pluck('name', 'id');
    })
    ->searchable()
    ->preload()
    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status Pengiriman')
                    ->options([
                        'dikemas' => 'Dikemas',
                        'disiapkan' => 'Disiapkan',
                        'dalam_perjalanan' => 'Dalam Perjalanan',
                        'terkirim' => 'Terkirim',
                        'selesai' => 'Selesai',
                    ])
                    ->default('dikemas')
                    ->disabled()
                    ->required(),
                DateTimePicker::make('prepared_at')
                    ->label('Disiapkan Pada')
                    ->disabled()
                    ->visible(fn($record) => $record && $record->prepared_at),
                DateTimePicker::make('shipped_at')
                    ->label('Dalam Perjalanan Pada')
                    ->disabled()
                    ->visible(fn($record) => $record && $record->shipped_at),
                DateTimePicker::make('received_at')
                    ->label('Diterima Pada')
                    ->disabled()
                    ->visible(fn($record) => $record && $record->received_at),
                DateTimePicker::make('returned_at')
                    ->label('Kembali Pada')
                    ->disabled()
                    ->visible(fn($record) => $record && $record->returned_at),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('delivery_number')
                    ->searchable()
                    ->label('No. Pengiriman')
                    ->copyable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('car.car_number')
                    ->searchable()
                    ->label('Mobil'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Supir'),

                Tables\Columns\TextColumn::make('recipient.name')
                    ->sortable()
                    ->label('Penerima'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jml')
                    ->summarize(Sum::make())
                    ->suffix(' Box'),

                Tables\Columns\TextColumn::make('received_qty')
                    ->label('Jml. Diterima')
                    ->suffix(' Box')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('returned_qty')
                    ->label('Jml. Dikembalikan')
                    ->suffix(' Box')
                    ->toggleable(isToggledHiddenByDefault: true),
                //  ->visible(fn ($record) => $record->received_qty !== null),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'dikemas' => 'secondary',
                        'disiapkan' => 'secondary',
                        'dalam_perjalanan' => 'warning',
                        'terkirim' => 'info',
                        'selesai' => 'success',
                        default => 'info',
                    })
                    ->label('Status'),
                Tables\Columns\ImageColumn::make('proof_delivery')
                    ->square(),
                Tables\Columns\TextColumn::make('prepared_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Disiapkan Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipped_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dalam Perjalanan Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Diterima Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Selesai Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('Tanggal Pengiriman')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('delivery_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('delivery_date', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
            
                        if ($data['from']) {
                            $indicators[] = 'Dari: ' . Carbon::parse($data['from'])->translatedFormat('d M Y');
                        }
            
                        if ($data['until']) {
                            $indicators[] = 'Sampai: ' . Carbon::parse($data['until'])->translatedFormat('d M Y');
                        }
            
                        return $indicators;
                    }),
            ])
            
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('kirimWhatsApp')
                        ->label('Kirim WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->action(function (Delivery $record) {
                            // Validasi nomor telepon
                            $phone = $record->recipient->phone ?? '';
                            if (empty($phone)) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Nomor telepon tidak tersedia')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Format nomor HP
                            $phone = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($phone, '0')) {
                                $phone = '62' . substr($phone, 1);
                            } elseif (!str_starts_with($phone, '62')) {
                                $phone = '62' . $phone;
                            }

                            // Validasi format nomor Indonesia
                            if (!preg_match('/^62\d{9,13}$/', $phone)) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Format nomor telepon tidak valid')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Pastikan short_code sudah ada
                            if (empty($record->short_code)) {
                                $record->short_code = $record->generateShortCode();
                                $record->save();
                            }

                            // Gunakan short URL custom
                            $shortUrl = config('app.url') . '/s/' . $record->short_code;
                            $deliveryNumber = $record->delivery_number;

                            // Siapkan pesan WhatsApp
                            $message = "📦 *No. Pengiriman: {$deliveryNumber}*\nMakan Bergisi Gratis sedang dalam perjalanan! 🚚\nCek status pengiriman Anda melalui link berikut:\n{$shortUrl}";
                            $encodedMessage = urlencode($message);

                            // Tampilkan notifikasi sukses
                            Notification::make()
                                ->title('Berhasil')
                                ->body('Membuka WhatsApp untuk mengirim link tracking')
                                ->success()
                                ->send();

                            $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";
                            return redirect()->away($whatsappUrl);
                        })
                        ->requiresConfirmation()
                        ->openUrlInNewTab()
                        ->modalHeading('Kirim Link Tracking')
                        ->modalDescription('Apakah Anda yakin ingin mengirim link tracking via WhatsApp?')
                        ->modalSubmitActionLabel('Ya, Kirim'),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),

                    Tables\Actions\Action::make('inputReceivedQty')
                        ->label('Jumlah Diterima')
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

                    Tables\Actions\Action::make('uploadProofDelivery')
                        ->label('Upload Bukti Pengiriman')
                        ->icon('heroicon-o-camera')
                        ->color('warning')
                        ->visible(fn(Delivery $record) => $record->status === 'terkirim' && is_null($record->proof_delivery))
                        ->form([
                            FileUpload::make('proof_delivery')
                                ->label('Bukti Pengiriman')

                        ])
                        ->action(function (Delivery $record, array $data) {
                            $record->proof_delivery = $data['proof_delivery'];
                            $record->save();

                            Notification::make()
                                ->title('Bukti pengiriman berhasil disimpan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('inputReturedQty')
                        ->label('Jumlah Dikembalikan')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->visible(fn(Delivery $record) => $record->status === 'terkirim' && is_null($record->returned_qty))
                        ->form([
                            TextInput::make('returned_qty')
                                ->label('Jumlah Dikembalikan')
                                ->numeric()
                                ->required()
                                ->suffix('Box')
                                ->helperText('Masukkan jumlah barang yang dikembalikan')
                        ])
                        ->action(function (Delivery $record, array $data) {
                            $record->returned_qty = $data['returned_qty'];
                            $record->save();

                            Notification::make()
                                ->title('Jumlah dikembalikan berhasil disimpan')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('setPrepared')
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
                    Tables\Actions\Action::make('setShipped')
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

                    Tables\Actions\Action::make('setDelivered')
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


                    Tables\Actions\Action::make('setCompleted')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
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

                    Tables\Actions\Action::make('viewProofDelivery')
                        ->label('Lihat Bukti')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(fn(Delivery $record) => !is_null($record->proof_delivery))
                        ->modalHeading('Bukti Pengiriman')
                        ->modalContent(fn(Delivery $record) => view('filament.modals.view-proof-delivery', [
                            'imageUrl' => $record->proof_delivery,
                        ])),

                ]),

            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'setPrepared',
            'setShipped',
            'setDelivered',
            'inputReceivedQty',
            'setCompleted',
            'setReturned',
            'viewProofDelivery',
            'uploadProofDelivery',
            'inputReturedQty',
            'kirimWhatsApp',
        ];
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDeliveries::route('/'),
        ];
    }
}
