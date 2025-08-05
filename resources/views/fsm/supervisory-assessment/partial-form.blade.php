<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('house_locality', 'Area Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('house_locality',$application->buildings->house_locality ?? '', ['class' => 'form-control' , 'disabled' => 'disabled']) !!}
        </div>
    </div>

    {!! Form::hidden('application_id', $application->id) !!}
    <div class="form-group row required">
        {!! Form::label('block_number', 'Block Number', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('block_number', $application->buildings->block_number ?? '', ['class' => 'form-control' , 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_name', 'Road Number / Road Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('road_name',$application->buildings->road_name ?? '', ['class' => 'form-control' , 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_code', 'Road Code', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('road_code', $application->road_code ?? '', ['class' => 'form-control' , 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('bin', 'BIN', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('bin', $application->bin ?? '', ['class' => 'form-control' , 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_name', 'Owner Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('owner_name',$application->customer_name ?? '', ['class' => 'form-control', 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_gender', 'Owner Gender', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::select('owner_gender', ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'],$application->customer_gender ?? '', ['class' => 'form-control', 'placeholder' => 'Select Gender', 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_contact', 'Owner Contact', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('owner_contact',$application->customer_contact ?? '', ['class' => 'form-control', 'disabled' => 'disabled']) !!}
        </div>
    </div>

  <div class="form-group row required">
    {!! Form::label('containment_volume', 'Containment Volume (m³)', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-6">
        {!! Form::number('containment_volume', null, [
            'class' => 'form-control',
            'placeholder' => 'Containment Volume (m³)',
            'required',
            'step' => 'any',
            'min' => '0',
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
        ]) !!}
    </div>
    </div>


    <div class="form-group row required">
        {!! Form::label('road_width', 'Road Width (m)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('road_width', null, ['class' => 'form-control', 'placeholder' => 'Road Width (m)', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('distance_from_nearest_road', 'Distance from Nearest Motorable Road (m)', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('distance_from_nearest_road', null, ['class' => 'form-control', 'placeholder' => 'Distance from Nearest Motorable Road (m)', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('appropriate_desludging_vehicle_size', 'Appropriate Desludging Vehicle Size', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('appropriate_desludging_vehicle_size', null, ['class' => 'form-control', 'placeholder' => 'Appropriate Desludging Vehicle Size', 'required']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('confirmed_emptying_date', 'Confirmed Emptying Date', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('confirmed_emptying_date', $application->proposed_emptying_date ?? '', ['class' => 'form-control flatpickr-reschedule', 'placeholder' => 'Confirmed Emptying Date', 'required', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('advance_paid_amount', 'Advance Paid Amount', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::number('advance_paid_amount', null, ['class' => 'form-control', 'placeholder' => 'Advance Paid Amount', 'required']) !!}
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


