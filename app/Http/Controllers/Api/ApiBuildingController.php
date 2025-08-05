<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\StructureType;
use App\Models\BuildingInfo\BuildingSurvey;
use App\Models\BuildingInfo\Building;
use App\Models\LayerInfo\Ward;
use App\Models\UtilityInfo\Roadline;
use App\Models\BuildingInfo\FunctionalUse;
use App\Models\BuildingInfo\UseCategory;
use App\Models\LayerInfo\Lic;
use App\Models\BuildingInfo\WaterSource;
use App\Models\UtilityInfo\WaterSupplys;
use App\Models\BuildingInfo\SanitationSystem;
use App\Models\BuildingInfo\SanitationSystemTechnology;
use App\Models\BuildingInfo\BuildContain;
use App\Models\UtilityInfo\SewerLine;
use App\Models\Fsm\Containment;
use App\Models\Fsm\Ctpt;
use App\Models\UtilityInfo\Drain;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Fsm\BuildToilet;
use App\Helpers\KeywordMatcher;
use App\Services\BuildingInfo\BuildingStructureService;
use App\Http\Requests\BuildingInfo\BuildingRequest;


 

class ApiBuildingController extends Controller
{
    protected BuildingStructureService $buildingStructureService;
    public function __construct(BuildingStructureService $buildingStructureService)
    {
        $this->buildingStructureService = $buildingStructureService;
    }
   public function getBuildingFormAPI($id)
    {
        $building = Building::findOrFail($id);
        if (!empty($building->Owners)) {
        $owner = $building->Owners;
        $building->owner_name = $owner->owner_name ?? '';
        $building->owner_gender = $owner->owner_gender ?? '';
        $building->owner_contact = $owner->owner_contact ?? '';
        $building->nid = $owner->nid ?? '';
        }
   
        $building->main_building = $building->building_associated_to ? false : true;

        $structure_type = StructureType::orderBy('type', 'asc')->pluck('type', 'id')->map('ucwords')->toArray();
        $water_source_raw = WaterSource::orderBy('source', 'asc')->pluck('source', 'id')->toArray();
        $water_source = $this->moveOthersToEnd(array_map('ucwords', $water_source_raw));
        $building->lic_status = $building->lic_id ? "1" : "0";

        $toiletConnection = SanitationSystem::whereNotIn('id', [9, 10, 12])->pluck('sanitation_system', 'id')->toArray();
        $defecationPlace = SanitationSystem::whereIn('id', [9, 10, 12])->pluck('sanitation_system', 'id')->toArray();

        $building->ctpt_name = $building->sharedToilets->pluck('id')->toArray() ?? [];

        $buildingBin = Building::whereNull('building_associated_to')->whereNull('deleted_at')->distinct('bin')->pluck('bin', 'bin')->toArray();
        $bin = BuildContain::whereNull('deleted_at')->distinct('bin')->pluck('bin', 'bin')->toArray();

        $ward = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();

        $road_code = Roadline::get(['code', 'name'])->mapWithKeys(function ($item) {
            return [$item->code => ($item->name ? $item->code . ' - ' . $item->name : $item->code)];
        })->toArray();

        $sewer_code = SewerLine::whereNull('deleted_at')->pluck('code', 'code')->toArray();

        $containment_id = Containment::whereNull('deleted_at')->distinct('id')->pluck('id', 'id')->toArray();

        $ctpt_raw = Ctpt::where('status', true)
            ->where('type', 'Community Toilet')
            ->get(['id', 'name'])
            ->mapWithKeys(fn($item) => [$item->id => ($item->name ? $item->id . ' - ' . $item->name : $item->id)])
            ->toArray();

        $capitalizedctpt = array_map(fn($value) => ucwords($value), $ctpt_raw);

        $drain_code = Drain::pluck('code', 'code')->toArray();

        $licNames = Lic::whereNull('deleted_at')->orderBy('community_name')->pluck('community_name', 'id')->toArray();

        $models = UseCategory::select('id', 'name', 'functional_use_id')->orderBy('name')->get();
        $use_category_id = [];
        foreach ($models as $model) {
            $use_category_id[$model->functional_use_id][$model->id] = $model->name;
        }

        $functional_use = FunctionalUse::orderBy('name')->pluck('name', 'id')->toArray();

        $waterSupply = WaterSupplys::pluck('code', 'code')->toArray();


        $fields = [
            [
                "label" => __('Owner Name'),
                "inputType" => 'text',
                "name" => 'owner_name',
                "value" => $building->owner_name,
                "placeholder" => __('Owner Name'),
                "required" => true,
            ],
            [
                "label" => __('Owner NID'),
                "inputType" => 'text',
                "name" => 'nid',
                "value" => $building->nid,
                "placeholder" => __('Owner NID'),
                "required" => false,
            ],
            [
                "label" => __('Owner Gender'),
                "inputType" => 'select',
                "name" => 'owner_gender',
                "value" => $building->owner_gender,
                "options" => ['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')],
                "placeholder" => __('Select Gender'),
                "required" => false,
            ],
            [
                "label" => __('Owner Contact'),
                "inputType" => 'text',
                "name" => 'owner_contact',
                "value" => $building->owner_contact,
                "placeholder" => __('Owner Contact'),
                "required" => false,
            ],
            
            [
                "label" => __('Main Building'),
                "inputType" => 'checkbox',
                "name" => 'main_building',
                "value" => $building->main_building,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "required" => false,
            ],
            [
                "label" => __('BIN of Main Building'),
                "inputType" => 'select',
                "name" => 'building_associated_to',
                "value" => $building->building_associated_to,
                "options" => $bin,
                "placeholder" => __('BIN of Main Building'),
                "required" => false,
            ],
            [
                "label" => __('Ward Number'),
                "inputType" => 'select',
                "name" => 'ward',
                "value" => $building->ward,
                "options" => $ward,
                "placeholder" => __('Ward Number'),
                "required" => true,
            ],
             [
                "label" => __('Road Code'),
                "inputType" => 'select',
                "name" => 'road_code',
                 "value" => $road_code[$building->road_code] ?? null, 
                "options" => $road_code,
                "placeholder" => __('Select Road Code'),
                "required" => false,
            ],
            [
                "label" => __('Road Name'),
                "inputType" => 'text',
                "name" => 'road_name',
                "value" => $building->road_name,
                "placeholder" => __('Road Name'),
                "required" => false,
            ],
             [
                "label" => __('Block Number'),
                "inputType" => 'text',
                "name" => 'block_number',
                "value" => $building->block_number,
                "placeholder" => __('Block Number'),
                "required" => false,
            ],
             [
                "label" => __('House Number'),
                "inputType" => 'text',
                "name" => 'house_number',
                "value" => $building->house_number,
                "placeholder" => __('House Number'),
                "required" => false,
            ],
              [
                "label" => __('House Locality/Address'),
                "inputType" => 'text',
                "name" => 'house_locality',
                "value" => $building->house_locality,
                "placeholder" => __('House Locality/Address'),
                "required" => false,
            ],
               [
                "label" => __('Tax Code/Holding ID'),
                "inputType" => 'text',
                "name" => 'tax_code',
                "value" => $building->tax_code,
                "placeholder" => __('Tax Code/Holding ID'),
                "required" => false,
            ],
            [
                "label" => __('Structure Type'),
                "inputType" => 'select',
                "name" => 'structure_type_id',
                "value" => $building->structure_type_id,
                "options" => $structure_type,
                "placeholder" => __('Select Structure Type'),
                "required" => true,
            ],
             [
                "label" => __('Surveyed Date'),
                "inputType" => 'date',
                "name" => 'structure_type_id',
                "value" => Carbon::parse($building->surveyed_date)->format('Y-m-d'),
                "placeholder" => __('Surveyed Date'),
                "required" => false,
            ],
             [
                "label" => __('Construction Date'),
                "inputType" => 'date',
                "name" => 'construction_year',
                "value" => Carbon::parse($building->construction_year)->format('Y-m-d'),
                "placeholder" => __('Construction Date'),
                "required" => true,
            ],
                [
                "label" => __('Number of Floors'),
                "inputType" => 'text',
                "name" => 'floor_count',
                "value" => $building->floor_count,
                "placeholder" => __('Number of Floors'),
                "required" => true,
            ],
             [
                "label" => __('Functional Use'),
                "inputType" => 'select',
                "name" => 'functional_use_id',
                "value" => $building->functional_use_id,
                "options" => $functional_use,
                "placeholder" => __('Select Functional Use'),
                "required" => false,
            ],
             [
                "label" => __('Use Category'),
                "inputType" => 'select',
                "name" => 'use_category_id',
                "value" => $building->use_category_id,
                "options" => $use_category_id,
                "placeholder" => __('Select Use Category'),
                "required" => false,
            ],
            [
                "label" => __('Office or Business Name'),
                "inputType" => 'text',
                "name" => 'office_business_name',
                "value" => $building->office_business_name,
                "placeholder" => __('Office or Business Name'),
                "required" => true,
            ],
             [
                "label" => __('Number of Households'),
                "inputType" => 'number',
                "name" => 'household_served',
                "value" => $building->household_served,
                "placeholder" => __('Number of Households'),
                "required" => true,
            ],
             [
                "label" => __('Male Population'),
                "inputType" => 'number',
                "name" => 'male_population',
                "value" => $building->male_population,
                "placeholder" => __('Male Population'),
                "required" => true,
            ],
             [
                "label" => __('Female Population'),
                "inputType" => 'number',
                "name" => 'female_population',
                "value" => $building->female_population,
                "placeholder" => __('Female Population'),
                "required" => true,
            ],
              [
                "label" => __('Other Population'),
                "inputType" => 'number',
                "name" => 'other_population',
                "value" => $building->other_population,
                "placeholder" => __('Other Population'),
                "required" => true,
            ],
              [
                "label" => __('Population of Building'),
                "inputType" => 'number',
                "name" => 'population_served',
                "value" => $building->population_served,
                "placeholder" => __('Population of Building'),
                "required" => true,
            ],
              [
                "label" => __('Differently Abled Male Population'),
                "inputType" => 'number',
                "name" => 'diff_abled_male_pop',
                "value" => $building->diff_abled_male_pop,
                "placeholder" => __('Differently Abled Male Population'),
                "required" => true,
            ],
              [
                "label" => __('Differently Abled Female Population'),
                "inputType" => 'number',
                "name" => 'diff_abled_female_pop',
                "value" => $building->diff_abled_female_pop,
                "placeholder" => __('Differently Abled Female Population'),
                "required" => true,
            ],
               [
                "label" => __('Differently Abled Other Population'),
                "inputType" => 'number',
                "name" => 'diff_abled_others_pop',
                "value" => $building->diff_abled_others_pop,
                "placeholder" => __('Differently Abled Other Population'),
                "required" => true,
            ],
            [
                "label" => __('Is Low Income House'),
                "inputType" => 'select',
                "name" => 'low_income_hh',
                "value" => $building->low_income_hh,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "placeholder" => __('Is Low Income House'),
                "required" => true,
            ],
              [
                "label" => __('Located In LIC'),
                "inputType" => 'select',
                "name" => 'lic_status',
                "value" => $building->lic_status,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "placeholder" => __('Located In LIC'),
                "required" => true,
            ],
            [
                "label" => __('LIC Name'),
                "inputType" => 'select',
                "name" => 'lic_id',
                "value" => $building->lic_id,
                "options" => $licNames,
                "placeholder" => __('LIC Name'),
                "required" => true,
            ],
            [
                "label" => __('Main Drinking Water Source'),
                "inputType" => 'select',
                "name" => 'water_source_id',
                "value" => $building->water_source_id,
                "options" => $water_source,
                "placeholder" => __('Main Drinking Water Source'),
                "required" => true,
            ],
              [
                "label" => __('Water Supply Customer ID'),
                "inputType" => 'text',
                "name" => 'water_customer_id',
                "value" => $building->water_customer_id,
                "placeholder" => __('Water Supply Customer ID'),
                "required" => true,
            ],
             [
                "label" => __('Water Supply Pipe Line Code'),
                "inputType" => 'select',
                "name" => 'watersupply_pipe_code',
                "value" => $building->watersupply_pipe_code,
                "options" => $waterSupply,
                "placeholder" => __('Water Supply Pipe Line Code'),
                "required" => false,
            ],
              [
                "label" => __('Water Bill Payment Status'),
                "inputType" => 'select',
                "name" => 'water_status',
                "value" => $building->water_status,
                "options" => ['Yes' => __('Yes'), 'No' => __('No'), 'NA' => __('NA')],
                "placeholder" => __('Water Bill Payment Status'),
                "required" => true,
            ],
                [
                "label" => __('Well in Premises'),
                "inputType" => 'select',
                "name" => 'well_presence_status',
                "value" => $building->well_presence_status,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "placeholder" => __('Well in Premises'),
                "required" => true,
            ],
                 [
                "label" => __('Distance of Well from Closest Containment (m)'),
                "inputType" => 'number',
                "name" => 'distance_from_well',
                "value" => $building->distance_from_well,
                "placeholder" => __('Distance of Well from Closest Containment (m)'),
                "required" => true,
            ],
               [
                "label" => __('SWM Customer ID'),
                "inputType" => 'text',
                "name" => 'swm_customer_id',
                "value" => $building->swm_customer_id,
                "placeholder" => __('SWM Customer ID'),
                "required" => false,
            ],
                 [
                "label" => __('Presence of Toilet'),
                "inputType" => 'select',
                "name" => 'toilet_status',
                "value" => $building->toilet_status,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "placeholder" => __('Presence of Toilet'),
                "required" => true,
            ],
                 [
                "label" => __('Defecation Place'),
                "inputType" => 'select',
                "name" => 'defecation_place',
                "value" => $building->defecation_place,
                "options" =>$defecationPlace,
                "placeholder" => __('Defecation Place'),
                "required" => true,
            ],
                   [
                "label" => __('Community Toilet Name'),
                "inputType" => 'select',
                "name" => 'ctpt_name',
                "value" => $building->ctpt_name,
                "options" =>$capitalizedctpt,
                "placeholder" => __('Community Toilet Name'),
                "required" => true,
            ],
                   [
                "label" => __('Number of Toilets'),
                "inputType" => 'number',
                "name" => 'toilet_count',
                "value" => $building->toilet_count,
                "placeholder" => __('Number of Toilets'),
                "required" => true,
            ],
             [
                "label" => __('Households with Private Toilet'),
                "inputType" => 'number',
                "name" => 'household_with_private_toilet',
                "value" => $building->household_with_private_toilet,
                "placeholder" => __('Households with Private Toilet'),
                "required" => false,
            ],
              [
                "label" => __('Population with Private Toilet'),
                "inputType" => 'number',
                "name" => 'population_with_private_toilet',
                "value" => $building->population_with_private_toilet,
                "placeholder" => __('Population with Private Toilet'),
                "required" => false,
            ],
             [
                "label" => __('Toilet Connection'),
                "inputType" => 'select',
                "name" => 'sanitation_system_id',
                "value" => $building->sanitation_system_id,
                "options" =>$toiletConnection,
                "placeholder" => __('Toilet Connection'),
                "required" => true,
            ],
               [
                "label" => __('BIN of Pre-Connected Building'),
                "inputType" => 'select',
                "name" => 'build_contain',
                "value" => $building->build_contain,
                "options" =>$bin,
                "placeholder" => __('BIN of Pre-Connected Building'),
                "required" => true,
            ],
                 [
                "label" => __('Building Accessible to Desludging Vehicle'),
                "inputType" => 'select',
                "name" => 'desludging_vehicle_accessible',
                "value" => $building->desludging_vehicle_accessible,
                "options" => ['true' => __('Yes'), 'false' => __('No')],
                "placeholder" => __('Building Accessible to Desludging Vehicle'),
                "required" => false,
            ],
            [
                "label" => __('Sewer Code'),
                "inputType" => 'select',
                "name" => 'sewer_code',
                "value" => $building->sewer_code,
                "options" => $sewer_code,
                "placeholder" => __('Sewer Code'),
                "required" => false,
            ],
                 [
                "label" => __('WASA Bill Payment Status'),
                "inputType" => 'select',
                "name" => 'wasa_status',
                "value" => $building->wasa_status,
                "options" => ['Yes' => __('Yes'), 'No' => __('No'), 'NA' => __('NA')],
                "placeholder" => __('WASA Bill Payment Status'),
                "required" => false,
            ],
              [
                "label" => __('Drain Code'),
                "inputType" => 'select',
                "name" => 'drain_code',
                "value" => $building->drain_code,
                "options" => $drain_code,
                "placeholder" => __('Drain Code'),
                "required" => false,
            ],

            [
                    "label" => __('Building Footprint (KML File)'),
                    "inputType" => 'file',
                    "name" => 'geom',
                    "value" => null, // File inputs cannot have preset values
                    "required" => false,
                    "hint" => __('KML File size should not be more than 1MB'),
                    "accept" => '.kml', // Restrict accepted file type to KML
            ],
                [

                "label" => __('WASA Bill No.'),
                "inputType" => 'text',
                "name" => 'wasa_bill_no',
                "value" => $building->wasa_bill_no,
                "placeholder" => __('WASA Bill No.'),
                "required" => false,
            ],
            [
                "label" => __('House Image'),
                "inputType" => 'file', 
                "name" => 'house_image',
                "value" => null, 
                "required" => false,
                "hint" => __('Image (JPG, JPEG) size should not be more than 5MB'),
                "accept" => 'image/jpeg', 
            ]

        ];

        return response()->json([
            'status' => true,
            'fields' => $fields,
        ]);
    }

    private function moveOthersToEnd(array $arr): array
    {
        $othersKey = array_search('Others', $arr);
        if ($othersKey !== false) {
            $othersValue = $arr[$othersKey];
            unset($arr[$othersKey]);
            $arr['others'] = $othersValue;
        }
        return $arr;
    }



public function updateBuildingDataApi( Request $request, $id)
{
     return $this->buildingStructureService->updateBuildingData($request, $id, 'api');
}


}