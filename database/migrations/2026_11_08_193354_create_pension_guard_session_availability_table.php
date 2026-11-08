<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pension_guard_session_availability', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('pension_guard_users')
                ->nullOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(
                ['is_active', 'starts_at', 'ends_at'],
                'pg_session_availability_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pension_guard_session_availability');
    }
};