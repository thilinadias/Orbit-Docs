<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Credentials</span>
            <a href="{{ route('credentials.create', $organization->slug) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Add Credential
            </a>
        </div>
    </x-slot>

    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Password</th>
                        <th class="px-4 py-3">Asset</th>
                        <th class="px-4 py-3">URL</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($credentials as $credential)
                    <tr class="text-gray-700 dark:text-gray-400">
                        <td class="px-4 py-3">
                            <div class="flex items-center text-sm">
                                <a href="{{ route('credentials.show', [$organization->slug, $credential->id]) }}" class="font-semibold hover:underline">{{ $credential->title }}</a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="font-mono">{{ $credential->username ?? '-' }}</span>
                            <button class="ml-2 text-gray-500 hover:text-gray-700" onclick="copyToClipboard('{{ $credential->username }}')" title="Copy Username">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center" x-data="{ revealed: false, password: '••••••••' }">
                                <span class="font-mono" x-text="password"></span>
                                <button @click="
                                    if(!revealed) {
                                        fetch('{{ route('credentials.reveal', ['credential' => $credential->id, 'organization' => $organization->slug]) }}')
                                        .then(response => response.json())
                                        .then(data => {
                                            password = data.password;
                                            revealed = true;
                                            setTimeout(() => { password = '••••••••'; revealed = false; }, 30000); // Auto hide
                                        });
                                    } else {
                                        copyToClipboard(password);
                                        // Optional feedback
                                    }
                                " class="ml-2 text-purple-600 hover:text-purple-800 focus:outline-none">
                                    <template x-if="!revealed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </template>
                                    <template x-if="revealed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </template>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $credential->asset->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($credential->url)
                            <a href="{{ $credential->url }}" target="_blank" class="text-purple-600 hover:underline">Launch</a>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                             <div class="flex items-center space-x-4 text-sm">
                                <a href="{{ route('credentials.edit', [$organization->slug, $credential->id]) }}" class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                </a>
                             </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="6" class="px-4 py-3 text-center text-gray-500">No credentials found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
            {{ $credentials->links() }}
        </div>
    </div>
</x-app-layout>
