<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = json_decode(file_get_contents('https://raw.githubusercontent.com/bahiirwa/uganda-APIs/master/districts.json'), TRUE);

        if(isset($districts[0]['districts']) && !empty($districts[0]['districts'])){
            foreach ($districts[0]['districts'] as $key => $value) {
                District::updateOrCreate(['short_code' => $value['id']],[
                    'short_code' => $value['id'],
                    'name' => $value['name'],
                ]);
            }
        }
    }
}
