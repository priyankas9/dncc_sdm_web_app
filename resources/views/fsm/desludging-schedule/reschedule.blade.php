<div class="modal fade" id="rescheduleEmptyingModals" tabindex="-1" role="dialog" aria-labelledby="rescheduleEmptyingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="rescheduleEmptyingModalLabel"> Reschedule Emptying Date</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
         <form id="rescheduleFormContainer" method="POST">
            @csrf
            <div class="modal-body" id="rescheduleForm" >
                        <input type="hidden" id="binId_reschedule" name="bin">
                        <!-- Owner Details Section -->
                        <div id="ownerDetailsSection">
                            <h5 class="mb-3">Owner Details</h5>
                            <div class="form-group row">
                                {!! Form::label('customer_name', 'Owner Name', ['class' => 'col-sm-4 col-form-label']) !!}
                                <div class="col-sm-8">
                                    {!! Form::text('customer_name', null, ['class' => 'form-control', 'placeholder' => 'Owner Name','id' => 'customer_names']) !!}
                                </div>
                            </div>
                            <div class="form-group row">
                                {!! Form::label('customer_contact', 'Owner Contact (Phone)', ['class' => 'col-sm-4 col-form-label']) !!}
                                <div class="col-sm-8">
                                    {!! Form::text('customer_contact', null, ['class' => 'form-control', 'placeholder' => 'Owner Contact (Phone)','id'=> 'customer_contacts']) !!}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Applicant Details Section -->
                        <h5 class="mb-3 d-flex align-items-center justify-content-between">
                            Applicant Details
                           
                        </h5>
                        <div class="form-group row">
                            {!! Form::label('applicant_name', 'Applicant Name', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('applicant_name', null, ['class' => 'form-control', 'placeholder' => 'Applicant Name']) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('applicant_gender', 'Applicant Gender', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::select('applicant_gender', array("Male"=>"Male","Female"=>"Female","Other" =>"Other"),null, ['class' => 'form-control', 'placeholder' => 'Applicant Gender']) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('applicant_contact', 'Applicant Contact Number', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('applicant_contact', null, ['class' => 'form-control', 'placeholder' => 'Applicant Contact Number']) !!}
                            </div>
                        </div>
                        <hr class="my-4">
                        <!-- Application Details Section -->
                        <h5 class="mb-3">Application Details</h5>
                        <div class="form-group row">
                                {!! Form::label('service_provider_id', 'Service Provider', ['class' => 'col-sm-4 col-form-label']) !!}
                                <div class="col-sm-8">
                                    {!! Form::select('service_provider_id', $serviceProvider, null, ['class' => 'form-control', 'placeholder' => 'Select Service Provider']) !!}
                                </div>
                        </div>
                        <div class="form-group row">
                        {!! Form::label('proposed_emptying_date', 'Proposed Emptying Date', ['class' => 'col-sm-4 col-form-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::date('proposed_emptying_date', null, ['class' => 'form-control', 'placeholder' => 'Proposed Emptying Date']) !!}
                          
                        </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('supervisory_assessment_date', 'Supervisory Assessment Date', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::date('supervisory_assessment_date', null, ['class' => 'form-control', 'placeholder' => 'Supervisory Assessment Date']) !!}
                            </div>
                        </div>

                     
                     
            </div>
         
                    <div class="modal-footer" style=" justify-content: flex-end;"" id="buttons">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
            <!-- Buttons -->
          
        </form>
            </div>
        </div>
    </div>