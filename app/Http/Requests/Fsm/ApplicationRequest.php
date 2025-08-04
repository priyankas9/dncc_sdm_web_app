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
            'road_code' => request()->isMethod('post') ? 'required' : 'nullable',
            'bin' => request()->isMethod('post') ? 'required' : 'nullable',
            'ward' => 'nullable|integer|min:1',
            'customer_name' => 'required',
            'customer_gender' => 'required',
            'customer_contact' => 'required',
            'applicant_name' => 'required',
            'applicant_gender' => 'required',
            'applicant_contact' => 'required',
            'containment_code' => '',
            'proposed_emptying_date' => 'required|date',
            'supervisory_assessment_date' => 'required|date|before_or_equal:proposed_emptying_date',
            'service_provider_id' => 'required|integer',
           
           
            
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
            'road_code.required' => __('The Street Name/ Street Code is required.'),
            'bin.required' => __('The House Number / BIN is required.'),
            'ward.integer' => __('The Ward must be an integer.'),
            'ward.min' => __('The Ward must be at least 1.'),
            'customer_name' => '',
            'customer_gender' => '',
            'customer_contact.integer' => __('The owner contact must be an integer.'),
            'applicant_name.required' => __('The Applicant Name is required.'),
            'applicant_gender.required' => __('The Applicant Gender is required.'),
            'applicant_contact.required' => __('The Applicant Contact (Phone) is required.'),
            'containment_code' => '',
            'proposed_emptying_date.required' => __('The Proposed Emptying Date is required.'),
            'service_provider_id.required' => __('The Service Provider Name is required.'),
            'landmark' => '',
           
            'supervisory_assessment_date.required' => 'The Supervisory Assessment Date is required.',
            'supervisory_assessment_date.date' => 'The Supervisory Assessment Date must be a valid date.',
            'supervisory_assessment_date.before_or_equal' => 'The Supervisory Assessment Date must be before the Proposed Emptying Date.',
          
            

        ];
    }

}