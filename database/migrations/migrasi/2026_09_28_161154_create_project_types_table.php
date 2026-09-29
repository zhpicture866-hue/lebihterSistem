<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lebihtersistem.project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // "Wedding Syariah", "Event", "Company Profile", dst
            $table->string('code')->unique();        // "wedding", "event", "company-profile" — dipakai di kode kalau perlu cek tipe spesifik
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lebihtersistem.project_types');
    }
};
