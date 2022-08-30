<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $table = 'generalsettings';

    protected $fillable = [
        'current_study_year',
        'current_study_term',
        'contact_one',
        'contact_two',
        'contact_email',
        'contact_address',
        'term_ends_on',
        'next_term_begins_on',
    ];
}
