<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

}
