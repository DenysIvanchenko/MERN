<?php

namespace App\Imports;

use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Support\Facades\Session;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function  __construct($class_id,$term,$year_of_study,$stram_id,$residential_status)
    {
        $this->class_id = $class_id;
        $this->term = $term;
        $this->year_of_study = $year_of_study;
        $this->residential_status = $residential_status;
        $this->stream = $stram_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        Validator::make($rows->toArray(), [
             '*.date_of_birth' => 'required',
             '*.gender' => 'required',
             '*.nationality' => 'required',
             '*.student_name' => 'required',
         ])->validate();

        try 
        {
            foreach ($rows as $row) 
            {
                $latestStudent = User::orderBy('id','DESC');
                $prefix = 'STMARYS'.$this->year_of_study.''.$this->class_id;

                if($latestStudent->count() > 0){
                    $registraion_no = $prefix.''.str_pad($latestStudent->first()->id + 1, 3, "0", STR_PAD_LEFT);    
                }else{
                    $registraion_no = $prefix.''.str_pad(0 + 1, 3, "0", STR_PAD_LEFT);
                }

                $exists = StudentDetail::where(['class_id' => $this->class_id,'student_name' => $row['student_name']])->count();

                if($exists == 0)
                {
                    $usr['name'] = $row['student_name'];
                    $usr['username'] = $registraion_no;
                    $usr['password'] = Hash::make($registraion_no);
                    $usr['user_type'] = 'student';
                    $usr['user_staus'] = 'active';
                    $usr['is_deleted'] = 'no';
                    $usr['role'] = 2;
                    $usr['created_by'] = Auth::user()->id;
                    $usr['updated_by'] = Auth::user()->id;

                    $user = User::updateOrCreate(['username' => $registraion_no], $usr);
                    $user->assignRole([2]);

                    $stud['user_id'] = $user->id;
                    $stud['student_name'] = $row['student_name'];
                    $stud['registration_no'] = $registraion_no;
                    $stud['date_of_birth'] = $row['date_of_birth'];
                    $stud['gender'] = $row['gender'];
                    $stud['nationality'] = $row['nationality'];
                    $stud['class_id'] = $this->class_id;
                    $stud['stram_id'] = $this->stream;
                    $stud['term'] = $this->term;
                    $stud['year_of_study'] = $this->year_of_study;
                    $stud['residential_status'] = $this->residential_status;
                    $stud['student_staus'] = 'active';
                    $stud['is_deleted'] = 'no';
                    $stud['created_by'] = Auth::user()->id;
                    $stud['updated_by'] = Auth::user()->id;

                    $student = StudentDetail::create($stud);
                }
            }
            
            Session::flash('success','Students imported successfully'); 
              
        } catch (Exception $e) {
            Session::flash('error','Oops something went wrong');
        }
    }
}
