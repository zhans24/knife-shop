<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knife extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'rarity', 'float_value', 'wear_level', 'price', 'description', 'color', 'image_url'
    ];
}
