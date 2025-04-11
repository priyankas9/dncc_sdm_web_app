<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Pdf\PdfGeneration;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PdfGenerationController extends Controller
{
    public function index()
    {
        $page_title = "Pdf Data";
        return view('pdf/index', compact('page_title'));
    }


    public function getData()
    {
       
        $pdfBodyData = PdfGeneration::select('*');
      
        return DataTables::of($pdfBodyData)
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE',

                'route' => ['pdf.destroy', $model->id]]);

                if (Auth::user()->can('Edit Pdf Data')) {
                    $content .= '<a title="Edit" href="' . action("PdfGenerationController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('Delete Pdf Data')) {
                    $content .= '<a href="#" title="Delete"  class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->editColumn('paragraph',function ($model){
                return html_entity_decode($model->paragraph);
           })
            ->make(true);
    }


}
