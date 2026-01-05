<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'photo', 'tagline'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getPhotoAttribute($value)
    {
        if (!$value) {
            return null; // No image available
        }

        return url(Storage::url($value)); // domain.com/storage/categories/nama-photo.jpg
    }
}
