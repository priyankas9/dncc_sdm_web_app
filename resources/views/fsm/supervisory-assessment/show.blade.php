@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\SupervisoryAssessmentController@index') }}" class="btn btn-info">{{ __("Back to List") }}</a>
    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">

            <div class="form-group row">
                {!! Form::label('house_locality', __('Area Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('house_locality', $supervisoryassessment->house_locality, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('block_number', __('Block Number'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('block_number', $supervisoryassessment->block_number, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('road_name', __('Road Number / Road Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('road_name', $supervisoryassessment->road_name, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('road_code', __('Road Code'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('road_code', $supervisoryassessment->road_code, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('bin', $supervisoryassessment->bin, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('owner_name', __('Owner Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('owner_name', $supervisoryassessment->owner_name, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('owner_gender', __('Owner Gender'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('owner_gender', $supervisoryassessment->owner_gender, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('owner_contact', __('Owner Contact'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('owner_contact', $supervisoryassessment->owner_contact, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('containment_volume', __('Containment Volume (m³)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('containment_volume', $supervisoryassessment->containment_volume, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('road_width', __('Road Width (m)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('road_width', $supervisoryassessment->road_width, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('distance_from_nearest_road', __('Distance from Nearest Motorable Road (m)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('distance_from_nearest_road', $supervisoryassessment->distance_from_nearest_road, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('appropriate_desludging_vehicle_size', __('Appropriate Desludging Vehicle Size'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('appropriate_desludging_vehicle_size', $supervisoryassessment->appropriate_desludging_vehicle_size, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('confirmed_emptying_date', __('Confirmed Emptying Date'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::date('confirmed_emptying_date', $supervisoryassessment->confirmed_emptying_date, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('advance_paid_amount', __('Advance Paid Amount'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::text('advance_paid_amount', $supervisoryassessment->advance_paid_amount, ['class' => 'form-control', 'disabled']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('advance_payment_receipt', __('Advance Payment Receipt'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    @if($supervisoryassessment->advance_payment_receipt)
                        <a href="{{ asset('storage/supervisoryassessment/receipts/' . $supervisoryassessment->advance_payment_receipt) }}" target="_blank">
                            <img src="{{ asset('storage/supervisoryassessment/receipts/' . $supervisoryassessment->advance_payment_receipt) }}" width="150" alt="Receipt Image" />
                        </a>
                    @else
                        <p>{{ __('No receipt uploaded') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@stop
