@extends('products.layout')

@section('title')
Create New Product
@endsection

@section('content')

<form action="{{ route('products.store') }}" method="POST">
    @csrf

    <div class="row mb-3">
        <label class="col-form-label col-sm-2">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" placeholder="Name">
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-form-label col-sm-2">Description</label>
        <div class="col-sm-10">
            <textarea class="form-control" style="height: 150px" name="description" placeholder="Product Description"></textarea>
        </div>
    </div>
    <div class="wide action">
        <button type="submit" class="btn btn-success">Create</button>
        <a class="btn btn-primary" href="{{ route('products.index') }}"> Back</a>
    </div>
    </div>

</form>
@endsection