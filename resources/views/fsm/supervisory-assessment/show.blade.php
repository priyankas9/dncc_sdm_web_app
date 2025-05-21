<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\SupervisoryAssessmentController@index') }}" class="btn btn-info">Back to List</a>
    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">
        <div class="form-group row required">
    {!! Form::label('holding_number','Holding Number',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('holding_number', $supervisoryassessment->holding_number, ['class' => 'form-control', 'placeholder' => 'Enter Holding Number', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('owner_name', 'Owner Name', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('owner_name', $supervisoryassessment->owner_name, ['class' => 'form-control', 'placeholder' => 'Enter Owner Name', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('owner_gender', 'Owner Gender', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::select('owner_gender', ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'], $supervisoryassessment->owner_gender, ['class' => 'form-control', 'placeholder' => 'Select Gender', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('owner_contact', 'Owner Contact', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('owner_contact', $supervisoryassessment->owner_contact, ['class' => 'form-control', 'placeholder' => 'Enter Contact Number', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('containment_type', 'Containment Type', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('containment_type', $supervisoryassessment->containment_type, ['class' => 'form-control', 'placeholder' => 'Enter Containment Type', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('containment_outlet_connection','Containment Outlet Connection',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('containment_outlet_connection', $supervisoryassessment->containment_outlet_connection, ['class' => 'form-control', 'placeholder' => 'Enter Outlet Connection', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('containment_volume','Containment Volume (m³)',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('containment_volume', $supervisoryassessment->containment_volume, ['class' => 'form-control', 'placeholder' => 'Enter Containment Volume (m³)', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('road_width','Road Width',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('road_width', $supervisoryassessment->road_width, ['class' => 'form-control', 'placeholder' => 'Enter Road Width', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('distance_from_nearest_road','Distance from Nearest Road (m)',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('distance_from_nearest_road', $supervisoryassessment->distance_from_nearest_road, ['class' => 'form-control', 'placeholder' => 'Enter Distance (m)', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('septic_tank_length','Septic Tank Length (m)',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('septic_tank_length', $supervisoryassessment->septic_tank_length, ['class' => 'form-control', 'placeholder' => 'Enter Length', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('septic_tank_width','Septic Tank Width',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('septic_tank_width', $supervisoryassessment->septic_tank_width, ['class' => 'form-control', 'placeholder' => 'Enter Width', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('septic_tank_depth','Septic Tank Depth',['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('septic_tank_depth', $supervisoryassessment->septic_tank_depth, ['class' => 'form-control', 'placeholder' => 'Enter Depth', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('number_of_pit_rings', 'Number of Pit Rings', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('number_of_pit_rings', $supervisoryassessment->number_of_pit_rings, ['class' => 'form-control', 'placeholder' => 'Enter Number of Pit Rings', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('pit_diameter', 'Pit Diameter', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('pit_diameter', $supervisoryassessment->pit_diameter, ['class' => 'form-control', 'placeholder' => 'Enter Pit Diameter', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('pit_depth', 'Pit Depth', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('pit_depth', $supervisoryassessment->pit_depth, ['class' => 'form-control', 'placeholder' => 'Enter Pit Depth', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('appropriate_desludging_vehicle_size', 'Appropriate Desludging Vehicle Size', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('appropriate_desludging_vehicle_size', $supervisoryassessment->appropriate_desludging_vehicle_size, ['class' => 'form-control', 'placeholder' => 'Enter Desludging Vehicle Size', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('number_of_trips', 'Number of Trips', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('number_of_trips', $supervisoryassessment->number_of_trips, ['class' => 'form-control', 'placeholder' => 'Enter Number of Trips', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('confirmed_emptying_date', 'Confirmed Emptying Date', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::date('confirmed_emptying_date', $supervisoryassessment->confirmed_emptying_date, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('advance_paid_amount', 'Advance Paid Amount', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('advance_paid_amount', $supervisoryassessment->advance_paid_amount, ['class' => 'form-control', 'placeholder' => 'Enter Advance Paid Amount', 'step' => '0.01', 'disabled' => 'disabled']) !!}
    </div>
</div>


</div>
    </div>
</div><!-- /.box -->
@stop

