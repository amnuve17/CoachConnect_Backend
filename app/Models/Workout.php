<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'client_id',
        'date',
        'notes',
        'title', 
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

}
