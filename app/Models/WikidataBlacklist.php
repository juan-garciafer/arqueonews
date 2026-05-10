<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WikidataBlacklist extends Model
{
    protected $table = 'wikidata_blacklist';
    
    protected $fillable = [
        'wikidata_id',
        'nombre',
        'razon',
    ];
}
