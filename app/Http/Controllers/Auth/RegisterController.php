<?php

namespace App\Http\Controllers\Auth;

use App\Mail\ConfirmationMail;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
    	$confirmationToken = Hash::make(str_random(8));

	    $user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
		    'confirmation_token' => $confirmationToken
        ]);
	    $this->sendConfirmationEmail($user);
	    return $user;
    }

	/**
	 * If we will have more emails, then we should create service and move this function there
	 * @param User $user
	 *
	 * @return mixed
	 */
	private function sendConfirmationEmail(User $user) {
		return Mail::to($user)->send(new ConfirmationMail($user));
    }

    public function confirmRegistration($confirmToken)
    {
    	$userToConfirm = User::where('confirmation_token', $confirmToken)->first();
	    if ($userToConfirm) {
			$userToConfirm->confirmation_token = null;
			$userToConfirm->confirmed = true;
			$userToConfirm->save();
	    }
	    return Redirect(route('home'));


    }
}
