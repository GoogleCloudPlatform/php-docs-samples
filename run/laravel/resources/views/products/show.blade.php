@extends('products.layout')

@section('title')
Product #{{$product->id}}
@endsection

@section('actions')
<a class="btn btn-secondary" href="{{ route('products.index') }}">Back</a>
<a class="btn btn-primary" href="{{ route('products.edit',$product->id) }}">Edit</a>
@endsection

@section('content')
<div class="row mb-3">
    <label class="col-form-label col-sm-2">Name</label>
    <div class="col-sm-10  display-data">
        {{ $product->name }}
    </div>
</div>

<div class="row mb-3">
    <label class="col-form-label col-sm-2">Description</label>
    <div class="col-sm-10 display-data">

        {{ $product->description }}
    </div>
</div>
@endsection