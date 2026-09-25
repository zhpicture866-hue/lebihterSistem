<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'CREATE SCHEMA IF NOT EXISTS lebihtersistem'
        );

        if (!Schema::hasTable('lebihtersistem.notifications')) {
            Schema::create('lebihtersistem.notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->string('type');

                $table->uuidMorphs('notifiable');

                $table->text('data');

                $table->timestamp('read_at')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lebihtersistem.notifications'
        );
    }
};