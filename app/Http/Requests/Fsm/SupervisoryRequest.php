<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class SupervisoryRequest extends FormRequest
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
           
            'holding_number' => 'required|numeric|gt:0',
            'owner_name' => 'required',
            'owner_gender' => 'required',
            'owner_contact' => 'required|integer|min:1',
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
            'appropriate_desludging_vehicle_size' => 'required|min:1',
            'number_of_trips' => 'required|integer|gt:0',
            'confirmed_emptying_date' => 'required',
            'advance_paid_amount' => 'required|integer',
          

        ];
    }
    public function messages()
    {
        return [
          
    
            'holding_number.required' => 'The Holding Number is required.',
            'holding_number.numeric' => 'The Holding Number must be numeric.',
            'holding_number.gt' => 'The Holding Number must be greater than 0.',
    
            'owner_name.required' => 'The Owner Name is required.',
            'owner_gender.required' => 'The Owner Gender is required.',
            'owner_contact.required' => 'The Owner Contact Number is required.',
            'owner_contact.integer' => 'The Owner Contact Number must be a number.',
            'owner_contact.min' => 'The owner contact number cannot be zero or negative.',

            'containment_type.required' => 'The Containment Type is required.',
            'containment_outlet_connection.required' => 'The Containment Outlet Connection Type is required.',
            'containment_volume.required' => 'The Containment Volume (m³) is required.',
            'containment_volume.numeric' => 'The Containment Volume (m³) must be numeric.',
            'containment_volume.gt' => 'The Containment Volume (m³) must be greater than 0.',
    
            'road_width.required' => 'The Road Width (m) is required.',
            'road_width.numeric' => 'The Road Width (m) must be numeric.',
            'road_width.gt' => 'The Road Width (m) must be greater than 0.',
    
            'distance_from_nearest_road.required' => 'The Distance from the Nearest Road (m) is required.',
            'distance_from_nearest_road.integer' => 'The Distance from the Nearest Road (m) must be an integer.',
            'distance_from_nearest_road.gt' => 'The Distance from the Nearest Road (m) must be greater than 0.',
    
            'septic_tank_length.required' => 'The Septic Tank Length (m) is required.',
            'septic_tank_length.numeric' => 'The Septic Tank Length (m) must be numeric.',
            'septic_tank_length.gt' => 'The Septic Tank Length (m) must be greater than 0.',
    
            'septic_tank_width.required' => 'The Septic Tank Width (m) is required.',
            'septic_tank_width.numeric' => 'The Septic Tank Width (m) must be numeric.',
            'septic_tank_width.gt' => 'The Septic Tank Width (m) must be greater than 0.',
    
            'septic_tank_depth.required' => 'The Septic Tank Depth (m) is required.',
            'septic_tank_depth.numeric' => 'The Septic Tank Depth (m) must be numeric.',
            'septic_tank_depth.gt' => 'The Septic Tank Depth (m) must be greater than 0.',
    
            'number_of_pit_rings.required' => 'The Number of Pit Rings is required.',
            'number_of_pit_rings.integer' => 'The Number of Pit Rings must be an integer.',
            'number_of_pit_rings.gt' => 'The Number of Pit Rings must be greater than 0.',
    
            'pit_diameter.required' => 'The Pit Diameter (mm)is required.',
            'pit_diameter.numeric' => 'The Pit Diameter (mm) must be numeric.',
            'pit_diameter.gt' => 'The Pit Diameter (mm) must be greater than 0.',
    
            'pit_depth.required' => 'The Pit Depth (m) is required.',
            'pit_depth.numeric' => 'The Pit Depth (m)  must be numeric.',
            'pit_depth.gt' => 'The Pit Depth (m) must be greater than 0.',
    
            'appropriate_desludging_vehicle_size.required' => 'The Appropriate Desludging Vehicle Size is required.',
            'appropriate_desludging_vehicle_size.min' => 'The Appropriate Desludging Vehicle Size cannot be zero or negative.',
    
            'number_of_trips.required' => 'The Number of Trips is required.',
            'number_of_trips.integer' => 'The Number of Trips must be an integer.',
            'number_of_trips.gt' => 'The Number of Trips must be greater than 0.',
    
            'confirmed_emptying_date.required' => 'The Confirmed Emptying Date is required.',
    
            'advance_paid_amount.required' => 'The Advance Paid Amount is required.',
            'advance_paid_amount.integer' => 'The Advance Paid Amount must be an integer.',
        ];
    }
}
