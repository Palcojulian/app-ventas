<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->double('precio_unitario');
            $table->double('total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
