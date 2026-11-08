<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pension_guard_legal_acceptances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pension_guard_user_id')
                ->constrained('pension_guard_users')
                ->cascadeOnDelete();

            $table->string('terms_version', 30);
            $table->timestamp('terms_accepted_at');

            $table->string('privacy_version', 30);
            $table->timestamp('privacy_acknowledged_at');

            $table->timestamps();

            $table->index(
                ['pension_guard_user_id', 'terms_version'],
                'pg_legal_user_terms_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pension_guard_legal_acceptances');
    }
};