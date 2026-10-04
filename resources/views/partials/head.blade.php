<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link
    href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|inter:400,500,600,700|poppins:400,500,600,700"
    rel="stylesheet"
/>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
