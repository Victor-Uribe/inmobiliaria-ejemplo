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
        Schema::create('property_buyers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            $table->string('buyer_number')->nullable();
            $table->string('buyer_name');
            $table->string('buyer_phone', 30)->nullable();
            $table->string('buyer_email')->nullable();

            // Precio pactado de venta.
            $table->decimal('sale_price', 14, 2)->default(0);

            // Descuento autorizado.
            $table->decimal('discount_amount', 14, 2)->default(0);

            // Enganche pactado.
            $table->decimal('down_payment_amount', 14, 2)->default(0);

            // Pago mensual fijo.
            $table->decimal('monthly_payment_amount', 14, 2)->default(0);

            // Día límite de pago, ejemplo: 5 de cada mes.
            $table->unsignedTinyInteger('payment_due_day')->default(5);

            // Fecha de venta.
            $table->date('sale_date')->nullable();

            // Mes desde el que empieza a pagar.
            // Guardar como primer día del mes, ejemplo: 2026-07-01.
            $table->date('first_payment_period')->nullable();

            // active, paid, cancelled
            $table->string('status', 20)->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('property_id');
            $table->index('buyer_number');
            $table->index('buyer_phone');
            $table->index('status');
            $table->index('first_payment_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_buyers');
    }
};
