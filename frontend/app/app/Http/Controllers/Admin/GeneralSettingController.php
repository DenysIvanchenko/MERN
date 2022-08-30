<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\GeneralSetting;

class GeneralSettingController extends Controller
{
    public function index(Request $request)
    {
        $data['settings'] = GeneralSetting::first();
        $data['title'] = 'General Settings';
        return view('admin.general_settings', $data);
    }

    public function update(Request $request)
    {
        try 
        {
            $upd['current_study_year'] = $request->current_study_year;    
            $upd['current_study_term'] = $request->current_study_term;    
            $upd['contact_one'] = $request->contact_one;    
            $upd['contact_two'] = $request->contact_two;    
            $upd['contact_email'] = $request->contact_email;    
            $upd['contact_address'] = $request->contact_address;    
            $upd['term_ends_on'] = $request->term_ends_on;    
            $upd['next_term_begins_on'] = $request->next_term_begins_on;
            GeneralSetting::updateOrCreate(['id' => 1],$upd);
            return redirect()->route('admin.general-settings-page')->with('success', 'Settings updated successfully');
        } catch (Exception $e) {
             return redirect()->route('admin.general-settings-page')->with('error', 'Oops something went wrong');
        }
    }
}
