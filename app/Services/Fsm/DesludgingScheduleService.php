<?php

namespace App\Services\Fsm;

use App\Enums\TreatmentPlantType;
use App\Models\Fsm\DesludgingScheduleController;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;
use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\DesludgingSchedule;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;

class DesludgingScheduleService
{

    protected $session;
    protected $instance;

    /**
     * Constructs a new LandfillSite object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/
    }
    public function getAllData($data)
    {
        $fetch_id = "SELECT * FROM (
                    SELECT DISTINCT ON (c.id)
                        b.bin, 
                        b.house_number, 
                        b.house_locality, 
                        b.road_code, 
                        o.owner_name, 
                        o.owner_gender,
                        o.owner_contact, 
                        c.next_emptying_date,
                        c.status,
                        c.id,
                        b.ward,
                        b.household_served,
                        b.population_served,
                        b.toilet_count
                    FROM fsm.containments c
                    LEFT JOIN building_info.build_contains bc 
                        ON bc.containment_id = c.id 
                        AND bc.deleted_at IS NULL 
                        AND bc.bin IS NOT NULL 
                        AND bc.containment_id IS NOT NULL
                    LEFT JOIN building_info.buildings b 
                        ON b.bin = bc.bin 
                        AND b.deleted_at IS NULL
                    LEFT JOIN building_info.owners AS o 
                        ON o.bin = b.bin AND o.deleted_at IS NULL
                    LEFT JOIN fsm.applications a
                        ON a.containment_id = c.id AND a.emptying_status = false
                   WHERE   
								c.next_emptying_date IS Not NULL AND
                        (c.status = 0 OR c.status = 4 OR c.status IS null )
                    ORDER BY c.id
                ) final_result
                ORDER BY final_result.next_emptying_date;
                ;";
    
        $buildingResults = DB::select($fetch_id);
    
        return Datatables::of($buildingResults)
            ->addColumn('action', function ($building) {
                return
                  '<a href="javascript:void(0);"
                    class="btn btn-md mb-1 confirm-emptying-btn"
                    title="Confirm Schedule Desludging"
                    class="btn btn-sm mb-1 confirm-emptying-btn"
                    style="background-color: #17A2B8; color: white; margin-right: 2px;"
                    data-action_type="confirm"
                    data-bin="' . $building->bin . '"
                    data-containment_id ="' . $building->id  . '"
                    data-ward ="' . $building->ward  . '"
                    data-road_code ="' . $building->road_code  . '"
                    data-household_served ="' . $building->household_served  . '"
                    data-population_served ="' . $building->population_served  . '"
                    data-toilet_count ="' . $building->toilet_count  . '"
                    data-owner_name="' . htmlspecialchars($building->owner_name, ENT_QUOTES, 'UTF-8') . '"
                    data-owner_contact="' . $building->owner_contact . '"
                    data-next_emptying_date="' . $building->next_emptying_date . '"
                    data-owner_gender="' . $building->owner_gender . '">
                    <i class="fa-solid fa-check"></i>
                  </a>' .
                  '<a href="javascript:void(0);"
                    title="Reschedule Desludging"
                    class="btn btn-md mb-1 reschedule-emptying-btn"
                    style="background-color:rgb(235, 158, 15); color: white; margin-right: 2px;"
                    data-bin="' . $building->bin . '"
                    data-action_type="reschedule"
                    data-ward ="' . $building->ward  . '"
                    data-containment_id ="' . $building->id  . '"
                    data-toilet_count ="' . $building->toilet_count  . '"
                    data-household_served ="' . $building->household_served  . '"
                    data-population_served ="' . $building->population_served  . '"
                    data-road_code ="' . $building->road_code  . '"
                    data-owner_name="' . htmlspecialchars($building->owner_name, ENT_QUOTES, 'UTF-8') . '"
                    data-owner_contact="' . $building->owner_contact . '"
                    data-next_emptying_date="' . $building->next_emptying_date . '"
                    data-owner_gender="' . $building->owner_gender . '">
                     <i class="fa-regular fa-clock"></i>
                  </a>' .
                '<button title="Disagree for Schedule Desludging"
                    class="btn btn-md mb-1 btn-unconfirm-emptying ' . ($building->status == 4 ? 'static-ping' : '') . '"
                    style="background-color:rgb(184, 23, 26); color: white;"
                    data-bin="' . $building->bin . '"
                    data-owner_contact="' . $building->owner_contact . '"
                    data-owner_name="' . $building->owner_name . '"
                    data-next_emptying_date="' . $building->next_emptying_date . '">
                    <i class="fa-solid fa-xmark"></i>
                </button>'

                ;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function getContainmentData ()
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
            -- checks if WASA status is not paid
			    b.wasa_status = false OR b.wasa_status IS NULL
                -- this flag ensures that once containment is emptied through the schedule, it will not be selected again
			AND c.emptied_status = false
            -- this flag checks for 0: not scheduled and 4: denied once only
			AND (c.status = 0 OR c.status = 4 or c.status is NULL)
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
    public function fetchSiteSettings()
    {
        $site_settings = DB::table('public.sdm_sitesettings')->whereNull('deleted_at')->get();
        return $site_settings; 
    }
   public function tripsAllocated($date)
    {
        $confirmed_applications = Application::where('proposed_emptying_date', $date)->count();
        $confirmed_application_ids = Application::where('emptying_status', false)->pluck('containment_id');
        $auto_scheduled_applications = Containment::where('emptied_status', 'true')
            ->whereNotIn('id', $confirmed_application_ids)
            ->where('next_emptying_date', $date)
            ->count();

        $site_settings = $this->fetchSiteSettings()->keyBy('name');
        $daily_trip_capacity = $site_settings['Trip Capacity Per Day']->value;

        // Check if date is a holiday or weekend
        $weekends = explode(',', $site_settings['Weekend']->value);
        $holidays = array_map('trim', explode(',', $site_settings['Holiday Dates']->value));
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->format('l');

        if (in_array($dayOfWeek, $weekends) || in_array($carbonDate->format('Y-m-d'), $holidays)) {
            return 0;
        }

        $remaining_trips = max(0, (int)$daily_trip_capacity - (int)$auto_scheduled_applications - (int)$confirmed_applications);

        return $remaining_trips;
    }

    public function fetchContainmentsInRange($start, $end, $containments)
    {
        return $containments->slice($start , $end - $start );
    }
    public function setEmptyingDate()
    {
       
        try{
        // fetch all values required from site settings
        $site_settings = $this->fetchSiteSettings()->keyBy('name');
        $containments = $this->getContainmentData();
        $priorityCount = $containments->whereNull('priority')->count();
        if ($priorityCount != 0) {
            $this->setPriority(null);
            dd("test");
        }
        $today = Carbon::now(); // Get today's date
        $start_date = Carbon::createFromFormat('Y-m-d', $site_settings['Schedule Desludging Start Date']->value)->format('Y-m-d');

        if($today->diff($start_date)->invert == true)
        {
            $start_date = $today->addDays($site_settings['Schedule Regeneration Period']->value)->format('Y-m-d');  
        }
        $counter = 0;
        $weekends = explode(',', $site_settings['Weekend']->value);
        $holiday_dates = array_map('trim', explode(',', $site_settings['Holiday Dates']->value));

        $containmentupdates = [];
        do{
            
            $set_date = $start_date;
             // Skip weekends and holidays efficiently
             if (in_array( date("l", strtotime($set_date)), $weekends) || 
                    in_array($set_date, $holiday_dates)) {
                    $set_date = Carbon::createFromFormat('Y-m-d',$set_date)->addDay()->format('Y-m-d');
            }
            $remaining_trips = $this->tripsAllocated($set_date);
           
            $selected_containments = $this->fetchContainmentsInRange($counter, $counter + (int)$remaining_trips, $containments);
            foreach ($selected_containments as $containment) {
                $containmentupdates[] = [
                    'id' => $containment->id,
                    'next_emptying_date' => $start_date
                ]; 
            }
            $counter += $remaining_trips;
            $start_date = Carbon::createFromFormat('Y-m-d',$set_date)->addDay()->format('Y-m-d');// Move to the next day after processing
           
           
        }
       
        while ($counter <= count($containments));
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
        }
        catch (\Exception $e) {
            \Log::error('Error in set_emptying_date: ', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to set emptying date. Please try again.']);
        }
    }
    

    public function tripsAllocatedRange($request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
    
        $site_settings = $this->fetchSiteSettings()->keyBy('name');
        $weekends = array_map('trim', explode(',', $site_settings['Weekend']->value));
        $holidays = array_map('trim', explode(',', $site_settings['Holiday Dates']->value));
    
        $current_date = $start_date;
        $trips_allocated = [];
    
        while ($current_date <= $end_date) {
            $carbonDate = Carbon::parse($current_date);
            $dayOfWeek = $carbonDate->format('l');
            $isHoliday = in_array($carbonDate->format('Y-m-d'), $holidays);
            $isWeekend = in_array($dayOfWeek, $weekends);
    
            if (!$isHoliday && !$isWeekend) {
                $trips_allocated[$current_date] = [
                    'trips' => $this->tripsAllocated($current_date),
                    'is_holiday' => $isHoliday,
                    'is_weekend' => $isWeekend
                ];
            } else {
                // if you still want to list holidays/weekends with 0 trips
                $trips_allocated[$current_date] = [
                    'trips' => 0,
                    'is_holiday' => $isHoliday,
                    'is_weekend' => $isWeekend
                ];
            }
    
            $current_date = $carbonDate->addDay()->format('Y-m-d');
        }
    
        return response()->json($trips_allocated);
    }
    /**
     * Disagree with emptying date
     *
     * @param string $bin
     * @return \Illuminate\Http\JsonResponse
     */    

