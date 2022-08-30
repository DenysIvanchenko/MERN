<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentDetail;
use Auth;
use Illuminate\Validation\Rule;
use App\Rules\MatchOldPassword;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Dashboard';
        return view('student.index', $data);
    }

    public function profile(Request $request){
        $data['title'] = 'My Profile';
        $data['admin_details'] = StudentDetail::where('user_id', Auth::user()->id)->first();
        return view('student.profile',$data);
    }

    public function updateProfile(Request $request){
        $request->validate([
            'name' => 'required',
            'username' => [
                            'required',
                            Rule::unique('users')
                            ->where(function($query) use($request){
                                $query->where('id', '!=', $request->user()->id);
                                $query->where('username', $request->username);
                            }),
                        ],
        ]);

        $update['name'] = $request->name;
        $update_student['gender'] = $request->gender;
        $update_student['date_of_birth'] = $request->date_of_birth;
        if($request->user_image){
            $update_student['profile_picture'] = $request->user_image;    
        }
        try {
            User::where('id', Auth::user()->id)->update($update);
            StudentDetail::updateOrCreate(['user_id' => Auth::user()->id],$update_student);
            return redirect()->route('student.profile-page')->with('success', 'Profile Updated Successfully');
        } catch (Exception $e) {
            return redirect()->route('student.profile-page')->with('error', 'Oops something went wrong');
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
            return redirect()->route('student.login-page')->with('success', 'Password updated successfully');
        } catch (Exception $e) {
            return redirect()->route('student.profile-page')->with('error', 'Oops something went wrong');
        }
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect()->route('student.login-page')->with('success', 'Logged out successfully');
    }
}
