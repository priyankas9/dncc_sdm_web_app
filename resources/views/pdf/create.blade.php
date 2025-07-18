@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
{!! Form::open(['url' => 'pdf/pdf-generation', 'class' => 'form-horizontal']) !!}
		@include('pdf.partial-form', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.box -->
@stop