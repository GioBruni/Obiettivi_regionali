<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(middleware: 'guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override il metodo credentials, ma aggiungo il controllo enabled=1
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');

        $user = User::where($this->username(), $request->get($this->username()))->first();

        if ($user && $user->enable == 0) {
            throw ValidationException::withMessages([
                $this->username() => ["Utente disabilitato"],
            ]);
        }

        return $credentials;
    }

    protected function authenticated(Request $request, $user): void
    {
        UserLog::create([
            'user_id' => $user->id,
            'super_action' => config("constants.SUPER_ACTION.LOGIN"),
        ]);
    }
}
