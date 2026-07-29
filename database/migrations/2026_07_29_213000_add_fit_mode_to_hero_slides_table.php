<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            // smart | contain | cover — görselin banda nasıl yerleşeceğini belirler.
            $table->string('fit_mode', 16)->default('smart')->after('fill_mode');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn('fit_mode');
        });
    }
};
