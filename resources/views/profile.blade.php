@extends('main')

@section('content')

<div class="row justify-content-center">
	<div class="col-md-4">
		<div class="card">
			<div class="card-header">Profile</div>
			<div class="card-body">
				<form action="{{ route('sample.profile_validation') }}" method="post" enctype="multipart/form-data">
					@csrf
                    @foreach ($data as $row)
					<div class="form-group mb-3">
					<input type="text" name="name" class="form-control" placeholder="Name" value="{{$row->name}}"/>
					@if($errors->has('name'))
						<span class="text-danger">{{ $errors->first('name') }}</span>
					@endif
				</div>
				<div class="form-group mb-3">
					<input type="text" name="email" class="form-control" placeholder="Email Address" value="{{$row->email}}"/>
					@if($errors->has('email'))
						<span class="text-danger">{{ $errors->first('email') }}</span>
					@endif
				</div>
				<div class="form-group mb-3">
					<input type="password" name="password" class="form-control" placeholder="Password"/>
					@if($errors->has('password'))
						<span class="text-danger">{{ $errors->first('password') }}</span>
					@endif
				</div>
                <div class="form-group mb-3">
					<input type="file" name="user_image" class="form-control" />
					@if($errors->has('user_image'))
						<span class="text-danger">{{ $errors->first('user_image') }}</span>
					@endif
                    <br/>
                    @if ($row->user_image != '')
                        <img src="{{asset('images/'. $row->user_image)}}" width="150" alt="img-thumbnail">
                    @endif
                    <input type="hidden" name="hidden_user_image" value="{{$row->user_image}}">
				</div>

                <div class="d-grid mx-auto">
                    <button type="submit" class="btn btn-dark btn-block">Save</button>
                </div>


                    @endforeach
				</form>
			</div>
		</div>
	</div>
</div>

@endsection('content')
