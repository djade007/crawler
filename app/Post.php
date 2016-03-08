<?php

namespace Olajide;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //

    protected $guarded = [];

    protected $casts = [
        'author' => 'array',
        'data' => 'array'
    ];

    protected $dates = ['created_at', 'updated_at', 'date'];

    public function scopeNairaland($query) {
        return $query->where('type', 'nairaland');
    }

    public function scopeStackoverflow($query) {
        return $query->where('type', 'stackoverflow');
    }

    public function getTagsAttribute() {
        $type = $this->getAttribute('type');
        if($type == 'nairaland') return 'programming';
        return implode(', ', $this->getAttribute('data')['tags']);
    }

    public function getDatetoAttribute() {
        return $this->getAttribute('date')->diffForHumans();
    }
}
