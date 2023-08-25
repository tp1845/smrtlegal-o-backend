<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Team;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangeEmailAddress;
use App\Models\User;
use Illuminate\Support\Str;
 
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['confirm_email']]);
    }

    public function confirm_email(Request $request, $hash, $email) {
        $user = User::whereRaw('md5(email) = "' . $hash . '"')->first();
           
        if ($user) {
            $user->email = $email;
            $user->save();
        }

        return redirect($request->input('redirect'));
    }

    public function update(Request $request) {
        $user = auth()->user();

        $oldemail = $user->email;
        $newemail = $request->input('email');
        
        $request->validate([            
            'fname' => 'string',
            'lname' => 'string',
            'phone' => 'string',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
        ]);

        if($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('public/avatars');
        }

        if ($request->input('email') != $user->email) {
            $redirect = $request->input('redirect');
            try {
                $link =  url("/") . "/user/confirm-email/" .md5($user->email) . "/" .  $request->input('email') . "?redirect=" . $redirect;
                Mail::to($request->email)
                    ->send(new ChangeEmailAddress($link, $request->email));
            } catch (\Exeption $e) {
                // ...
            }
        }

        if ( ! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 401);
        }

       
        $user->fname = $request->input('fname');
        $user->lname = $request->input('lname');
        $user->phone = $request->input('phone');
        // $user->email = $request->input('email');
        $user->save();

        if ($newemail != $oldemail) {
            return response()->json([
                'status' => 'success',
                'data' => $user,
                'message' => 'You will receive a confirmation email update',
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $user,
            'message' => 'The profile successfully updated',
        ]);
    }

    public function update_settings(Request $request) {
        $user = auth()->user();

        $request->validate([          
            'setting' => ['string', 'required', Rule::in([
                    'assignee_changes', 
                    'status_cahnges', 
                    'tasks_assigned_to_me',
                    'document_edited',
                    'new_version_published',
                    'due_date_changes',
                    'due_date_overdue',
                    'before_due_date_reminder'
                    ])],
            'value' => 'required|boolean',
        ]);

        $setting = $request->input('setting');
        $value = $request->input('value');
        
        if ( ! $user->settings) {
            $default = [
                'assignee_changes' => false, 
                'status_cahnges' => false, 
                'tasks_assigned_to_me' => false,
                'document_edited' => false,
                'new_version_published' => false,
                'due_date_changes' => false,
                'due_date_overdue' => false,
                'before_due_date_reminder' => false
            ];

            $default[$setting] = $value;

            $user->settings()->create($default);
            $user->save();
        } else {
            $user->settings->{$setting} = $value;
            $user->settings->save();
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Successfully updated'
        ]);
    }

    public function get_settings(Request $request) {
        $user = auth()->user();
        
        return response()->json([
            'status' => 'success',
            'data' => $user->settings,
        ]);
    }

    public function get_roles(Request $request) {
        $roles = Role::all();

        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);
    }

    public function get_teams(Request $request) {
        $user = auth()->user();
        $teams = $user->teams()->get();

        $teams = $teams->map(function($team) {
            $team->members = TeamMember::with('user', 'role')->where('team_id', $team->id)->get();
            return $team;
        });

        return response()->json([
            'status' => 'success',
            'data' => $teams,
        ]);
    }

    public function reset_password(Request $request) {
        $user = null;

        if ($request->input('email')) {
            $user = User::where('email', $request->input('email'))->first();
        }

        if ( ! Hash::check($request->input('oldpassword'), $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is not correct',
            ], 400);
        }

        if ( ! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 401);
        }

        $request->validate([
            'email' => 'string|email',
            'oldpassword' => 'string|required',
            'password' => [
                'required',
                'string',
                'min:6',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ]
        ],
        [
            'regex' => '<ul>
                            <li>An English uppercase character (A-Z)</li>
                            <li>An English lowercase character (a-z)</li>
                            <li>A number (0-9) and/or symbol (such as !, #, or %)</li>
                        </ul>
                    '
        ]);

        

        $user->password = bcrypt($request->input('password'));
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'The password successfully updated',
        ]);
    }

    public function get_last_notifications(Request $reqeust) {
        $user = auth()->user();

        return response()->json([
            'data' => $user->notifications->map(function($item) {
                $item->{'created_at_humans'} = $item->created_at->diffForHumans();
                return $item;
            })
        ], 200);
    }

    public function projects(Request $request) {
    
        $sortBy = $request->input('sortBy');
        $sortValue = $request->input('sortValue');

        $user = auth()->user();

        $projects = $user->projects()->with('document.typeDocument', 'team.members')->get();
        
        $projects = $projects->sortBy(function($item) use ($sortBy, $sortValue) {
            return $item->created_at;
            
            if ($sortBy == 'created_at') {
                return $item->created_at;
            }

            if ($sortBy == 'name') {
                return $item->name;
            }

            if ($sortBy == 'date') {
                return $item->duedate;
            }
           
            if ($sortBy == 'status') {
                return Str::of(strtolower($item->status))->kebab() != $sortValue;
            }

            if ($sortBy == 'team') {
                return $item->team->id != $sortValue;
            }

            if ($sortBy == 'doctype') {
                return ! empty($item->document->typeDocument->id) && $item->document->typeDocument->id != $sortValue;
            }
            
            return true;
        });

        
        // if (in_array($sortValue, ['newest-first', 'z-a'])) {
        //     $projects = $projects->reverse();
        // }
        $projects = $projects->reverse();
        $data = $projects->values()->all();
        // foreach ($projects as $project) {
        //     $project->update(['status' => 'new']);
        // }

        // $member = TeamMember::where('team_id', $team->id)->where('email', $request->email)->first();
        // $member->update(['accepted' => true, 'user_id' => $user->id]);
        return response()->json([
            'data' => $data,
        ], 200);
    }
}
