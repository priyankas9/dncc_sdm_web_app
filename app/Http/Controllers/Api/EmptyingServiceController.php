<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\EmptyingApiRequest;
use App\Http\Requests\Fsm\SupervisoryAssessmentRequest;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\EmployeeInfo;
use App\Models\Fsm\Emptying;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\SupervisoryAssessment;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Fsm\VacutugType;
use App\Models\User;
use DateTimeZone;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use stdClass;

class EmptyingServiceController extends Controller
{
    public function getAssessedApplications()
    {
        try {
            $user = Auth::user();
    
            // Base query
            $query = Application::select(
                'applications.*',
                'buildings.house_number as building_house_number',
                'roads.carrying_width',
                'containments.size as containment_size' // Directly fetch containment size
            )
            ->join('building_info.buildings', function ($join) {
                $join->on(DB::raw('CAST(applications.bin AS VARCHAR)'), '=', 'buildings.bin');
            })
            ->leftJoin('utility_info.roads', 'applications.road_code', '=', 'roads.code') // Join with Road model
            ->leftJoin('fsm.containments', 'applications.containment_id', '=', 'containments.id') // Link containment directly
            ->where('applications.emptying_status', false);
    
            // Apply role-specific filtering
            if ($user->hasRole('Service Provider - Emptying Operator')) {
                $query->where('applications.service_provider_id', $user->service_provider_id);
            }
    
            // Fetch the applications
            $applications = $query->get();
    
            // Add geometry data and image status to each application
            $imageFolder = storage_path('app/public/emptyings/houses');
    
            foreach ($applications as $application) {
                // Fetch geometry data for each application
                $application->geometry = json_decode(
                    $application->buildings()
                        ->select(DB::raw('public.ST_AsGeoJSON(geom) AS coordinates'))
                        ->pluck('coordinates')
                        ->first()
                ) ?? null;
    
                // Check for the existence of an image for each application
                $imageFile = $imageFolder . DIRECTORY_SEPARATOR . $application->bin . '.jpg';
                $application->image_status = file_exists($imageFile) ? "true" : "false";
            }
    
            // Return the response with the applications and their image status
            return response()->json([
                'success' => true,
                'data' => [
                    'applications' => $applications
                ],
                'message' => 'Applications retrieved successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }    
    
     public function getAssessedSupervisoryApplications()
    {
        try {
            $user = Auth::user();
    
            // Base query
            $query = Application::select(
                'applications.*',
                'buildings.house_number as building_house_number',
                'roads.carrying_width',
                'containments.size as containment_size' // Directly fetch containment size
            )
            ->join('building_info.buildings', function ($join) {
                $join->on(DB::raw('CAST(applications.bin AS VARCHAR)'), '=', 'buildings.bin');
            })
            ->leftJoin('utility_info.roads', 'applications.road_code', '=', 'roads.code') // Join with Road model
            ->leftJoin('fsm.containments', 'applications.containment_id', '=', 'containments.id') // Link containment directly
            ->where('applications.supervisory_assessment_status', false);
    
            // Apply role-specific filtering
            if ($user->hasRole('Service Provider - Emptying Operator')) {
                $query->where('applications.service_provider_id', $user->service_provider_id);
            }
    
            // Fetch the applications
            $applications = $query->get();
    
            // Add geometry data and image status to each application
            $imageFolder = storage_path('app/public/emptyings/houses');
    
            foreach ($applications as $application) {
                // Fetch geometry data for each application
                $application->geometry = json_decode(
                    $application->buildings()
                        ->select(DB::raw('public.ST_AsGeoJSON(geom) AS coordinates'))
                        ->pluck('coordinates')
                        ->first()
                ) ?? null;
    
                // Check for the existence of an image for each application
                $imageFile = $imageFolder . DIRECTORY_SEPARATOR . $application->bin . '.jpg';
                $application->image_status = file_exists($imageFile) ? "true" : "false";
            }
    
            // Return the response with the applications and their image status
            return response()->json([
                'success' => true,
                'data' => [
                    'applications' => $applications
                ],
                'message' => 'Applications retrieved successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }   

    public function getTreatmentPlants()
    {
        try {
            // Filter treatment plants by label (FSTP and Co-Treatment Plant)
            $treatmentplants = TreatmentPlant::Operational()
                ->whereIn('type', [3, 4]) // Use labels directly
                ->latest()
                ->select('id', 'name')
                ->get();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    
        return response()->json([
            'success' => true,
            'data' => [
                'treatment-plants' => $treatmentplants
            ],
            'message' => 'Treatment Plants',
        ]);
    }
    
    
    public function getVacutugs(){
        try {
            $vacutugs = VacutugType::where(function($q){
                $q->where("status","=",true)
                    ->where("service_provider_id",'=',Auth::user()->service_provider_id);
            })
                ->orderBy('capacity')->select('id','license_plate_number', 'width', 'capacity')->get();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $vacutugs,
            'message' => 'Vacutugs'
        ];
    }

    public function getDrivers(){
        try {
            $drivers = EmployeeInfo::Active()->where(function($q) {
                    $q->where('employee_type','=','Driver')
                    ->where("service_provider_id",'=',Auth::user()->service_provider_id);
                })
                ->pluck('name','id')
                ->toArray();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $drivers,
            'message' => 'Drivers.'
        ];
    }

    public function getEmptiers(){
        try {
            $emptiers = EmployeeInfo::Active()->where(function($q){
                $q->where('employee_type','=','Cleaner/Emptier')
                ->where("service_provider_id",'=',Auth::user()->service_provider_id);
            })
                ->pluck('name','id')
                ->toArray();
        } catch (\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'data' => $emptiers,
            'message' => 'Emptiers.'
        ];
    }
    
    /**
    * Save an emptying service record along with related data.
    *
    * @param  EmptyingApiRequest  $request
    * @return array
    */
    public function save(EmptyingApiRequest $request)
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
    
        DB::beginTransaction();
        $emptying = null;
    
        try {
            // Validate the request data
            if ($request->validated()) {
                $emptying = Emptying::create($request->all());
                $application = Application::findOrFail($request->application_id);
                // updating containment information
                $containment = Containment::find($application->containment_id);
                $containment->last_emptied_date = $emptying->emptied_date = now();
                $containment->next_emptying_date = now()->addYears(3);
                $containment->emptied_status = true;
                $containment->no_of_times_emptied = $containment->no_of_times_emptied ? 1 : $containment->no_of_times_emptied  + 1;
                $containment->save();
                if ($application->emptying_status && $emptying) {
                    if ($emptying) {
                        $emptying->forceDelete();
                    }
    
                    return response()->json([
                        'status' => false,
                        'message' => "Emptying service is already done for this application."
                    ], 500);
                }
    
                $emptying->service_provider_id = $application->service_provider_id;
                $emptying->user_id = \Auth::user()->id;
                $allowedFileExt = ['jpg', 'jpeg', 'png', 'PNG', 'JPG', 'JPEG'];
    
                // Handle receipt image (required)
                if (!$request->hasFile('receipt_image') || !in_array($request->receipt_image->getClientOriginalExtension(), $allowedFileExt)) {
                    if ($emptying) {
                        $emptying->forceDelete();
                        $application->emptying_status = false;
                        $application->save();
                    }
                    return response()->json([
                        'status' => false,
                        'message' => "Error! Receipt image is required and must be in a valid format."
                    ], 500);
                }
    
                try {
                    $dateTime = now()->setTimezone(new DateTimeZone('Asia/Kathmandu'))->format('Y_m_d_H_i_s');
                    $extension_receipt = $request->receipt_image->getClientOriginalExtension();
                    $filename_receipt = $emptying->id . '_' . $emptying->application_id . '_' . $dateTime . '.' . $extension_receipt;
    
                    $storeReceiptImg = Image::make($request->receipt_image);
                    if ($storeReceiptImg->filesize() > 5 * 1024 * 1024) {
                        $storeReceiptImg->resize(null, 1080, function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode($extension_receipt, 50);
                    }
                    $storeReceiptImg->save(Storage::disk('local')->path('/public/emptyings/receipts/' . $filename_receipt));
                    $emptying->receipt_image = $filename_receipt;
                } catch (\Throwable $th) {
                    if ($emptying) {
                        $emptying->forceDelete();
                        $application->emptying_status = false;
                        $application->save();
                    }
                    return response()->json([
                        'status' => false,
                        'message' => "Error saving receipt image."
                    ], 500);
                }
    
                // Handle house image (optional)
                if ($request->hasFile('house_image') && in_array($request->house_image->getClientOriginalExtension(), $allowedFileExt)) {
                    try {
                        $extension_house = $request->house_image->getClientOriginalExtension();
                        $filename_house = $application->bin . '.' . $extension_house;
    
                        $storeHouseImg = Image::make($request->house_image);
                        if ($storeHouseImg->filesize() > 5 * 1024 * 1024) {
                            $storeHouseImg->resize(null, 1080, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode($extension_house, 50);
                        }
                        $storeHouseImg->save(Storage::disk('local')->path('/public/emptyings/houses/' . $filename_house));
    
                        $emptying->house_image = $filename_house;
                    } catch (\Throwable $th) {
                        \Log::error('House image saving failed: ' . $th->getMessage());
                    }
                } else {
                    // No house image uploaded, set the bin value
                    $emptying->house_image = $application->bin;
                }
    
                $emptying->save();
               
                $application->emptying_status = true;
                $application->save();

            }
    
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($emptying) {
                $emptying->forceDelete();
                $application->emptying_status = false;
                $application->save();
            }
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    
        return [
            'success' => true,
            'message' => 'Emptying service saved successfully.'
        ];
    }
    
    public function saveSupervisoryAssessment(SupervisoryAssessmentRequest $request)
    {
       
    
        DB::beginTransaction();
        $assessment = null;
    
        try {
            // Validate the request data
            if ($request->validated()) {
                $assessment = new SupervisoryAssessment();
                $assessment->application_id = $request->application_id;
                $assessment->holding_number = $request->holding_number;
                $assessment->owner_name = $request->owner_name;
                $assessment->owner_gender = $request->owner_gender;
                $assessment->owner_contact = $request->owner_contact;
                $assessment->containment_type = $request->containment_type;
                $assessment->containment_outlet_connection = $request->containment_outlet_connection;
                $assessment->containment_volume = $request->containment_volume;
                $assessment->road_width = $request->road_width;
                $assessment->distance_from_nearest_road = $request->distance_from_nearest_road;
                $assessment->septic_tank_length = $request->septic_tank_length;
                $assessment->septic_tank_width = $request->septic_tank_width;
                $assessment->septic_tank_depth = $request->septic_tank_depth;
                $assessment->number_of_pit_rings = $request->number_of_pit_rings;
                $assessment->pit_diameter = $request->pit_diameter;
                $assessment->pit_depth = $request->pit_depth;
                $assessment->appropriate_desludging_vehicle_size = $request->appropriate_desludging_vehicle_size;
                $assessment->number_of_trips = $request->number_of_trips;
                $assessment->confirmed_emptying_date = $request->confirmed_emptying_date;
                $assessment->advance_paid_amount = $request->advance_paid_amount;
                
                // Save the assessment
                $assessment->save();
                $application = Application::where('id', $request->application_id)->first();
                $application->supervisory_assessment_status = true;
                $application->save();
            }
    
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($assessment) {
                $assessment->forceDelete();
                $assessment->emptying_status = false;
                $assessment->save();
            }
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    
        return [
            'success' => true,
            'message' => 'Supervisory assessment saved successfully.'
        ];
    }

}
