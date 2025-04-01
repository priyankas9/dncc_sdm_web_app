<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Fsm\TreatmentplantPerformanceTestRequest;
use App\Models\Site\SiteSetting;
use App\Services\Fsm\TreatmentplantPerformanceTestService;
use App\Services\Site\SiteSettingService;
use App\Models\Fsm\TreatmentPlantPerformanceTest;
use Illuminate\Support\Facades\DB;

class SiteSettingController extends Controller
{   
    protected SiteSettingService $sitesetting;
    public function __construct(SiteSettingService $sitesetting)
    {
        $this->middleware('auth');
        $this->sitesetting = $sitesetting;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
{
    $page_title = "Site Setting";
    $settings = DB::table('sdm_sitesettings')->orderBy('id')->get(['name', 'value', 'remarks', 'data_type', 'options']);
    
    $data = [];
    foreach ($settings as $setting) {
        if ($setting->data_type === 'select' || $setting->data_type === 'multiselect') {
            $options = $setting->options;

            if (is_string($options)) {
                // Clean up options string
                $optionsString = trim($options, '"\'');
                $optionsArray = explode(',', $optionsString);
                $optionsArray = array_map(function($option) {
                    return trim($option, '"\' '); // Remove quotes and trim whitespace
                }, $optionsArray);
            } elseif (is_array($options)) {
                // Clean up options array
                $optionsArray = array_map(function($option) {
                    return trim($option, '"\' '); // Remove quotes and trim whitespace
                }, $options);
            } else {
                $optionsArray = [];
            }

            $data[$setting->name] = [
                'value' => $setting->value,
                'remarks' => $setting->remarks,
                'data_type' => $setting->data_type,
                'options' => $optionsArray // Use the cleaned options array
            ];
        } else {
            $data[$setting->name] = [
                'value' => $setting->value,
                'remarks' => $setting->remarks,
                'data_type' => $setting->data_type,
                'options' => $setting->options // No changes for non-select types
            ];
        }
      
    }

    // Uncomment for debugging
//   dd($data);
    
    return view('site.index', compact('page_title', 'data'));
}


    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $result = $this->sitesetting->storeOrUpdate($data);
   
        return redirect('site/site-setting')->with('success', ' Site Setting updated successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
