<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'nombre',
        'pais_id',
        'tipo',
        'wikidata_id',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function aliases()
    {
        return $this->hasMany(Alias::class);
    }
}
