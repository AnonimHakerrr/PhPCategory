<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'photo',
        'description',

    ];
    /**
     * Get the products that owns the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
