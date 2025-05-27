@extends('layouts.dashboard')
@push('style')
<style type="text/css">
   /* Hide DataTables search box */
        .dataTables_filter {
            display: none;
        }

        /* Title row styling */
        .form-title-row {
            display: flex;
            align-items: center;
        }

        .form-title-row h2 {
            margin: 0;
            padding: 2px 0;
        }

        /* Disabled select appearance */
        .disabled-select {
            color: black;
        }

        /* Select2 full width */
        .select2-container {
            width: 100% !important;
        }

        /* Multi-select choice styling */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: red;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
        }

        /* Hide remove button in readonly multi-select */
        .readonly-select2 .select2-selection__choice__remove {
            display: none;
        }

        /* Selected option in dropdown list */
        .select2-container--default .select2-results__option[aria-selected="true"] {
            color: red !important;
        }

        /* Hover highlight color for options */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #e0e0e0;
            color: black;
        }

        /* Placeholder text */
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
            color: #999;
        }

        /* Margin consistency */
        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            padding: 8px 12px;
        }

        /* Flatpickr multiple input styling */
        .flatpickr-multiple {
            background-color: #fff;
            border: 1px solid #ced4da;
            padding: 8px 12px;
            border-radius: 4px;
        }

        /* Buttons spacing */
        .card-footer button,
        .card-footer span {
            margin-right: 8px;
        }
        input[disabled].flatpickr-multiple {
            pointer-events: none;
            background-color: #e9ecef;
            opacity: 1;
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
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : '') ,  'placeholder' => $details['name']
            ]) !!}
        @elseif ($inputType === 'date')
            {!! Form::date($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),
                'onclick' => 'this.showPicker();', 'placeholder' => $details['name']
            ]) !!}
        @elseif ($inputType === 'multi')
              {!! Form::select($key . '[]', array_combine($options, $options), old($key, explode(',', $details['value'])), [
            'class' => 'form-control select2-multi' . ($errors->has($key) ? ' is-invalid' : ''),
            'multiple' => 'multiple',   'placeholder' => $details['name']
                 ]) !!}
            @elseif ($inputType === 'minput')
            {!! Form::text($key, old($key, $details['value']), [
                'class' => 'form-control flatpickr-multiple' . ($errors->has($key) ? ' is-invalid' : ''),
                  'placeholder' => $details['name']
            ]) !!}
        @elseif ($inputType === 'number')
            {!! Form::number($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),
                'oninput' => "this.value = this.value < 1 ? '' : this.value",  'placeholder' => $details['name']
            ]) !!}
        @else
            {!! Form::$inputType($key, old($key, $details['value']), [
                'class' => 'form-control' . ($errors->has($key) ? ' is-invalid' : ''),  'placeholder' => $details['name']
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
$(document).ready(function () {
    // Initialize Select2 for multi-select fields
    $('.select2-multi').select2({
        width: '100%'
    });

    // Initialize flatpickr and store instances
    const flatpickrInstances = [];
    $('.flatpickr-multiple').each(function() {
        const instance = flatpickr(this, {
            mode: 'multiple',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            onReady: function(selectedDates, dateStr, instance) {
                // Store the instance
                flatpickrInstances.push(instance);
                // Set initial state based on readonly
                if ($(instance.element).prop('readonly')) {
                    disableFlatpickrInstance(instance);
                }
            }
        });
    });

    // Function to disable a flatpickr instance
    function disableFlatpickrInstance(instance) {
        instance.set('clickOpens', false);
        instance._input.disabled = true;
        instance._input.readOnly = true;
        instance._input.style.pointerEvents = 'none';
        instance._input.style.backgroundColor = '#e9ecef';
        instance.close(); // Ensure calendar is closed
    }

    // Function to enable a flatpickr instance
    function enableFlatpickrInstance(instance) {
        instance.set('clickOpens', true);
        instance._input.disabled = false;
        instance._input.readOnly = false;
        instance._input.style.pointerEvents = 'auto';
        instance._input.style.backgroundColor = '#fff';
    }

    // Function to toggle readonly/disabled state
    function toggleReadOnly(readonly) {
        $('input').not('.flatpickr-multiple').prop('readonly', readonly);
        $('select').prop('disabled', readonly);

        if (readonly) {
            // Disable select2s visually
            $('.select2-multi').prop('disabled', true).trigger('change');
            
            // Disable all flatpickr instances
            flatpickrInstances.forEach(instance => {
                disableFlatpickrInstance(instance);
            });
        } else {
            $('.select2-multi').prop('disabled', false).trigger('change');
            
            // Enable all flatpickr instances
            flatpickrInstances.forEach(instance => {
                enableFlatpickrInstance(instance);
            });
        }
    }

    // Initially readonly
    toggleReadOnly(true);

    // Edit button click
    $('#editButton').click(function () {
        toggleReadOnly(false);
        $('#editButton').hide();
        $('#saveButton').show();
    });

    // Check if form has validation errors, then unlock fields
    if ($('.alert-danger').length > 0) {
        toggleReadOnly(false);
        $('#editButton').hide();
        $('#saveButton').show();
    } else {
        $('#saveButton').hide();
        $('#editButton').show();
    }
});
</script>
@endpush