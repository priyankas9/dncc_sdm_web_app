<?php

namespace App\Http\Controllers\fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DesludgingReintegrationController extends Controller
{
    public function index()
    {
        $page_title = "Scheduled Desludging Reintegration";
       

        return view('fsm.desludging-reintegration.index', compact('page_title'));
    }
    //
    public function getData(Request $request)
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
                  WHERE  (c.status = '4' OR c.status = '5') 
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
      return DataTables::of($buildingResults)->make(true);
    }
}
