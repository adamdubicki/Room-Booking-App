<?php

namespace App\Observers;
use App\Invitation;

class MeetingObserver
{
    public function deleted($meeting)
    {
        $update = Invitation::where('meeting_id','=',$meeting->id)
          ->update(['status' => 'cancelled']);
    }
}
