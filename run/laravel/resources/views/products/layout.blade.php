<!DOCTYPE html>
<html>

<head>
    <title>Laravel Demo App Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</head>

<body>

    <div class="container pt-5">
        <div class="row">
            <div class="col-lg-12 pt-3 pb-4">
                <div class="float-start">
                    <h2> @yield('title')</h2>
                </div>
                <div class="float-end">
                     @yield('actions')
                </div>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Uh-oh!</strong> There was an error:<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </div>

</body>

</html>