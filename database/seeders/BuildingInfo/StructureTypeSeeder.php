<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\StructureType;
use DB;

class StructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [ 1 , 'Pucca' ],
            [ 2 , 'Tin' ],
            [ 3 , 'Kutcha' ],
            [ 4 , 'Semi Pucca' ],
            [ 5 , 'Other' ],
            [ 6 , 'N/A']


        );
     
     foreach ($types as $type) {
    
         $existStructureType =  DB::table('building_info.structure_types')
                 ->where('type', $type[1])
                 ->first();
         if(!$existStructureType) {
            StructureType::insert([
             'id' => $type[0],
             'type' => $type[1],
         ]);
         }
     }

    }
}
