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

class DeliveryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationGroup = 'Produksi & Pengiriman';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delivery_number')
                        ->label('No. Pengiriman')
                        ->default(function() {
                            $date = Carbon::now();
                            $randomStr = Str::random(3);
                            return 'SPPG-SLW/' . $date->format('d/m/Y') . '/' . strtoupper($randomStr);
                        })
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                Forms\Components\DatePicker::make('delivery_date')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('recipient_id')
                    ->relationship('recipient', 'name')
                    ->required(),
                Forms\Components\TextInput::make('qty')
                ->suffix('Box'),
                Forms\Components\Select::make('status')
                ->label('Status Pengiriman')
                ->options([
                    'dikemas' => 'Dikemas',
                    'dalam_perjalanan' => 'Dalam Perjalanan',
                    'terkirim' => 'Terkirim',
                ])
                ->default('dikemas')
                    ->disabled()
                ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delivery_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dikemas' => 'secondary',
                        'dalam_perjalanan' => 'warning',
                        'terkirim' => 'success',
                        default => 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('setShipped')
                    ->label('Dalam Perjalanan')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn (Delivery $record) => $record->status === 'dikemas')
                    ->action(function (Delivery $record) {
                        $record->status = 'dalam_perjalanan';
                        $record->save();
                        
                        Notification::make()
                            ->title('Status berhasil diperbarui ke Dalam Perjalanan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('setDelivered')
                    ->label('Terkirim')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Delivery $record) => $record->status === 'dalam_perjalanan')
                    ->action(function (Delivery $record) {
                        $record->status = 'terkirim';
                        $record->save();
                        
                        Notification::make()
                            ->title('Status berhasil diperbarui ke Terkirim')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('kirimWhatsApp')
                    ->label('Kirim WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (Delivery $record) {
                        // Format tanggal untuk pesan
                        $formattedDate = Carbon::parse($record->delivery_date)->format('d/m/Y');

                        // Format pesan WhatsApp
                        $message = "Informasi Pengiriman:\n"
                            . "Tanggal: {$formattedDate}\n"
                            . "No. Order: {$record->delivery_number}\n"
                            . "Jumlah: {$record->qty}\n"
                            . "Nama Sekolah: {$record->recipient->name}\n"
                            . "Status: {$record->status}";

                        // Encode pesan untuk URL WhatsApp
                        $encodedMessage = urlencode($message);

                        // Ambil nomor WhatsApp sekolah
                        $phoneNumber = $record->school->phone ?? '';

                        // Format nomor telepon ke format internasional
                        // Jika nomor dimulai dengan '0', ganti dengan kode negara Indonesia (62)
                        if (strlen($phoneNumber) > 0) {
                            if (substr($phoneNumber, 0, 1) === '0') {
                                $phoneNumber = '62' . substr($phoneNumber, 1);
                            }
                            // Jika nomor tidak dimulai dengan '+' atau '62', tambahkan '62'
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
            'setShipped',
            'setDelivered', 
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