<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
     protected $fillable = [
        'poem_id',
        'user_id',
        'content',
    ];

    // علاقة: التعليق ينتمي إلى قصيدة واحدة
    public function poem()
    {
        return $this->belongsTo(Poem::class);
    }

    // علاقة: التعليق ينتمي إلى مستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

