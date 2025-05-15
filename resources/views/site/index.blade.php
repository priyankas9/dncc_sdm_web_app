@extends('layouts.dashboard')
@push('style')
<style type="text/css">
    .dataTables_filter {
        display: none;
    }

    .form-title-row {
        display: flex;
        align-items: center;
    }

    .form-title-row h2 {
        margin: 0;
        padding: 2px 0;
    }

    .disabled-select {
        color: black;
    }

    /* Change the text color of the selected options in the Select2 dropdown */
    /* Change the text color of the selected options in the Select2 dropdown */
    /* Change the text color of the selected options in the dropdown */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: black;
    }

    .readonly-select2 .select2-selection__choice {
        color: black !important;
        /* Ensures text color is black */
        background-color: #f0f0f0;
        /* Optional: Change background color to differentiate */
    }

    .select2-multi {
        color: red;
    }

    .readonly-select2 .select2-selection__choice__remove {
        display: none;
        /* Optional: Hide the remove button if you don't want users to deselect options */
    }

    .select2-selection__choice {
        color: black;
    }

    .select2-selection__choice__display {
        color: red;
    }

    /* Change the text color of the selected options in the dropdown list */
    .select2-container--default .select2-results__option[aria-selected="true"] {
        color: red !important;
    }
</style>
@endpush
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="card">
    <div class="card-body">
        {!! Form::model([
        'method' => 'PATCH',
        'action' => ['Site\SiteSettingController@update'],
        'class' => 'form-horizontal',
        'id' => 'editForm',
        ]) !!}
        <div class="container-fluid">
            <div class="row form-title-row">
                <div class="col-sm-3">
                    <p style="font-size: 20px; font-style: bold;">Name</p>
                </div>
                <div class="col-sm-2">
                    <p style="font-size: 20px;font-style: bold;">Value</p>
                </div>
                <div class="col-sm-2">
                    <p style="font-size: 20px;font-style: bold;">Remarks</p>
                </div>
            </div>
            <hr>
        </div>
        @foreach ($data as $key => $details)
        <div class="form-group row">
    {!! Form::label($key, ucwords(str_replace('_', ' ', $key)), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-2">
        @php
            $inputType = 'text';  // Default input type
            $options = [];  // Placeholder for options
            // Clean up options if provided
            if (isset($details['options'])) {
                if (is_string($details['options'])) {
                    $optionsString = trim($details['options'], '"\'');
                    $optionsArray = explode(',', $optionsString);
                    $options = array_map('trim', $optionsArray);
                } elseif (is_array($details['options'])) {
                    $options = array_map('trim', $details['options']);
                }
            }
            // Set input type based on data_type
            if (str_contains($details['data_type'], 'integer')) {
                $inputType = 'number';
            } elseif (str_contains($details['data_type'], 'date')) {
                $inputType = 'date';
                
            } elseif (str_contains($details['data_type'], 'multi')) {
                $inputType = 'multi';
            } elseif (str_contains($details['data_type'], 'minput')) {
                $inputType = 'minput'; // Text input for comma-separated dates
            } elseif (str_contains($details['data_type'], 'select')) {
                $inputType = 'select';
            }
        @endphp
        @if ($inputType === 'text')
            {{-- Input for comma-separated holiday dates --}}
            {!! Form::text($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),
                'placeholder' => 'Enter dates as YYYY-MM-DD, separated by commas'
            ]) !!}
        @elseif ($inputType === 'select')
            {!! Form::select($key, array_combine($options, $options), old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : '')
            ]) !!}
        @elseif ($inputType === 'date')
            {!! Form::date($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),
                'onclick' => 'this.showPicker();'
            ]) !!}
        @elseif ($inputType === 'multi')
            {!! Form::select($key . '[]', array_combine($options, $options), old($key, explode(',', $details['value'])), [
                'class' => 'form-control select2-multi' . ($errors->has($key) ? ' is-invalid' : ''),
                'multiple' => 'multiple'
            ]) !!}
            @elseif ($inputType === 'minput')
            {!! Form::text($key, old($key, $details['value']), [
                'class' => 'form-control flatpickr-multiple' . ($errors->has($key) ? ' is-invalid' : ''),
                'placeholder' => 'Select multiple dates'
            ]) !!}
        @elseif ($inputType === 'number')
            {!! Form::number($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),
                'oninput' => "this.value = this.value < 1 ? '' : this.value",
            ]) !!}
        @else
            {!! Form::$inputType($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : '')
            ]) !!}
        @endif
        @if ($errors->has($key))
            <span class="invalid-feedback">{{ $errors->first($key) }}</span>
        @endif
    </div>
    <div class="col-sm-5">
        {!! Form::text($key . '_remark', old($key . '_remark', $details['remarks']), [
            'class' => 'form-control' . ($errors->has($key . '_remark') ? ' is-invalid' : ''),
            'placeholder' => 'Remark'
        ]) !!}
        @if ($errors->has($key . '_remark'))
            <span class="invalid-feedback">{{ $errors->first($key . '_remark') }}</span>
        @endif
    </div>
    </div>
        @endforeach
    </div>
</div><!-- /.box-body -->
<div class="card-footer">
    <span id="editButton" class="btn btn-info">Edit</span>
    <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">Save</button>
</div><!-- /.box-footer -->
</div>
{!! Form::close() !!}
</div>
</div><!-- /.box -->
@stop
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Function to toggle readonly attribute
        function toggleReadOnly(readonly) {
            $('input').prop('readonly', readonly);
            $('select').prop('disabled', readonly);
        }
        // Initially set form fields as read-only
        toggleReadOnly(true);
        // Edit button click event
        $('#editButton').click(function() {
            $('input').removeAttr('readonly');
            $('select').removeAttr('disabled');
            $('#editButton').hide();
            $('#saveButton').show();
        });
        // Check for errors and update buttons accordingly
        var hasErrors = $('.alert-danger').length > 0;
        if (hasErrors) {
            $('input').removeAttr('readonly');
            $('select').removeAttr('disabled');
            $('#editButton').hide();
            $('#saveButton').show();
        } else {
            $('#saveButton').hide();
            $('#editButton').show();
        }
        // Initialize select2 for multi-select fields
        $('.select2-multi').select2({
            placeholder: 'Select options',
            allowClear: true
        }).on('select2:select', function(e) {
            // Add inline style to selected options
            $(this).next('.select2-container').find('.select2-selection__choice').css('color', 'black');
        });
        // Handle form submission for multi-select fields
        $('form').on('submit', function(e) {
            const multiselectFields = $('select[multiple]');
            multiselectFields.each(function() {
                const selectedOptions = $(this).val();
                const hiddenInput = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', this.name.replace('[]', ''))
                    .val(selectedOptions.join(','));
                $(this).after(hiddenInput);
                $(this).prop('disabled', true);
            });
        });
        flatpickr('.flatpickr-multiple', {
            mode: 'multiple',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y'
        });
    });
</script>
@endpush