<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\SupervisoryAssessmentRequest;
use App\Http\Requests\Fsm\SupervisoryRequest;
use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Owner;
use App\Models\Fsm\Application;
use App\Models\Fsm\Containment;
use App\Models\Fsm\ContainmentType;
use App\Models\Fsm\SupervisoryAssessment;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Venturecraft\Revisionable\Revision;
use Yajra\DataTables\DataTables;

class SupervisoryAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fsm.supervisory-assessment.index');
    }
    public function getData(Request $request)
    {
        $data = $request->all();
       
            $pdfBodyData = SupervisoryAssessment::select('*');
            
            return DataTables::of($pdfBodyData)
             ->filter(function ($query) use ($data) {
                // if ($data['trtpltid']) {
                //     $query->where('id', $data['trtpltid']);
                // }
                if ($data['owner_name']) {

                    $query->where('owner_name', 'ILIKE', '%' .  trim($data['owner_name']) . '%');
                }

                if ($data['application_id']) {
                    $query->where('application_id', 'ILIKE', '%' . $data['application_id'] . '%');
                }

                if ($data['holding_num']) {
                    $query->where('holding_number', 'ILIKE', '%'.$data['holding_num'].'%');
                }
            })
                ->addColumn('action', function ($model) {
                    $content = \Form::open(['method' => 'DELETE',
                    'route' => ['supervisory-assessment.destroy', $model->id]]);
                    if (Auth::user()->can('View Emptying')) {
                        $content .= '<a title="Detail" href="' . route('supervisory-assessment.show', [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                    }
                    if (Auth::user()->can('View Emptyings History')) {
                    $content .= '<a title="History" href="' . route('supervisory-assessment.history', $model->id) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                    }
                    if (Auth::user()->can('Delete Emptying')) {
                        $content .= '<a title="Delete"  class="delete  btn-danger btn  btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                    }
                    $content .= \Form::close();
                    return $content;
                })
                ->rawColumns(['emptying_status', 'feedback_status', 'action'])
                ->make(true);
               
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {      
        $value = rtrim(request()->getQueryString(), '=');
       
        $page_title = 'Add Supervisory Assessment';
        // Get the slug from the query string
        $slug = array_keys($request->query())[0] ?? null;
        $owner_detail  = Owner::where('bin', $slug)->first();
        $application = Application::where('bin', $slug)->first();
        $containment_id = BuildContain::where('bin', $slug)->first()->containment_id;
        $containment = Containment::where('id', $containment_id)->first();
        $type_id = Containment::where('id', $containment_id)->first()->type_id;
        $containment_type = ContainmentType::pluck('type', 'id');
        
        return view('fsm.supervisory-assessment.create', compact('page_title', 'slug', 'owner_detail', 'type_id', 'containment_type', 'containment', 'application', 'value'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupervisoryRequest $request)
    {   $slug = $request->slug; 
        $application = Application::where('bin', $slug)->first();
    
        // Store the data
        $assessment = new SupervisoryAssessment();
        $assessment->application_id = $application->id;
        $assessment->holding_number = $request->holding_number;
        $assessment->owner_name = $request->owner_name;
        $assessment->owner_gender = $request->owner_gender;
        $assessment->owner_contact = $request->owner_contact;
        $assessment->containment_type = $request->containment_type;
        $assessment->containment_outlet_connection = $request->containment_outlet_connection;
        $assessment->containment_volume = $request->containment_volume;
        $assessment->road_width = $request->road_width;
        $assessment->distance_from_nearest_road = $request->distance_from_nearest_road;
        $assessment->septic_tank_length = $request->septic_tank_length;
        $assessment->septic_tank_width = $request->septic_tank_width;
        $assessment->septic_tank_depth = $request->septic_tank_depth;
        $assessment->number_of_pit_rings = $request->number_of_pit_rings;
        $assessment->pit_diameter = $request->pit_diameter;
        $assessment->pit_depth = $request->pit_depth;
        $assessment->appropriate_desludging_vehicle_size = $request->appropriate_desludging_vehicle_size;
        $assessment->number_of_trips = $request->number_of_trips;
        $assessment->confirmed_emptying_date = $request->confirmed_emptying_date;
        $assessment->advance_paid_amount = $request->advance_paid_amount;
        
        // Save the assessment
        $assessment->save();
    
        // Update the application status

        $application = Application::where('bin', $slug)->first();
        $application->supervisory_assessment_status = true;
        $application->save();
    
        // Redirect with a success message
        return redirect(route('application.index'))->with('success', 'Supervisory Assessment created successfully');
    }
    
    public function history($id)
    {
        try {
            $supervisoryassessment = SupervisoryAssessment::findOrFail($id);
            $revisions = Revision::all()
                ->where('revisionable_type', get_class($supervisoryassessment))
                ->where('revisionable_id', $id)
                ->groupBy(function ($item) {
                    return $item->created_at->format("D M j Y");
                })
                ->sortByDesc('created_at')
                ->reverse();
        } catch (\Throwable $e) {
            return redirect(route('supervisory-assessment.index'))->with('error', 'Failed to generate history.');
        }
        return view('fsm.supervisory-assessment.history', compact('supervisoryassessment', 'revisions'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supervisoryassessment = SupervisoryAssessment::find($id);
       
        if ($supervisoryassessment) {
            $page_title = "Supervisory Assessment Details";
            return view('fsm/supervisory-assessment.show', compact('page_title', 'supervisoryassessment'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supervisoryassessment = SupervisoryAssessment::find($id);
       
        if ($supervisoryassessment) {
            $page_title = "Edit Supervisory Assessment";
            $application = Application::find($supervisoryassessment->application_id);
            $owner_detail = SupervisoryAssessment::where('id', $id)->first();
            $containment = SupervisoryAssessment::where('id', $id)->first();
            $type_id = SupervisoryAssessment::where('id', $id)->first();
            $indexAction = url()->previous();
            return view('fsm.supervisory-assessment.edit',compact('page_title','supervisoryassessment','indexAction', 
            'application', 
            'owner_detail', 
            'containment','type_id'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SupervisoryRequest $request, $id)
    {
        $assessment = SupervisoryAssessment::find($id);
        if ($assessment) {
            $assessment->holding_number = $request->holding_number;
            $assessment->owner_name = $request->owner_name;
            $assessment->owner_gender = $request->owner_gender;
            $assessment->owner_contact = $request->owner_contact;
            $assessment->containment_type = $request->containment_type;
            $assessment->containment_outlet_connection = $request->containment_outlet_connection;
            $assessment->containment_volume = $request->containment_volume;
            $assessment->road_width = $request->road_width;
            $assessment->distance_from_nearest_road = $request->distance_from_nearest_road;
            $assessment->septic_tank_length = $request->septic_tank_length;
            $assessment->septic_tank_width = $request->septic_tank_width;
            $assessment->septic_tank_depth = $request->septic_tank_depth;
            $assessment->number_of_pit_rings = $request->number_of_pit_rings;
            $assessment->pit_diameter = $request->pit_diameter;
            $assessment->pit_depth = $request->pit_depth;
            $assessment->appropriate_desludging_vehicle_size = $request->appropriate_desludging_vehicle_size;
            $assessment->number_of_trips = $request->number_of_trips;
            $assessment->confirmed_emptying_date = $request->confirmed_emptying_date;
            $assessment->advance_paid_amount = $request->advance_paid_amount;
            $assessment->save();
            return redirect('fsm/supervisory-assessment')->with('success','Supervisory Assessment updated successfully');
        } else {
            return redirect('fsm/supervisory-assessment')->with('error','Failed to update supervisory assessment');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supervisoryassessment = SupervisoryAssessment::find($id);

        if ($supervisoryassessment) {
                $supervisoryassessment->delete();
                return redirect('fsm/supervisory-assessment')->with('success','Supervisory Assessment deleted successfully!');
        } 
        else 
        {
            return redirect('fsm/supervisory-assessment')->with('error','Failed to delete Supervisory Assessment');
        }
    }

   public function download()
{
    $searchData = request('searchData');
    $owner_name = request('owner_name');
    $application_id = request('application_id');
    $holding_num = request('holding_num');

    // Custom header labels you want in the CSV
    $columns = [
        'Assessment Request ID', 'Application ID', 'Holding Number', 'Owner Name', 'Owner Gender', 'Owner Contact Number',
        'Containment Type', 'Containment Outlet Connection', 'Containment Volume', 'Road Width',
        'Distance from Nearest Road', 'Septic Tank Length', 'Septic Tank Width', 'Septic Tank Depth',
        'Number of Pit Rings', 'Pit Diameter', 'Pit Depth', 'Appropriate Desludging Vehicle Size',
        'Number of Trips', 'Confirmed Emptying Date', 'Advance Paid Amount'
    ];

    // Build query
    $query = SupervisoryAssessment::select(
        'id', 'application_id', 'holding_number', 'owner_name', 'owner_gender', 'owner_contact',
        'containment_type', 'containment_outlet_connection', 'containment_volume', 'road_width',
        'distance_from_nearest_road', 'septic_tank_length', 'septic_tank_width', 'septic_tank_depth',
        'number_of_pit_rings', 'pit_diameter', 'pit_depth', 'appropriate_desludging_vehicle_size',
        'number_of_trips', 'confirmed_emptying_date', 'advance_paid_amount'
    )->whereNull('deleted_at');

    if (!empty($owner_name)) {
        $query->where('owner_name', 'ILIKE', '%' . $owner_name . '%');
    }
    if (!empty($application_id)) {
        $query->where('application_id', 'ILIKE', '%' . $application_id . '%');
    }
    if (!empty($holding_num)) {
        $query->where('holding_number', 'ILIKE', '%' . $holding_num . '%');
    }

    $style = (new StyleBuilder())
        ->setFontBold()
        ->setFontSize(13)
        ->setBackgroundColor(Color::rgb(228, 228, 228))
        ->build();

    $writer = WriterFactory::create(Type::CSV);

    $writer->openToBrowser('Supervisory Assessment.csv')
        ->addRowWithStyle($columns, $style); // Top row of CSV

    $query->chunk(5000, function ($records) use ($writer) {
        foreach ($records as $data) {
            $values = [
                $data->id,
                $data->application_id,
                $data->holding_number,
                $data->owner_name,
                $data->owner_gender,
                $data->owner_contact,
                $data->containment_type,
                $data->containment_outlet_connection,
                $data->containment_volume,
                $data->road_width,
                $data->distance_from_nearest_road,
                $data->septic_tank_length,
                $data->septic_tank_width,
                $data->septic_tank_depth,
                $data->number_of_pit_rings,
                $data->pit_diameter,
                $data->pit_depth,
                $data->appropriate_desludging_vehicle_size,
                $data->number_of_trips,
                $data->confirmed_emptying_date,
                $data->advance_paid_amount
            ];

            $writer->addRow($values);
        }
    });

    $writer->close();
}


    
    
}
