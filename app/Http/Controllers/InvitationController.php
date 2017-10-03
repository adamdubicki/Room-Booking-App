<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\User;
use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Meeting Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles validation and CRUD of Invitations.
    |
    */

    /**
     * Get all the invitations for a meeting.
     *
     * @param  unsigned int $meeting_id
     *
     * @return \Illuminate\Http\Response
     */
    public function index($meeting_id)
    {
        $meeting = Meeting::where('id', $meeting_id)->get();
        if(is_null($meeting))
        {
            return response()->json(["message"=>"Meeting does not exist."],404);
        }
        $invitations = Invitation::where('meeting_id', $meeting_id)->get();
        return response()->json(array("invitations"=>$invitations));
    }

    /**
     * Add an invitation to a meeting.
     *
     * @param  unsigned int $meeting_id
     *
     * @return \Illuminate\Http\Response
     */
    public function store($meeting_id)
    {
        $new = request()->all();
        $new += array('meeting_id' => $meeting_id);
        $invitation = new Invitation();
        if($invitation->validate($new))
        {
            $invitation = Invitation::create([
                'user_id' => $new['user_id'],
                'meeting_id' => $new['meeting_id'],
                'status' => 'pending',
            ]);
            return response()->json(array("invitation"=>$invitation));
        }
        else
        {
            return response()->json($invitation->errors(), 400);
        }
    }

    /**
     * Delete a users invitation for a meeting.
     *
     * @param  unsigned int $meeting_id
     * @param  unsigned int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($meeting_id, $user_id)
    {
        $meeting = Meeting::find($meeting_id);
        $user = Auth::user();
        $invitation = Invitation::where('meeting_id', $meeting_id)->where('user_id', $user_id);
        if(is_null($meeting))
        {
            return response()->json(["message"=>"Meeting does not exist"],404);
        }
        if ($meeting->user_id != $user->id)
        {
            return response()->json(["message"=>"Forbidden: You do not have permission to uninvite users to this meeting."],403);
        }
        if(is_null($invitation))
        {
            return response()->json(["message"=>"Invitation does not exist"],404);
        }
        $invitation->delete();
        return response()->json([],204);
    }

    /**
     * Get a users invitations.
     * @param? status[accepted|rejected|cancelled|pending]
     * @param  Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'status'=> 'in:pending,cancelled,accepted,rejected'
        ]);

        $invitations = Invitation::where('user_id', Auth::user()->id);
        if(!is_null($request->get('status')))
        {
            $invitations->where('status', $request->get('status'));
        }

        return response()->json(array("invitations"=>$invitations->get()));
    }

    /**
     * Accept or Reject an invitation
     * @param? unsigned int $invation_id
     * @param  Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $invitation_id)
    {
        $this->validate($request, [
            'status'=> 'in:accepted,rejected'
        ]);
        $invitation = Invitation::where('user_id', Auth::user()->id)
            ->where('id', $invitation_id)->first();
        if(is_null($invitation))
        {
            return response()->json(["message"=>"Meeting does not exist."],404);
        }
        elseif ($invitation->status == 'cancelled')
        {
            return response()->json(["message"=>"Meeting has been cancelled."],400);
        }
        else
        {
            $invitation->status = $request->get('status');
            return response()->json(array("invitation"=>$invitation));
        }
    }

}
