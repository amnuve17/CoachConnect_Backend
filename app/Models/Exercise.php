<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

}
