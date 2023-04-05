<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_id',
        'title',
        'url',
        'image',
        'content',
        'date',
        'views',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }
}
