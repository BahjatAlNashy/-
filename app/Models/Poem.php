<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;

class Poem extends Model
{
    //
    protected $fillable =
    [
'title',
'saying_date',
'description',
'user_id',
'is_private', // ✨ أُضيف هنا ✨

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

 public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

public function isFavoritedBy($user)
{
    if (!$user) {
        return false;
    }
    // هذا الاستعلام يترجم إلى:
    // SELECT * FROM favorites WHERE poem_id = [ID القصيدة] AND user_id = [ID المستخدم] LIMIT 1
    return $this->favorites()->where('user_id', $user->id)->exists(); 
}

public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
