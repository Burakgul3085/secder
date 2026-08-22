<?php

namespace App\Filament\Crm\Resources\Donations\Schemas;

use App\Filament\Crm\Resources\CrmProjects\CrmProjectResource;
use App\Filament\Crm\Resources\DonationTypes\DonationTypeResource;
use App\Models\DonationType;
use App\Models\Donor;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Support\Crm\LookupDeletionGuard;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DonationForm
{
    public static function configure(Schema $schema): Schema
    {
        $guard = app(LookupDeletionGuard::class);

        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Select::make('donor_id')
                    ->label('Bağışçı')
                    ->relationship('donor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Donor $record): string => $record->full_name . ($record->phone ? ' · ' . $record->phone : ''))
                    ->searchable(['first_name', 'last_name', 'phone', 'email'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('first_name')->label('Ad')->required(),
                        TextInput::make('last_name')->label('Soyad')->required(),
                        TextInput::make('phone')->label('Telefon'),
                        TextInput::make('email')->label('E-posta')->email(),
                    ]),
                Select::make('donation_type_id')
                    ->label('Bağış Türü')
                    ->options(fn (): array => $guard->selectableOptions(DonationType::class))
                    ->searchable()
                    ->preload()
                    ->suffixAction(
                        Action::make('manageDonationTypes')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->tooltip('Bağış türlerini yönet')
                            ->url(DonationTypeResource::getUrl('index'))
                            ->openUrlInNewTab(),
                    )
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Bağış Türü Adı')
                            ->required()
                            ->maxLength(120),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $code = Str::slug($data['name'], '_');
                        $baseCode = $code !== '' ? $code : 'type';
                        $suffix = 1;

                        while (DonationType::query()->where('code', $code)->exists()) {
                            $code = $baseCode . '_' . $suffix;
                            $suffix++;
                        }

                        return DonationType::query()->create([
                            'name' => $data['name'],
                            'code' => $code,
                            'is_active' => true,
                            'sort_order' => ((int) DonationType::query()->max('sort_order')) + 1,
                        ])->id;
                    })
                    ->helperText('Listede yoksa + ile ekleyin veya dişli ikondan türleri yönetin.'),
                Select::make('payment_method_id')
                    ->label('Ödeme Türü')
                    ->options(fn (): array => $guard->selectableOptions(PaymentMethod::class))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Ödeme Türü Adı')
                            ->required()
                            ->maxLength(120),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $code = Str::slug($data['name'], '_');
                        $baseCode = $code !== '' ? $code : 'payment';
                        $suffix = 1;

                        while (PaymentMethod::query()->where('code', $code)->exists()) {
                            $code = $baseCode . '_' . $suffix;
                            $suffix++;
                        }

                        return PaymentMethod::query()->create([
                            'name' => $data['name'],
                            'code' => $code,
                            'is_active' => true,
                            'sort_order' => ((int) PaymentMethod::query()->max('sort_order')) + 1,
                        ])->id;
                    })
                    ->helperText('Listede yoksa + ile ekleyin.'),
                Select::make('project_id')
                    ->label('Proje / Faaliyet')
                    ->options(fn (): array => $guard->selectableOptions(Project::class, 'title'))
                    ->searchable()
                    ->preload()
                    ->suffixAction(
                        Action::make('manageProjects')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->tooltip('Proje / faaliyetleri yönet')
                            ->url(CrmProjectResource::getUrl('index'))
                            ->openUrlInNewTab(),
                    )
                    ->createOptionForm([
                        TextInput::make('title')
                            ->label('Proje / Faaliyet Adı')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $slug = Str::slug($data['title']);
                        $baseSlug = $slug !== '' ? $slug : 'proje';
                        $suffix = 1;

                        while (Project::query()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $suffix;
                            $suffix++;
                        }

                        return Project::query()->create([
                            'title' => $data['title'],
                            'slug' => $slug,
                            'status' => 'devam-ediyor',
                            'is_active' => true,
                            'sort_order' => ((int) Project::query()->max('sort_order')) + 1,
                        ])->id;
                    })
                    ->helperText('Listede yoksa + ile ekleyin veya dişli ikondan faaliyetleri yönetin.'),
                TextInput::make('amount')
                    ->label('Tutar')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step(0.01),
                Select::make('currency')
                    ->label('Para Birimi')
                    ->options([
                        'TRY' => 'TRY',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default('TRY')
                    ->required(),
                DateTimePicker::make('donated_at')
                    ->label('Bağış Tarihi')
                    ->default(now())
                    ->required(),
                TextInput::make('donation_number')
                    ->label('Bağış No')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                TextInput::make('receipt_number')
                    ->label('Makbuz No')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
            ]),
            Textarea::make('description')
                ->label('Açıklama')
                ->rows(3)
                ->helperText('Makbuzda görünecek açıklama (isteğe bağlı).')
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label('Not')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }
}
