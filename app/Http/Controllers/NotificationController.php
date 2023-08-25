<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\TeamMember;
use App\Notifications\AcceptRequestToChangeRole;
use App\Notifications\RejectRequestToChangeRole;
use App\Notifications\RefreshNotifications;
use App\Models\User;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function accept(Request $request, Notification $notification) {
        $auth = auth()->user();

        if (in_array($notification->type, ['App\Notifications\RequestsToChangeRole'])) {
            $target_user = $notification->data->from ?? false;
            $target_role = $notification->data->role ?? false;
            $target_team = $notification->data->team ?? false;
            
            TeamMember::where('team_id', $target_team->id)->where('user_id', $target_user->id)
                ->update(['role_id' => $target_role->id]);
            
            $user = User::where('email', $target_user->email)->first();
            if ($user) {
                $user->notify(new AcceptRequestToChangeRole($target_role, $target_team));
            }

            $notification->read_at = now();
            $notification->save();

            $auth->notify(new RefreshNotifications());

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully accepted',
            ], 200);
        }
    }

    public function reject(Request $request, Notification $notification) {
        $auth = auth()->user();
        if (in_array($notification->type, ['App\Notifications\RequestsToChangeRole'])) {
            $from = $notification->data->from ?? false;
            $role = $notification->data->role ?? false;
            $team = $notification->data->team ?? false;

            // $auth->unreadNotifications->where('id', $notification->id)->markAsRead();
            $notification->read_at = now();
            $notification->save();

            $auth->notify(new RefreshNotifications());

            $user = User::where('email', $from->email)->first();
            if ($user) {
                $user->notify(new RejectRequestToChangeRole($role, $team));
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully rejected',
        ], 200);
    }

    public function read(Request $request, Notification $notification) {
        $user = auth()->user();

        // $user
        //     ->unreadNotifications
        //     ->where('id', $notification->id)
        //     ->markAsRead();
        $notification->read_at = now();
        $notification->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully readed',
        ], 200);
    }
}
