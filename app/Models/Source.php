<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    //
    protected $fillable = 
    [
'source_type',
'source',
'poem_id',
'url'
    ];

    public function poems()
    {
        return $this->belongsTo(Poem::class);
    }

    public function lessons()
    {
        return $this->belongsTo(lesson::class);
    
    }

    public function activitiess()
    {
        return $this->belongsTo(activities::class);
    }
    // public function getSourceUrlAttribute()
    // {
    //     return Storage::url($this->source);
    // }
}
