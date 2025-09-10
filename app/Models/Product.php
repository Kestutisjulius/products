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

    public function getPhotoAttribute($value)
    {
       
        if (empty($value)) {
            return url('/images/no-image.png'); 
        }

        if (preg_match('#^https?://#i', $value)) {
            return preg_replace('#^http://#i', 'https://', $value);
        }

        return url($value);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
