<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="data()" :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>OrbitDocs</title>

        <script>
            // Immediately apply the theme to avoid FOUC
            if (localStorage.getItem('dark') === 'true' || (!('dark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                window.dark = true;
            } else {
                document.documentElement.classList.remove('dark');
                window.dark = false;
            }
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <script>
            window.SEARCH_URL = "{{ route('api.search') }}";
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- CKEditor 5 -->
        <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
        
        <style>
            .ck-editor__editable_inline { min-height: 200px; }
            
            /* CKEditor Dark Mode Comprehensive Overrides */
            .dark .ck-reset_all, .dark .ck-reset_all * { color: #d1d5db !important; }
            .dark .ck.ck-editor__main>.ck-editor__editable { 
                background: #1f2937 !important; 
                border-color: #374151 !important; 
                color: #d1d5db !important; 
            }
            .dark .ck.ck-toolbar { background: #374151 !important; border-color: #374151 !important; }
            .dark .ck.ck-button { background: #374151 !important; color: #d1d5db !important; }
            .dark .ck.ck-button:hover { background: #4b5563 !important; }
            .dark .ck.ck-button.ck-on { background: #6b7280 !important; color: #fff !important; }
            .dark .ck-toolbar__separator { background: #4b5563 !important; }
            
            /* Balloon and Dropdowns */
            .dark .ck.ck-list { background: #1f2937 !important; }
            .dark .ck.ck-list__item .ck-button:hover:not(.ck-disabled) { background: #374151 !important; }
            .dark .ck.ck-balloon-panel { background: #1f2937 !important; border-color: #374151 !important; }
            .dark .ck.ck-balloon-panel[class*="arrow_n"]:after { border-bottom-color: #1f2937 !important; }
            .dark .ck.ck-balloon-panel[class*="arrow_s"]:after { border-top-color: #1f2937 !important; }
            
            /* Input fields in balloons */
            .dark .ck.ck-input { background: #374151 !important; border-color: #4b5563 !important; color: #d1d5db !important; }
            
            /* Tables in editor and content */
            .dark .ck-content table td, .dark .ck-content table th { border-color: #4b5563 !important; color: #d1d5db !important; }
            .dark .ck-content blockquote { border-left-color: #6b7280 !important; color: #9ca3af !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-100 dark:bg-gray-900 dark:text-gray-200">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
            
            @if(!($hideSidebar ?? false))
                @include('layouts.sidebar')
            @endif

            <!-- Mobile Sidebar Backdrop -->
            <div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"></div>
            
            <!-- Mobile Sidebar -->
            <aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden" x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150" x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu" @keydown.escape="closeSideMenu">
               <!-- Mobile sidebar content same as desktop, ideally shared component, simplifying for now -->
               <div class="py-4 text-gray-500 dark:text-gray-400">
                    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">OrbitDocs</a>
                    <ul class="mt-6">
                        <li class="relative px-6 py-3">
                             <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="{{ route('dashboard', $currentOrganization->slug ?? Auth::user()->organizations->first()->slug ?? '#') }}">
                                <span class="ml-4">Dashboard</span>
                            </a>
                        </li>
                    </ul>
               </div>
            </aside>

            <div class="flex flex-col flex-1 w-full">
                <header class="z-10 py-4 bg-white shadow-sm dark:bg-[#1a1c23] border-b border-gray-100 dark:border-gray-700/50">
                    <div class="flex items-center justify-between h-full px-6 {{ ($hideSidebar ?? false) ? 'w-full' : 'container mx-auto' }} text-purple-600 dark:text-purple-300">
                        <!-- Left: Logo & Nav -->
                        <div class="flex items-center justify-start {{ ($hideSidebar ?? false) && !($topNav ?? false) ? 'md:w-1/4' : 'flex-1' }}">
                            <!-- Mobile hamburger -->
                            <button class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple" @click="isSideMenuOpen = !isSideMenuOpen" aria-label="Menu">
                                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                            </button>

                            @if($hideSidebar ?? false)
                                <a class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center mr-8" href="{{ route('root') }}">
                                    @if($logo = \App\Models\Setting::get('system_logo'))
                                        <img src="{{ asset('storage/' . $logo) }}" alt="System Logo" class="h-8 w-auto mr-3">
                                    @else
                                        <x-application-logo class="h-8 w-8 mr-3 fill-current text-purple-600" />
                                    @endif
                                    <span class="hidden lg:inline-block font-extrabold tracking-tighter">{{ \App\Models\Setting::get('system_name', 'OrbitDocs') }}</span>
                                </a>
                            @endif

                            @if(isset($currentOrganization))
                                <div class="hidden md:flex items-center ml-4 mr-6 px-3 py-1 bg-purple-100 dark:bg-purple-900/50 border border-purple-200 dark:border-purple-700 rounded-md shadow-sm">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 01-2-2H7a2 2 0 012 2h6z"></path></svg>
                                    <span class="text-xs font-extrabold uppercase tracking-widest text-purple-700 dark:text-purple-300">
                                        {{ $currentOrganization->name }} {{ $currentOrganization->isSuspended() ? '(Suspended)' : '' }}
                                        
                                        @php
                                            $contextSite = null;
                                            // Check Route Model Binding
                                            if (request()->route('site') instanceof \App\Models\Site) {
                                                $contextSite = request()->route('site');
                                            }
                                            // Check Query Parameter
                                            elseif (request()->filled('site_id')) {
                                                $contextSite = \App\Models\Site::find(request('site_id'));
                                            }
                                            // Check if Viewing Item with Site (Optional, might be heavy, skipping for now to keep it clean)
                                        @endphp

                                        @if($contextSite)
                                            <span class="text-gray-400 dark:text-gray-500 mx-1">/</span>
                                            {{ $contextSite->name }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if($topNav ?? false)
                                <nav class="hidden md:flex items-center space-x-6">
                                    <a href="{{ route('dashboard', $currentOrganization->slug ?? Auth::user()->organizations->first()->slug ?? '#') }}" class="text-xs font-bold uppercase tracking-widest {{ request()->routeIs('dashboard') ? 'text-purple-600 dark:text-purple-400 border-b-2 border-purple-600' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} pb-1 transition-colors">Dashboard</a>
                                    <a href="{{ route('organizations.index') }}" class="text-xs font-bold uppercase tracking-widest {{ request()->routeIs('organizations.index') ? 'text-purple-600 dark:text-purple-400 border-b-2 border-purple-600' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} pb-1 transition-colors">Organizations</a>
                                    
                                    @can('user.view')
                                    <a href="{{ route('users.index') }}" class="text-xs font-bold uppercase tracking-widest {{ request()->routeIs('users.index') ? 'text-purple-600 dark:text-purple-400 border-b-2 border-purple-600' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} pb-1 transition-colors">Users</a>
                                    @endcan

                                    <a href="{{ route('root') }}" class="text-xs font-bold uppercase tracking-widest {{ request()->routeIs('root') ? 'text-purple-600 dark:text-purple-400 border-b-2 border-purple-600' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} pb-1 transition-colors">Global</a>
                                </nav>
                            @endif
                        </div>

                        <!-- Center: Search Bar -->
                        <div class="flex-1 max-w-xl mx-4 group">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none group-focus-within:text-purple-500">
                                    <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input 
                                    @click="openSearch"
                                    class="w-full pl-10 pr-4 py-2 text-sm text-gray-700 placeholder-gray-500 bg-gray-100 border-transparent rounded-lg dark:placeholder-gray-500 dark:bg-gray-700/50 dark:text-gray-200 focus:placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:border-purple-300 focus:outline-none focus:shadow-outline-purple transition-all duration-200 cursor-pointer" 
                                    type="text" 
                                    placeholder="Search anything... ( press ' / ' )" 
                                    readonly
                                />
                            </div>
                        </div>

                        <!-- Right: Profile & Theme -->
                        <div class="flex items-center justify-end {{ ($hideSidebar ?? false) && !($topNav ?? false) ? 'md:w-1/4' : 'flex-1' }}">

                        <!-- Profile menu -->
                        <ul class="flex items-center flex-shrink-0 space-x-6">
                            <!-- Theme toggler -->
                            <li class="flex">
                                <button class="rounded-md focus:outline-none focus:shadow-outline-purple" @click="toggleTheme" aria-label="Toggle Color Mode">
                                    <!-- Sun/Moon SVG -->
                                    <template x-if="!dark">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                                    </template>
                                    <template x-if="dark">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100 2h1a1 1 0 100-2h-1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
                                    </template>
                                </button>
                            </li>

                            @if($hideSidebar ?? false)
                            <li class="flex border-l dark:border-gray-700 pl-6 ml-2">
                                <a href="{{ route('profile.edit') }}" class="flex items-center text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-purple-600 transition-colors group">
                                     <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Profile
                                </a>
                            </li>

                            <li class="flex border-l dark:border-gray-700 pl-6 ml-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center text-xs font-bold uppercase tracking-widest text-red-500 hover:text-red-700 transition-colors group">
                                        <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </li>
                            @endif
                            
                            <!-- Profile -->
                            <li class="relative" x-data="{ isProfileMenuOpen: false }">
                                <button class="align-middle px-3 py-2 rounded-md focus:shadow-outline-purple focus:outline-none" 
                                        @click="isProfileMenuOpen = !isProfileMenuOpen" 
                                        @keydown.escape="isProfileMenuOpen = false" 
                                        aria-label="Account" 
                                        aria-haspopup="true">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">
                                        {{ Auth::user()->name }}
                                    </span>
                                </button>
                                <div x-show="isProfileMenuOpen" 
                                     x-transition:leave="transition ease-in duration-150" 
                                     x-transition:leave-start="opacity-100" 
                                     x-transition:leave-end="opacity-0" 
                                     @click.away="isProfileMenuOpen = false" 
                                     @keydown.escape="isProfileMenuOpen = false" 
                                     class="absolute right-0 z-50 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700" 
                                     aria-label="submenu"
                                     style="display: none;">
                                    <ul class="flex flex-col">
                                        <li class="flex">
                                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200" href="{{ route('organizations.index') }}">
                                                <svg class="w-4 h-4 mr-3" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 01-2-2H7a2 2 0 012 2h6z"></path>
                                                </svg>
                                                <span>Manage Organizations</span>
                                            </a>
                                        </li>
                                        @if(Auth::user()->is_super_admin || Auth::user()->can('user.manage'))
                                        <li class="flex">
                                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200" href="{{ route('roles.index') }}">
                                                <svg class="w-4 h-4 mr-3" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                </svg>
                                                <span>Roles & Permissions</span>
                                            </a>
                                        </li>
                                        <li class="flex">
                                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200" href="{{ route('audit-logs.index') }}">
                                                <svg class="w-4 h-4 mr-3" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <span>Audit Logs</span>
                                            </a>
                                        </li>
                                        @endif
                                        <li class="flex">
                                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200" href="{{ route('profile.edit') }}">
                                                <svg class="w-4 h-4 mr-3" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Profile</span>
                                            </a>
                                        </li>
                                        <li class="flex">
                                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                                    <svg class="w-4 h-4 mr-3" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h7a3 3 0 013 3v1"></path>
                                                    </svg>
                                                    <span>Log out</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </header>
                
                <main class="flex-1 overflow-y-auto">
                    <div class="px-6 mx-auto grid w-full">
                        @if (isset($header))
                        <div class="my-6">
                            {{ $header }}
                        </div>
                        @endif
                        
                        {{ $slot }}
                    </div>
                </main>
                <x-footer />
            </div>
        </div>

        <!-- Global Search Overlay -->
        <div 
            x-show="isSearchOpen" 
            x-transition:enter="transition ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            x-transition:leave="transition ease-in duration-100" 
            x-transition:leave-start="opacity-100 scale-100" 
            x-transition:leave-end="opacity-0 scale-95" 
            class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4 sm:px-6 mt-16"
            @keydown.window.slash="if (['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName) || $event.target.isContentEditable) return; $event.preventDefault(); openSearch()"
            @keydown.window.ctrl.k.prevent="openSearch"
            @keydown.window.escape="closeSearch"
            x-cloak
        >
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="closeSearch"></div>

            <div class="relative bg-white dark:bg-[#1a1c23] w-full max-w-5xl rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col md:flex-row h-[600px]">
                
                <!-- Left Pane: Search & Results -->
                <div class="w-full md:w-[45%] flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-[#1a1c23]">
                    <!-- Search Header -->
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Global Search</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">Include archived</span>
                                <input type="checkbox" class="rounded bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-purple-600 focus:ring-purple-600 h-3 w-3">
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input 
                                id="global-search-input"
                                type="text" 
                                x-model="searchQuery"
                                @input.debounce.300ms="performSearch"
                                @keydown.arrow-down.prevent="selectNextSearchResult"
                                @keydown.arrow-up.prevent="selectPrevSearchResult"
                                @keydown.enter.prevent="navigateToSelectedResult"
                                class="block w-full pl-10 pr-3 py-3 bg-gray-50 dark:bg-[#111317] border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent sm:text-lg font-bold transition-colors"
                                placeholder="Search..."
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <!-- Results List -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 bg-white dark:bg-[#1a1c23]">
                        <template x-if="searchResults.length === 0 && searchQuery.length >= 2 && !isSearching">
                            <div class="p-8 text-center text-gray-500">
                                <p class="text-sm italic">No results found for "<span x-text="searchQuery"></span>"</p>
                            </div>
                        </template>

                        <template x-if="searchQuery.length < 2">
                            <div class="p-8 text-center">
                                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase font-bold tracking-widest">Type at least 2 characters...</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-[10px] text-gray-400 dark:text-gray-500">/ Short-cut</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-[10px] text-gray-400 dark:text-gray-500">Ctrl + K</span>
                                </div>
                            </div>
                        </template>

                        <div class="space-y-1">
                            <template x-for="(result, index) in searchResults" :key="index">
                                <div 
                                    :id="'search-result-' + index"
                                    @mouseenter="searchSelectedIndex = index"
                                    @click="navigateToSelectedResult"
                                    class="group flex items-center px-4 py-3 rounded-lg cursor-pointer transition-all duration-150"
                                    :class="searchSelectedIndex === index ? 'bg-purple-600/10 dark:bg-purple-600/20 border border-purple-500/50' : 'border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50'"
                                >
                                    <div class="flex-shrink-0 mr-4">
                                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 group-hover:bg-purple-600 group-hover:border-purple-600 transition-colors">
                                            <!-- Dynamic Icon -->
                                            <template x-if="result.icon === 'office-building'">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 01-2-2H7a2 2 0 012 2h6z"></path></svg>
                                            </template>
                                            <template x-if="result.icon === 'cpu'">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2-2h10a2 2 0 002 2v10a2 2 0 00-2 2z"></path></svg>
                                            </template>
                                            <template x-if="result.icon === 'document-text'">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </template>
                                            <template x-if="result.icon === 'key'">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                            </template>
                                            <template x-if="result.icon === 'user'">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="result.title"></p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 uppercase font-medium" x-text="result.subtitle"></p>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <span class="text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-2 py-1 rounded-md uppercase font-bold" x-text="result.type"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Search Footer -->
                    <div class="p-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-[10px] text-gray-400 dark:text-gray-500 uppercase font-bold tracking-widest bg-gray-50 dark:bg-[#1a1c23]/50">
                        <div class="flex items-center space-x-4">
                            <span><span class="bg-gray-200 dark:bg-gray-800 px-1 rounded mx-0.5 text-gray-500">↑↓</span> navigate</span>
                            <span><span class="bg-gray-200 dark:bg-gray-800 px-1 rounded mx-0.5 text-gray-500">⏎</span> select</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span><span class="bg-gray-200 dark:bg-gray-800 px-1 rounded mx-0.5 text-gray-500">esc</span> cancel</span>
                        </div>
                    </div>
                </div>

                <!-- Right Pane: Preview -->
                <div class="hidden md:flex w-[55%] flex-col bg-gray-50 dark:bg-[#111317]">
                    <template x-if="searchSelectedIndex >= 0 && searchResults[searchSelectedIndex]">
                        <div class="p-8 h-full flex flex-col">
                            <div class="flex items-center space-x-6 mb-12">
                                <div class="w-20 h-20 flex items-center justify-center rounded-2xl bg-white dark:bg-[#1a1c23] border border-gray-200 dark:border-gray-700 shadow-sm text-3xl font-bold text-purple-600 dark:text-purple-500">
                                    <span x-text="searchResults[searchSelectedIndex].title.charAt(0)"></span>
                                </div>
                                <div>
                                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100" x-text="searchResults[searchSelectedIndex].title"></h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest mt-1" x-text="searchResults[searchSelectedIndex].type"></p>
                                </div>
                            </div>

                            <div class="flex-1 space-y-6">
                                <template x-for="(val, key) in searchResults[searchSelectedIndex].preview" :key="key">
                                    <div class="flex items-start">
                                        <div class="w-32 flex-shrink-0 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pt-1" x-text="key"></div>
                                        <div class="flex-1 text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="val"></div>
                                    </div>
                                </template>
                            </div>

                            <div class="pt-8 mt-auto border-t border-gray-200 dark:border-gray-800">
                                <a :href="searchResults[searchSelectedIndex].url" class="inline-flex items-center justify-center w-full px-6 py-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-purple-900/20 dark:shadow-purple-900/40">
                                    Open Item Details
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    </template>
                    <template x-if="searchSelectedIndex < 0">
                        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400 dark:text-gray-600 space-y-4">
                            <svg class="w-24 h-24 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-xs font-bold uppercase tracking-widest">Select a result to preview details</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <script>
            function copyToClipboard(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(function() {
                        // Success
                    }, function(err) {
                        fallbackCopyTextToClipboard(text);
                    });
                } else {
                    fallbackCopyTextToClipboard(text);
                }
            }

            function fallbackCopyTextToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }

                document.body.removeChild(textArea);
            }
        </script>
    </body>
</html>
