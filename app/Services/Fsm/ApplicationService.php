<?php
// Last Modified Date: 11-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;

use App\Classes\FormField;
use App\Http\Requests\Fsm\ApplicationRequest;
use App\Models\Fsm\HelpDesk;
use App\Models\Fsm\ServiceProvider;
use App\Models\LayerInfo\Ward;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Fsm\DesludgingScheduleController;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Swm\Route;
use App\Models\UtilityInfo\Roadline;
use Carbon\Carbon;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Venturecraft\Revisionable\Revision;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Datetime;
use PDF;


class ApplicationService
{

    protected $session;
    protected $instance;
    protected string $indexAction;
    protected $createRoute,$exportRoute;
    protected $createPartialForm, $createFormFields, $createFormAction;
    protected $showFormFields, $editFormFields, $filterFormFields;
    protected $reportRoute;
    /**
     * Constructs a new ApplicationService object.
     *
     *
     */
    public function __construct()
    {
     $this->createPartialForm = 'fsm.application.partial-form';
     $this->createFormFields = [
            ["title" => "Address",
                "fields" => [
                    new FormField(
                        label: 'Street Name / Street Code',
                        labelFor: 'road_code',
                        inputType: 'multiple-select',
                        inputId: 'road_code',
                        selectValues: [],
                        required: true,
                        inputValue: null,
                    ),
                    new FormField(
                        label: 'House Number / BIN',
                        labelFor: 'bin',
                        inputType: 'multiple-select',
                        inputId: 'bin',
                        selectValues: [],
                        required: true,
                        inputValue: null
                    ),
                    new FormField(
                        label: 'Containment ID',
                        labelFor: 'containment_id',
                        inputType: 'text', 
                        inputId: 'containment_id',
                        selectValues: [],
                        placeholder: 'Containment ID',
                        inputValue: null
                    ),     
                    new FormField(
                        label: 'Ward Number ',
                        labelFor: 'ward',
                        inputType: 'select',
                        inputId: 'ward',
                        placeholder: 'Ward Number ',
                        selectValues: Ward::orderBy('ward')->pluck('ward','ward')->toArray(),
                        inputValue: null
                    ),
                ]],
            ["title" => "Owner Details",
                "fields" => [
                    new FormField(
                        label: 'Owner Name',
                        labelFor: 'customer_name',
                        inputType: 'text',
                        inputId: 'customer_name',
                        placeholder: 'Owner Name',
                        inputValue: null
                    ),
                    new FormField(
                        label: 'Owner Gender',
                        labelFor: 'customer_gender',
                        inputType: 'select',
                        inputId: 'customer_gender',
                        selectValues: ["Male"=>"Male","Female"=>"Female","Others"=>"Others"],
                        placeholder: 'Owner Gender',
                        inputValue: null
                    ),
                    new FormField(
                        label: 'Owner Contact (Phone)',
                        labelFor: 'customer_contact',
                        inputType: 'text', 
                        inputId: 'customer_contact',
                        selectValues: [],
                        placeholder: 'Owner Contact (Phone)',
                        oninput: "validateOwnerContactInput(this)", 
                        inputValue: null
                    )                    
                ]],
            ["title" => "Applicant Details",
                "copyDetails"=>true,
                "fields" => [
                    new FormField(
                        label: "Applicant Name",
                        labelFor: 'applicant_name',
                        inputType: 'text',
                        inputId: 'applicant_name',
                        selectValues: [],
                        required: true,
                        placeholder: 'Applicant Name',
                    ),
                    new FormField(
                        label: "Applicant Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'select',
                        inputId: 'applicant_gender',
                        selectValues: ["Male"=>"Male","Female"=>"Female","Others"=>"Others"],
                        required: true,
                        placeholder: 'Applicant Gender',
                    ),
                    new FormField(
                        label: "Applicant Contact (Phone)",
                        labelFor: 'applicant_contact',
                        inputType: 'text',
                        inputId: 'applicant_contact',
                        selectValues: [],
                        required: true,
                        placeholder: 'Applicant Contact (Phone)',
                        oninput: "validateOwnerContactInput(this)", 
                    ),
                ]],

            ["title" => "Application Details",
                "fields" => [
                    new FormField(
                        label: 'Proposed Emptying Date',
                        labelFor: 'proposed_emptying_date',
                        inputType: 'date',
                        inputId: 'proposed_emptying_date',
                        required: true,
                        placeholder: 'Proposed Emptying Date',
                        inputValue: null,
                        customValue: null
                    ),
                    new FormField(
                        label: 'Supervisory Assessment Date',
                        labelFor: 'supervisory_assessment_date', 
                        inputType: 'date',
                        inputId: 'supervisory_assessment_date', 
                        required: true,
                        placeholder: 'Supervisory Assessment Date',
                        customValue: null
                    ),
                    new FormField(
                        label: 'Service Provider Name',
                        labelFor: 'service_provider_id',
                        inputType: 'select',
                        inputId: 'service_provider_id',
                        selectValues: [],
                        required: true,
                        placeholder: 'Service Provider Name',
                    ),
                    new FormField(
                        label: 'Emergency Desludging',
                        labelFor: 'emergency_desludging_status',
                        inputType: 'select',
                        inputId: 'emergency_desludging_status',
                        selectValues: array("1" => "Yes" , "0" => "No"),
                        required: true,
                        placeholder: 'Emergency Desludging',
                    ),
                ]],
               
        ];
        $this->createFormAction = route('application.store');
        $this->indexAction = route('application.index');
        $this->createRoute = route('application.create');
        $this->exportRoute = route('application.export');
        $this->reportRoute = 'true';
        $this->filterFormFields = [
            [
                new FormField(
                    label: 'BIN',
                    labelFor: 'bin',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'text',
                    inputId: 'bin',
                    selectValues: [],
                    required: true,
                    placeholder: 'BIN',
                ),

            new FormField(
                label: 'House Number',
                labelFor: 'house_address',
                 labelClass: 'col-md-2 col-form-label ',
                    inputType: 'text',
                inputId: 'house_address',
                placeholder: 'House Number',
            ),
                new FormField(
                    label: 'Owner Name',
                    labelFor: 'customer_name',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'text',
                    inputId: 'customer_name',
                    placeholder: 'Owner Name',
                ),

            ],
            [  
                new FormField(
                label: 'Application ID',
                labelFor: 'application_id',
                labelClass: 'col-md-2 col-form-label ',
                inputType: 'text',
                inputId: 'application_id',
                placeholder: 'Application ID',
            ),
                new FormField(
                    label: 'Emptying Status',
                    labelFor: 'emptying_status',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'select',
                    inputId: 'emptying_status',
                    selectValues: ["true"=>"Yes","false"=>"No"],
                    placeholder: 'Emptying Status',
                    autoComplete: "off",
                ),
                new FormField(
                    label: 'Sludge Collection Status',
                    labelFor: 'sludge_collection_status',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'select',
                    inputId: 'sludge_collection_status',
                    selectValues: ["true"=>"Yes","false"=>"No"],
                    placeholder: 'Sludge Collection Status',
                    autoComplete: "off",
                ),

            ],
            [
                new FormField(
                    label: 'Feedback Status',
                    labelFor: 'feedback_status',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'select',
                    inputId: 'feedback_status',
                    selectValues: ["true"=>"Yes","false"=>"No"],
                    placeholder: 'Feedback Status',
                    autoComplete: "off",
                ),
                new FormField(
                    label: 'Street Name/ Street Code',
                    labelFor: 'road_code',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'multiple-select',
                    inputId: 'road_code',
                    selectValues: [],
                    placeholder: 'Street Name/ Street Code',
                ),
                new FormField(
                    label: 'Supervisory Assessment Date',
                    labelFor: 'supervisory_assessment_date',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'date',
                    inputId: 'supervisory_assessment_date',
                    placeholder: 'Supervisory Assessment Date',
                )
               
            ],
            [
                new FormField(
                    label: 'Proposed Emptying Date',
                    labelFor: 'proposed_emptying_date',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'date',
                    inputId: 'proposed_emptying_date',
                    placeholder: 'Proposed Emptying Date',
                ),
                new FormField(
                    label: 'Ward Number ',
                    labelFor: 'ward',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'select',
                    inputId: 'ward',
                    placeholder: 'Ward Number',
                    selectValues: Ward::orderBy('ward')->pluck('ward','ward')->toArray(),
                ),
                new FormField(
                    label: 'Service Provider Name',
                    labelFor: 'service_provider_id',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'select',
                    inputId: 'service_provider_id',
                    placeholder: 'Service Provider Name',
                    selectValues:  [],
                ),
               
            ],
            [
                 new FormField(
                    label: 'Date From',
                    labelFor: 'date_from',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'date',
                    inputId: 'date_from',
                    placeholder: 'Date From',
                ),
                new FormField(
                    label: 'Date To',
                    labelFor: 'date_to',
                    labelClass: 'col-md-2 col-form-label ',
                    inputType: 'date',
                    inputId: 'date_to',
                    required: true,
                    placeholder: 'Date To',
                ),
            ],
        ];
    }

