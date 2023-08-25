<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Illuminate\Support\Facades\Mail;
use App\Mail\SuccessfullySignUp;
use App\Mail\ForgotPassword;

use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;

use App\Models\Team;
use App\Models\TeamMember;
 
class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'resend',
                                                     'activate', 'forgot', 'reset',
                                                     'google_redirect', 'callback_google']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $exist = User::where('email', $request->input('email'))->first();
        if ( ! $exist) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is not an account registered with this email account. Please check the email address or <a class="text-[#1860CC] !text-[10px] underline underline-offset-2" href="/signup">set up your account</a>'
            ], 401);
        }

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password you’ve entered is incorrect.',
            ], 401);
        }

        $user = Auth::user();
        if ( ! $user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'The account is not activated',
            ], 401);
        }
 
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
    }

    public function me(Request $request) {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }

    public function activate(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $user->email_verified_at = now();
        $user->save();

        return redirect($request->input('redirect'));
    }

    public function resend(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'redirect' => 'required'
        ]);

        try {
            $link =  url("/") . "/user/activate?email=" . $request->email . "&redirect=" . $request->input('redirect') . '/signin?email=' . $request->email;
            Mail::to($request->email)
                ->send(new SuccessfullySignUp($link, $request->email));
        } catch (\Exeption $e) {
            // ...
        }
    }

    public function register(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:6',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'team' => 'string'
        ], [
            'unique' => 'This email address is already registered. 
                Please proceed to the <a class="text-[#1860CC] !text-[14px] underline underline-offset-2" href="/signin">Sign In</a> page to login or click <a href="/forgot" class="text-[#1860CC] !text-[14px] underline underline-offset-2">Forgot Password</a> to reset your password.',
            'regex' => '<ul>
                    <li>An English uppercase character (A-Z)</li>
                    <li>An English lowercase character (a-z)</li>
                    <li>A number (0-9) and/or symbol (such as !, #, or %)</li>
                </ul>
            '
        ]);

        $user = User::create([
            'name' => $request->name ?? "",
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $default = [
            'assignee_changes' => true, 
            'status_cahnges' => true, 
            'tasks_assigned_to_me' => true,
            'document_edited' => true,
            'new_version_published' => true,
            'due_date_changes' => true,
            'due_date_overdue' => true,
            'before_due_date_reminder' => true
        ];

        $user->settings()->create($default);

        if ($request->input('team')) {
            $team = Team::whereRaw('md5(id) = "' . $request->input('team') . '"')->first();
            if ($team) {
                $member = TeamMember::where('team_id', $team->id)->where('email', $request->email)->first();
                $member->update(['accepted' => true, 'user_id' => $user->id]);
            }
        }

        $token = Auth::login($user);

        try {
            $link =  url("/") . "/user/activate?email=" . $request->email . "&redirect=" . $request->input('redirect') . '/signin?email=' . $request->email;
            Mail::to($user)
                ->send(new SuccessfullySignUp($link, $request->email));
        } catch (\Exeption $e) {
            // ...
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function forgot(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
            'link' => 'required'
        ], [
            'exists' => 'There is not an account registered with this email account. 
                Please check the email address or <a href="/signup" class="text-[#1860CC] !text-[14px] underline underline-offset-2">set up your account</a>'
        ]);

        $link = $request->link . '/reset/' . md5($request->email);
        try {
            Mail::to($request->email)
                ->send(new ForgotPassword($link, $request->email));
        } catch (\Exception $e) {
            // ...
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully sent email for reset password',
        ]);
    }

    public function reset(Request $request) {
        $request->validate([
            'hash' => 'string',
            'email' => 'string|email',
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

        if ($request->input('hash')) {
            $user = User::whereRaw('md5(email) = "' . $request->input('hash') . '"')->first();
        }

        if ($request->input('email')) {
            $user = User::where('email', $request->input('email'))->first();
        }

        if ( ! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 401);
        }

        $user->password = bcrypt($request->input('password'));
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'The password successfully updated',
        ]);
    }

    public function google_redirect(Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl()
        ]);
    }

    public function callback_google(Request $request) {
        $googleUser = Socialite::driver('google')->stateless()->user();
        
        $user = User::where('email', $googleUser->email)->first();
        if ( ! $user) {
            $user = User::create([
                'email' => $googleUser->email,
                'name' => $googleUser->name,
                'password' => bcrypt(time())
            ]);
            $user->save();
        }

        $token = Auth::login($user);
        return view('social-connect', ['token' => $token, 'user' => $user]);
    }
}
