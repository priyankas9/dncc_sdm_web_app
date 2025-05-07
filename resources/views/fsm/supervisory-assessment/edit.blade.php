<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
{!! Form::model($supervisoryassessment, [
    'method' => 'PATCH',
    'action' => ['Fsm\SupervisoryAssessmentController@update', $supervisoryassessment->id],
    'class' => 'form-horizontal',
   
]) !!}
    @include('fsm/supervisory-assessment.partial-form', ['submitButtomText' => 'Update'])
{!! Form::close() !!}
</div><!-- /.card -->
@stop