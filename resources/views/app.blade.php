<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Testa uzdevums</title>

        @vite([
            "resources/css/app.css",
            "resources/js/app.ts",
            "resources/js/Pages/{$page['component']}.vue"
         ])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
