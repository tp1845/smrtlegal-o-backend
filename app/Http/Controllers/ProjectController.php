<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\Document;
use App\Models\Category;
use App\Notifications\RequestsToChangeRole;
use Carbon\Carbon;
use App\Models\Approve;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

use App\Mail\Invite3PartyCollaborate;
use App\Mail\InviteMemberToProject;

use App\Mail\SendEmail;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function requests_to_change_role(Request $request, Project $project, User $user) {
        $request->validate([   
            'role' => [
                'required',
                'exists:roles,id'
            ]
        ]);

        $team = $project->team;
        $leads = $project->team->members()->where('role_id', 1)->get();

        $roleTo = Role::find($request->input('role'));
        if (count($leads)) {
            $member = TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->first();
            $roleFrom = Role::find($member->role_id);
            $leadNames = [];
            collect($leads)->each(function($lead) use ($roleTo, $user, $team, &$leadNames) {
                $lead->notify(new RequestsToChangeRole($roleTo, $user, $team));        
                $leadNames []= $lead->fname && $lead->lname ? $lead->fname . ' ' . $lead->lname : $lead->email;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Your request to udpate your role from "' . $roleFrom->name . '" to 
                            "' . $roleTo->name . '" was sent to the project lead ' . implode(',', $leadNames) . '. You will be notified when your role is updated.',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead not found'
            ], 400);
        }
    }

    public function create(Request $request) {
        $user = auth()->user();
        
        $request->validate([   
            'name' => 'required|string',
            'duedate' => 'required',
            'notes' => '',
            'team' => '',
            'members' => '',
            'user' => 'integer',
        //    'ai_summary' => 'string',
            
            'documentname' => 'required',
        //    'category' => 'required',     //->option
        //    'type' => 'required',         //->option
            'document' => 'required',

            'final_approver' => '',
            'approvers' => '',
            'signatories' => '',
            'save_for_future' => 'boolean',
            
        //    'content' => 'string',
            'summaryhtml' => 'string',
        ], [
            'documentname.required' => 'The document name field is required.',
        ]);
        
        $leads = [];
        foreach (json_decode($request->input('leads')) as $key => $value) {
            if ($value === true) {
                $leads[] = $key;
            }
        }
        
        if(sizeof( $leads ) === 0) $leads[] = $user->email  ;

        $leads = collect($leads)->map(function($leadEmail) {
            $res = User::where('email', $leadEmail)->first();
            return $res;
        });

        $documentPath = $request->file('document')->store('/public/documents');

        $team = ! empty( $request->team) ? json_decode($request->team) : false;
        $team = ! empty($team) ? Team::find($team->id) : $team;

        $members = $request->input('members') ?: [];
        $members = collect($members)->map(function($member) { return json_decode($member); });

        $signatories = $request->input('signatories') ?: [];
        $signatories = collect($signatories)->map(function($signatory) { return json_decode($signatory); });
        
        $external_collaborators = $request->input('external_collaborators') ?: [];
        $external_collaborators = collect($external_collaborators)->map(function($external_collaborator) { return json_decode($external_collaborator); });
        
        if ($team && $request->input('signatories') && $request->input('save_for_future')) {
            foreach(collect($request->input('signatories')) as $signatory) {
                $signatory = json_decode($signatory);
                $email = $signatory->email;
                $name = $signatory->name;

                $roleSignatureId = Role::getIdRoleBySlug('signatory');
                $exist = TeamMember::where('email', $email)->where('role_id', $roleSignatureId)->first();
                if ( ! $exist) {
                    TeamMember::create([
                        'team_id' => $team->id,
                        'email' => $email,
                        'name' => $name,
                        'user_id' => 0,
                        'role_id' => $roleSignatureId,
                    ]);
                }
            }
        }

        $document = Document::create([
            'name' => $request->input('documentname'),
            'user_id' => $user->id,
            'type_id' => $request->input('type_id') ?? 0,
            'category_id' => $request->input('category_id') ?? 0,
            'type' => $request->input('type') ?? '',
            'category' => $request->input('category') ?? '',
            'path' => $documentPath,
            'content' => $request->input('content'),
        ]);

        $project = Project::create([
            'name' => $request->input('name'),
            'user_id' => $request->input('user') ?? 0,
            'due_date' => Carbon::parse($request->input('duedate'))->format('Y-m-d'),
            'summary' => $request->input('notes'),
            'status' => 'PENDING',
            'team_id' => 1,///! empty($request->team)? json_decode($request->team)->id : 0,
            'document_id' => $document->id,
            'reminder_id' => $request->input('reminder_id') ?? 0,
            'reminder' =>  Carbon::parse($request->input('reminderdate'))->format('Y-m-d'),

            'members' => $members,
            'signatory' => $signatories,
            'external_collaborators' => $external_collaborators,

            'ai_summary' => $request->input('ai_summary') ?? '',
            'summaryhtml' => $request->input('summaryhtml'),
            'leads' => ''//$leads
        ]);

        if ( ! empty($members)) {
            collect($members)->each(function($member) use ($project, $request, $user, $team) {

                $mail_to = "";
                $mail_invite_link = "";

                $in_platform = User::where('email', $member->email)->count();
                if ($in_platform) {
                    $exist =  $team->members()->where('users.email', $member->email)->count();
                    if ($exist) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'User already exist in team',
                        ], 400);
                    }
                    
                    $team->members()->attach($in_platform, ['role_id' => $member->role->value, 'name' => $member->name, 'email' => $member->email]);
                
                    $mail_to = $member->email;
                    $mail_invite_link = url("/") . '/user/invite/accept/' . md5($team->id) . '/' . $mail_to . '?redirect=' . $request->input('redirect') . '/signin';
                } else {
                    
                    $team && DB::table('team_members')->insert([
                                'name' => $member->name,
                                'email' => $member->email,
                                'role_id' => $member->role->value,
                                'team_id' => $team->id,
                                'user_id' => '0'
                    ]);

                    $mail_to = $member->email;
                    $mail_invite_link = $request->input('redirect') . '/signup-by-invitation/' . md5($team ? $team->id : '') . "?email=" . $mail_to;
                }

                $from = $user->fname ? $user->fname . ' ' . $user->lname : $user->email;

                Mail::to($member->email)
                    ->send(new InviteMemberToProject($from, $team->name ?? 'Team', $project->name, $request->input('type'), $mail_invite_link));
            });
        }
        

        if ( ! empty($external_collaborators)) {
            collect($external_collaborators)->each(function($collaborator) use ($project, $request, $user) {
                Mail::to($collaborator->email)
                    ->send(new Invite3PartyCollaborate($user->fname . ' '. $user->lname, $project->name, $request->input('type'), $project->due_date));
                });
        }

        if ( ! empty($request->approvers)) {
            $final_approver = $request->final_approver ? json_decode($request->final_approver) : false;
            
            collect($request->approvers)->each(function($approver) use ($final_approver, $document) {
                $approver = json_decode($approver);
                Approve::create([
                    'name' => $approver->name,
                    'email' => $approver->email,
                    'document_id' => $document->id,
                    'is_final' => $final_approver ? ($final_approver->value == $approver->email) : false
                ]);
            });
        }



        return response()->json([
            'data' => $project,
            'status' => 'success',
            'message' => "Successfully created"
        ]);
    }

    public function get_categories(Request $request) {
        $categories = Category::all();
        return response()->json([
            'data' => $categories,
        ]);
    }

    public function get(Request $request, Project $project) {
        return response()->json([
            'data' => $project->load(['document.typeDocument', 'document.category', 'team', 'team.members'])
        ]);
    }

    public function notifications(Request $request, Project $project) {
        $notifications = $project->notifications;

        return response()->json([
            'data' => $notifications->map(function($item) {
                $item->{'created_at_humans'} = $item->created_at->diffForHumans();
                return $item;
            }),
        ]);
    }

    public function get_archived(Request $request) {
        $filter = $request->only(['owner', 'date', 'team_id', 'document_id']);

        $user = auth()->user();
        $team_ids =  $user->teams()->get()->pluck('id');
       
        $projects = Project::query();
        if ( ! empty($filter['owner'])) {
            if ($filter['owner'] == 'a-z') {
                $projects = $projects->orderBy('name', 'asc');
            } else if ($filter['owner'] == 'z-a') {
                $projects = $projects->orderBy('name', 'desc');
            }
        }

        if ( ! empty($filter['date'])) {
            if ($filter['date'] == 'newest-first') {
                $projects = $projects->orderBy('name', 'asc');
            } else if ($filter['date'] == 'oldest-first') {
                $projects = $projects->orderBy('name', 'desc');
            }
        }

        if ( ! empty($filter['document_id'])) {
            $ids = explode(',', $filter['document_id']);
            if (count($ids)) {
                $projects = $projects->whereHas('document', function($q) use ($ids) {
                    return $q->whereHas('typeDocument', function($q2) use ($ids) {
                        return $q2->whereIn('id', $ids);
                    });
                });
            }
        }

        if ( ! empty($filter['team_id'])) {
            $projects = $projects->where('team_id', $filter['team_id']);
        }
        
        $projects = $projects->with(['team', 'document.typeDocument'])->whereIn('team_id', $team_ids)->get();

        return response()->json([
            'data' => $projects,
        ]);
    }

    public function send_mail() {
        $details = [
            'subject' => 'Mail from Laravel',
            'body' => 'This is a test email sent from Laravel using PHPMailer.',
            'recipient' => 'hopeman1102@gmail.com'
        ];
    
        Mail::to($details['recipient'])->send(new SendEmail($details));
    
        return "Email sent successfully.";
    }

    public function get_status(Request $request, Project $project) {
        $status = $project->status;

        return response()->json([
            'data' => $status,
        ]);
    }

    public function update_status(Request $request, Project $project) {
        $project->update(['status'=> 'new']);    
        return response()->json([
            'data' => 'success',
        ]);
    }
}
