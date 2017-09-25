<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    //

    protected $fillable = [
        'name', 'description', 'user_id', 'room_id', 'start_time', 'end_time'
    ];

    public function creator(){
      return $this->belongsTo('App\User');
    }

}
