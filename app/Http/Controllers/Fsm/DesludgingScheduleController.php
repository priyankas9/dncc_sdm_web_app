<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\ServiceProvider;
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
        $serviceProvider = ServiceProvider::withTrashed()->pluck("company_name", "id")->toArray();

        return view('fsm.desludging-schedule.index', compact('page_title', 'serviceProvider'));
    }
    public function getData(Request $request)
    {   
        
        return $this->desludgingScheduleService->getAllData($request);
    }
    public function fetchSiteSettings()
    {
        $site_settings = DB::table('public.sdm_sitesettings')->whereNull('deleted_at')->get();
        return $site_settings;
       
    }
    public function getContainments()
    {
        // Fetch all containments that have non-null priority and fstp_distance
        $containments = Containment::whereNotNull('priority')
            ->whereNotNull('fstp_distance')
            ->whereNotNull('priority')
            ->orderBy('priority')
            ->orderBy('fstp_distance')
            ->get();
        return $containments;
    }
    public function setPrioritySequence()
    {
        // Fetch site settings for desludging wards
        $wardsForDesludging = DB::table('sdm_sitesettings')
            ->where('name', 'Wards for Schedule Desludging')
            ->pluck('value');
        // Fetch containments
        $containments = DB::select('
        SELECT DISTINCT ON (final_result.id) final_result.*
        FROM (
            SELECT 
                c.priority,
                c.fstp_distance,
                c.closest_fstp_id,
                c.construction_date,
                c.last_emptied_date,
                c.next_emptying_date,
                c.status,
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
            WHERE (c.status IS NULL OR c.status = 1 OR c.emptied_status = false)
                AND (b.wasa_status IS NULL OR b.wasa_status = false) 
                AND c.deleted_at IS NULL
        ) final_result
            ');
            $updates = [];
        foreach ($containments as $containment) {
            $constructionDate = Carbon::parse($containment->construction_date);
            $lastEmptiedDate = $containment->last_emptied_date ? Carbon::parse($containment->last_emptied_date) : null;
            $now = Carbon::now();
            // Determine priority
            if (is_null($lastEmptiedDate)) {
                // Case 1: last emptied date is NULL
                $yearsSinceConstruction = $constructionDate->diffInYears($now);
                $old_priority = $containment->priority;
                if ($yearsSinceConstruction > 3) {
                    $containment->priority = 1;
                } elseif ($yearsSinceConstruction > 1  && $yearsSinceConstruction <= 3) {
                    $containment->priority = 2;
                } else {
                    $containment->priority = 3;
                }
            } else {
                // Case 2, 3, 4: last emptied date is not NULL
                $yearsSinceLastEmptied = $lastEmptiedDate->diffInYears($now);

                if ($yearsSinceLastEmptied > 3) {
                    $containment->priority = 1;
                } elseif ($yearsSinceLastEmptied > 1 && $yearsSinceLastEmptied <= 3) {
                    $containment->priority = 2;
                } else {
                    $containment->priority = 3;
                }
            }
            
            $oldfstp_distance = $containment->fstp_distance;
            $oldfstp_id = $containment->closest_fstp_id;
            // Fetch all FSTPs with specified conditions
            $fstps = DB::table('fsm.treatment_plants')
                ->whereIn('type', [3]) //filterbytextnotid
                ->where('status', true)
                ->get();
            if ($fstps->count() == 1) {
                $fstp = $fstps->first();
                $distanceResult = DB::selectOne("
                SELECT ST_Distance(
                    ST_Transform(c.geom, 4326),
                    ST_Transform(f.geom, 4326)
                ) AS distance
                FROM fsm.containments c, fsm.treatment_plants f
                WHERE c.id = ? AND f.id = ?
            ", [$containment->id, $fstp->id]);
                $containment->fstp_distance = $distanceResult->distance;
                $containment->closest_fstp_id = $fstp->id;
            } else {
                $distances = [];
                foreach ($fstps as $fstp) {
                    $distanceResult = DB::selectOne("
                    SELECT ST_Distance(
                        ST_Transform(c.geom, 4326),
                        ST_Transform(f.geom, 4326)
                    ) AS distance
                    FROM fsm.containments c, fsm.treatment_plants f
                    WHERE c.id = ? AND f.id = ?
                ", [$containment->id, $fstp->id]);
                    $distances[$fstp->id] = $distanceResult->distance;
                }
                if (!empty($distances)) {
                    $containment->fstp_distance = min($distances);
                    // $containment->closest_fstp_id = array_search($containment->fstp_distance, $distances);
                } else {
                    $containment->fstp_distance = null;
                    // $containment->closest_fstp_id = null;
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
    public function setEmptyingDate()
    {
        try {
            $site_settings = $this->fetchSiteSettings()->keyBy('name');
            $this->setPrioritySequence();
            $containments = $this->getContainments();
            $trip_capacity = $site_settings['Trip Capacity Per Day']->value;
    
            // Parse the start date in the 'Y-m-d' format
            $start_date = Carbon::createFromFormat('Y-m-d', $site_settings['Schedule Desludging Start Date']->value)->format('Y-m-d');
            $counter = 0;
    
            // Convert Weekend and Holiday Dates from string to array
            $weekends = explode(',', $site_settings['Weekend']->value);
            $holiday_dates = explode(',', $site_settings['Holiday Dates']->value);
            $containmentupdates = [];
            do {
                $currentDate = Carbon::parse($start_date);
              
                // Skip weekends and holidays efficiently
                while (in_array( date("l", strtotime($currentDate)), $weekends) || in_array($currentDate->format('Y-m-d'), $holiday_dates)) {
                    
                    $currentDate->addDay();
                }
    
                $start_date = $currentDate->format('Y-m-d');
                
                // Select containments in range based on trips per day
                $selected_containments = $this->fetchContainmentsInRange($counter, $counter + (int)$trip_capacity, $containments);
                foreach ($selected_containments as $containment) {
                    $containmentupdates[] = [
                        'id' => $containment->id,
                        'next_emptying_date' => $start_date
                    ]; 
                }
                $counter += $trip_capacity;
                $currentDate->addDay();  // Move to the next day after processing
                $start_date = $currentDate->format('Y-m-d');
    
            } while ($counter <= count($containments));
            if (!empty($containmentupdates)) {
                foreach (array_chunk($containmentupdates, 500) as $chunk) {
                    foreach ($chunk as $update) {
                        Containment::where('id', $update['id'])->update([
                            'next_emptying_date' => $update['next_emptying_date']
                        ]);
                        
                    }
                }
            }
            // Return success response
            return response()->json(['status' => 'success', 'message' => 'Next emptying dates have been successfully regenerated.']);
        } catch (\Exception $e) {
            // Return error response in case of failure
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function fetchContainmentsInRange($start, $end, $containments)
    {
        return $containments->slice($start , $end - $start );
    }
    public function export(Request $request)
    {
        $data = $request->all();
        return $this->desludgingScheduleService->download($data);
    }
    public function submitApplication(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_gender' => 'required|string|max:50',
            'applicant_contact' => 'required|string|max:20',
            'service_provider_id' => 'required',
            'proposed_emptying_date' => 'nullable|date',
            'supervisory_assessment_date' => 'nullable|date|after:proposed_emptying_date',
        ]);

        try {
            // Save the application
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

            // Get containment_id from the build_contains table using the provided bin
            $containment = DB::table('building_info.build_contains')
                ->where('bin', $request->bin)
                ->first();

            if (!$containment) {
                return response()->json(['status' => 'error', 'message' => 'Containment not found for the provided bin.']);
            }

            // Determine the correct status
            $nextEmptyingDate = $containment->next_emptying_date ?? null;
            $proposedEmptyingDate = $request->proposed_emptying_date ?? null;
            $status = ($nextEmptyingDate === $proposedEmptyingDate) ? 0 : 3;

            // Update containment status
            DB::table('fsm.containments')
                ->where('id', $containment->containment_id)
                ->update(['status' => $status]);

            return response()->json(['status' => 'success', 'message' => 'Application submitted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Error submitting application: ', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to submit application. Please try again.']);
        }
    }
    public function disagreeEmptying(Request $request, $bin)
    {
        // Find the containment_id from the build_contains table using the bin
        $buildContain = DB::table('building_info.build_contains')
            ->where('bin', $bin)
            ->first();
    
        // If no record is found, return an error
        if (!$buildContain) {
            return response()->json(['error' => 'Containment not found for the provided bin'], 404);
        }
    
        // Now, find the corresponding containment in the fsm.containments table using the containment_id
        $containment = DB::table('fsm.containments')
            ->where('id', $buildContain->containment_id)
            ->first();
    
        // If containment is not found, return an error response
        if (!$containment) {
            return response()->json(['error' => 'Containment not found'], 404);
        }
    
        // Update the `status` column to 1 in the fsm.containments table
        DB::table('fsm.containments')
            ->where('id', $buildContain->containment_id)
            ->update(['status' => 1]);
    
        // Return a success response
        return response()->json(['success' => 'Containment status updated to 2']);
    }   
}
