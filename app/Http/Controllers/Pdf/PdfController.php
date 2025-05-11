<?php

namespace App\Http\Controllers\Pdf;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuildingInfo\Building;
use App\Models\Pdf\PdfGeneration ;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class PdfController extends Controller
{
    public function index()
    {
        $page_title = "Pdf Data";
        $body =   PdfGeneration::pluck('subject','subject');      
        return view('pdf/index', compact('page_title','body'));
    }
    public function getData()
    {
        $pdfBodyData = PdfGeneration::select('*');
        return DataTables::of($pdfBodyData)
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE',
                'route' => ['pdf-generation.destroy', $model->id]]);
                if (Auth::user()->can('Edit Pdf Data')) {
                    $content .= '<a title="Edit" href="' . action("Pdf\PdfController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('Delete Pdf Data')) {
                    $content .= '<a href="#" title="Delete"  class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }
                $content .= '<a title="Generate PDF" href="#" class="btn btn-info btn-xs generate-pdf-btn" data-id="' . $model->id . '" data-toggle="modal" data-target="#export-single-notice">
                                <i class="fa-regular fa-file-pdf"></i>
                            </a>';
                $content .= \Form::close();
                return $content;
            })
            ->editColumn('paragraph',function ($model){
                return html_entity_decode($model->paragraph);
           })
            ->make(true);

    }
      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Pdf Data";
        return view('pdf/create', compact('page_title'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
      
        try
        {
        $pdf_data = new PdfGeneration();
        $pdf_data->subject = $request->subject ? $request->subject : null;
        $pdf_data->date = $request->date ? $request->date : null;
        $pdf_data->unique_ref = $request->unique_ref ? $request->unique_ref : null;
        $pdf_data->paragraph = $request->paragraph ? htmlentities($request->paragraph) : null;
        $pdf_data->save();
        return redirect('pdf/pdf-generation')->with('success',"Pdf Data added successfully");
        }catch (Exception $e){
            return redirect('pdf/pdf-generation')->with('error',"Unable to add Pdf Data");
        }
      
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_title = "Edit Pdf Data";
        $pdf_data =  PdfGeneration::find($id);
        return view('pdf/edit', compact('page_title', 'pdf_data'));
    }

    // public function generatePdfReport($id)
    // {
    //     try
    //     {
            
    //     $pdf_data = PdfGeneration::latest()->get();
    //     $holding = Building::find($id);        
    //     $url =  "http://".$_SERVER['HTTP_HOST'] ."/dncc-smart-sanitaion-service/submit-plan/".$holding->gid;   
    //     }catch (\Throwable $th){
    //         return view('errors.404');
    //     }
       
        
    //     return PDF::loadView('pdf.pdf',compact('pdf_data','holding','url'))->inline('Application Report.pdf');
    // }
    public function generatePdfReport($id, Request $request)
    {
        // Fetch data according to the id
        $pdf_data = PdfGeneration::find($id);

        // Simple PDF content (as a Blade view)
        return FacadePdf::loadView('pdf.pdf', compact('pdf_data'))
                  ->download('report.pdf');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try
        {
        $pdf_data =  PdfGeneration::find($id);
        $pdf_data->subject = $request->subject ? $request->subject : null;
        $pdf_data->date = $request->date ? $request->date : null;
        $pdf_data->unique_ref = $request->unique_ref ? $request->unique_ref : null;
        $pdf_data->paragraph = $request->paragraph ? htmlentities($request->paragraph) : null;
        $pdf_data->save();
        return redirect('pdf/pdf-generation')->with('success',"Pdf Data edited successfully");
        }
        catch (Exception $e){
            return redirect('pdf/pdf')->with('error',"Unable to edit Pdf Data");
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
        $pdf_data = PdfGeneration::find($id);

        if ($pdf_data) {
                $pdf_data->delete();
                return redirect('pdf/pdf-generation')->with('success','Pdf Data deleted successfully!');
        } 
        else 
        {
            return redirect('pdf/pdf-generation')->with('error','Failed to delete Pdf Data');
        }
    }
}
