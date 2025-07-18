<div class="card-body">
	
	<div class="form-group row">
		{!! Form::label('subject','Subject',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('subject',null,['class' => 'form-control', 'placeholder' => 'Subject']) !!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('paragraph','Body',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::textarea('paragraph',null,['class' => 'form-control', 'placeholder' => 'Body']) !!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('unique_ref','Unique Reference Number',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('unique_ref',null,['class' => 'form-control', 'placeholder' => 'Unique Reference Number']) !!}
		</div>
	</div><div class="form-group row">
		{!! Form::label('date','Date of Notice',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('date',null,['class' => 'form-control', 'placeholder' => 'Date of Notice']) !!}
		</div>
	</div>
	
</div><!-- /.box-body -->
<div class="card-footer">
	<a href="{{ action('Pdf\PdfController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

