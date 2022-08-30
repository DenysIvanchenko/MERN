<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nationality;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nationalities = json_decode(file_get_contents('https://raw.githubusercontent.com/Dinuks/country-nationality-list/master/countries.json'), TRUE);

        if(isset($nationalities) && count($nationalities) > 0){
            foreach ($nationalities as $key => $value) {
                Nationality::updateOrCreate(['short_code' => $value['alpha_3_code']],[
                    'short_code' => $value['alpha_3_code'],
                    'name' => $value['en_short_name'],
                ]);
            }
        }
    }
}
