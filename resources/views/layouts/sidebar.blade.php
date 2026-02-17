<aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0 border-r border-gray-200 dark:border-gray-700"
       x-data="{
           init() {
               const savedScroll = localStorage.getItem('sidebarScroll');
               if (savedScroll) {
                   this.$el.scrollTop = savedScroll;
               }
           },
           saveScroll() {
               localStorage.setItem('sidebarScroll', this.$el.scrollTop);
           }
       }"
       @scroll.debounce.100ms="saveScroll">
    <div class="py-4 text-gray-500 dark:text-gray-400">
        <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center" href="{{ route('root') }}">
            @if($logo = \App\Models\Setting::get('system_logo'))
                <img src="{{ asset('storage/' . $logo) }}" alt="System Logo" class="h-8 w-auto mr-3">
            @else
                <x-application-logo class="h-8 w-8 mr-3 fill-current text-purple-600" />
            @endif
            <span>{{ \App\Models\Setting::get('system_name', 'OrbitDocs') }}</span>
        </a>
        @if(isset($currentOrganization))
        <div class="px-6 py-2 mt-2 flex items-center">
            @if(isset($site) && $site->logo)
                <img src="{{ asset('storage/' . $site->logo) }}" alt="Site Logo" class="h-8 w-auto mr-2 rounded shadow-sm">
            @elseif($currentOrganization->logo)
                <img src="{{ asset('storage/' . $currentOrganization->logo) }}" alt="Org Logo" class="h-8 w-auto mr-2 rounded shadow-sm">
            @endif
            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                {{ $currentOrganization->name }}
            </div>
        </div>
        @endif
        
        <!-- Global Navigation -->
        <ul class="mt-6">
            <li class="relative px-6 py-3">
                @if(request()->routeIs('root'))
                <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                @endif
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('root') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('root') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                    <span class="ml-4">Global Overview</span>
                </a>
            </li>

            <li class="relative px-6 py-3">
                @if(request()->routeIs('settings.index'))
                <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                @endif
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('settings.index') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('settings.index') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-4">System Settings</span>
                </a>
            </li>
        </ul>

        <!-- Main Dashboard Link -->
        <ul class="mt-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-4">
             <div class="px-6 mb-2">Organization Context</div>
            <li class="relative px-6 py-3">
                @if(request()->routeIs('dashboard'))
                <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                @endif
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('dashboard') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('dashboard', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-4">Dashboard</span>
                </a>
            </li>
            
            <!-- Manage MSPs (Admin Only or High Level) -->
            <li class="relative px-6 py-3">
                @if(request()->routeIs('organizations.*'))
                <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                @endif
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('organizations.*') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('organizations.index') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 01-2-2H7a2 2 0 012 2h6z"></path>
                    </svg>
                    <span class="ml-4">Manage MSPs</span>
                </a>
            </li>
        </ul>

        <!-- Core Assets Group -->
        <div class="px-6 my-6 mb-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
            Core Assets
        </div>
        <ul>
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('assets.*') && !request('type') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('assets.index', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <span class="ml-4">Configurations</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('contacts.*') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('contacts.index', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="ml-4">Contacts</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('documents.*') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('documents.index', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="ml-4">Documents</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('sites.*') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('sites.index', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-4">Locations</span>
                </a>
            </li>
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('credentials.*') ? 'text-gray-800 dark:text-gray-100' : '' }}" 
                   href="{{ route('credentials.index', $currentOrganization->slug ?? 'demo-msp') }}">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span class="ml-4">Passwords</span>
                </a>
            </li>
        </ul>

        <!-- Apps & Services -->
        <div class="px-6 my-6 mb-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
            Apps & Services
        </div>
        <ul>
            @foreach(['Active Directory', 'Applications', 'Backup', 'Email', 'Internet/WAN', 'LAN', 'Licensing', 'Mobile Devices', 'Managed Printers', 'Vendor Management', 'Shared Drives', 'Site Summary'] as $type)
            <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => $type]) }}">
                    <span class="ml-4">{{ $type }}</span>
                </a>
            </li>
            @endforeach
        </ul>

        <!-- Domains -->
        <div class="px-6 my-6 mb-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
            Domains
        </div>
        <ul>
            @foreach(['Domain Tracker', 'SSL Tracker'] as $type)
            <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => $type]) }}">
                    <span class="ml-4">{{ $type }}</span>
                </a>
            </li>
            @endforeach
        </ul>

        <!-- Networking -->
        <div class="px-6 my-6 mb-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
            Networking
        </div>
        <ul>
            @foreach(['Router', 'Wireless', 'Server Hardware', 'Servers', 'Network Switch', 'SAN Storage', 'Network Device', 'NAS Storage', 'VPN', 'WAP', 'Wifi'] as $type)
            <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => $type]) }}">
                    <span class="ml-4">{{ $type }}</span>
                </a>
            </li>
            @endforeach
        </ul>

        <!-- MSP Information -->
        <div class="px-6 my-6 mb-2 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
            MSP Information
        </div>
        <ul>
             <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => 'MSP Information']) }}">
                    <span class="ml-4">MSP Information</span>
                </a>
            </li>
             <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => 'Primary MSP Information']) }}">
                    <span class="ml-4">Primary MSP Info</span>
                </a>
            </li>
             <li class="relative px-6 py-1">
                <a class="inline-flex items-center w-full text-sm text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" 
                   href="{{ route('assets.index', ['organization' => $currentOrganization->slug ?? 'demo-msp', 'type' => 'Client Support Scope']) }}">
                    <span class="ml-4">Client Support Scope</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Logout Section at bottom -->
    <div class="px-6 py-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Log Out
            </button>
        </form>
    </div>
</aside>
