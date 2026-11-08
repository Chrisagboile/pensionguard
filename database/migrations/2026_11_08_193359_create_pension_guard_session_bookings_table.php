<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pension_guard_session_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('pension_guard_users')
                ->cascadeOnDelete();

            $table->foreignId('availability_id')
                ->constrained('pension_guard_session_availability')
                ->restrictOnDelete();

            // Snapshot of the customer's selected appointment.
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            // Must be a multiple of 30 minutes.
            $table->unsignedSmallInteger('duration_minutes');

            // telephone or video.
            $table->string('channel', 20);

            // Calculated by the server: £25 per 30 minutes.
            $table->unsignedInteger('amount_pence');
            $table->string('currency', 3)->default('gbp');

            // pending, confirmed, cancelled, expired.
            $table->string('status', 20)->default('pending');

            // unpaid, paid, failed, expired.
            $table->string('payment_status', 20)->default('unpaid');

            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();

            $table->dateTime('checkout_expires_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'created_at'],
                'pg_session_booking_user_idx'
            );

            $table->index(
                ['starts_at', 'ends_at', 'status'],
                'pg_session_booking_overlap_idx'
            );

            $table->index(
                ['payment_status', 'checkout_expires_at'],
                'pg_session_booking_expiry_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pension_guard_session_bookings');
    }
};