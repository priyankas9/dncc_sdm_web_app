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
        $this->middleware('permission:List Schedule Desludging', ['only' => ['index']]);
        $this->middleware('permission:View Schedule Desludging', ['only' => ['getData']]);
       
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
       return $this->desludgingScheduleService->getContainmentData();

    }
    public function setPriority($id)
    {
        return $this->desludgingScheduleService->setPriority($id);

    }
    public function set_emptying_date()
    {
       return $this->desludgingScheduleService->setEmptyingDate();
    }
    public function test()
    {
        $this->set_emptying_date();
        $this->setPriority(null);
        dd("doint");
    }
    public function trips_allocated_range (Request $request)
    {
        return $this->desludgingScheduleService->tripsAllocatedRange($request);
    }
    public function trips_allocated($date)
    {
       return $this->desludgingScheduleService->tripsAllocated($date);      

    }
    public function export(Request $request)
    {
        $data = $request->all();
        return $this->desludgingScheduleService->download($data);
    }
    public function disagreeEmptying($bin)
    {   
        return $this->desludgingScheduleService->disagreeEmptying($bin);
    }

    public function redirectToApplication(Request $request)
    {
        return $this->desludgingScheduleService->redirectApplication($request);
    }
   
    
}
