<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'description',
        'size',
        'photo',
        'updated_at',
    ];

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
