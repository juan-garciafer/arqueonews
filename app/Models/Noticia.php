<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $table = 'noticias';

    protected $fillable = [
        'fuente_id',
        'titulo',
        'descripcion',
        'url_imagen',
        'url_noticia',
        'pais',
        'fecha_publicacion',
    ];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function carpetas()
    {
        return $this->belongsToMany(Carpeta::class, 'carpeta_noticias');
    }
}
