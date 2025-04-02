@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<div class="card card-info">
    {!! Form::open(['url' => 'fsm/supervisory-assessment', 'class' => 'form-horizontal']) !!}
    
    <!-- Hidden input to pass the slug value -->
    <input type="hidden" name="slug" value="{{ $value }}">

    @include('fsm.supervisory-assessment.partial-form', ['submitButtomText' => 'Save'])

    {!! Form::close() !!}
</div><!-- /.card -->

<script> 
    // You can add any custom JS if needed here
</script>
@stop
