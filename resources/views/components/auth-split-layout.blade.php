<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>OrbitDocs</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col md:flex-row">
            
            <!-- Left Side: Branding & Background -->
            <div class="w-full md:w-1/2 lg:w-3/5 bg-gray-900 relative overflow-hidden flex flex-col justify-center items-center text-white p-12">
                <!-- Background Decoration (Gradients/Circles) -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-purple-900 to-gray-900 opacity-90"></div>
                <!-- Decorative Circles -->
                <div class="absolute top-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2 animate-blob"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 translate-x-1/2 translate-y-1/2 animate-blob animation-delay-2000"></div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col items-center text-center max-w-lg">
                    <!-- Logo -->
                    <div class="mb-8 p-4 bg-white/10 backdrop-blur-sm rounded-2xl shadow-xl">
                        {{-- Force Default Logo for now --}}
                        <x-application-logo class="w-24 h-24 fill-current text-white" />
                    </div>
                    
                    <!-- System Name -->
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-300">
                        OrbitDocs
                    </h1>
                    
                    <!-- Tagline/Description -->
                    <p class="text-lg text-gray-300 mb-8 font-light">
                        Secure Documentation & Asset Management for MSPs.
                    </p>

                    <p class="text-sm text-gray-400 font-medium">
                        Login into your account to access the dashboard.
                    </p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="w-full md:w-1/2 lg:w-2/5 flex items-center justify-center p-8 bg-white dark:bg-gray-800">
                <div class="w-full max-w-md space-y-8">
                    <div class="text-center md:text-left">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ $title ?? 'Welcome Back' }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $description ?? 'Please enter your details to sign in.' }}
                        </p>
                    </div>

                    {{ $slot }}
                    
                    <div class="mt-8 text-center text-xs text-gray-400">
                        &copy; {{ date('Y') }} OrbitDocs. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
