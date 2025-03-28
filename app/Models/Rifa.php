<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rifa extends Model
{
    use HasFactory;
    protected $fillable = ['lote', 'fecha', 'generado_por', 'cantidad_boletos', 'estado'];

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }
}