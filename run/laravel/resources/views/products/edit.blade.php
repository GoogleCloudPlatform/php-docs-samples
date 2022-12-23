@extends('products.layout')

@section('title')
Edit Product #{{$product->id}}
@endsection

@section('actions')
<a class="btn btn-primary" href="{{ route('products.index') }}">Back</a>
@endsection

@section('content')

<form action="{{ route('products.update',$product->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <label class="col-form-label col-sm-2">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" value="{{ $product->name }}" class="form-control" placeholder="Name">
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-form-label col-sm-2">Description</label>
        <div class="col-sm-10">
            <textarea class="form-control" style="height: 150px" name="description" placeholder="Product Description">{{ $product->description }}</textarea>
        </div>
    </div>
    <div class="wide action">
        <button type="submit" class="btn btn-success">Update</button>
    </div>

</form>
@endsection