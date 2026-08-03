<?php

namespace Database\Seeders;

use App\Models\CrmUser;
use App\Models\DocumentTemplate;
use App\Models\DonationType;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        CrmUser::query()->updateOrCreate(
            ['email' => 'crm@secder.org'],
            [
                'name' => 'SECDER CRM Yöneticisi',
                'password' => 'CrmSecder2026!',
                'role' => 'super_admin',
                'is_active' => true,
            ],
        );

        $donationTypes = [
            ['name' => 'Genel Bağış', 'code' => 'general', 'sort_order' => 1],
            ['name' => 'Zekat', 'code' => 'zakat', 'sort_order' => 2],
            ['name' => 'Fitre', 'code' => 'fitre', 'sort_order' => 3],
            ['name' => 'Kurban', 'code' => 'kurban', 'sort_order' => 4],
            ['name' => 'Proje Bağışı', 'code' => 'project', 'sort_order' => 5],
            ['name' => 'Kurumsal Bağış', 'code' => 'corporate', 'sort_order' => 6],
        ];

        foreach ($donationTypes as $type) {
            DonationType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'is_active' => true,
                    'sort_order' => $type['sort_order'],
                ],
            );
        }

        $paymentMethods = [
            ['name' => 'Nakit', 'code' => 'cash', 'sort_order' => 1],
            ['name' => 'Banka Havalesi / EFT', 'code' => 'bank_transfer', 'sort_order' => 2],
            ['name' => 'Kredi Kartı', 'code' => 'credit_card', 'sort_order' => 3],
            ['name' => 'POS', 'code' => 'pos', 'sort_order' => 4],
            ['name' => 'Online Ödeme', 'code' => 'online', 'sort_order' => 5],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'is_active' => true,
                    'sort_order' => $method['sort_order'],
                ],
            );
        }

        DocumentTemplate::query()->updateOrCreate(
            ['type' => DocumentTemplate::TYPE_RECEIPT],
            [
                'name' => 'Standart Makbuz',
                'blade_view' => 'crm.documents.receipt',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        DocumentTemplate::query()
            ->where('type', '!=', DocumentTemplate::TYPE_RECEIPT)
            ->update(['is_active' => false, 'is_default' => false]);
    }
}
