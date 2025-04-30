@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
{!! Form::model($pdf_data, ['method' => 'PATCH', 'url' => 'pdf/pdf-generation/' . $pdf_data->id, 'class' => 'form-horizontal']) !!}
		@include('pdf/partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.box -->
@stop