<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Room;
use Illuminate\Support\Facades\Auth;
use DB;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    public function show($room_id)
    {
        $room = Room::find($room_id);
        if(is_null($room)){
            return response()->json(["message"=>"Room does not exist."], 404);
        } else {
            return response()->json($room);
        }
    }

    public function getMeetings(Request $request, $room_id)
    {
        $this->validate($request, [
            'before'=>'date_format:Y-m-d H:i',
            'after'=>'date_format:Y-m-d H:i',
        ]);
        $before = request()->has('before') ? request()->get('before') : Date("Y-m-d H:i'");
        $after = request()->has('after') ? request()->get('after') : '2010-01-01 00:00';

        $meetings = DB::select(DB::raw(
            "SELECT *
            FROM meetings
            WHERE room_id = :room_id AND start_time < :before AND start_time > :after"
        ), array('room_id'=>$room_id, 'after'=>$after,'before'=>$before));
        return response()->json($meetings);
    }
}
