<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminDetail;
use App\Models\SubjectClass;
use App\Models\StudentDetail;
use Auth;
use Illuminate\Validation\Rule;
use App\Rules\MatchOldPassword;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $data['class_students'] = SubjectClass::with('students')->where('is_deleted','no')->where('class_status','active')->get();
        $students = StudentDetail::where('is_deleted','no')->where('student_staus','active')->get();
        $data['residential_counts'] = $students->groupBy('residential_status')->toArray();
        $data['gender_counts'] = $students->groupBy('gender')->toArray();
        $data['title'] = 'Dashboard';
        return view('admin.index', $data);
    }

    public function profile(Request $request){
        $data['title'] = 'My Profile';
        $data['admin_details'] = AdminDetail::where('user_id', Auth::user()->id)->first();
        return view('admin.profile',$data);
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

        if($request->user_image)
        {
            $update_admin['profile_picture'] = $request->user_image;
        }

        $update['email'] = $request->email;
        $update['name'] = $request->name;

        $update_admin['contact_one'] = $request->contact_one;
        $update_admin['contact_two'] = $request->contact_two;
        $update_admin['gender'] = $request->gender;

        try {
            User::where('id', Auth::user()->id)->update($update);
            AdminDetail::updateOrCreate(['user_id' => Auth::user()->id],$update_admin);
            return redirect()->route('admin.profile-page')->with('success', 'Profile Updated Successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.profile-page')->with('error', 'Oops something went wrong');
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
            return redirect()->route('admin.login-page')->with('success', 'Password updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.profile-page')->with('error', 'Oops something went wrong');
        }
    }

    public function fileManager(Request $request)
    {
        $data['title'] = 'File Manager';
        return view('admin.file-manager',$data);
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect()->route('login-page')->with('success', 'Logged out successfully');
    }
}
