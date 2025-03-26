<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    use HasFactory;
    protected $fillable = ['rifa_id', 'codigo', 'numeros'];

    public function rifa()
    {
        return $this->belongsTo(Rifa::class);
    }
}
