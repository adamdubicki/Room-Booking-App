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
        $meetings = Meeting::where('user_id', $user_id);

        if($request->has('before'))
        {
            $meetings->where('end_time', '<', $request->get('before'));
        }

        if($request->has('after'))
        {
            $meetings->where('start_time', '>', $request->get('after'));
        }

        return response()->json(array("meetings"=>$meetings->get()));
    }

    /**
     * Update a meeting instance with new parameters.
     *
     * @param  Illuminate\Http\Request $request
     * @param  unsigned int $meeting_id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $meeting_id)
    {
        $meeting = Meeting::find($meeting_id);
        if(is_null($meeting))
        {
            return response()->json(["message"=>"Meeting does not exist."],404);
        }
        elseif ($meeting->user_id != Auth::user()->id)
        {
            return response()->json(["message"=>"Forbidden: You do not have permission to edit this meeting."],403);
        }
        else
        {
            $meeting->name = $request->has('name') ? $request->get('name'): $meeting->name;
            $meeting->description = $request->has('description') ? $request->get('description'): $meeting->description;
            $meeting->room_id = $request->has('room_id') ? $request->get('room_id'): $meeting->room_id;
            $meeting->start_time = $request->has('start_time') ? $request->get('start_time'): $meeting->start_time;
            $meeting->end_time = $request->has('end_time') ? $request->get('end_time'): $meeting->end_time;
            if($meeting->validate($meeting->toArray()))
            {
                return response()->json(array("meeting"=>$meeting), 200);
            }
            else
            {
                return response()->json($meeting->errors());
            }
        }
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
        $meeting = Meeting::find($meeting_id);
        if(is_null($meeting))
        {
            return response()->json(["message"=>"Meeting does not exist"],404);
        }
        else
        {
            return response()->json(array("meeting"=>$meeting));
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

        $meeting = Meeting::find($meeting_id);

        if(is_null($meeting))
        {
            return response()->json(["message"=>"Meeting does not exist."],404);
        }
        elseif ($meeting->user_id != Auth::user()->id)
        {
            return response()->json(["message"=>"Forbidden: You do not have permission to delete this meeting."],403);
        }
        else
        {
            $meeting->delete();
            return response()->json([],204);
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
        $new = $request->all();
        $meeting = new Meeting();
        if($meeting->validate($new))
        {
            $meeting = Meeting::create([
              'name' => $request->input('name'),
              'description' => $request->input('description'),
              'user_id' => Auth::user()->id,
              'room_id' => $request->input('room_id'),
              'start_time' => $request->input('start_time'),
              'end_time' => $request->input('end_time'),
            ]);
            return response()->json(array("meeting"=>$meeting), 201);
        }
        else
        {
            return response()->json($meeting->errors(), 400);
        }
    }
}
