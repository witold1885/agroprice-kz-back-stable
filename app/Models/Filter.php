<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = [
        'filter_group_id',
        'value',
    ];

    public function filterGroup()
    {
        return $this->belongsTo(filterGroup::class);
    }
}
