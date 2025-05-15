<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('holding_number','Holding Number',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('holding_number', null, ['class' => 'form-control', 'placeholder' => 'Enter Holding Number', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>
    
    <div class="form-group row required">
    {!! Form::label('owner_name', 'Owner Name', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('owner_name', $owner_detail ? $owner_detail->owner_name : null, ['class' => 'form-control', 'placeholder' => 'Enter Owner Name','oninput' => "this.value = this.value.replace(/[^a-zA-Z]/g, '')"
]) !!}
    </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_gender', 'Owner Gender', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('owner_gender', ['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'], $owner_detail ? $owner_detail->owner_gender : null, ['class' => 'form-control', 'placeholder' => 'Select Owner Gender']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('owner_contact', 'Owner Contact', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('owner_contact', $owner_detail ? $owner_detail->owner_contact : null, ['class' => 'form-control', 'placeholder' => 'Enter Owner Number','oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
]) !!}
        </div>
    </div>

    
    <div class="form-group row required">
    {!! Form::label('containment_type', 'Containment Type', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('containment_type', $type_id ? ($containment_type[$type_id] ?? 'Unknown Type') : null, ['class' => 'form-control', 'placeholder' => 'Enter Containment Type']) !!}
    </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('containment_outlet_connection','Containment Outlet Connection',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('containment_outlet_connection', null, ['class' => 'form-control', 'placeholder' => 'Enter Containment Outlet Connection']) !!}
        </div>
    </div>
    
    <div class="form-group row required">
        {!! Form::label('containment_volume','Containment Volume',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('containment_volume', $containment ? $containment->size : null, ['class' => 'form-control', 'placeholder' => 'Enter Containment Volume', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>
    
    <div class="form-group row required">
        {!! Form::label('road_width','Road Width',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('road_width', null, ['class' => 'form-control', 'placeholder' => 'Enter Road Width' ,'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>
    
    <div class="form-group row required">
        {!! Form::label('distance_from_nearest_road','Distance from Nearest Road',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('distance_from_nearest_road', null, ['class' => 'form-control', 'placeholder' => 'Enter Distance from Nearest Road', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('septic_tank_length','Septic Tank Length',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('septic_tank_length', $containment ? $containment->tank_length : null, ['class' => 'form-control', 'placeholder' => 'Enter Septic Tank Length', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('septic_tank_width','Septic Tank Width',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('septic_tank_width',  $containment ? $containment->tank_width : null, ['class' => 'form-control', 'placeholder' => 'Enter Septic Tank Width', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('septic_tank_depth','Septic Tank Depth',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('septic_tank_depth', $containment ? $containment->depth : null, ['class' => 'form-control', 'placeholder' => 'Enter Septic Tank Depth', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
        </div>
    </div>


    <div class="form-group row required">
    {!! Form::label('number_of_pit_rings', 'Number of Pit Rings', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('number_of_pit_rings', null, ['class' => 'form-control', 'placeholder' => 'Enter Number of Pit Rings', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('pit_diameter', 'Pit Diameter', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('pit_diameter', null, ['class' => 'form-control', 'placeholder' => 'Enter Pit Diameter', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('pit_depth', 'Pit Depth', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('pit_depth', null, ['class' => 'form-control', 'placeholder' => 'Enter Pit Depth', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('appropriate_desludging_vehicle_size', 'Appropriate Desludging Vehicle Size', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('appropriate_desludging_vehicle_size', null, ['class' => 'form-control', 'placeholder' => 'Enter Desludging Vehicle Size', 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('number_of_trips', 'Number of Trips', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('number_of_trips', null, ['class' => 'form-control', 'placeholder' => 'Enter Number of Trips', 'oninput' => "this.value = this.value < 1 ? '' : this.value"]) !!}
    </div>
</div>

<div class="form-group row required">
    {!! Form::label('confirmed_emptying_date', 'Confirmed Emptying Date', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
       {!! Form::text('confirmed_emptying_date', $application ? $application->proposed_emptying_date : null, [
            'class' => 'form-control flatpickr-reschedule',
            'id' => 'confirmed_emptying_date',
            'autocomplete' => 'off',
            'placeholder' => 'mm/dd/yyyy',
            'style' => 'background-color: #fff !important; cursor: pointer;'
        ]) !!}

    </div>
</div>

<div class="form-group row required">
    {!! Form::label('advance_paid_amount', 'Advance Paid Amount', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::number('advance_paid_amount', null, ['class' => 'form-control', 'placeholder' => 'Enter Advance Paid Amount', 'step' => '0.01', 'oninput' => "this.value = this.value < 1 ? '' : this.value"]) !!}
    </div>
</div>

    
</div>
<div class="card-footer">
<a href="{{ action('Fsm\ApplicationController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div>


