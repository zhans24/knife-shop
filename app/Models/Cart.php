<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'knife_id', 'quantity'];

    public function knife()
    {
        return $this->belongsTo(Knife::class);
    }
}
