<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
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
            'road_code' => 'required',
            'bin' => request()->isMethod('post') ? 'required' : 'nullable',
            'ward' => 'nullable|integer|min:1',
            'customer_name' => '',
            'customer_gender' => '',
            'customer_contact' => 'nullable|integer',
            'applicant_name' => 'required',
            'applicant_gender' => 'required',
            'applicant_contact' => 'required|integer',
            'containment_code' => '',
            'proposed_emptying_date' => 'required|date|after_or_equal:'.date('m/d/Y'),
            'supervisory_assessment_date' => 'nullable|date|before:proposed_emptying_date',
            'service_provider_id' => 'required|integer',
            'landmark' => '',
            'emergency_desludging_status' => 'required|boolean',
            'household_served' => 'nullable|integer|min:1',
            'population_served' => 'nullable|integer|min:1',
            'toilet_count' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get the error messages to display if validation fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'road_code.required' => 'The Street Name/ Street Code is required.',
            'bin.required' => 'The House Number / BIN is required.',
            'ward.integer' => 'The Ward must be an integer.',
            'ward.min' => 'The Ward must be at least 1.',
            'customer_name' => '',
            'customer_gender' => '',
            'customer_contact.integer' => 'The owner contact must be an integer',
            'applicant_name.required' => 'The Applicant Name is required.',
            'applicant_gender.required' => 'The Applicant Gender is required.',
            'applicant_contact.required' => 'The Applicant Contact (Phone) is required.',
            'applicant_contact.required' => 'The Applicant Contact (Phone) is required.',
            'containment_code' => '',
            'proposed_emptying_date.required' => 'The Proposed Emptying Date is required.',
            'service_provider_id.required' => 'The Service Provider Name is required.',
            'landmark' => '',
            'emergency_desludging_status.required' => 'The Emergency Desludging  is required.',
            'emergency_desludging_status.boolean' => 'The Emergency Desludging must be Yes or No.',
            'household_served.integer' => 'The Household Served must be an integer.',
            'household_served.min' => 'The Household Served must be at least 1.',
            'population_served.integer' => 'The Population Served must be an integer.',
            'population_served.min' => 'The Population Served must be at least 1.',
            'toilet_count.integer' => 'The Toilet Count must be an integer.',
            'toilet_count.min' => 'The Toilet Count must be at least 1.',
            

        ];
    }

}