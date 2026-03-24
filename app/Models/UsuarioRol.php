<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuario_rol';

    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_rol',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
