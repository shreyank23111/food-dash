<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class AuthController extends Controller
{
    public function showSignup() { return view('signup'); }
    public function showLogin() { return view('login'); }

    public function register(Request $request) {
        // Validation (Requirement: Appropriate Validations)
        $request->validate([
            'first_name' => 'required|string|max:100',
            'email' => 'required|email|unique:accounts,email',
            'password' => 'required|min:6',
            'dob' => 'required|date'
        ]);

        // Database Logic (Requirement: Creation of table with constraints)
        DB::transaction(function () use ($request) {
            // 1. Create the Account
            $accountId = DB::table('accounts')->insertGetId([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Encryption
                'phone' => $request->phone,
                'created_at' => now()
            ]);

            // 2. Create the User (Specialization)
            DB::table('users')->insert([
                'user_id' => $accountId,
                'dob' => $request->dob
            ]);
        });

        Mail::to($request->email)->send(new WelcomeUserMail($request->first_name));

    return redirect('/login')->with('success', 'Account created! Check your email.');

        // return redirect('/login')->with('success', 'Signup successful! Please login.');
    }

public function login(Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $userId = Auth::id();
        $user = Auth::user();

        // 1. Identify Role and SAVE IT

        if ($user->role === 'admin') { 
        session(['user_role' => 'admin']);
        return redirect()->intended('/admin/dashboard');
    }
    
        if (DB::table('RESTAURANT_OWNER')->where('owner_id', $userId)->exists()) {
            session(['user_role' => 'owner', 'user_name' => $user->first_name]);
            return redirect()->intended('/restaurant/dashboard');
        } 
        
        session(['user_role' => 'customer', 'user_name' => $user->first_name]);
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials.']);
}


    public function logout() {
        Session::flush();
        return redirect('/login');
    }
}