<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticiaLike extends Model
{
    protected $fillable = ['user_id', 'noticia_id'];
    
    public function noticia()
    {
        return $this->belongsTo(Noticia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
