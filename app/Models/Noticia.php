<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $table = 'noticias';

    protected $fillable = [
        'external_id',
        'hash',
        'titulo',
        'descripcion',
        'url_noticia',
        'url_imagen',
        'pais',
        'fecha_publicacion',
        'categoria',
        'source',
        'codigo_pais',
        'prioridad',
    ];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function carpetas()
    {
        return $this->belongsToMany(Carpeta::class, 'carpeta_noticia');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'codigo_pais', 'codigo_iso');
    }
}
