<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Room;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
  public function index()
  {
    $rooms = Room::all();
    return response()->json($rooms);
  }
}
