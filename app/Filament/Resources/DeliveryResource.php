<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
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
                    ->relationship('user', 'name')
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
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('kirimWhatsApp')
                        ->label('Kirim WhatsApp')
                        ->tooltip('Kirim pesan WhatsApp ke penerima pengiriman')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('default')
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
                        }),
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
