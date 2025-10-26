<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
     protected $fillable = [
        'poem_id',
        'lesson_id',
        'saying_id',
        'user_id',
        'content',
    ];

    // علاقة: التعليق ينتمي إلى قصيدة واحدة
    public function poem()
    {
        return $this->belongsTo(Poem::class);
    }

    // علاقة: التعليق ينتمي إلى درس واحد
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // علاقة: التعليق ينتمي إلى قول
    public function saying()
    {
        return $this->belongsTo(Saying::class);
    }

    // علاقة: التعليق ينتمي إلى مستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

