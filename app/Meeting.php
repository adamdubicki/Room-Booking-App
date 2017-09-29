<?php

namespace App;

use DateTime;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Meeting extends Model
{
    use SoftDeletes;

    public static $MAX_MEETING_DURATION_MINUTES = 180;
    public static $MIN_MEETING_DURATION_MINUTES = 15;

    protected $dates = ['deleted_at'];

    private $errors;

    protected $fillable = [
        'name', 'description', 'user_id', 'room_id', 'start_time', 'end_time'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    protected $rules = array(
        'room_id' => 'required|exists:rooms,id|integer',
        'name' => 'required|max:255',
        'description'=>'nullable|string|max:255',
        'start_time' => 'required|date_format:Y-m-d H:i|after:now',
        'end_time' => 'required|after_or_equal:start_time|date_format:Y-m-d H:i',
    );

    /**
     * Return the validation errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Return a map array for self validation on update..
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'room_id' => $this->room_id,
            'name' => $this->name,
            'description' => $this->description,
            'start_time' => date('Y-m-d H:i', strtotime($this->start_time)),
            'end_time' => date('Y-m-d H:i', strtotime($this->end_time)),
            'id' => $this->id,
        );
    }

    /**
     * Test if a meeting is a valid instance or not.
     *
     * @return boolean
     */
    public function validate($data)
    {
        $validator = Validator::make($data, $this->rules);

        $tester = new Meeting();
        $tester->start_time = $data['start_time'];
        $tester->end_time = $data['end_time'];
        $tester->room_id = $data['room_id'];
        $tester->id = $this->id;

        $duration = $tester->getMeetingDurationMins();
        if($duration > Meeting::$MAX_MEETING_DURATION_MINUTES)
        {
            $validator->errors()->add('message',"Meeting duration cannot exceed " . Meeting::$MAX_MEETING_DURATION_MINUTES . " minutes.");
        }
        elseif($duration < Meeting::$MIN_MEETING_DURATION_MINUTES)
        {
            $validator->errors()->add('message',"Meeting duration must be atleast " . Meeting::$MIN_MEETING_DURATION_MINUTES . " minutes.");
        }

        $conflictingMeetings = $tester->getConflictingMeetings();
        if(count($conflictingMeetings) > 0)
        {
            $validator->errors()->add('conflicts', $conflictingMeetings);
        }

        if(count($validator->errors()) > 0)
        {
              $this->errors = ["errors"=>$validator->errors()];
              return false;
        }
        return true;
    }


    /**
     * Get the duration of a meeting in meetings.
     * @return float
     */
    public function getMeetingDurationMins()
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        return round( ($end - $start) / 60, 2);
    }

    public function getConflictingMeetings()
    {
        $ignore = is_null($this->id) ? -1 : $this->id;
        $meetings = DB::select(DB::raw(
          "SELECT id, name, description, start_time, end_time, room_id
          FROM meetings
          WHERE room_id = :room_id AND NOT ((start_time > :end_time) OR (end_time < :start_time)) AND deleted_at IS NULL AND id <> :ignore"
        ), array(
            'room_id' =>$this->room_id,
            'start_time' =>$this->start_time,
            'end_time' => $this->end_time,
            'ignore'=>$ignore,
        ));

        return $meetings;
    }

}
