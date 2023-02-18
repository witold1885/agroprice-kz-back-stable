<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function filters()
    {
        return $this->hasMany(Filter::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_filter_groups');
    }
}
