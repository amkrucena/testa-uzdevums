<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Testa uzdevums</title>

        @vite("resources/css/app.css")
        @vite("resources/js/app.ts")
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
