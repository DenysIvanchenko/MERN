<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StaffDetail;
use Auth;
use Illuminate\Validation\Rule;
use App\Rules\MatchOldPassword;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Dashboard';
        return view('staff.index', $data);
    }

    public function profile(Request $request){
        $data['title'] = 'My Profile';
        $data['admin_details'] = StaffDetail::where('user_id', Auth::user()->id)->first();
        return view('staff.profile',$data);
    }

    public function updateProfile(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => [
                            'required',
                            Rule::unique('users')
                            ->where(function($query) use($request){
                                $query->where('id', '!=', $request->user()->id);
                                $query->where('email', $request->email);
                            }),
                        ],
            'username' => [
                            'required',
                            Rule::unique('users')
                            ->where(function($query) use($request){
                                $query->where('id', '!=', $request->user()->id);
                                $query->where('username', $request->username);
                            }),
                        ],
        ]);

        $update['email'] = $request->email;
        $update['name'] = $request->name;
        $update_staff['contact_one'] = $request->contact_one;
        $update_staff['contact_two'] = $request->contact_two;
        $update_staff['gender'] = $request->gender;

        try {
            User::where('id', Auth::user()->id)->update($update);
            StaffDetail::updateOrCreate(['user_id' => Auth::user()->id],$update_staff);
            return redirect()->route('staff.profile-page')->with('success', 'Profile Updated Successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.profile-page')->with('error', 'Oops something went wrong');
        }
    }

    public function updatePassword(Request $request){
        $request->validate([
            'currentpassword' => ['required', new MatchOldPassword],
            'newpassword' => ['required'],
            'confirmpassword' => ['same:newpassword'],
        ]);
        
        try {
            User::find(auth()->user()->id)->update(['password'=> Hash::make($request->newpassword)]);
            Auth::logout();
            return redirect()->route('staff.login-page')->with('success', 'Password updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.profile-page')->with('error', 'Oops something went wrong');
        }
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect()->route('staff.login-page')->with('success', 'Logged out successfully');
    }
}
