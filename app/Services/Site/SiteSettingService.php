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
         
         // Retrieve existing site settings
         $performance_test = SiteSetting::get();
     
         // Prepare new settings
         $new_settings = [
             "Next Emptying Date Assignment Period" => [
                 'value' => $data['Next_Emptying_Date_Assignment_Period'] ?? null,
                 'remarks' => $data['Next_Emptying_Date_Assignment_Period_remark'] ?? null
             ],
             "Trip Capacity Per Day" => [
                 'value' => $data['Trip_Capacity_Per_Day'] ?? null,
                 'remarks' => $data['Trip_Capacity_Per_Day_remark'] ?? null
             ],
             "Schedule Desludging Start Date" => [
                 'value' => $data['Schedule_Desludging_Start_Date'] ?? null,
                 'remarks' => $data['Schedule_Desludging_Start_Date_remark'] ?? null
             ],
             "Wards for Schedule Desludging" => [
                 'value' => $data['Wards_for_Schedule_Desludging'] ?? [],
                 'remarks' => $data['Wards_for_Schedule_Desludging_remark'] ?? null
             ],
             "Notification Period Prior to Desludging" => [
                 'value' => $data['Notification_Period_Prior_to_Desludging'] ?? null,
                 'remarks' => $data['Notification_Period_Prior_to_Desludging_remark'] ?? null
             ],
             "Notification Period to Non-compliant households" => [
                 'value' => $data['Notification_Period_to_Non-compliant_households'] ?? null,
                 'remarks' => $data['Notification_Period_to_Non-compliant_households_remark'] ?? null
             ],
             "Next Emptying Date Period" => [
                 'value' => $data['Next_Emptying_Date_Period'] ?? null,
                 'remarks' => $data['Next_Emptying_Date_Period_remark'] ?? null
             ],
             "Working Hours" => [
                 'value' => $data['Working_Hours'] ?? null,
                 'remarks' => $data['Working_Hours_remark'] ?? null
             ],
             "Holiday Dates" => [
                'value' => $data['Holiday_Dates'] ??  null,
                'remarks' => $data['Holiday_Dates_remark'] ?? null
            ],
             "Weekend" => [
                 'value' => $data['Weekend'] ?? [],
                 'remarks' => $data['Weekend_remark'] ?? null
             ],
              "Schedule Regeneration Period" => [
                 'value' => $data['Schedule_Regeneration_Period'] ?? [],
                 'remarks' => $data['Schedule_Regeneration_Period_remark'] ?? null
             ],
            
         ];
     
         // Define validation rules
         $rules = [];
         foreach ($performance_test as $setting) {
             if (str_contains($setting->data_type, 'integer')) {
                 $rules[$setting->name] = 'nullable|integer';
             } elseif (str_contains($setting->data_type, 'date')) {
                 $rules[$setting->name] = 'nullable|date';
             } elseif (str_contains($setting->data_type, 'select') || str_contains($setting->data_type, 'multi-select')) {
                 $rules[$setting->name] = 'nullable';
             } else {
                 $rules[$setting->name] = 'nullable|string';
             }
             $rules[$setting->name . '_remark'] = 'nullable|string';
         }
         $attributes = [
            'Next_Emptying_Date_Assignment_Period' => 'Next Emptying Date Assignment Period',
            'Trip_Capacity_Per_Day' => 'Trip Capacity Per Day',
            'Schedule_Regeneration_Period' => 'Schedule Regeneration Period',
            'Working_Hours' => 'Working Hours',
            // add others as needed
        ];
         // Additional specific rules
         $rules['Next_Emptying_Date_Assignment_Period'] = 'integer|min:1|max:365';
         $rules['Trip_Capacity_Per_Day'] = 'integer|min:1';
         $rules['Schedule_Regeneration_Period'] = 'integer|min:1';
         $rules['Working_Hours'] = 'max:24';
    
         // Validate the date
         $validator = Validator::make($data, $rules, [], $attributes);
     
         // Exit early if validation fails
         if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
         }
     
         // Flag to check if any settings were updated
         $settingsUpdated = false;
     
         // Save settings only if validation passes
         foreach ($new_settings as $key => $settingData) {
             $setting = $performance_test->where('name', $key)->first();
             if ($setting) {
                 // Handle multi-select values
                 if (str_contains($setting->data_type, 'multi-select')) {
                     $settingData['value'] = is_array($settingData['value']) ? implode(',', $settingData['value']) : $settingData['value'];
                 }
                 $setting->value = $settingData['value'];
                 $setting->remarks = $settingData['remarks'];
                 $setting->save();
                 $settingsUpdated = true; // Mark that an update has occurred
             }
         }
    
         // Redirect with success message only if settings were updated
         if ($settingsUpdated) {
             return redirect()->back()->with('success', 'Settings updated successfully!');
         } else {
             // Optionally handle the case where no settings were updated
             return redirect()->back()->with('info', 'No settings were changed.');
         }
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
