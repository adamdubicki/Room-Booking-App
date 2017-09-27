<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'description', 'user_id', 'room_id', 'start_time', 'end_time'
    ];

    public function creator(){
      return $this->belongsTo('App\User');
    }

}
