<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('house_locality', 'Area Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('house_locality', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    {!! Form::hidden('application_id', $application->id) !!}
    <div class="form-group row required">
        {!! Form::label('block_number', 'Block Number', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('block_number', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_name', 'Road Number / Road Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('road_name', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_code', 'Road Code', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('road_code', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('bin', 'BIN', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('bin', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_name', 'Owner Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('owner_name', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_gender', 'Owner Gender', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::select('owner_gender', ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'], null, ['class' => 'form-control', 'placeholder' => 'Select Gender']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_contact', 'Owner Contact', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('owner_contact', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('containment_volume', 'Containment Volume (m³)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('containment_volume', null, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_width', 'Road Width (m)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('road_width', null, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('distance_from_nearest_road', 'Distance from Nearest Motorable Road (m)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('distance_from_nearest_road', null, ['class' => 'form-control', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('appropriate_desludging_vehicle_size', 'Appropriate Desludging Vehicle Size', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('appropriate_desludging_vehicle_size', null, ['class' => 'form-control', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('confirmed_emptying_date', 'Confirmed Emptying Date', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('confirmed_emptying_date', null, ['class' => 'form-control flatpickr-reschedule', 'required', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('advance_paid_amount', 'Advance Paid Amount', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('advance_paid_amount', null, ['class' => 'form-control', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('advance_payment_receipt', 'Advance Payment Receipt', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::file('advance_payment_receipt', ['class' => 'form-control', 'accept' => 'image/*', 'required']) !!}
            @if(isset($supervisoryassessment) && $supervisoryassessment->advance_payment_receipt)
                <div class="mt-2">
                    <a href="{{ asset('storage/supervisoryassessment/receipts/' . $supervisoryassessment->advance_payment_receipt) }}" target="_blank">
                        <img src="{{ asset('storage/supervisoryassessment/receipts/' . $supervisoryassessment->advance_payment_receipt) }}" width="120" />
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>



    
</div>
<div class="card-footer">
<a href="{{ action('Fsm\ApplicationController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div>


