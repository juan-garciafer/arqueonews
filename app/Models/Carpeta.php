<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carpeta extends Model
{
    protected $table = 'carpetas';

    protected $fillable = [
        'nombre',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function noticias()
    {
        return $this->belongsToMany(Noticia::class, 'carpeta_noticias');
    }
}
