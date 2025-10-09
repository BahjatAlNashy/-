<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class activities extends Model
{
    //
 protected $fillable = [
  'title',
  'date',
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
}

