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
            'house_locality' => 'required',
            'block_number' => 'required',
            'road_name' => 'required',
            'road_code' => 'required',
            'bin' => 'required',
            'owner_name' => 'required',
            'owner_gender' => 'required',
            'owner_contact' => 'required|integer',
            'containment_volume' => 'required|numeric|gt:0',
            'road_width' => 'required|numeric|gt:0',
            'distance_from_nearest_road' => 'required|integer|gt:0',
            'appropriate_desludging_vehicle_size' => 'required',
            'confirmed_emptying_date' => 'required',
            'advance_paid_amount' => 'required|integer',
            'advance_payment_receipt' => 'required',
          

        ];
    }
    public function messages()
    {
        return [
           'application_id.required' => 'Application ID is required.',
    'application_id.exists' => 'The selected Application ID is invalid.',
    'house_locality.required' => 'Area Name is required.',
    'block_number.required' => 'Block number is required.',
    'road_name.required' => 'Road name is required.',
    'road_code.required' => 'Road code is required.',
    'bin.required' => 'BIN is required.',
    'owner_name.required' => 'Owner name is required.',
    'owner_gender.required' => 'Owner gender is required.',
    'owner_contact.required' => 'Owner contact number is required.',
    'owner_contact.integer' => 'Owner contact must be a valid number.',
    'containment_volume.required' => 'Containment volume is required.',
    'containment_volume.numeric' => 'Containment volume must be a numeric value.',
    'containment_volume.gt' => 'Containment volume must be greater than 0.',
    'road_width.required' => 'Road width is required.',
    'road_width.numeric' => 'Road width must be a numeric value.',
    'road_width.gt' => 'Road width must be greater than 0.',
    'distance_from_nearest_road.required' => 'Distance from nearest road is required.',
    'distance_from_nearest_road.integer' => 'Distance from nearest road must be a valid number.',
    'distance_from_nearest_road.gt' => 'Distance from nearest road must be greater than 0.',
    'appropriate_desludging_vehicle_size.required' => 'Please select an appropriate desludging vehicle size.',
    'confirmed_emptying_date.required' => 'Confirmed emptying date is required.',
    'advance_paid_amount.required' => 'Advance paid amount is required.',
    'advance_paid_amount.integer' => 'Advance paid amount must be a valid number.',
    'advance_payment_receipt.required' => 'Advance payment receipt is required.',


        ];
    }
    
}
