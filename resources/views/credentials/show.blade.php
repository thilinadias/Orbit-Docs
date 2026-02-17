<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                 <a href="{{ route('credentials.index', $currentOrganization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Back
                </a>
                <span class="text-lg font-semibold">{{ $credential->title }}</span>
            </div>
             <a href="{{ route('credentials.edit', [$currentOrganization->slug, $credential->id]) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
             <h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Credential Details</h4>
             
             <div class="space-y-4">
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Category</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $credential->category ?? 'General' }}</span>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Username</span>
                    <div class="flex items-center space-x-2">
                        <span class="font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $credential->username }}</span>
                        <button class="text-purple-600 hover:text-purple-800 focus:outline-none" title="Copy Username" onclick="copyToClipboard('{{ $credential->username }}')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Password</span>
                    <div class="flex items-center space-x-2" x-data="{ show: false, password: '' }">
                        <button 
                            @click="if(!show) { fetch('{{ route('credentials.reveal', [$currentOrganization->slug, $credential->id]) }}').then(r => r.json()).then(d => { password = d.password; show = true; setTimeout(() => { show = false; password = ''; }, 30000); }) } else { show = false; }"
                            class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                            <span x-text="show ? 'Hide' : 'Reveal'">Reveal</span>
                        </button>
                        <span x-show="show" x-text="password" class="font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded select-all"></span>
                    </div>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">URL</span>
                    @if($credential->url)
                    <a href="{{ $credential->url }}" target="_blank" class="text-purple-600 dark:text-purple-400 hover:underline truncate">{{ $credential->url }}</a>
                    @else
                    <span class="text-gray-500">-</span>
                    @endif
                </div>
             </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
             <h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Security & Lifecycle</h4>
             <div class="space-y-4">
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Owner</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $credential->owner ?? '-' }}</span>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Access Scope</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $credential->access_scope ?? 'Private' }}</span>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Expiry Date</span>
                     <span class="text-gray-700 dark:text-gray-300 {{ $credential->expiry_date && $credential->expiry_date->isPast() ? 'text-red-600 font-bold' : '' }}">
                        {{ $credential->expiry_date ? $credential->expiry_date->format('Y-m-d') : 'Never' }}
                    </span>
                </div>
                 <div class="flex flex-col">
                    <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">Last Rotated</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $credential->last_rotated_at ? $credential->last_rotated_at->format('Y-m-d') : '-' }}</span>
                </div>
                 <div class="flex items-center space-x-4 mt-2">
                     <span class="px-2 py-1 text-xs font-semibold leading-tight rounded-full {{ $credential->auto_rotate ? 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100' : 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-100' }}">
                        Auto-Rotate: {{ $credential->auto_rotate ? 'Yes' : 'No' }}
                    </span>
                    <span class="px-2 py-1 text-xs font-semibold leading-tight rounded-full {{ $credential->access_log_enabled ? 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100' : 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-100' }}">
                        Log Access: {{ $credential->access_log_enabled ? 'Yes' : 'No' }}
                    </span>
                 </div>
             </div>
        </div>
    </div>
</x-app-layout>
