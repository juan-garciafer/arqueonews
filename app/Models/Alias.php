<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    protected $fillable = [
        'nombre',
        'keyword_id',
    ];

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }
}
