<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        return view('auth.admin_login');
    }

    public function checkLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            if(Auth::user()->role == 1){
                return redirect()->route('admin.dashboard-page')->with('success', 'Logged in successfully');
            }else{
                Auth::logout();
                return redirect()->route('login-page')->with('success', "Sorry you don't have the access");
            }
        }else{
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Invalid username or password',
                ]);
        }
    }

    public function staffLogin(Request $request)
    {
        return view('auth.staff_login');
    }

    public function staffCheckLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){
            if(Auth::user()->user_type == 'staff'){
                return redirect()->route('staff.dashboard-page')->with('success', 'Logged in successfully');
            }else{
                Auth::logout();
                return redirect()->route('login-page')->with('success', "Sorry you don't have the access");
            }
        }else{
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Invalid username or password',
                ]);
        }
    }

    public function studentLogin(Request $request)
    {
        return view('auth.student_login');
    }

    public function studentCheckLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){
            if(Auth::user()->user_type == 'student'){
                return redirect()->route('student.dashboard-page')->with('success', 'Logged in successfully');
            }else{
                Auth::logout();
                return redirect()->route('login-page')->with('success', "Sorry you don't have the access");
            }
        }else{
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Invalid username or password',
                ]);
        }
    }

    public function adminForgotPassword(Request $request)
    {
        return view('auth.admi_forgot_password');
    }

    public function updateAdminForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password_confirmation' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $get_user = User::where('email', $request->email);

        if($get_user->count() > 0){
            $get_user->update(['password'=>Hash::make($request->password)]);
            return redirect()->route('login-page')->with('success', "Password updated successfully");

        }else{
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Invalid email',
                ]);
        }
    }

    public function staffForgotPassword(Request $request)
    {
        return view('auth.staff_forgot_password');
    }

    public function updateStaffForgotPassword(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'confirm_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $get_user = User::where('username', $request->email);

        if($get_user->count() > 0){
            $get_user->update(['password'=>ash::make($request->password)]);
            return redirect()->route('login-page')->with('success', "Password updated successfully");

        }else{
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Invalid username',
                ]);
        }
    }
}
