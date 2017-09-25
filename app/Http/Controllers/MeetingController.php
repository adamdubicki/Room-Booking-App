<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function index()
    {
      $meetings = Meeting::where('user_id', Auth::user()->id)->get();
      return response()->json($meetings);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_time'
        ]);

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
