<?php

namespace App\Console\Commands;

use App\Support\Crm\PrivateDocumentStorage;
use Illuminate\Console\Command;

class SecureReceiptDocuments extends Command
{
    protected $signature = 'crm:secure-receipts';

    protected $description = 'Makbuz PDF dosyalarını public diskten private diske taşır.';

    public function handle(): int
    {
        $moved = PrivateDocumentStorage::migrateLegacy();

        $this->components->info("Taşınan makbuz dosyası: {$moved}");

        return self::SUCCESS;
    }
}
