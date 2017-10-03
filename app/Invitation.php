<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use DateTime;
use DB;
use App\Meeting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Invitation extends Model
{

    private $errors;

    protected $fillable = [
        'user_id', 'meeting_id', 'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function meeting()
    {
        return $this->belongsTo('App\Meeting');
    }

    /**
     * Special validation messages.
     */
    private $messages = [
        'user_id.unique' => 'User has already invited to this meeting.'
    ];

    /**
     * Validation rules.
     */
    protected $rules = array(
        'meeting_id' => 'required|exists:meetings,id|integer',
        'user_id' => 'required|integer|exists:users,id',
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
     * Test if an invitation is a valid instance or not.
     *
     * @return boolean
     */
    public function validate($data)
    {
        $validator = Validator::make($data, $this->rules, $this->messages);
        $meeting = Meeting::find($data['meeting_id']);
        $invitation = Invitation::where('meeting_id',$data['meeting_id'])->where('user_id',$data['user_id'])->get();

        if(!is_null($meeting))
        {
            if($meeting->user_id != Auth::user()->id)
            {
                $validator->errors()->add('message',"User does not have permission to invite other users to meeting.");
            }
            elseif($meeting->user_id == $data['user_id'])
            {
                $validator->errors()->add('message',"User cannot invite themself to their own meeting.");
            }
        }

        if(count($invitation) > 0)
        {
          $validator->errors()->add('user_id',"User has already been added to meeting.");
        }

        if(count($validator->errors()) > 0)
        {
              $this->errors = ["errors"=>$validator->errors()];
              return false;
        }
        return true;
    }
}
