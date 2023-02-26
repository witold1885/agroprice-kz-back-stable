<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'order',
        'image',
        'url',
        'description',
        'meta_heading',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /*public function products() {
        return $this->belongsToMany(Product::class, 'product_categories');
    }*/

    public function filterGroups()
    {
        return $this->belongsToMany(FilterGroup::class, 'category_filter_groups');
    }

}
