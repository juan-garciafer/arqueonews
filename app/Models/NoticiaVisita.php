<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticiaVisita extends Model
{
    protected $fillable = [
        'user_id',
        'noticia_id',
        'token_invitado',
        'direccion_ip'
    ];
    public $timestamps = true;
    const CREATED_AT = 'visitado_en';
    const UPDATED_AT = null;

    public function noticia()
    {
        return $this->belongsTo(Noticia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
