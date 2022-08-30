<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Auth;
use Carbon\Carbon;
use App\Models\HmComment;

class HmCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hm_comments = [
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'old_curriculam',
                'average_marks_from' => '0',
                'average_marks_to' => '19',
                'comment' => 'WORK HARD',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'old_curriculam',
                'average_marks_from' => '20',
                'average_marks_to' => '39',
                'comment' => 'WORK HARD',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'old_curriculam',
                'average_marks_from' => '40',
                'average_marks_to' => '59',
                'comment' => 'Tried',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'old_curriculam',
                'average_marks_from' => '60',
                'average_marks_to' => '79',
                'comment' => 'Fair',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'old_curriculam',
                'average_marks_from' => '80',
                'average_marks_to' => '100',
                'comment' => 'Aim Higher',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '0',
                'average_marks_to' => '4',
                'comment' => 'WORK HARD',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '5',
                'average_marks_to' => '9',
                'comment' => 'Tried',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '10',
                'average_marks_to' => '14',
                'comment' => 'Fair',
            ],
            [
                'study_level' => 'ordinary_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '15',
                'average_marks_to' => '20',
                'comment' => 'Aim Higher',
            ],
            [
                'study_level' => 'advanced_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '0',
                'average_marks_to' => '4',
                'comment' => 'WORK HARD',
            ],
            [
                'study_level' => 'advanced_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '5',
                'average_marks_to' => '9',
                'comment' => 'Tried',
            ],
            [
                'study_level' => 'advanced_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '10',
                'average_marks_to' => '14',
                'comment' => 'Fair',
            ],
            [
                'study_level' => 'advanced_level',
                'curriculam' => 'new_curriculam',
                'average_marks_from' => '15',
                'average_marks_to' => '20',
                'comment' => 'Aim Higher',
            ]
        ];
        
        foreach ($hm_comments as $value) {
            HmComment::create([
                'study_level' => $value['study_level'],
                'curriculam' => $value['curriculam'],
                'average_marks_from' => $value['average_marks_from'],
                'average_marks_to' => $value['average_marks_to'],
                'comment' => $value['comment'],
                'comments_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        dd('Commnets added');
    }
}
