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
        'image',
        'url',
        'description',
        'meta_heading',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function children() {
        return $this->hasMany(Category::class,'parent_id');
    }

    public function parent() {
        return $this->belongsTo(Category::class,'parent_id');
    }
}
