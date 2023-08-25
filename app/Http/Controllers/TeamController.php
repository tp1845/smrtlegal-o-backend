<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvintationSignUp;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['accept_invite']]);
    }

    public function remove_member(Request $request, Team $team) {
        $request->validate([   
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
        ]]);

        $email = $request->input('email');

        $team->members()->wherePivot('email', $email)->detach();
        $targetMember = $team->members()->where('users.email', $email)->first();
        if ($targetMember) {
            $team->members()->detach($targetMember);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted',
        ], 200);
    }

    public function member_update(Request $request, Team $team) {
        $request->validate([   
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'role' => [
                'required',
                'exists:roles,id'
            ]
        ]);

        TeamMember::where('team_id', $team->id)->where('email', $request->input('email'))
            ->update(['role_id' => $request->input('role')]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated',
        ], 200);
    }

    public function add_member(Request $request, Team $team) {
        $user = auth()->user();

        $request->validate([   
            'members' => [
                'array'
            ],
            'redirect' => [
                'required'
            ]
        ]);

        $members = $request->input('members');
        foreach(collect($members) as $member) {
            $email = $member['email'];
            $role = $member['role']['value'];
            $name = $member['name'];

            $mail_to = "";
            $mail_invite_link = "";
            
            $in_platform = User::where('email', $email)->first();
            if ($in_platform) {
                $exist = TeamMember::where('team_id', $team->id)->where('user_id', $in_platform->id)->count();
                if ($exist) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User already exist in team',
                    ], 401);
                } else {
                    $team->members()->attach($in_platform, ['user_id' => $in_platform->id, 'role_id' => $role, 'name' => $name, 'email' => $email]);
                    
                    $mail_to = $in_platform->email;
                    $mail_invite_link = url("/") . '/user/invite/accept/' . md5($team->id) . '/' . $mail_to . '?redirect=' . $request->input('redirect') . '/signin';
                }
            } else {
                $exist = TeamMember::where('team_id', $team->id)->where('email', $email)->count();
                if ($exist) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User already exist in team',
                    ], 400);
                } else {
                    $insertId = DB::table('team_members')->insert([
                        'name' => $name,
                        'email' => $email,
                        'role_id' => $role,
                        'team_id' => $team->id,
                        'user_id' => '0'
                    ]);
                    $mail_to = $email;
                    $mail_invite_link = $request->input('redirect') . '/signup-by-invitation/' . md5($team->id)  . "?email=" . $mail_to;
                }
            }

            try {
                $from = $user->fname ? $user->fname . ' ' . $user->lname : $user->email;

                Mail::to($mail_to)
                    ->send(new InvintationSignUp($team->name, $from, $mail_invite_link));
            } catch (\Exeption $e) {
                // ...
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully added',
        ], 200);
    }

    public function create_team(Request $request) {
        $request->validate([ 
            'name' => [
                'required',
                'string'
            ],
            'members' => [
                'array'
            ],
            'redirect' => [
                'required'
            ]
        ]);

        $members = $request->input('members');

        $team = new Team();
        $team->name = $request->input('name');
        $team->save();

        $role = Role::get()->first();
        $user = auth()->user();

        $team->members()->attach($user, ['role_id' => $role->id, 
            'name' => ($user->fname && $user->lname ? $user->fname . " " . $user->lname : $user->email
        ) , 'email' => $user->email ]);

        if ( ! empty($members)) {
            collect($members)->each(function($member) use (&$team, $request, $user) {
                $mail_to = "";
                $mail_invite_link = "";

                $in_platform = User::where('email', $member['email'])->count();
                if ($in_platform) {
                    $exist =  $team->members()->where('users.email', $request->input('email'))->count();
                    if ($exist) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'User already exist in team',
                        ], 400);
                    }
                    
                    $team->members()->attach($in_platform, ['role_id' => $member['role']['value'], 'name' => $member['name'], 'email' => $member['email']]);
                
                    $mail_to = $member['email'];
                    $mail_invite_link = url("/") . '/user/invite/accept/' . md5($team->id) . '/' . $mail_to . '?redirect=' . $request->input('redirect') . '/signin';
                } else {
                    DB::table('team_members')->insert([
                        'name' => $member['name'],
                        'email' => $member['email'],
                        'role_id' => $member['role']['value'],
                        'team_id' => $team->id,
                        'user_id' => '0'
                    ]);

                    $mail_to = $member['email'];
                    $mail_invite_link = $request->input('redirect') . '/signup-by-invitation/' . md5($team->id) . "?email=" . $mail_to;
                }

                try {
                    $from = $user->fname ? $user->fname . ' ' . $user->lname : $user->email;
    
                    Mail::to($mail_to)
                    ->send(new InvintationSignUp($team->name, $from, $mail_invite_link));
                } catch (\Exeption $e) {
                    // ...
                }

            });
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully created',
            'data' => $team,
        ], 201);
    }

    public function accept_invite(Request $request, $team_hash, $email) {
        $team = Team::whereRaw('md5(id) = "' . $team_hash . '"')->first();
        if ($team) {
            $member = TeamMember::where('team_id', $team->id)->where('email', $email)->first();
            $member->update(['accepted' => true]);
        }

        return redirect($request->input('redirect'));
    }

    public function request_to_change_role(Request $request, User $user) {
        
    }
}
