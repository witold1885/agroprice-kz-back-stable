<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_negotiable',
        'location_id',
        'status',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function contact()
    {
        return $this->hasOne(ProductContact::class);
    }

    public function getPersonAttribute()
    {
        return $this->contact->person ?? null;
    }

    public function getEmailAttribute()
    {
        return $this->contact->email ?? null;
    }

    public function getPhoneAttribute()
    {
        return $this->contact->phone ?? null;
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
