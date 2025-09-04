<!DOCTYPE html>
<html lang="en" class="">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @routes
	@vite(['resources/js/app.js', 'resources/css/app.scss'])
	@inertiaHead
</head>

<body class="bg-zinc-50 font-sans text-black/90 antialiased dark:bg-zinc-900 dark:text-white/90">
	@inertia
</body>

</html>
