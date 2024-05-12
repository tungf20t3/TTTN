<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\VerifyEmailUser;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'inactive'
        ]);
    
        event(new Registered($user));
    
        $link = route('verifyUserEmail', ['id' => $user->id]);
    
        try {
            Mail::send(
                'mail.verify-email-user',
                compact('link'),
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Verify Your Email Address');
                }
            );
    
            toastr('Register successfully! Check your email address');
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: '.$e->getMessage());
            // Handle the error appropriately
        }
    
        return redirect()->route('login');
    }
    
    public function verifyUserEmail(Request $rq){
       try{
        $user = User::find($rq->get('id'));
        $user->status = 'active';
        $user->save();
        toastr('Register successfully! Check your email address');
       }catch (\Exception $e){
        toastr('Error!');

       }
       return redirect()->route('login');



    }
}
