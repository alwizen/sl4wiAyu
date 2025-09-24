<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Helpers\BitlyHelper;
use App\Models\Delivery;
use App\Models\User;
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
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DeliveryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Pengiriman';

    protected static ?string $label = 'Delivery';

    public static function afterCreate(Model $record, Form $form): void
    {
        // Notifikasi untuk pengguna yang sedang login (sebagai feedback langsung)
        Notification::make()
            ->title('Pengiriman baru berhasil dibuat!')
            ->success()
            ->send();
        $superAdmins = User::whereHas('roles', fn($query) => $query->where('name', 'super_admin'))->get();

        if ($superAdmins->isNotEmpty()) { // Pastikan ada super admin yang ditemukan
            Notification::make()
                ->title('Pengiriman Baru Dibuat: #' . $record->id)
                ->body('Pengiriman dengan ID ' . $record->id . ' telah berhasil dibuat.')
                ->info() // Atau success(), warning(), dll.
                ->sendToDatabase($superAdmins);
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delivery_number')
                    ->label('No. Pengiriman')
                    ->columnSpanFull()
                    ->default(function () {
                        $date = Carbon::now();
                        $randomStr = Str::random(3);
                        return 'SPPG/' . $date->format('dmy') . '/' . strtoupper($randomStr);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\DatePicker::make('delivery_date')
                    ->label('Tanggal Pengiriman')
                    ->default(now())
                    ->required(),

                Forms\Components\TimePicker::make('time_delivery')
                    ->label('Jam Pengiriman')
                    ->default('07:00')
                    ->native(false)
                    ->format('H:i')
                    ->seconds(false)
                    ->required(),

                Forms\Components\Select::make('recipient_id')
                    ->label('Nama Penerima')
                    ->relationship('recipient', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->label('Jumlah')
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
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->label('Supir')
                    ->options(function () {
                        return \App\Models\User::whereHas('roles', function ($query) {
                            $query->where('name', 'driver');
                        })->pluck('name', 'id');
                    })
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
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('delivery_number')
                    ->searchable()
                    ->label('No. Pengiriman')
                    ->copyable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->label('Tanggal'),

                Tables\Columns\TextColumn::make('time_delivery')
                    ->time('H:i')
                    ->suffix(' Wib')
                    ->label('Jam'),

                Tables\Columns\TextColumn::make('car.car_number')
                    ->searchable()
                    ->label('Mobil'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Supir'),

                Tables\Columns\TextColumn::make('recipient.name')
                    ->searchable()
                    ->label('Penerima'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jml')
                    ->summarize(Sum::make()
                        ->label('Total')
                        ->suffix('Box'))
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
                            ->when($data['from'], fn($q) => $q->whereDate('delivery_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('delivery_date', '<=', $data['until']));
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
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\ViewAction::make()
                        ->color('primary'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger'),
                    \Filament\Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->url(fn(Delivery $record) => route('delivery.print', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('kirimWhatsApp')
                        ->label('Kirim WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(fn(Delivery $record) => $record->whatsapp_url)
                        ->openUrlInNewTab()
                        ->visible(fn(Delivery $record) => !empty($record->whatsapp_url))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Link Tracking')
                        ->modalDescription('Apakah Anda yakin ingin mengirim link tracking via WhatsApp?')
                        ->modalSubmitActionLabel('Ya, Kirim'),

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
                                ->image()
                                ->optimize('webp')
                                ->directory('buktiPengiriman')
                                ->resize(50),
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
                            $recipient = auth()->user();

                            Notification::make()
                                ->title('Pengiriman berhasil ditandai sebagai Disiapkan')
                                ->success()
                                ->sendToDatabase($recipient);
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
                        ->visible(fn(Delivery $record) => $record->status === 'terkirim' && !is_null($record->returned_qty))
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

                ])->button()
                    ->label('Tindakan')
                    ->icon('heroicon-o-paper-clip')
                    ->size(ActionSize::Small)
                // ->outlined(),

            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
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
