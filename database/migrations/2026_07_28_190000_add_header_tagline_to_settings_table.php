<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header'daki logo altında görünen kısa kurumsal slogan.
     * SEO meta açıklamasından (site_description) bağımsız tutulur.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('header_tagline', 80)->nullable()->after('site_description');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('header_tagline');
        });
    }
};
