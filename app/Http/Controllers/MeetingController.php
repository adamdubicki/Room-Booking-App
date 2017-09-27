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
    public function index(Request $request)
    {
        $this->validate($request, [
            'user_id'=>'integer|exists:users,id',
            'before'=>'date_format:Y-m-d H:i',
            'after'=>'date_format:Y-m-d H:i',
        ]);
        $user_id = request()->has('user_id') ? request()->get('user_id'): Auth::user()->id;
        $before = request()->has('before') ? request()->get('before') : Date("Y-m-d H:i'");
        $after = request()->has('after') ? request()->get('after') : '2010-01-01 00:00';

        $meetings = DB::select(DB::raw(
            "SELECT *
            FROM meetings
            WHERE user_id = :user_id AND start_time < :before AND start_time > :after"
        ), array('user_id'=>$user_id, 'after'=>$after,'before'=>$before));
        return response()->json($meetings);
    }

    private function getMeetingDurationHours($start_time, $end_time)
    {
        $t1 = DateTime::createFromFormat('Y-m-d H:i', $start_time);
        $t2 = DateTime::createFromFormat('Y-m-d H:i', $end_time);
        $interval = $t2->diff($t1);
        $hours = ($interval->days * 24) + $interval->h + ($interval->i / 60) + ($interval->s / 3600);
        return $hours;
    }

    private function getConflictingMeetings($start_time, $end_time, $room_id)
    {
        $meetings = DB::select(DB::raw(
          "SELECT id, name, description, start_time, end_time
          FROM meetings
          WHERE room_id = :room_id AND NOT ((start_time > :end_time) OR (end_time < :start_time)) AND deleted_at IS NULL"
        ), array('room_id' =>$room_id, 'start_time' =>$start_time, 'end_time' => $end_time));

        return $meetings;
    }

    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
          'room_id' => 'required|exists:rooms,id|integer',
          'name' => 'required|max:255',
          'description'=>'nullable|string|max:255',
          'start_time' => 'required|date_format:Y-m-d H:i',
          'end_time' => 'required|after_or_equal:start_time|date_format:Y-m-d H:i'
        ]);

        $validator->validate();
        if($this->getMeetingDurationHours(request()->input('start_time'), request()->input('end_time')) > 3)
        {
            $validator->errors()->add('message','Meeting duration cannot exceed 3 hours');
        }
        $conflictingMeetings = $this->getConflictingMeetings(request()->input('start_time'), request()->input('end_time'), request()->input('room_id'));
        if(count($conflictingMeetings) > 0){
            $validator->errors()->add('conflicts', $conflictingMeetings);
        }

        return $validator;

    }

    public function show($meeting_id)
    {
        $meeting = Meeting::find($id);
        if(is_null($meeting)){
            return response()->json(["message"=>"Meeting does not exist"],404);
        } else {
            return response()->json($meeting);
        }
    }

    public function delete($meeting_id){
        $meeting = Meeting::find($meeting_id);
        if(is_null($meeting)){
            return response()->json(["message"=>"Meeting does not exist"],404);
        } elseif ($meeting->user_id != Auth::user()->id) {
            return response()->json(["message"=>"Forbidden: You do not have permission to delete this meeting. "],403);
        } else {
            $meeting->delete();
            return 204;
        }
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request->all());

        if(count($validator->errors()) > 0){
            return response()->json(['errors'=>$validator->errors()]);
        }

        $meetings = Meeting::create([
          'name' => $request->input('name'),
          'description' => $request->input('description'),
          'user_id' => Auth::user()->id,
          'room_id' => $request->input('room_id'),
          'start_time' => $request->input('start_time'),
          'end_time' => $request->input('end_time'),
        ]);
        return response()->json($meetings);
    }
}
