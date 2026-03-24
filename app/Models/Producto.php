<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'id_categoria',
        'precio_venta',
        'costo',
        'stock_actual',
        'stock_minimo',
        'unidad_medida',
        'estado',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto');
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }
}
