<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DateTime;
use DB;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Meeting Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles validation and CRUD of meetings.
    |
    */

    /**
     * Get all the meetings for a user, filterable by
     * before, after and user id.
     * @param? dateTime before
     * @param? dateTime after
     * @param? unsigned int user_id
     *
     * @param  Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'user_id'=>'integer|exists:users,id',
            'before'=>'date_format:Y-m-d H:i',
            'after'=>'date_format:Y-m-d H:i',
        ]);
        $user_id = request()->has('user_id') ? request()->get('user_id'): Auth::user()->id;
        $before = request()->has('before') ? request()->get('before') : Date("Y-m-d H:i");
        $after = request()->has('after') ? request()->get('after') : '2010-01-01 00:00';

        $meetings = DB::select(DB::raw(
            "SELECT *
            FROM meetings
            WHERE user_id = :user_id AND start_time < :before AND start_time > :after"
        ), array('user_id'=>$user_id, 'after'=>$after,'before'=>$before));
        return response()->json($meetings);
    }

    /**
     * Update a meeting instance with new parameters.
     *
     *
     * @param  Illuminate\Http\Request $request
     * @param  unsigned int $meeting_id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $meeting_id)
    {
        $meeting = Meeting::find($meeting_id);
        if(is_null($meeting)) {
            return response()->json(["message"=>"Meeting does not exist."],404);
        } elseif ($meeting->user_id != Auth::user()->id) {
            return response()->json(["message"=>"Forbidden: You do not have permission to edit this meeting."],403);
        } else {
            $new_meeting_attributes = array(
              'name' => request()->has('name') ? $request->get('name'): $meeting->name,
              'description' => request()->has('description') ? $request->get('description'): $meeting->description,
              'room_id' => request()->has('room_id') ? $request->get('room_id'): $meeting->room_id,
              'start_time' => request()->has('start_time') ? $request->get('start_time'): $meeting->start_time,
              'end_time' => request()->has('end_time') ? $request->get('end_time'): $meeting->end_time,
            );
            $validator = $this->validator($new_meeting_attributes);
            if(count($validator->errors()) > 0){
                return response()->json(['message'=>'The given data was invalid.','errors'=>$validator->errors()]);
            }
            $meeting->name = $new_meeting_attributes['name'];
            $meeting->description = $new_meeting_attributes['description'];
            $meeting->room_id = $new_meeting_attributes['room_id'];
            $meeting->start_time = $new_meeting_attributes['start_time'];
            $meeting->end_time = $new_meeting_attributes['end_time'];
            $meeting->save();
            return response()->json($meeting);
        }
    }

    /**
     * Get the number of hours between the start time and
     * end_time of a meeting.
     *
     * @param  dateTime $start_time
     * @param  dateTime $end_time
     *
     * @return int
     */
    private function getMeetingDurationHours($start_time, $end_time)
    {
        $t1 = DateTime::createFromFormat('Y-m-d H:i', $start_time);
        $t2 = DateTime::createFromFormat('Y-m-d H:i', $end_time);
        $interval = $t2->diff($t1);
        $hours = ($interval->days * 24) + $interval->h + ($interval->i / 60) + ($interval->s / 3600);
        return $hours;
    }


    /**
     * Find all of the meetings which will with the new meeting.
     * Ignore is a meeting id to ignore, used to prevent a meeting
     * conflicting with itself on an update. Set to -1 on a store.
     *
     * @param  dateTime  $start_time
     * @param  dateTime $end_time
     * @param  unsigned int $room_id
     * @param  unsigned int $ignore
     *
     * @return array
     */
    private function getConflictingMeetings($start_time, $end_time, $room_id, $ignore_meeting_id)
    {
        $meetings = DB::select(DB::raw(
          "SELECT id, name, description, start_time, end_time
          FROM meetings
          WHERE room_id = :room_id AND NOT ((start_time > :end_time) OR (end_time < :start_time)) AND deleted_at IS NULL AND id <> :ignore"
        ), array(
            'room_id' =>$room_id,
            'start_time' =>$start_time,
            'end_time' => $end_time,
            'ignore'=>$ignore_meeting_id
        ));

        return $meetings;
    }

    /**
     * Validate a meeting for store and update.
     *
     * @param  array  $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
          'room_id' => 'required|exists:rooms,id|integer',
          'name' => 'required|max:255',
          'description'=>'nullable|string|max:255',
          'start_time' => 'required|date_format:Y-m-d H:i|after:now',
          'end_time' => 'required|after_or_equal:start_time|date_format:Y-m-d H:i'
        ]);

        $validator->validate();
        if($this->getMeetingDurationHours($data['start_time'], $data['end_time']) > Meeting::$MAX_MEETING_DURATION_HOURS) {
            $validator->errors()->add('message',"Meeting duration cannot exceed " . Meeting::$MAX_MEETING_DURATION_HOURS . " hours.");
        }

        // On updates, meetings should not conflict with themselves.
        $ignore_meeting_id = request()->isMethod('patch') ? request()->meeting_id : -1;

        $conflictingMeetings = $this->getConflictingMeetings(
            request()->input('start_time'),
            request()->input('end_time'),
            request()->input('room_id'),
            $ignore_meeting_id
        );

        if(count($conflictingMeetings) > 0) {
            $validator->errors()->add('conflicts', $conflictingMeetings);
        }

        return $validator;

    }

    /**
     * Show a meeting by its id
     *
     * @param unsigned int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($meeting_id)
    {
        $meeting = Meeting::find($id);
        if(is_null($meeting)){
            return response()->json(["message"=>"Meeting does not exist"],404);
        } else {
            return response()->json($meeting);
        }
    }


    /**
     * Soft delete a meeting by its id.
     *
     * @param unsigned int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($meeting_id){

        $meeting = Meeting::find($id);

        if(is_null($meeting)){
            return response()->json(["message"=>"Meeting does not exist"],404);
        } elseif ($meeting->user_id != Auth::user()->id) {
            return response()->json(["message"=>"Forbidden: You do not have permission to delete this meeting. "],403);
        } else {
            $meeting->delete();
            return 204;
        }
    }

    /**
     * Validate and save a meeting.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all());

        if(count($validator->errors()) > 0){
            return response()->json(['errors'=>$validator->errors()]);
        }

        $meeting = Meeting::create([
          'name' => $request->input('name'),
          'description' => $request->input('description'),
          'user_id' => Auth::user()->id,
          'room_id' => $request->input('room_id'),
          'start_time' => $request->input('start_time'),
          'end_time' => $request->input('end_time'),
        ]);
        return response()->json($meeting);
    }
}
