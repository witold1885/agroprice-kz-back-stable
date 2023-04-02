<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'url',
        'image',
        'content',
        'author',
        'views',
        'meta_description',
        'meta_keywords',
    ];
}
