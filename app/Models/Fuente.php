<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
    protected $table = 'fuentes';

    protected $fillable = [
        'nombre',
        'rss_url',
    ];

    public function noticias()
    {
        return $this->hasMany(Noticia::class);
    }
}
