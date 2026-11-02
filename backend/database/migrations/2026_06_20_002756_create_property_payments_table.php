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
        Schema::create('property_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_buyer_id')
                ->constrained('property_buyers')
                ->cascadeOnDelete();

            // Fecha real en la que el cliente pagó.
            $table->date('paid_at');

            // Mes al que se aplica el pago.
            // Guardar como primer día del mes.
            // Ejemplo: pago aplicado a julio 2026 = 2026-07-01.
            $table->date('applied_period')->nullable();

            // Fecha límite de pago de ese mes.
            // Ejemplo: si el día de pago es 5, julio = 2026-07-05.
            $table->date('applied_due_date')->nullable();

            // Monto real pagado.
            $table->decimal('amount', 14, 2);

            // monthly, down_payment, adjustment
            $table->string('payment_type', 30)->default('monthly');

            // applied, cancelled
            $table->string('status', 20)->default('applied');

            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->string('folio')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['property_buyer_id', 'paid_at']);
            $table->index(['property_buyer_id', 'applied_period']);
            $table->index(['property_buyer_id', 'status']);
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_payments');
    }
};
