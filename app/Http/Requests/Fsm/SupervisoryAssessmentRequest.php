<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class SupervisoryAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'application_id' => 'required|exists:fsm.applications,id',
            'holding_number' => 'required|numeric|gt:0',
            'owner_name' => 'required',
            'owner_gender' => 'required',
            'owner_contact' => 'required|integer',
            'containment_type' => 'required',
            'containment_outlet_connection' => 'required',
            'containment_volume' => 'required|numeric|gt:0',
            'road_width' => 'required|numeric|gt:0',
            'distance_from_nearest_road' => 'required|integer|gt:0',
            'septic_tank_length' => 'required|numeric|gt:0',
            'septic_tank_width' => 'required|numeric|gt:0',
            'septic_tank_depth' => 'required|numeric|gt:0',
            'number_of_pit_rings' => 'required|integer|gt:0',
            'pit_diameter' => 'required|numeric|gt:0',
            'pit_depth' => 'required|numeric|gt:0',
            'appropriate_desludging_vehicle_size' => 'required',
            'number_of_trips' => 'required|integer|gt:0',
            'confirmed_emptying_date' => 'required',
            'advance_paid_amount' => 'required|integer',
          

        ];
    }
    public function messages()
    {
        return [
            'application_id.required' => 'The application ID is required.',
            'application_id.exists' => 'The specified application does not exist.',
    
            'holding_number.required' => 'The holding number is required.',
            'holding_number.numeric' => 'The holding number must be numeric.',
            'holding_number.gt' => 'The holding number must be greater than 0.',
    
            'owner_name.required' => 'The owner name is required.',
            'owner_gender.required' => 'The owner gender is required.',
            'owner_contact.required' => 'The owner contact number is required.',
            'owner_contact.integer' => 'The owner contact number must be a number.',
    
            'containment_type.required' => 'The containment type is required.',
            'containment_outlet_connection.required' => 'The containment outlet connection type is required.',
            'containment_volume.required' => 'The containment volume is required.',
            'containment_volume.numeric' => 'The containment volume must be numeric.',
            'containment_volume.gt' => 'The containment volume must be greater than 0.',
    
            'road_width.required' => 'The road width is required.',
            'road_width.numeric' => 'The road width must be numeric.',
            'road_width.gt' => 'The road width must be greater than 0.',
    
            'distance_from_nearest_road.required' => 'The distance from the nearest road is required.',
            'distance_from_nearest_road.integer' => 'The distance from the nearest road must be an integer.',
            'distance_from_nearest_road.gt' => 'The distance must be greater than 0.',
    
            'septic_tank_length.required' => 'The septic tank length is required.',
            'septic_tank_length.numeric' => 'The septic tank length must be numeric.',
            'septic_tank_length.gt' => 'The septic tank length must be greater than 0.',
    
            'septic_tank_width.required' => 'The septic tank width is required.',
            'septic_tank_width.numeric' => 'The septic tank width must be numeric.',
            'septic_tank_width.gt' => 'The septic tank width must be greater than 0.',
    
            'septic_tank_depth.required' => 'The septic tank depth is required.',
            'septic_tank_depth.numeric' => 'The septic tank depth must be numeric.',
            'septic_tank_depth.gt' => 'The septic tank depth must be greater than 0.',
    
            'number_of_pit_rings.required' => 'The number of pit rings is required.',
            'number_of_pit_rings.integer' => 'The number of pit rings must be an integer.',
            'number_of_pit_rings.gt' => 'The number of pit rings must be greater than 0.',
    
            'pit_diameter.required' => 'The pit diameter is required.',
            'pit_diameter.numeric' => 'The pit diameter must be numeric.',
            'pit_diameter.gt' => 'The pit diameter must be greater than 0.',
    
            'pit_depth.required' => 'The pit depth is required.',
            'pit_depth.numeric' => 'The pit depth must be numeric.',
            'pit_depth.gt' => 'The pit depth must be greater than 0.',
    
            'appropriate_desludging_vehicle_size.required' => 'The appropriate desludging vehicle size is required.',
    
            'number_of_trips.required' => 'The number of trips is required.',
            'number_of_trips.integer' => 'The number of trips must be an integer.',
            'number_of_trips.gt' => 'The number of trips must be greater than 0.',
    
            'confirmed_emptying_date.required' => 'The confirmed emptying date is required.',
    
            'advance_paid_amount.required' => 'The advance paid amount is required.',
            'advance_paid_amount.integer' => 'The advance paid amount must be an integer.',
        ];
    }
    
}
