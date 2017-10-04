<?php

namespace App\Http\Controllers;

use App\Meeting;
use Illuminate\Http\Request;
use App\Room;
use Illuminate\Support\Facades\Auth;
use DB;

class RoomController extends Controller
{
  /*
  |--------------------------------------------------------------------------
  | Room Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles displaying rooms, and meetings by room.
  |
  */

  /**
   * Show all of the rooms.
   *
   * @return \Illuminate\Http\Response
   */
    public function index()
    {
        $rooms = Room::all();
        return response()->json(array("rooms"=>$rooms));
    }

    /**
     * Show a room by $room_id
     *
     * @param  unsigned int $room_id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($room_id)
    {
        $room = Room::find($room_id);
        if(is_null($room)) {
            return response()->json(["message"=>"Room does not exist."], 404);
        } else {
            return response()->json(array("room"=>$room));
        }
    }

    /**
     * Get all the meetings for a room.
     * @param? dateTime before
     * @param? dateTime after
     *
     * @param  Illuminate\Http\Request $request
     * @param  unsigned int $room_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getMeetings($room_id)
    {
        $this->validate(request(), [
            'before'=>'date_format:Y-m-d H:i',
            'after'=>'date_format:Y-m-d H:i',
        ]);
        $room = Room::where('id', $room_id)->get();
        if(is_null($room)){
            return response()->json(["message"=>"Room does not exist."], 404);
        }
        $meetings = Meeting::where('room_id', $room_id);
        if(request()->has('before'))
        {
            $meetings->where('end_time', '<', request()->get('before'));
        }

        if(request()->has('after'))
        {
            $meetings->where('start_time', '>', request()->get('after'));
        }

        return response()->json(array("meetings"=>$meetings->get()));
    }
}
