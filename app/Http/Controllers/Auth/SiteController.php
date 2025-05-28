<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\Site\SiteSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
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
    $settings = DB::table('sdm_sitesettings')->orderBy('id')->where('deleted_at', null)->get(['name', 'value', 'remarks', 'data_type', 'options']);
   
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
                 'name' => $setting->name,
                'value' => $setting->value,
                'remarks' => $setting->remarks,
                'data_type' => $setting->data_type,
                'options' => $optionsArray // Use the cleaned options array
            ];
        } else {
            $data[$setting->name] = [
                'name' => $setting->name,
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
    // Get all input data
    $data = $request->all();

    // Retrieve existing site settings for validation rules
    $performance_test = SiteSetting::get();

    // Define validation rules
    $rules = [];
    $customAttributes = [
        'Next_Emptying_Date_Assignment_Period' => 'Next Emptying Date Assignment Period',
        'Trip_Capacity_Per_Day' => 'Trip Capacity Per Day',
        'Schedule_Regeneration_Period' => 'Schedule Regeneration Period',
        'Working_Hours' => 'Working Hours',
        // Add other field mappings as needed
    ];

    // Dynamic rules based on data_type
    foreach ($performance_test as $setting) {
        $fieldName = str_replace(' ', '_', $setting->name);
        
        if (str_contains($setting->data_type, 'integer')) {
            $rules[$fieldName] = 'nullable|integer';
        } elseif (str_contains($setting->data_type, 'date')) {
            $rules[$fieldName] = 'nullable|date';
        } elseif (str_contains($setting->data_type, 'select') || str_contains($setting->data_type, 'multi-select')) {
            $rules[$fieldName] = 'nullable';
        } else {
            $rules[$fieldName] = 'nullable|string';
        }
        
        // Add remark field rule
        $rules[$fieldName.'_remark'] = 'nullable|string';
    }

    // Additional specific validation rules
    $rules['Next_Emptying_Date_Assignment_Period'] = 'required|integer|min:1|max:365';
    $rules['Trip_Capacity_Per_Day'] = 'required|integer|min:1';
    $rules['Schedule_Regeneration_Period'] = 'required|integer|min:1';

    // Validate the data
    $validator = Validator::make($data, $rules, [], $customAttributes);

    if ($validator->fails()) {
        return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
    }

    // If validation passes, proceed with update
    $updateResult = $this->sitesetting->storeOrUpdate($data);

    if ($updateResult) {
        return redirect('auth/site-setting')
            ->with('success', 'Site settings updated successfully!');
    }

    return redirect('auth/site-setting')
        ->with('info', 'No changes were made to site settings.');
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