    /**
     * Get form fields for creating application.
     *
     * @return array
     */
   public function getCreateFormFields()
{   
    // Retrieve all session values
    $nextEmptyingDate = session('next_emptying_date');
    $bin = session('bin');
    $ownerName = session('owner_name');
    $ownerContact = session('owner_contact');
    $containmentId = session('containment_id');
    $ownerGender = session('owner_gender');
    $ward = session('ward');
    $population_served = session('population_served');
    $household_served = session('household_served');
    $toilet_count = session('toilet_count');
    $road_code = session('road_code');
    $action_type = session('action_type');
   
    // Process each field in the form
    foreach ($this->createFormFields as &$section) {
        foreach ($section['fields'] as &$field) {
            switch ($field->inputId) {
                case 'proposed_emptying_date':
                    $field->inputValue = $nextEmptyingDate;
                    $field->disabled = ($action_type == "confirm");
                    break;
                    
                case 'population_served':
                    $field->inputValue = $population_served;
                    $field->disabled = !empty($population_served);
                    break;
                    
                case 'household_served':
                    $field->inputValue = $household_served;
                    $field->disabled = !empty($household_served);
                    break;
                    
                case 'toilet_count':
                    $field->inputValue = $toilet_count;
                    $field->disabled = !empty($toilet_count);
                    break;
                    
                case 'bin':
                    $selectedBin = is_array($bin) ? $bin : (is_null($bin) ? [] : [$bin]);
                    $field->selectedValue = $selectedBin;
                    $field->disabled = !empty($selectedBin);
                    break;
                    
                case 'customer_name':
                    $field->inputValue = $ownerName;
                    $field->disabled = !empty($ownerName);
                    break;
                    
                case 'road_code':
                    $selectedRoadCode = is_array($road_code) ? $road_code : (is_null($road_code) ? [] : [$road_code]);
                    $field->selectedValue = $selectedRoadCode;
                    $field->disabled = !empty($selectedRoadCode);
                    break;
                    
                case 'customer_contact':
                    $field->inputValue = $ownerContact;
                    $field->disabled = !empty($ownerContact);
                    break;
                    
                case 'customer_gender':
                    $field->selectedValue = $ownerGender;
                    $field->disabled = !empty($ownerGender);
                    break;
                    
                case 'containment_id':
                    $field->inputValue = $containmentId;
                    $field->disabled = !empty($containmentId);
                    break;
                    
                case 'ward':
                    $field->selectedValue = $ward;
                    $field->disabled = !empty($ward);
                    break;
            }
        }
    }
    
    return $this->createFormFields;
}
    
