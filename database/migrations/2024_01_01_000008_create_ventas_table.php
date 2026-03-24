<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura')->unique();
            $table->foreignId('id_cliente')->constrained('users')->onDelete('cascade');
            $table->double('subtotal')->default(0);
            $table->double('impuestos')->default(0);
            $table->double('descuento')->default(0);
            $table->double('total')->default(0);
            $table->string('metodo_pago')->nullable();
            $table->string('estado')->default('pendiente');
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
