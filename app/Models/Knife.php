<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knife extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_hash_name',
        'type',
        'steam_name',
        'wear_level',
        'price',
        'image_url',
        'description',
        'steam_data_updated_at',
    ];
}