    public function disagreeEmptying($bin)
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

    public function redirectApplication ($request)
    {
        session()->flash('bin', $request->bin);
        session()->flash('owner_name', $request->owner_name);
        session()->flash('owner_contact', $request->owner_contact);
        session()->flash('next_emptying_date', $request->next_emptying_date);
        session()->flash('owner_gender', $request->owner_gender);
        session()->flash('ward', $request->ward);
        session()->flash('containment_id', $request->containment_id);
        session()->flash('road_code', $request->road_code);
        session()->flash('toilet_count', $request->toilet_count);
        session()->flash('household_served', $request->household_served);
        session()->flash('population_served', $request->population_served);
        session()->flash('action_type', $request->action_type);

        return redirect()->route('application.create', ['action_type' => $request->action_type]);
    }
    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */
    public function download($data)
    {
        
        $searchData = $data['searchData'] ?? null; // Use null coalescing operator for cleaner code
        $columns = ['BIN', 'Containment ID','House Number', 'Block Number', 'Road Number', 'Owner Name', 'Owner Contact', 'Next Emptying Date'];
    
        // Prepare the query using Eloquent query builder
        $buildingResults = Containment::select(
            'buildings.bin',
            'buildings.house_number',
            'buildings.house_locality',
            'buildings.road_code',
            'owners.owner_name',
            'owners.owner_contact',
            'containments.next_emptying_date',
            'containments.id',
        )
        ->leftJoin('building_info.build_contains as bc', function($join) {
            $join->on('bc.containment_id', '=', 'containments.id')
                 ->whereNull('bc.deleted_at')
                 ->whereNotNull('bc.bin')
                 ->whereNotNull('bc.containment_id');
        })
        ->leftJoin('building_info.buildings as buildings', function($join) {
            $join->on('buildings.bin', '=', 'bc.bin')
                 ->whereNull('buildings.deleted_at');
        })
        ->leftJoin('building_info.owners as owners', function($join) {
            $join->on('owners.bin', '=', 'buildings.bin')
                 ->whereNull('owners.deleted_at');
        })
        ->leftJoin('fsm.applications as applications', function($join) {
            $join->on('applications.containment_id', '=', 'containments.id')
                 ->where('applications.emptying_status', false);
        }) 
        ->where(function($query) {
            $query->WhereNotNull('containments.next_emptying_date')
                  ->orWhereIn('containments.status', [0, 4, NULL]);
        })
        ->orderBy('containments.next_emptying_date', 'asc');
    
          
        // Set up the CSV writer and file
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();
    
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Desludging_Schedule.csv'); // Ensure the file is CSV
        $writer->addRowWithStyle($columns, $style); // Write the header row with style
    
        // Process query data in chunks and write rows to the CSV file
        $buildingResults->chunk(5000, function ($desludgingData) use ($writer) {
            foreach ($desludgingData as $desludging) {
                $values = [];
                $values[] = $desludging->bin;
                $values[] = $desludging->house_number;
                $values[] = $desludging->house_locality;
                $values[] = $desludging->road_code;
                $values[] = $desludging->owner_name;
                $values[] = $desludging->owner_contact;
                $values[] = $desludging->next_emptying_date;
                $writer->addRow($values); // Write each row
            }
        });
    
        // Close the writer to finish the file download
        $writer->close();
    }
    
}