    /**
     * Get form fields for showing application.
     *
     * @return array
     */
    public function getShowFormFields($application)
    {
        $address = Application::select('building_info.buildings.house_number AS house_address')
        ->leftJoin('building_info.buildings', 'building_info.buildings.bin', '=', 'applications.bin')
        ->where('applications.bin', $application->bin)
        ->first();
        
        $this->showFormFields = [
            ["title" => "Address",
                "fields" => [
                    new FormField(
                        label: 'Street Name / Street Code',
                        labelFor: 'road_code',
                        inputType: 'label',
                        inputId: 'road_code',
                        labelValue: $application->road_code,
                    ),
                    new FormField(
                        label: 'BIN',
                        labelFor: 'bin',
                        inputType: 'label',
                        inputId: 'bin',
                        labelValue: $application->bin,
                    ),
                    new FormField(
                        label: 'House Number',
                        labelFor: 'house_address',
                        inputType: 'label',
                        inputId: 'house_address',
                        labelValue: $address->house_address
                    ),
                    new FormField(
                        label: 'Containment ID',
                        labelFor: 'containment_id',
                        inputType: 'label', 
                        inputId: 'containment_id',
                        labelValue: $application->containment_id,
                    ),   
                    new FormField(
                        label: 'Ward Number',
                        labelFor: 'ward',
                        inputType: 'label',
                        inputId: 'ward',
                        labelValue: $application->ward,
                    ),
                ]],
            ["title" => "Owner Details",
                "fields" => [
                    new FormField(
                        label: 'Owner Name',
                        labelFor: 'customer_name',
                        inputType: 'label',
                        inputId: 'customer_name',
                        labelValue: $application->customer_name,
                    ),
                    new FormField(
                        label: 'Owner Gender',
                        labelFor: 'customer_gender',
                        inputType: 'label',
                        inputId: 'customer_gender',
                        labelValue: $application->customer_gender,
                    ),
                    new FormField(
                        label: 'Owner Contact (Phone)',
                        labelFor: 'customer_contact',
                        inputType: 'label',
                        inputId: 'customer_contact',
                        labelValue: $application->customer_contact,
                    ),
                ]],
            ["title" => "Applicant Details",
                "fields" => [
                    new FormField(
                        label: "Applicant Name",
                        labelFor: 'applicant_name',
                        inputType: 'label',
                        inputId: 'applicant_name',
                        labelValue: $application->applicant_name,
                    ),
                    new FormField(
                        label: "Applicant Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'label',
                        inputId: 'applicant_gender',
                        labelValue: $application->applicant_gender,
                    ),
                    new FormField(
                        label: "Applicant Contact (Phone)",
                        labelFor: 'applicant_contact',
                        inputType: 'label',
                        inputId: 'applicant_contact',
                        labelValue: $application->applicant_contact,
                    ),
                ]],

            ["title" => "Application Details",
                "fields" => [
                    new FormField(
                        label: 'Proposed Emptying Date',
                        labelFor: 'proposed_emptying_date',
                        inputType: 'label',
                        inputId: 'proposed_emptying_date',
                        labelValue: date('m/d/Y', strtotime($application->proposed_emptying_date)),
                    ),
                    new FormField(
                        label: 'Supervisory Assessment Date',
                        labelFor: 'supervisory_assessment_date',
                        inputType: 'label',
                        inputId: 'supervisory_assessment_date',
                        labelValue: date('m/d/Y', strtotime($application->supervisory_assessment_date)),
                    ),
                    
                    new FormField(
                        label: 'Service Provider Name',
                        labelFor: 'service_provider_id',
                        inputType: 'label',
                        inputId: 'service_provider_id',
                        labelValue: $application->service_provider ? $application->service_provider()->withTrashed()->first()->company_name : 'Not Assigned',
                    ),
                     new FormField(
                        label: 'Emergency Desludging',
                        labelFor: 'emergency_desludging_status',
                        inputType: 'label',
                        inputId: 'emergency_desludging_status',
                        labelValue: $application->emergency_desludging_status ? 'Yes' : 'No',

                    ),
                ]],
        ];

        return $this->showFormFields;
    }
    /**
     * Get form fields for editing application.
     *
     * @return array
     */
    public function getEditFormFields($application)
    {
        if($application->emptying_status) {
                     $selectValueServiceProvider = ServiceProvider::withTrashed()->pluck("company_name","id")->toArray();
                 }
                 else {
                     $selectValueServiceProvider = ServiceProvider::Operational()->pluck("company_name","id")->toArray();
                 }
               
        $this->editFormFields = [
            ["title" => "Address",
                "fields" => [
                    new FormField(
                        label: 'Street Name / Street Code',
                        labelFor: 'road_code',
                        inputType: 'label',
                        inputId: 'road_code',
                        labelValue: $application->road_code,
                        placeholder: 'Street Name / Street Code',
                    ),
                    new FormField(
                        label: 'House Number / BIN',
                        labelFor: 'bin',
                        inputType: 'label',
                        inputId: 'bin',
                        labelValue: $application->bin,
                        placeholder: 'BIN',
                    ),
                    new FormField(
                        label: 'Containment ID',
                        labelFor: 'containment_id',
                        inputType: 'label', 
                        inputId: 'containment_id',
                        labelValue: $application->containment_id,
                    ),  
                    new FormField(
                        label: 'Ward Number ',
                        labelFor: 'ward',
                        inputType: 'label',
                        inputId: 'ward',
                        labelValue: $application->ward,
                        placeholder: 'Ward Number',
                    ),
                    new FormField(
                        label: 'Street Name / Street Code',
                        labelFor: 'road_code',
                        inputType: 'text',
                        inputId: 'road_code',
                        inputValue: $application->road_code,
                        hidden: true,
                        placeholder: 'Street Name / Street Code',
                    ),
                    new FormField(
                        label: 'Ward Number ',
                        labelFor: 'ward',
                        inputType: 'label',
                        inputId: 'text',
                        inputValue: $application->ward,
                        hidden: true,
                        placeholder: 'Ward Number ',
                    ),
                ]],
                [
                    "title" => "Owner Details",
                    "fields" => [
                        new FormField(
                            label: 'Owner Name',
                            labelFor: 'customer_name',
                            inputType: 'text',
                            inputId: 'customer_name',
                            inputValue: $application->customer_name,
                            placeholder: 'Owner Name',
                            disabled: true 
                        ),
                        new FormField(
                            label: 'Owner Gender',
                            labelFor: 'customer_gender',
                            inputType: 'select',
                            inputId: 'customer_gender',
                            selectValues: ["Male" => "Male", "Female" => "Female", "Others" => "Others"],
                            selectedValue: $application->customer_gender,
                            placeholder: 'Owner Gender',
                            disabled: true 
                        ),
                        new FormField(
                            label: 'Owner Contact (Phone)',
                            labelFor: 'customer_contact',
                            inputType: 'number',
                            inputId: 'customer_contact',
                            inputValue: $application->customer_contact,
                            placeholder: 'Owner Contact (Phone)',
                            disabled: true ,
                            
                        ),
                    ]
                    ],
                
            ["title" => "Applicant Details",
                "copyDetails"=>true,
                "fields" => [
                    new FormField(
                        label: "Applicant Name",
                        labelFor: 'applicant_name',
                        inputType: 'text',
                        inputId: 'applicant_name',
                        inputValue: $application->applicant_name,
                        selectValues: [],
                        required: true,
                        placeholder: 'Applicant Name',
                    ),
                    new FormField(
                        label: "Applicant Gender",
                        labelFor: 'applicant_gender',
                        inputType: 'select',
                        inputId: 'applicant_gender',
                        selectValues: ["Male"=>"Male","Female"=>"Female","Others"=>"Others"],
                        selectedValue: $application->applicant_gender,
                        required: true,
                        placeholder: 'Applicant Gender',
                    ),
                    new FormField(
                        label: "Applicant Contact (Phone)",
                        labelFor: 'applicant_contact',
                        inputType: 'number',
                        inputId: 'applicant_contact',
                        inputValue: $application->applicant_contact,
                        selectValues: [],
                        required: true,
                        placeholder: 'Applicant Contact (Phone)',
                        oninput: "validateOwnerContactInput(this)", 

                    ),
                ]],

    
            ["title" => "Application Details",
                "fields" => [
                     new FormField(
                        label: 'Proposed Emptying Date',
                        labelFor: 'proposed_emptying_date',
                        inputType: 'date',
                        inputId: 'proposed_emptying_date',
                        inputValue: Carbon::parse($application->proposed_emptying_date)->format('Y-m-d'), // Correct date format for HTML date input
                        required: true,
                        disabled: $application->emptying_status ? true : false, // Correct logic for disabling the field
                        placeholder: 'Proposed Emptying Date',
                    ),
                    new FormField(
                        label: 'Supervisory Assessment Date',
                        labelFor: 'supervisory_assessment_date',
                        inputType: 'date',
                        inputId: 'supervisory_assessment_date',
                        inputValue: Carbon::parse($application->supervisory_assessment_date)->format('Y-m-d'), // Correct date format for HTML date input
                        required: true,
                        disabled: $application->emptying_status ? true : false, // Correct logic for disabling the field
                        placeholder: 'Supervisory Assessment Date',
                    ),
                   
                    
                    new FormField(
                        label: 'Service Provider Name',
                        labelFor: 'service_provider_id',
                        inputType: 'select',
                        inputId: 'service_provider_id',
                        selectValues: $selectValueServiceProvider,
                        selectedValue: $application->service_provider_id,
                        required: true,
                        disabled:$application->emptying_status?true:'',
                        placeholder: 'Service Provider Name',
                    ),
                     new FormField(
                        label: 'Emergency Desludging',
                        labelFor: 'emergency_desludging_status',
                        inputType: 'select',
                        inputId: 'emergency_desludging_status',
                        selectValues: array("1" => "Yes" , "0" => "No"),
                        selectedValue: $application->emergency_desludging_status ? "1" : "0",
                        required: true,
                        placeholder: 'Emergency Desludging',
                    ),
                ]],
        ];
        return $this->editFormFields;
    }
    /**
     * Get action/route for create form.
     *
     * @return String
     */
    public function getCreateFormAction()
    {
        return $this->createFormAction;
    }
    /**
     * Get action/route for index page of Applications.
     *
     * @return String
     */
    public function getIndexAction()
    {
        return $this->indexAction;
    }
    /**
     * Get action/route for create page of Applications.
     *
     * @return String
     */
    public function getCreateRoute()
    {
        return $this->createRoute;
    }
    /**
     * Get action/route for exporting Applications.
     *
     * @return String
     */
    public function getExportRoute()
    {
        return $this->exportRoute;
    }
    public function getReportRoute()
    {
        return $this->reportRoute;
    }
    /**
     * Get action/route for edit form.
     *
     * @return String
     */
    public function getEditFormAction($application)
    {
        $this->editFormAction = route('application.update', $application);
        return $this->editFormAction;
    }
    /**
     * Get form fields for filter.
     *
     * @return array
     */
    public function getFilterFormFields()
    {
        return $this->filterFormFields;
    }
    /**
     * Get all the applications.
     *
     *
     * @return Application[]|Collection
     */
    public function getAllApplications(Request $request)
    {

        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
        {
          return  Application::select('applications.*', 'building_info.buildings.house_number AS house_address')
          ->leftJoin('building_info.buildings', 'building_info.buildings.bin', '=', 'applications.bin')
          ->whereNull('applications.deleted_at') 
          ->where('applications.service_provider_id', Auth::user()->service_provider_id);
        }
        else if(Auth::user()->hasRole('Treatment Plant - Admin'))
        {
           return Application::select('applications.*', 'building_info.buildings.house_number AS house_address')
           ->leftJoin('building_info.buildings', 'building_info.buildings.bin', '=', 'applications.bin')->whereHas("emptying",function($q) use($request){
                $q->where("treatment_plant_id","=",Auth::user()->treatment_plant_id)
                ->where("emptying_status", true)
                ->whereNull('deleted_at');
            });
        }
        else
        {
          return Application::select('applications.*', 'building_info.buildings.house_number AS house_address')
            ->leftJoin('building_info.buildings', 'building_info.buildings.bin', '=', 'applications.bin')
            ->whereNull('applications.deleted_at');
        }
    }
    /**
     * Get Datatables of Applications.
     *
     * @return DataTables
     * @throws Exception
     */
    public function getDatatable(Request $request)
    {
        
        return DataTables::of($this->getAllApplications($request))

            ->filter(function ($query) use ($request) {
                if ($request->bin){
                    $query->whereHas('buildings', function ($query) use ($request) {
                        $query->where('bin', 'ILIKE', '%' . $request->bin . '%');
                        $query->orWhere('bin', 'ILIKE', '%' . $request->bin . '%');
                    });
                }
                if ($request->house_address){
                    $query->where('building_info.buildings.house_number', 'ILIKE', '%' . $request->house_address . '%');
                }
                if ($request->customer_name){
                    $query->where('customer_name','ILIKE','%'.$request->customer_name.'%');
                }
                if ($request->ward){
                    $query->where('applications.ward',$request->ward);
                }
                if ($request->application_id){
                    $query->where('id',$request->application_id);
                }
                if (!is_null($request->emptying_status)){
                    $query->where('emptying_status', $request->emptying_status);
                }
                if (!is_null($request->feedback_status)){
                    $query->where('feedback_status', $request->feedback_status);
                }
                if (!is_null($request->sludge_collection_status)){
                    $query->where('sludge_collection_status', $request->sludge_collection_status);
                }
                if ($request->road_code){
                    $query->where('applications.road_code',$request->road_code);
                }
                if ($request->proposed_emptying_date){
                    $query->where('proposed_emptying_date',$request->proposed_emptying_date);
                }
                if ($request->service_provider_id){
                    $query->where('service_provider_id',$request->service_provider_id);
                }
                if ($request->date_from && $request->date_to && $request->date_from <= $request->date_to) {
                    $query->whereDate('application_date', '>=', $request->date_from);
                    $query->whereDate('application_date', '<=', $request->date_to);
                } 
                
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['application.destroy', $model->id]]);
                $content .= '<div class="">';
                if (Auth::user()->can('Edit Application')){
                    $content .= '<a title="Edit  Application Details" href="' . route('application.edit', [$model->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1 '. ($model->supervisory_assessment_status? ' anchor-disabled' : '') . '"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View Application')){
                    $content .= '<a title="View Application Details" href="' . route('application.show', [$model->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1"><i class="fa fa-list"></i></a> ';
                }
                
                if (Auth::user()->can('Edit Emptying') && $model->emptying_status){
                    $content .= '<a title="Edit Emptying Service Details" href="' . route("emptying.edit", [$model->with('emptying')->where('id',$model->id)->get()->first()->emptying->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1'. ( $model->sludge_collection_status  ? ' anchor-disabled' : '') . '"><i class="fa fa-recycle"></i></a> ';
                }
                if (Auth::user()->can('Edit Sludge Collection') && $model->sludge_collection_status){
                    $content .= '<a title="Edit Sludge Collection Details" href="' . route("sludge-collection.edit", [$model->sludge_collection->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1'. ( $model->feedback_status  ? ' anchor-disabled' : '') . '"><i class="fa fa-truck-moving"></i></a> ';
                }
                if (Auth::user()->can('Edit Feedback') && $model->feedback_status){
                    $content .= '<a title="Edit Feedback Details" href="' . route("feedback.edit", [$model->feedback->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1'. ( $model->feedback_status  ? ' anchor-disabled' : '') . '"><i class="fa fa-pencil"></i></a> ';
                }
                if (Auth::user()->can('Edit Sludge Collection') && $model->supervisory_assessment_status && $model->supervisory_assessment) {
                    $content .= '<a title="Edit Supervisory Assessment" href="' . route("supervisory-assessment.edit", $model->supervisory_assessment->id) . '" class="btn btn btn-info btn-sm mb-1 mb-1 '. ($model->emptying_status ? ' anchor-disabled' : '') . '"> <i class="fa-solid fa-clipboard-list"></i></a> ';
                }
                

                if (Auth::user()->can('View Application History')){
                $content .= '<a title="History" href="' . route('application.history', $model->id) . '" class="btn btn btn-info btn-sm mb-1 mb-1"><i class="fa fa-history"></i></a> ';
                
                if (Auth::user()->can('Delete Application')){
                    $content .= '<a title="Delete"  class="delete btn btn-danger  btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }
            }
                if (Auth::user()->can('Generate Application Report')){
                    if ($model->emptying_status == TRUE) {
                    $content .= '<a title="Generate Report" href="' . route('application.report', [$model->id]) . '" class="btn btn btn-info btn-sm mb-1 mb-1"><i class="fa-regular fa-file-pdf"></i></a> ';
                    }
                }

                $content .= '</div>';
                $content .= \Form::close();

                return $content;
            })
        ->editColumn('supervisory_assessment_status', function($model) {
                    $content = '<div class="application-quick__actions">';
                    
                    // Check if assessment exists (relation not null)
                    $assessmentExists = $model->supervisory_assessment !== null;
                    
                    // Show check or cross based on assessment existence
                    $content .= $assessmentExists 
                        ? '<i class="fa fa-check"></i>' 
                        : '<i class="fa fa-times"></i>';

                    if ($assessmentExists) {
                        // Assessment exists → show view button
                        $content .= '<a title="View Supervisory Assessment Details" href="' 
                                . route('supervisory-assessment.show', [$model->supervisory_assessment->id])
                                . '" class="btn btn-info btn-sm mb-1"> <i class="fa-solid fa-clipboard-list"></i></a> ';
                    } else {
                        // No assessment exists → show Add button
                        $content .= '<a title="Add Supervisory Assessment" href="' 
                                . route("supervisory-assessment.create", [$model->bin])
                                . '" class="btn btn-info btn-sm mb-1"> <i class="fa-solid fa-clipboard-list"></i></a> ';
                    }

                    $content .= '</div>';
                    return $content;
                })
            ->editColumn('emptying_status', function($model) {
                $content = '<div class="application-quick__actions">';
                $content .= $model->emptying_status ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
            
                if ($model->emptying_status == TRUE) {
                    if (Auth::user()->can('View Emptying')) {
                        $content .= '<a title="View Emptying Service Details" href="' 
                                    . route("emptying.show", [$model->with('emptying')->where('id', $model->id)->first()->emptying->id]) 
                                    . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-recycle"></i></a> ';
                    }
                } else {
                    if (Auth::user()->can('Add Emptying')) {
                        // Disable "Add Emptying" if supervisory_assessment_status is false
                        $disabledClass = $model->supervisory_assessment_status ? '' : ' anchor-disabled';
                        $content .= '<a title="Add Emptying Service Details" href="' 
                                    . route("emptying.create-id", [$model->id]) 
                                    . '" class="btn btn-info btn-sm mb-1' . $disabledClass . '"><i class="fa fa-recycle"></i></a> ';
                    }
                }
            
                $content .= '</div>';
                return $content;
            })
            
            ->editColumn('feedback_status',function ($model){
                $content = '<div class="application-quick__actions">';
                $content .= $model->feedback_status?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';

                if($model->feedback_status == FALSE)
                {
                    if (Auth::user()->can('Add Feedback')){
                        $content .= '<a title="Add Feedback Details" href="' . route("feedback.create-Feedback", [$model->id]) . '" class="btn btn-info btn-sm mb-1'. ( $model->emptying_status ? '' : ' anchor-disabled') . '"><i class="fa fa-pencil"></i></a> ';
                    }
                }
                else
                {
                    if (Auth::user()->can('View Feedback')){
                        $content .= '<a title="View Feedback Details" href="' . route("feedback.show", [$model->feedback->id]) . '" class="btn btn-info btn-sm mb-1'. ( $model->emptying_status ? '' : ' anchor-disabled') . '"><i class="fa fa-pencil"></i></a> ';
                    }
                }
                $content .= '</div>';
                return $content;
            })
            ->editColumn('sludge_collection_status',function ($model){
                $content = '<div class="application-quick__actions">';
                $content .= $model->sludge_collection_status?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';

                if($model->sludge_collection_status == FALSE)
                {
                    if (Auth::user()->can('Add Sludge Collection')){
                        $content .= '<a title="Add Sludge Collection Details" href="' . route("sludge-collection.create-id", [$model->id]) . '" class="btn btn-info btn-sm mb-1'. ( $model->emptying_status ? '' : ' anchor-disabled') . '"><i class="fa fa-truck-moving"></i></a> ';
                    }
                }
                else
                {
                    if (Auth::user()->can('View Sludge Collection')){
                        $content .= '<a title="View Sludge Collection Details" href="' . route("sludge-collection.show", [$model->sludge_collection->id]) . '" class="btn btn-info btn-sm mb-1'. ( $model->emptying_status ? '' : ' anchor-disabled') . '"><i class="fa fa-truck-moving"></i></a> ';
                    }
                }
                $content .= '</div>';
                return $content;
            })
            ->editColumn('service_provider_id',function ($model){
                 return $model->service_provider()->withTrashed()->first()->company_name??'Not Assigned';
            })
            ->rawColumns(['emptying_status','feedback_status','sludge_collection_status','action','supervisory_assessment_status'])

            ->make(true);
    }
    /**
     * Store new application.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|Redirector
     */
    public function createApplication(ApplicationRequest $request)
    {
       
        $previous_application_status = Application::where('containment_id',$request->containment_id)->where('emptying_status',false)->whereNULL('deleted_at')->exists();
        if($previous_application_status)
        {
            return redirect()->back()->withInput()->with('error',"Error! Containment already has running Application. ");
        }
        $application = '';
        if ($request->validated()){
            try {
                DB::transaction(function () use ($request) {
                    $application = Application::create($request->all());
                   
                    $building = Building::where('bin','=',$application->bin)->firstOrFail();
                    $owner = $building->owners;
                    $application->containment_id = $request->containment_id;
                    $application->customer_name = $request->customer_name??$owner->owner_name;
                    $application->customer_contact = $request->customer_contact??$owner->owner_contact;
                    $application->customer_gender = $request->customer_gender??$owner->owner_gender;
                    
                    $owner->fill([
                            "owner_name" => $request->customer_name??$owner->owner_name,
                            "owner_gender" => $request->customer_gender??$owner->owner_gender,
                            "owner_contact" => $request->customer_contact??$owner->owner_contact
                        ]
                    )->save();
                    $building->fill([
                        "ward" => $request->ward??$building->ward,
                        "road_code" => $request->road_code,

                    ])->save();
                    $building->household_served = $request->household_served ;
                    $building->population_served = $request->population_served;
                    $building->toilet_count = $request->toilet_count;
                    $building->save();
                    $application->application_date = now()->format('Y-m-d H:i:s');
                    $application->user_id = Auth::user()->id;
                    if($request->autofill === 'on'){
                        $application->applicant_name = $request->customer_name??$owner->owner_name??null;
                        $application->applicant_contact = $request->customer_contact??$owner->owner_contact??null;
                        $application->applicant_gender = $request->customer_gender??$owner->owner_gender??null;
                    };
                    $application->emergency_desludging_status = $request->emergency_desludging_status ?? $request->emergency_desludging_status ?? null;
                    $application->supervisory_assessment_date = $request->supervisory_assessment_date ?? $request->supervisory_assessment_date ?? null;
                
                   $application->save();
                   // Set containment status based on next_emptying_date vs proposed_emptying_date
                $containmentRecord = Containment::find($request->containment_id);
                  if ($request->action_type == 'confirm')
                  {
                    $containmentRecord->status = 1;
                    $containmentRecord->save();
                  } 
                  else if ($request-> action_type == 'reschedule' || empty($request->action_type))
                    {
                        $containmentRecord->status = 2;
                        app(DesludgingScheduleController::class)->set_emptying_date();
                        $containmentRecord->save();
                    }
                 
                });
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error',"Error! Application couldn't be created. ".$e);
            }
        }

        return redirect(route('application.index'))->with('success','Application created successfully');
    }

    /**
     * Update application.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|Redirector
     */
    public function updateApplication(ApplicationRequest $request, $id)
    {
        try {
            $application = Application::findOrFail($id);
            $application->update($request->all());
            if ($application->address != '-'){
                $building = Building::where('bin','=',$application->bin)->firstOrFail();
                $owner = $building->owners;
                $application->containment_id = $request->containment_id??$application->containment_id;
                $application->customer_name = $request->customer_name??$owner->owner_name;
                $application->customer_contact = $request->customer_contact??$owner->owner_contact;
                $application->customer_gender = $request->customer_gender??$owner->owner_gender;
                $owner->fill([
                        "owner_name" => $request->customer_name??$owner->owner_name,
                        "owner_gender" => $request->customer_gender??$owner->owner_gender,
                        "owner_contact" => $request->customer_contact??$owner->owner_contact,
                        "containment_id" => $request->containment_id??$application->containment_id
                    ]
                )->save();
                $building->fill([
                    "ward" => $request->ward??$building->ward,
                    "road_code" => $request->road_code??$building->road_code
                ])->save();
            }
            if ($application->address === '-'){
                $application->ward = $request->ward_no_addr??$application->ward;
                $application->road_code = $request->road_code_no_addr??$application->road_code;
                $application->proposed_emptying_date = $request->proposed_emptying_date_no_addr??$application->proposed_emptying_date;
            }
            $application->save();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error','Failed to update Application' . $e);
        }
        return redirect(route('application.index'))->with('success','Application updated successfully');
    }

    /**
     * Retrieve application history.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|Redirector
     */
    public function getApplicationHistory($id)
    {
        try {
            $application = Application::findOrFail($id);
            $revisions = Revision::all()
                ->where('revisionable_type',get_class($application))
                ->where('revisionable_id',$id)
                ->groupBy(function($item)
                {
                    return $item->created_at->format("D M j Y");
                })
                ->sortByDesc('created_at')
                ->reverse();
        } catch (\Throwable $e) {
            return redirect(route('application.index'))->with('error','Failed to generate history.');
        }
        return view('fsm.applications.history',compact('application','revisions'));
    }

    /**
     * Export applications.
     *
     * @throws Exception
     */
    // Ensure you have imported the Application model if not already imported

    public function export(Request $request)
    {
   
        // Retrieve request parameters
        $house_number = $request->bin;
        $house_address = $request->house_address;
        $customer_name = $request->customer_name;
        $ward = $request->ward;
        $application_id = $request->application_id;
        $emptying_status = $request->emptying_status;
        $feedback_status = $request->feedback_status;
        $sludge_collection_status = $request->sludge_collection_status;
        $road = $request->road_code;
        $proposed_emptying_date = $request->proposed_emptying_date;
        $service_provider_id = $request->service_provider_id;
        $date_from = $request->date_from;
        $date_to = $request->date_to;
    
        // Define CSV column headers
        $columns = [
            'Road Code',
            'BIN',
            'House Number',
            'Ward Number',
            'Owner Name',
            'Owner Gender',
            'Owner Contact (Phone)',
            'Application Date',
            'Applicant Name',
            'Applicant Gender',
            'Applicant Contact (Phone)',
            'Proposed Emptying Date',
            'Service Provider Name',
            'Emergency Desludging',
            'Emptying Status',
            'Sludge Collection Status',
            'Feedback Status',
        ];
    
        // Build the base query
        $query = DB::table('fsm.applications as a')
        ->leftJoin('building_info.buildings as b', 'b.bin', '=', 'a.bin')
        ->leftJoin('fsm.service_providers as s', 's.id', '=', 'a.service_provider_id')
        ->select(
                'a.bin',
                'b.house_number as house_address',
                'a.road_code',
                'a.ward',
                'a.customer_name',
                'a.customer_gender',
                'a.customer_contact',
                'a.application_date',
                'a.applicant_name',
                'a.applicant_gender',
                'a.applicant_contact',
                'a.proposed_emptying_date',
                's.company_name',
                'a.emergency_desludging_status',
                'b.household_served',
                'b.population_served',
                'b.toilet_count',
                'a.emptying_status',
                'a.sludge_collection_status',
                'a.feedback_status'
            )
            ->whereNull('a.deleted_at')
            ->orderBy('a.bin');
           
        // Apply additional conditions based on user roles and request parameters
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {
            $query->where('a.service_provider_id', Auth::user()->service_provider_id);
        } elseif (Auth::user()->hasRole('Treatment Plant - Admin')) {
            $query->whereHas('emptying', function ($q) {
                $q->where('treatment_plant_id', Auth::user()->treatment_plant_id);
            });
        }

        // Apply filters based on request parameters
        if (!empty($house_number)) {
            $query->where(function ($q) use ($house_number) {
                $q->where('a.bin', 'ILIKE', '%' . $house_number . '%')
                  ->orWhere('b.bin', 'ILIKE', '%' . $house_number . '%');
            });
        }
        if (!empty($house_address)) {
            $query->where('b.house_number', 'ILIKE', '%' . $house_address . '%');
        }
        
        if (!empty($customer_name)) {
            $query->where('a.customer_name', 'ILIKE', '%' . $customer_name . '%');
        }
        if (!empty($ward)) {
            $query->where('a.ward', $ward);
        }
        if (!empty($application_id)) {
            $query->where('a.id', $application_id);
        }
        if (!is_null($emptying_status)) {
            $query->where('a.emptying_status', $emptying_status);
        }
        if (!empty($feedback_status)) {
            $query->where('a.feedback_status', $feedback_status);
        }
        if (!empty($sludge_collection_status)) {
            $query->where('a.sludge_collection_status', $sludge_collection_status);
        }
        if (!empty($road)) {
            $query->where('a.road_code', $road);
        }
        if (!empty($proposed_emptying_date)) {
            $query->where('a.proposed_emptying_date', $proposed_emptying_date);
        }
        if (!empty($service_provider_id)) {
            $query->where('a.service_provider_id', $service_provider_id);
        }
        if ($date_from && $date_to) {
            $query->whereBetween('a.application_date', [$date_from, $date_to]);
        }

        $style = (new StyleBuilder())
        ->setFontBold()
        ->setFontSize(13)
        ->setBackgroundColor(Color::rgb(228, 228, 228))
        ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Applications.csv')
            ->addRowWithStyle($columns, $style);
        $query->chunk(5000, function ($applications) use ($writer) {
        // Add data rows to CSV
        foreach ($applications as $application) {
    
            // Prepare data for CSV row
            $values = [
                $application->road_code,
                $application->bin,
                $application->house_address,
                $application->ward,
                $application->customer_name,
                $application->customer_gender,
                $application->customer_contact,
                $application->application_date,
                $application->applicant_name,
                $application->applicant_gender,
                $application->applicant_contact,
                $application->proposed_emptying_date,
                $application->company_name,
                $application->emergency_desludging_status? 'True' : 'False',
                $application->emptying_status ? 'True' : 'False',
                $application->sludge_collection_status ? 'True' : 'False',
                $application->feedback_status ? 'True' : 'False',
            ];
    
            // Add row to CSV
            $writer->addRow($values);
        }
    });
    
        // Close the CSV file
        $writer->close();
    }
    
    /**
    * Fetches and generates a monthly report.
    *
    * @param int $year The year for the report.
    * @param int $month The month for the report.
    * @return \Illuminate\Http\Response The generated PDF report.
    */
    public function fethMonthlyReport($year, $month)
    {
        if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Municipality - Super Admin') || Auth::user()->hasRole('Municipality - IT Admin') && !Auth::user()->hasRole('Municipality - Executive') || Auth::user()->hasRole('Municipality - Help Desk')) {
        $monthWisequery = 'WITH application AS(

           SELECT service_providers.company_name AS serv_name, count(applications.id) AS applicationCount
            from fsm.service_providers
            LEFT JOIN fsm.applications ON service_providers.id= applications.service_provider_id where EXTRACT(YEAR FROM application_date) = '. $year . '
            and EXTRACT(Month from application_date)  = '. $month .'
            AND fsm.service_providers.deleted_at IS NULL
            AND fsm.applications.deleted_at IS NULL
            GROUP BY serv_name
        ),
        emptying as(
            select service_providers.company_name as serv_name, count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost  , sum(volume_of_sludge) as sludgeCount, count(volume_of_sludge) as sCount
            from fsm.service_providers
            LEFT JOIN fsm.applications ON service_providers.id= applications.service_provider_id
            Left JOIN fsm.emptyings ON emptyings.application_id = applications.id  where EXTRACT(YEAR FROM emptyings.emptied_date) ='. $year .'
            and EXTRACT(Month from emptyings.emptied_date)  = '. $month .' and EXTRACT(YEAR FROM applications.application_date) = '. $year . '
            and EXTRACT(Month from applications.application_date)  = '. $month .'
            AND fsm.service_providers.deleted_at IS NULL
            GROUP BY service_providers.company_name
        )
        select application.serv_name, applicationCount, emptyCount, sludgeCount, totalCost,sCount  from application full join emptying ON application.serv_name = emptying.serv_name; ';

        $monthWisecount= DB::Select($monthWisequery);

        $yearCountquery = 'with application as(
            select  count(applications.id) as applicationCount
            from fsm.applications
            where EXTRACT(YEAR FROM application_date) = '. $year .' and EXTRACT(Month from application_date)  <= '. $month .' AND fsm.applications.deleted_at IS NULL

        ),
        emptying as(
            SELECT COUNT(emptyings.id) AS emptyCount,
            SUM(total_cost) AS totalCost, SUM(volume_of_sludge) AS sludgeCount, count(volume_of_sludge) AS sCount  FROM  fsm.emptyings
            LEFT JOIN fsm.applications ON emptyings.application_id = applications.id WHERE
            EXTRACT(YEAR FROM emptyings.emptied_date) = ' . $year . '
            AND EXTRACT(MONTH FROM emptyings.emptied_date) <= ' . $month . '
            AND EXTRACT(YEAR FROM applications.application_date) = ' . $year . '
            AND EXTRACT(MONTH FROM applications.application_date) <= ' . $month . '

        )
        select applicationCount, emptyCount, sludgeCount, totalCost , sCount from application, emptying; ';

        $yearCount= DB::Select($yearCountquery);

        $wardMonthlyquery = ' with application as(
            select count(applications.id) as applicationCount ,APPLICATIONS.ward as award
                   from fsm.applications
                   where EXTRACT(YEAR FROM application_date) = '. $year .' and EXTRACT(MONTH FROM application_date) <= '. $month .' AND fsm.applications.deleted_at IS NULL
                   GROUP BY APPLICATIONS.ward
         ),
          emptying as(
            select count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost, sum(volume_of_sludge) as sludgeCount, count(volume_of_sludge) as sCount,ward as eward
                   from fsm.emptyings
                   Left JOIN fsm.applications  ON applications.id= emptyings.application_id  WHERE EXTRACT(YEAR FROM emptyings.emptied_date) = '. $year .'
                   and EXTRACT(MONTH FROM emptyings.emptied_date) <= '. $month .'
                   AND EXTRACT(YEAR FROM applications.application_date) = ' . $year . '
                   AND EXTRACT(MONTH FROM applications.application_date) <= ' . $month . '
                   GROUP BY APPLICATIONS.ward
               )
               select  applicationCount, emptyCount, sludgeCount, totalCost, sCount, award  from application a
               left join emptying e ON a.award = e.eward ORDER BY award ;' ;

        // converts month number to mont name
        $wardData= DB::Select($wardMonthlyquery);
        $dateObj   = DateTime::createFromFormat('!m', $month);
        $monthName = $dateObj->format('F');

        // return view('fsm.applications.monthly_report', compact('year', 'monthName','monthWisecount','yearCount','wardData'));
        return PDF::loadView('fsm.applications.monthly_report', compact('year', 'monthName','monthWisecount','yearCount','wardData'))->inline('Monthly Report.pdf');
          }
          else{

            $service_provider_id = User::where('id', '=',Auth::id())->pluck('service_provider_id')->first();

            $monthWisequery = 'with application as(
                select service_providers.company_name as serv_name, count(applications.id) as applicationCount
                from fsm.service_providers
                LEFT JOIN fsm.applications ON service_providers.id= applications.service_provider_id
                where EXTRACT(YEAR FROM application_date) = '. $year .'
                and EXTRACT(Month from application_date)  = '. $month .'
                and APPLICATIONS.service_provider_id='. $service_provider_id. '
                AND fsm.service_providers.deleted_at IS NULL
                AND fsm.applications.deleted_at IS NULL
                GROUP BY service_providers.company_name
            ),
            emptying as(
                select service_providers.company_name as serv_name, count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost  , sum(volume_of_sludge) as sludgecount, count(volume_of_sludge) as sCount
                from fsm.service_providers
                LEFT JOIN fsm.applications ON service_providers.id= applications.service_provider_id
                Left JOIN fsm.emptyings ON applications.id= emptyings.application_id  where EXTRACT(YEAR FROM emptied_date) ='. $year .'
                and EXTRACT(Month from emptied_date)  = '. $month .'
                AND EXTRACT(YEAR FROM applications.application_date) = ' . $year . '
                AND EXTRACT(MONTH FROM applications.application_date) = ' . $month . '
                and emptyings.service_provider_id='. $service_provider_id. '
                AND fsm.service_providers.deleted_at IS NULL

                GROUP BY service_providers.company_name
            )
            select application.serv_name, applicationCount, emptyCount,sludgecount, totalCost, sCount  from application full join emptying ON application.serv_name = emptying.serv_name; ';

            $monthWisecount= DB::Select($monthWisequery);
            $yearCountquery = 'with application as(
                select  count(applications.id) as applicationCount
                from fsm.applications
                where EXTRACT(YEAR FROM application_date) = '. $year . '
                and EXTRACT(Month from application_date)  <= '. $month .'
                and applications.service_provider_id='. $service_provider_id. 'AND fsm.applications.deleted_at IS NULL
            ),
            emptying as(
                select  count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost, sum(volume_of_sludge) as sludgeCount,  count(volume_of_sludge) as sCount
                from fsm.emptyings
                LEFT JOIN fsm.applications ON emptyings.application_id = applications.id
                 where EXTRACT(YEAR FROM emptied_date) = '. $year . '
                 and  EXTRACT(Month from emptied_date)  <= '. $month .'
                 AND EXTRACT(YEAR FROM applications.application_date) = ' . $year . '
                   AND EXTRACT(MONTH FROM applications.application_date) <= ' . $month . '
                and emptyings.service_provider_id='. $service_provider_id. '

            )
            select applicationCount, emptyCount, sludgeCount, totalCost, sCount  from application, emptying; ';

            $yearCount= DB::Select($yearCountquery);

            $wardMonthlyquery = ' with application as(
                select count(applications.id) as applicationCount ,APPLICATIONS.ward as award
                    from fsm.APPLICATIONS
                    where EXTRACT(YEAR FROM application_date) = '. $year . '
                    and EXTRACT(MONTH FROM application_date) <= '. $month . '
                    and applications.service_provider_id='. $service_provider_id. '
                    AND fsm.applications.deleted_at IS NULL
                       GROUP BY APPLICATIONS.ward
             ),
              emptying as(
                select count(emptyings.id)  as emptyCount, sum(total_cost) as totalCost,count(volume_of_sludge) as sludgeCount,  sum(volume_of_sludge) as sCount, ward as eward
                    from fsm.emptyings
                    Left JOIN fsm.applications ON applications.id= emptyings.application_id  WHERE EXTRACT(YEAR FROM emptied_date) = '. $year . '
                    and EXTRACT(MONTH FROM emptied_date) <= '. $month . '
                    AND EXTRACT(YEAR FROM applications.application_date) = ' . $year . '
                    AND EXTRACT(MONTH FROM applications.application_date) <= ' . $month . '
                    and emptyings.service_provider_id='. $service_provider_id. '
                    GROUP BY APPLICATIONS.ward
                   )

                   select  applicationCount, emptyCount, sludgeCount, totalCost, award, sCount from application a
	                left join emptying e ON a.award = e.eward ORDER BY award; ' ;

            $wardData= DB::Select($wardMonthlyquery);
            // converts month number to mont name
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $monthName = $dateObj->format('F');

            return PDF::loadView('fsm.applications.monthly_report', compact('year', 'monthName','monthWisecount','yearCount','wardData'))->inline('Monthly Report.pdf');
          }

        }
        /**
        * Generate a PDF report for a specific application.
        *
        * @param int $id The ID of the application.
        * @return \Illuminate\Http\Response The generated PDF report.
        */
        public function getApplicationReport($id){

            $application = Application::find($id);
            $containment = Containment::query()
            ->leftJoin('fsm.containment_types as ct', 'ct.id', '=', 'containments.type_id')
            ->select('containments.*', 'ct.type') 
            ->where('containments.id', $application->containment_id)
            ->whereNull('containments.deleted_at')
            ->get();

           return PDF::View('fsm.applications.application_report',compact('application','containment'))->inline('Application Report.pdf');
        }
        public function getBuildingDetails(Request $request)
        {
            try {
                // Fetch building by BIN
                $building = Building::where('bin', '=', $request->bin)->firstOrFail();
              
        
                // Use the `getContainmentIds` function to fetch filtered containment IDs
                $containmentIds = $building->containments->pluck('id');
                // Fetch additional related data
                $owner = $building->owners;
                $road = $building->roadlines;
                $application = Application::orderBy('id', 'DESC')->where('bin', $request->bin)->first();
        
                // Check if containments are empty
                

                // Debug the status value
               
      
                // Return the response
                return JsonResponse::fromJsonString(json_encode([
                    'test' => $road,
                    "customer_name" => $owner->owner_name ?? null,
                    "customer_gender" => $owner->owner_gender ?? null,
                    "customer_contact" => $owner->owner_contact ?? null,
                    "road" => $road->code ?? null,
                    "ward" => $building->ward ?? null,
                    "containments" => $containmentIds,
                    "household_served" => $building->household_served ?? null,
                    "population_served" => $building->population_served ?? null,
                    "toilet_count" => $building->toilet_count ?? null,
                    "status" => !empty($containmentIds)
                    
                ]), 200);
        
            } catch (Throwable $e) {
                // Handle exceptions
                return JsonResponse::fromJsonString(json_encode([
                    "error" => "Error getting building details!",
                    "details" => $e->getMessage()
                ]), 500);
            }
        }
       
       
}
