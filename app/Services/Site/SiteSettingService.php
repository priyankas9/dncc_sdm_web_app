<?php

namespace App\Services\Site;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Yajra\DataTables\DataTables;
use App\Models\Fsm\TreatmentPlantPerformanceTest;
use App\Models\Site\SiteSetting;
use App\Models\SiteSetting as ModelsSiteSetting;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;

class SiteSettingService
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

    /**
     * Store or update a newly created resource in storage.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */

   public function storeOrUpdate($data)
{
    // Retrieve all existing settings
    $existingSettings = SiteSetting::all();
    $settingsUpdated = false;

    // Mapping of request fields to setting names
    $settingsMap = [
        'Next_Emptying_Date_Assignment_Period_(Days)' => __('Next Emptying Date Assignment Period (Days)'),
        'Trip_Capacity_Per_Day' => __('Trip Capacity Per Day'),
        'Schedule_Desludging_Start_Date' => __('Schedule Desludging Start Date'),
        'Wards_for_Schedule_Desludging' => __('Wards for Schedule Desludging'),
        'Notification_Period_Prior_to_Desludging' => __('Notification Period Prior to Desludging'),
        'Notification_Period_to_Non-compliant_households' => __('Notification Period to Non-compliant households'),
        'Next_Emptying_Date_Period' => __('Next Emptying Date Period'),
        'Working_Hours' => __('Working Hours'),
        'Holiday_Dates' => __('Holiday Dates'),
        'Weekend' => __('Weekend'),
        'Schedule_Regeneration_Period' => __('Schedule Regeneration Period'),
        'Auto_Assign_Service_Provider' => __('Auto Assign Service Provider'),
    ];

    foreach ($settingsMap as $field => $settingName) {
        $setting = $existingSettings->where('name', $settingName)->first();
        
        if (!$setting) {
            continue;
        }

        // Get the values from request
        $value = $data[$field] ?? null;
        $remark = $data[$field.'_remark'] ?? null;

        // Handle multi-select fields
        if (str_contains($setting->data_type, 'multi-select') && is_array($value)) {
            $value = implode(',', $value);
        }

        // Check if the value has actually changed
        if ($setting->value != $value || $setting->remarks != $remark) {
            $setting->value = $value;
            $setting->remarks = $remark;
            $setting->save();
            $settingsUpdated = true;
        }
    }

    return $settingsUpdated;
}
     
     private function sanitizeHolidayDates($input)
     {
         // Split by commas and validate each date
         $dates = explode(',', $input);
     
         // Keep only valid dates in 'YYYY-MM-DD' format
         $validDates = array_filter($dates, function ($date) {
             return preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date));
         });
     
         // Rejoin as a comma-separated string without spaces
         return implode(',', $validDates);
     }
     
     
     
}
