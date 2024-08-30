@extends('layouts.app')

@section('head')
<style>
	.position-ref {
		margin-top:100px;
	}
	.code {
		font-size: 26px;
		padding: 0 15px 0 15px;
		text-align: center;
	}
	.well {
		width:40%;
		min-height: 20px;
		padding: 0px;
		margin-bottom: 20px;
		background-color: #f5f5f5;
		border: 1px solid #e3e3e3;
		border-radius: 0px;
		-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
		box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
	}
	code {
		padding: 2px 4px;
		font-size: 90%;
		color: #c7254e;
		background-color: #ecd8d8;
		border-radius: 4px;
	}
</style>
@stop

@section('content')
<div class="position-ref full-height">
	<div class="flex-center">
		<div class="code">
			{{ $response->codigo }} | <span style="font-size:18px;">{{ $response->mensaje }}</span>
		</div>

		@if (env('APP_DEBUG') && isset($response->detalle) && $response->detalle)
		<div class="code">
			<a role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="font-size:12px;">Ver detalle</a>

			<div class="collapse" id="collapseExample" style="margin-top:-10px;">
				<center>
					<code style="font-size:12px;">{{ $response->detalle }}</code>
				</center>
			</div>
		</div>
		@endif
	</div>
</div>
@stop