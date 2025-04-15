<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;
use App\Enums\TreatmentPlantType;
use Illuminate\Http\Request;
use App\Services\Fsm\DesludgingScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DesludgingScheduleController extends Controller
{
    protected DesludgingScheduleService $desludgingScheduleService;
    public function __construct(DesludgingScheduleService $desludgingScheduleService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Hotspot Identifications', ['only' => ['index']]);
        $this->middleware('permission:View Hotspot Identification', ['only' => ['show']]);
        $this->middleware('permission:Add Hotspot Identification', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Hotspot Identification', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Hotspot Identification', ['only' => ['destroy']]);
        $this->middleware('permission:Export Hotspot Identification', ['only' => ['export']]);
        $this->desludgingScheduleService = $desludgingScheduleService;
    }
    public function index()
    {
        $page_title = "Desludging Schedule";
        $serviceProvider = ServiceProvider::pluck("company_name", "id")->toArray();
        return view('fsm.desludging-schedule.index', compact('page_title', 'serviceProvider'));
    }
    public function getData(Request $request)
    {   
        return $this->desludgingScheduleService->getAllData($request);
    }
    public function getServiceProviderData(Request $request)
    {
        $serviceprovider = ServiceProvider::pluck("company_name", "id")->toArray();
        return view('fsm.desludging-schedule.confirm', compact('page_title', 'serviceProvider'));
    }
  
    public function fetchSiteSettings()
    {
        $site_settings = DB::table('public.sdm_sitesettings')->whereNull('deleted_at')->get();
        return $site_settings;
       
    }
    public function getContainments()
    {
        // fetch id of all containments that meet criteria for setting emptying date
        //  must not be emptied through the service delivery
        // does not pay WASA bill
        // status is either 0 or 4
        $fetch_id = "SELECT DISTINCT ON (final_result.id) final_result.*
        FROM (
            SELECT 
                c.id
            FROM fsm.containments c
            LEFT JOIN building_info.build_contains bc 
                ON bc.containment_id = c.id 
                AND bc.deleted_at IS NULL 
                AND bc.bin IS NOT NULL 
                AND bc.containment_id IS NOT NULL
            LEFT JOIN building_info.buildings b 
                ON b.bin = bc.bin 
                AND b.deleted_at IS NULL
            WHERE   
			    b.wasa_status = false
			AND c.emptied_status = false
			AND (c.status = 0 OR c.status = 4)
			AND
            c.deleted_at IS NULL
        ) final_result
        ORDER BY final_result.id;";

        $containment_id = DB::select($fetch_id);
        // re-query all fetched ID's and order them by first priority then distance from FSTP
        $containments = Containment::whereIN('id',array_column($containment_id,'id'))->ORDERBY('priority')->ORDERBY('fstp_distance')->get();
        return $containments;

    }
    public function setPriority($id)
    {
        // fetch all containments passed from $id
        if(empty($id))
        {
            $containments = Containment::whereNULL('deleted_at')->get();
        }
        else
        {
            $containments = Containment::find($id);
        }
        $updates = [];
        foreach ($containments as $containment) {
            $constructionDate = Carbon::parse($containment->construction_date);
            $lastEmptiedDate = $containment->last_emptied_date ? Carbon::parse($containment->last_emptied_date) : null;
            // storing values already in db for comparison and update
            $old_priority = $containment->priority;
            $oldfstp_distance = $containment->fstp_distance;
            $oldfstp_id = $containment->closest_fstp_id;
            $now = Carbon::now();
            // Determine priority
            // if containment has not been emptied 
            if (is_null($lastEmptiedDate)) {
                // if we dont have containment construction date, we assume highest priority
                if (is_null($constructionDate)) {
                    $containment->priority = 1;
                }
                else
                {
                    // if construction date present, we use same logic as emptying date
                    $yearsSinceConstruction = $constructionDate->diffInYears($now);
                    if ($yearsSinceConstruction > 3) {
                        $containment->priority = 1;
                    } elseif ($yearsSinceConstruction > 1  && $yearsSinceConstruction <= 3) {
                        $containment->priority = 2;
                    } else {
                        $containment->priority = 3;
                    }
                }
            } else {
                // priority logic according to last emptied
                $yearsSinceLastEmptied = $lastEmptiedDate->diffInYears($now);
                if ($yearsSinceLastEmptied > 3) {
                    $containment->priority = 1;
                } elseif ($yearsSinceLastEmptied > 1 && $yearsSinceLastEmptied <= 3) {
                    $containment->priority = 2;
                } else {
                    $containment->priority = 3;
                }
            }
            
           
            // Fetch all FSTPs with specified conditions
            $treatment_plant_types = ['FSTP', 'Co-Treatment Plant'];

            // Reverse map text values to IDs
            $typeIds = array_keys(array_filter(TreatmentPlantType::toEnumArray(), function ($label) use ($treatment_plant_types) {
                return in_array($label, $treatment_plant_types);
            }));

            $fstps = TreatmentPlant::whereIn('type', $typeIds)
                ->where('status', true)
                ->get();
            if ($fstps->count() == 0) {
                return response()->json(['status' => 'error', 'message' => 'No FSTP or Co-Treatment Plants registered. Please register treatment plant and try again.']);
            }
            elseif ($fstps->count() == 1) {
                $fstp = $fstps->first();
                $distanceResult = DB::selectOne("
                SELECT ROUND(ST_Distance(
                    ST_Transform(c.geom, 32646),
                    ST_Transform(f.geom, 32646)
                )::numeric , 6) AS distance
                FROM fsm.containments c, fsm.treatment_plants f
                WHERE c.id = ? AND f.id = ?
                ", [$containment->id, $fstp->id]);
                $containment->fstp_distance = $distanceResult->distance;
                $containment->closest_fstp_id = $fstp->id;
            } else {
                $distances = [];
                foreach ($fstps as $fstp) {
                    $distanceResult = DB::selectOne("
                    SELECT ROUND(ST_Distance(
                    ST_Transform(c.geom, 32646),
                    ST_Transform(f.geom, 32646)
                    )::numeric , 6) AS distance
                    FROM fsm.containments c, fsm.treatment_plants f
                    WHERE c.id = ? AND f.id = ?
                    ", [$containment->id, $fstp->id]);
                    $distances[$fstp->id] = $distanceResult->distance;
                }
                if (!empty($distances)) {
                    // if multiple FSTP, select the fstp closest
                    $containment->fstp_distance = min($distances);
                    $containment->closest_fstp_id = array_search($containment->fstp_distance, $distances);
                } else {
                    $containment->fstp_distance = null;
                    $containment->closest_fstp_id = null;
                }
            }
            if ($oldfstp_distance != $containment->fstp_distance || $oldfstp_id != $containment->closest_fstp_id || $old_priority != $containment->priority) {
                $updates[] = [
                    'id' => $containment->id,
                    'fstp_distance' =>  $containment->fstp_distance,
                    'closest_fstp_id' => $containment->closest_fstp_id,
                    'priority' =>  $containment->priority   
                ]; 
            } 
        } 
        if (!empty($updates)) {
            foreach (array_chunk($updates, 500) as $chunk) {
                foreach ($chunk as $update) {
                    Containment::where('id', $update['id'])->update([
                        'priority' => $update['priority'],
                        'fstp_distance' => $update['fstp_distance'],
                        'closest_fstp_id' => $update['closest_fstp_id']
                    ]);
                    
                }
            }
        }

    }
    public function set_emptying_date()
    {
       
        // try{
        // fetch all values required from site settings
        $site_settings = $this->fetchSiteSettings()->keyBy('name');
        $containments = $this->getContainments();
        $priorityCount = $containments->whereNotNull('priority')->count();
        if ($priorityCount === 0) {
            $this->setPriority(null);
        }
        $today = Carbon::now(); // Get today's date
        $start_date = Carbon::createFromFormat('Y-m-d', $site_settings['Schedule Desludging Start Date']->value)->format('Y-m-d');
        if($today->diff($start_date)->invert == true)
        {
            $start_date = $today->addDays($site_settings['Schedule Regeneration Period']->value)->format('Y-m-d');  
        }
        $counter = 0;
        $weekends = explode(',', $site_settings['Weekend']->value);
        $holiday_dates = explode(',', $site_settings['Holiday Dates']->value);
        $containmentupdates = [];
        do{
            
            $set_date = $start_date;
             // Skip weekends and holidays efficiently
             if (in_array( date("l", strtotime($set_date)), $weekends) || 
                    in_array($set_date, $holiday_dates)) {
                    $set_date = Carbon::createFromFormat('Y-m-d',$set_date)->addDay()->format('Y-m-d');
            }
            $remaining_trips = $this->trips_allocated($set_date);
           
            $selected_containments = $this->fetchContainmentsInRange($counter, $counter + (int)$remaining_trips, $containments);
            foreach ($selected_containments as $containment) {
                $containmentupdates[] = [
                    'id' => $containment->id,
                    'next_emptying_date' => $start_date
                ]; 
            }
            $counter += $remaining_trips;
            $start_date = Carbon::createFromFormat('Y-m-d',$set_date)->addDay()->format('Y-m-d');// Move to the next day after processing

        }while ($counter <= count($containments));
        if (!empty($containmentupdates)) {
            foreach (array_chunk($containmentupdates, 500) as $chunk) {
                foreach ($chunk as $update) {
                    Containment::where('id', $update['id'])->update([
                        'next_emptying_date' => $update['next_emptying_date']
                    ]);
                    
                }
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Next emptying dates have been successfully regenerated.']);
        // }
        // catch (\Exception $e) {
        //     \Log::error('Error in set_emptying_date: ', ['error' => $e->getMessage()]);
        //     return response()->json(['status' => 'error', 'message' => 'Failed to set emptying date. Please try again.']);
        // }
}

    public function fetchContainmentsInRange($start, $end, $containments)
    {
        return $containments->slice($start , $end - $start );
    }

    public function test()
    {
        $this->set_emptying_date();
        $this->setPriority(null);
        dd("doint");
    }
    public function trips_allocated_range ($start_date, $end_date)
    {
        $current_date = $start_date;
        $trips_allocated = [];
        while ($current_date){
            $remaining_trips = $this->trips_allocated($current_date);
            $trips_allocated[$current_date] = $remaining_trips;
            $current_date = Carbon::createFromFormat('Y-m-d', $current_date)->addDay()->format('Y-m-d');
            if ($current_date > $end_date) {
                break;
            }
        }
    }
    public function trips_allocated($date)
    {
        // count total number of applications with emptying status false that is set for the passed date
        $confirmed_applications = Application::where('proposed_emptying_date',$date)->count();
        // pluck corresponding containment ids
        $confirmed_application_ids = Application::where('emptying_status',false)->pluck('containment_id');
        // count total no. of containments auto scheduled through service chain excluding those already confirmed in application page
        $auto_scheduled_applications = Containment::where('emptied_status','true')->whereNOTIN('id',$confirmed_application_ids)->where('next_emptying_date',$date)->count();
        // ->where('status','3') need to add this later, count only those that matches status  emptied & scheduled again through system itself
        // return sum of both i.e. total trips that have been pre-alloted
        $daily_trip_capacity = $this->fetchSiteSettings()->keyBy('name')['Trip Capacity Per Day']->value;
        $remaining_trips = (integer)$daily_trip_capacity - (integer)$auto_scheduled_applications - (integer)$confirmed_applications;
        return $remaining_trips;       

    }
    public function export(Request $request)
    {
        $data = $request->all();
        return $this->desludgingScheduleService->download($data);
    }
    // public function submitApplication(Request $request)
    // {   
      
    //     // Validate the incoming data
    //     $request->validate([
    //         'applicant_name' => 'required|string|max:255',
    //         'applicant_gender' => 'required|string|max:50',
    //         'applicant_contact' => 'required|string|max:20',
    //         'service_provider_id' => 'required',
    //         'proposed_emptying_date' => 'nullable|date',
    //         //'supervisory_assessment_date' => 'nullable|date|before:proposed_emptying_date',
    //     ]);

    //     // try {
    //         // Save the application
    //         $application = new Application();
    //         $application->bin = $request->bin;
    //         $application->customer_name = $request->customer_name ?? null;
    //         $application->customer_gender = $request->customer_gender ?? null;
    //         $application->customer_contact = $request->customer_contact ?? null;
    //         $application->applicant_name = $request->applicant_name ?? null;
    //         $application->applicant_gender = $request->applicant_gender ?? null;
    //         $application->applicant_contact = $request->applicant_contact ?? null;
    //         $application->proposed_emptying_date = $request->proposed_emptying_date ?? null;
    //         $application->supervisory_assessment_date = $request->supervisory_assessment_date ?? null;
    //         $application->service_provider_id = $request->service_provider_id ?? null;
           
    //         $application->save();

    //         // Get containment_id from the build_contains table using the provided bin
    //         $containment = DB::table('building_info.build_contains')
    //             ->where('bin', $request->bin)
    //             ->first();
    //             if (!$containment) {
    //                 return response()->json(['status' => 'error', 'message' => 'Containment not found for the provided bin.']);
    //             }
    //         $nextEmptyingDate = Containment::where('id', $containment->containment_id)
    //             ->get();
            
    //         // Determine the correct status
    //         $EmptyingDate = $nextEmptyingDate->next_emptying_date ;
    //        dd($EmptyingDate);
    //         $proposedEmptyingDate = $request->proposed_emptying_date ?? null;
    //         $status = ($EmptyingDate === $proposedEmptyingDate) ? 1 : 2;
    //             dd($status,$proposedEmptyingDate,$EmptyingDate);
    //         // Update containment status
    //         DB::table('fsm.containments')
    //             ->where('id', $containment->containment_id)
    //             ->update(['status' => $status]);

    //             return response()->json(['status' =>'success', 'message' =>'Application submitted successfully.']);
    //     // } catch (\Exception $e) {
    //     //     \Log::error('Error submitting application: ', ['error' => $e->getMessage()]);
    //     //     return response()->json(['status' => 'error', 'message' => 'Failed to submit application. Please try again.']);
    //     // }
    // }
    public function disagreeEmptying(Request $request, $bin)
    {   
    // Find the containment_id from the build_contains table using the bin
            $buildContain = DB::table('building_info.build_contains')
                ->where('bin', $bin)
                ->first();

            if (!$buildContain) {
                return response()->json(['error' => 'Containment not found for the provided bin'], 404);
            }

            // Find the corresponding containment in the fsm.containments table
            $containment = DB::table('fsm.containments')
                ->where('id', $buildContain->containment_id)
                ->first();

            if (!$containment) {
                return response()->json(['error' => 'Containment not found'], 404);
            }

            $message = null;
            $newStatus = $containment->status; // default no change

            if (is_null($containment->status) || $containment->status == 0) {
                $newStatus = 4;
                $message = "If you disagree once, your request will be noted, but you can still rejoin the desludging schedule during the regeneration process";
            } 
            elseif ($containment->status == 4) {
                $newStatus = 5;
                $message = "If you disagree twice, you will be permanently removed from the desludging schedule.";
            }

            // Only update if there's a change
            if ($newStatus !== $containment->status) {
                DB::table('fsm.containments')
                    ->where('id', $buildContain->containment_id)
                    ->update(['status' => $newStatus]);
            }

            // Return response
            return response()->json(['success' => $message ?? "No change in status."]);
    }

    public function submitApplication(Request $request)
    {
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_gender' => 'required|string|max:50',
            'applicant_contact' => 'required|string|max:20',
            'service_provider_id' => 'required',
            'proposed_emptying_date' => 'nullable|date',
        ]);
    
        try {
            $application = new Application();
            $application->bin = $request->bin;
            $application->customer_name = $request->customer_name ?? null;
            $application->customer_gender = $request->customer_gender ?? null;
            $application->customer_contact = $request->customer_contact ?? null;
            $application->applicant_name = $request->applicant_name ?? null;
            $application->applicant_gender = $request->applicant_gender ?? null;
            $application->applicant_contact = $request->applicant_contact ?? null;
            $application->proposed_emptying_date = $request->proposed_emptying_date ?? null;
            $application->supervisory_assessment_date = $request->supervisory_assessment_date ?? null;
            $application->service_provider_id = $request->service_provider_id ?? null;
            $application->save();
    
            // Get containment_id
            $containment = DB::table('building_info.build_contains')
                ->where('bin', $request->bin)
                ->first();
    
            if (!$containment) {
                return response()->json(['status' => 'error', 'message' => 'Containment not found for the provided bin.']);
            }
    
            $containmentRecord = Containment::where('id', $containment->containment_id)->first();
            $EmptyingDate = $containmentRecord->next_emptying_date ?? null;
            $proposedEmptyingDate = $request->proposed_emptying_date ?? null;
            
            $status = (
                $EmptyingDate && $proposedEmptyingDate &&
                Carbon::parse($EmptyingDate)->eq(Carbon::parse($proposedEmptyingDate))
            ) ? 1 : 2;
    
            DB::table('fsm.containments')
                ->where('id', $containment->containment_id)
                ->update(['status' => $status]);
                if ($status === 2) {
                    $this->set_emptying_date();
                }
        
            return response()->json(['status' => 'success', 'message' => 'Application submitted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Error submitting application: ', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to submit application. Please try again.']);
        }
    }
    
}
