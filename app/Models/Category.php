<?php

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
    public function scopeRandom($query)
    {
        return $query->orderBy(DB::raw('RAND()'));
    }
}
