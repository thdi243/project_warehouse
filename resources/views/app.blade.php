<!DOCTYPE html>
<html lang="en">

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Warehouse</title>
        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">

        @viteReactRefresh
        @vite('resources/js/app.js')
    </head>

    <body>
        <div id="root"></div>
    </body>

</html>
