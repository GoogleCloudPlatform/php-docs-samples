@extends('products.layout')

@section('title')
Products
@endsection

@section('actions')
<a class="btn btn-success" href="{{ route('products.create') }}"> Create New Product</a>
@endsection

@section('content')
@if (count($products) > 0)
<table class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th style="width:10em">Actions</th>
    </tr>
    @foreach ($products as $product)
    <tr>
        <td><a href="{{ route('products.show',$product->id) }}">{{ $product->id }}</a></td>
        <td>{{ $product->name }}</td>
        <td>{{ $product->description }}</td>
        <td>
            <form action="{{ route('products.destroy',$product->id) }}" method="POST">

                <a class="btn btn-primary" href="{{ route('products.edit',$product->id) }}">Edit</a>

                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
@else
<p>No products. <a href="{{ route('products.create') }}">Create one.</a>
    @endif

    {!! $products->links() !!}

    @endsection