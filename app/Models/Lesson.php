<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    //
    protected $fillable =
    [
'title',
'saying_date',
'description',
'user_id'
    ];

    public function sources ()
    {
        return $this->hasMany(Source::class);
    }


    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy()
{
    // المستخدمون الذين فضلوا هذه القصيدة: العديد لـ العديد عبر جدول 'favorites'
    return $this->belongsToMany(User::class, 'favorites');
}

public function isFavoritedBy(User $user): bool
{
    return $this->favoritedBy()->where('user_id', $user->id)->exists();
}
}
