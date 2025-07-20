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
            'holding_number' => 'required',
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
           'application_id.required' => __('The Application ID is required.'),
            'application_id.exists' => __('The specified Application does not exist.'),

            'holding_number.required' => __('The Holding Number is required.'),
            'holding_number.numeric' => __('The Holding Number must be numeric.'),
            'holding_number.gt' => __('The Holding Number must be greater than 0.'),

            'owner_name.required' => __('The Owner Name is required.'),
            'owner_gender.required' => __('The Owner Gender is required.'),
            'owner_contact.required' => __('The Owner Contact Number is required.'),
            'owner_contact.integer' => __('The Owner Contact Number must be a number.'),
            'owner_contact.min' => __('The Owner Contact Number cannot be zero or negative.'),

            'containment_type.required' => __('The Containment Type is required.'),
            'containment_outlet_connection.required' => __('The Containment Outlet Connection Type is required.'),
            'containment_volume.required' => __('The Containment Volume (m³) is required.'),
            'containment_volume.numeric' => __('The Containment Volume (m³) must be numeric.'),
            'containment_volume.gt' => __('The Containment Volume (m³) must be greater than 0.'),

            'road_width.required' => __('The Road Width (m) is required.'),
            'road_width.numeric' => __('The Road Width (m) must be numeric.'),
            'road_width.gt' => __('The Road Width (m) must be greater than 0.'),

            'distance_from_nearest_road.required' => __('The Distance from the Nearest Road (m) is required.'),
            'distance_from_nearest_road.integer' => __('The Distance from the Nearest Road (m) must be an integer.'),
            'distance_from_nearest_road.gt' => __('The Distance from the Nearest Road (m) must be greater than 0.'),

            'septic_tank_length.required' => __('The Septic Tank Length (m) is required.'),
            'septic_tank_length.numeric' => __('The Septic Tank Length (m) must be numeric.'),
            'septic_tank_length.gt' => __('The Septic Tank Length (m) must be greater than 0.'),

            'septic_tank_width.required' => __('The Septic Tank Width (m) is required.'),
            'septic_tank_width.numeric' => __('The Septic Tank Width (m) must be numeric.'),
            'septic_tank_width.gt' => __('The Septic Tank Width (m) must be greater than 0.'),

            'septic_tank_depth.required' => __('The Septic Tank Depth (m) is required.'),
            'septic_tank_depth.numeric' => __('The Septic Tank Depth (m) must be numeric.'),
            'septic_tank_depth.gt' => __('The Septic Tank Depth (m) must be greater than 0.'),

            'number_of_pit_rings.required' => __('The Number of Pit Rings is required.'),
            'number_of_pit_rings.integer' => __('The Number of Pit Rings must be an integer.'),
            'number_of_pit_rings.gt' => __('The Number of Pit Rings must be greater than 0.'),

            'pit_diameter.required' => __('The Pit Diameter (m) is required.'),
            'pit_diameter.numeric' => __('The Pit Diameter (m) must be numeric.'),
            'pit_diameter.gt' => __('The Pit Diameter (m) must be greater than 0.'),

            'pit_depth.required' => __('The Pit Depth (m) is required.'),
            'pit_depth.numeric' => __('The Pit Depth (m) must be numeric.'),
            'pit_depth.gt' => __('The Pit Depth (m) must be greater than 0.'),

            'appropriate_desludging_vehicle_size.required' => __('The Appropriate Desludging Vehicle Size is required.'),
            'appropriate_desludging_vehicle_size.min' => __('The Appropriate Desludging Vehicle Size cannot be zero or negative.'),

            'number_of_trips.required' => __('The Number of Trips is required.'),
            'number_of_trips.integer' => __('The Number of Trips must be an integer.'),
            'number_of_trips.gt' => __('The Number of Trips must be greater than 0.'),

            'confirmed_emptying_date.required' => __('The Confirmed Emptying Date is required.'),

            'advance_paid_amount.required' => __('The Advance Paid Amount is required.'),
            'advance_paid_amount.integer' => __('The Advance Paid Amount must be an integer.'),


        ];
    }
    
}
