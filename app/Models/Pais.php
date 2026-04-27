<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'paises';

    protected $fillable = [
        'nombre',
        'codigo_iso',
        'lat',
        'lng',
    ];

    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }
}
