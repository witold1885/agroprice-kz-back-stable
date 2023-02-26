<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'url',
        'price',
        'price_negotiable',
        'location_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
        if ($this->contact) {
            if ($this->contact->person != '') {
                return $this->contact->person;
            }
        }
        elseif ($this->user) {
            if ($this->user->profile->fullname != '') {
                return $this->user->profile->fullname;
            }
        }
        else {
            return null;
        }
    }

    public function getEmailAttribute()
    {
        if ($this->contact) {
            if ($this->contact->email != '') {
                return $this->contact->email;
            }
        }
        elseif ($this->user) {
            if ($this->user->email != '') {
                return $this->user->email;
            }
        }
        else {
            return null;
        }
    }

    public function getPhoneAttribute()
    {
        if ($this->contact) {
            if ($this->contact->phone != '') {
                return $this->contact->phone;
            }
        }
        elseif ($this->user) {
            if ($this->user->profile->phone != '') {
                return $this->user->profile->phone;
            }
        }
        else {
            return null;
        }
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
