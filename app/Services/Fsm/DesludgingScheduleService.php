<?php

namespace App\Services\Fsm;

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
use App\Models\Fsm\Containment;
use App\Models\Fsm\DesludgingSchedule;
use App\Models\Fsm\ServiceProvider;

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
        $query = "
      	SELECT DISTINCT final_result.id,final_result.*
                FROM (
                    SELECT 
                        b.bin, 
						b.house_number, 
						b.house_locality, 
						b.road_code, 
						o.owner_name, 
						o.owner_contact, 
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
					LEFT JOIN building_info.owners AS o ON o.bin = b.bin AND o.deleted_at IS  NULL
                    WHERE  (c.status IS NULL OR c.status = '4') 
        AND (b.wasa_status IS NULL OR b.wasa_status = false) AND c.deleted_at IS NULL) final_result 
         order by  final_result.next_emptying_date ASC";
        $buildingResults = DB::SELECT($query);
        // Add the action column
        foreach ($buildingResults as $key => $building) {
            $buildingResults[$key]->action =
                '<a href="/view/' . $building->bin . '" class="btn btn-sm" style="background-color: #17A2B8; color: white;">View</a> ' .
                '<a href="/edit/' . $building->bin . '" class="btn btn-sm" style="background-color: #17A2B8; color: white;">Edit</a> ' .
                '<a href="/delete/' . $building->bin . '" class="btn btn-sm" style="background-color: #17A2B8; color: white;" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a> ' .
                '<a href="/download/' . $building->bin . '" class="btn btn-sm" style="background-color: #17A2B8; color: white;">Download</a>';
        }
        return Datatables::of($buildingResults)
            ->addColumn('action', function ($building) {
            return
                '<button title="Confirm Emptying" class="btn btn-sm mb-1 btn-confirm-emptying" style="background-color: #17A2B8; color: white;" data-bin="' . $building->bin . '"  data-owner_contact="' . $building->owner_contact . '"  data-owner_name="' . $building->owner_name . '" data-next-emptying-date="' . $building->next_emptying_date . '"><i class="fa-solid fa-circle-check"></i></button> ' .
                '<button title="Reschedule Emptying" class="btn btn-sm mb-1 btn-reschedule-emptying" style="background-color: #17A2B8; color: white;" data-bin="' . $building->bin . '"  data-owner_contact="' . $building->owner_contact . '"  data-owner_name="' . $building->owner_name . '" data-next-emptying-date="' . $building->next_emptying_date . '"><i class="fa-solid fa-clock"></i></button> ' .
                '<button title="Disagree for schedule desludging" class="btn btn-sm mb-1 btn-unconfirm-emptying" style="background-color: #17A2B8; color: white;" data-bin="' . $building->bin . '"  data-owner_contact="' . $building->owner_contact . '"  data-owner_name="' . $building->owner_name . '" data-next-emptying-date="' . $building->next_emptying_date . '"><i class="fa-solid fa-xmark"></i></button> ';
        })
            ->rawColumns(['action']) // This is necessary if you want to render HTML in the 'action' column
            ->make(true);
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
        $columns = ['BIN', 'House Number', 'Block Number', 'Road Number', 'Owner Name', 'Owner Contact', 'Next Emptying Date'];
    
        // Prepare the query using Eloquent query builder
        $buildingResults = Containment::select(
                'buildings.bin',
                'buildings.house_number',
                'buildings.block_number',
                'buildings.road_number',
                'owners.owner_name',
                'owners.owner_contact',
                'containments.next_emptying_date',
            )
            ->leftJoin('building_info.build_contains as bc', 'bc.containment_id', '=', 'containments.id')
            ->leftJoin('building_info.buildings as buildings', 'buildings.bin', '=', 'bc.bin')
            ->leftJoin('building_info.owners as owners', 'owners.bin', '=', 'buildings.bin')
            ->whereNull('containments.status')
            ->orderBy('containments.next_emptying_date', 'asc');
    
        // Set up the CSV writer and file
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();
    
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Desludging_Vehicles.csv'); // Ensure the file is CSV
        $writer->addRowWithStyle($columns, $style); // Write the header row with style
    
        // Process query data in chunks and write rows to the CSV file
        $buildingResults->chunk(5000, function ($desludgingData) use ($writer) {
            foreach ($desludgingData as $desludging) {
                $values = [];
                $values[] = $desludging->bin;
                $values[] = $desludging->house_number;
                $values[] = $desludging->block_number;
                $values[] = $desludging->road_number;
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
