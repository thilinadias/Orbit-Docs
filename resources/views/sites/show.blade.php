<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col">
            <nav class="flex mb-2 text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2">
                    <li>
                        <a href="{{ route('dashboard', $currentOrganization->slug) }}" class="hover:text-purple-600 transition-colors">
                            {{ $currentOrganization->name }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-400">{{ $site->name }}</span>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center justify-between mt-2">
                <div class="flex flex-col">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 leading-none mb-1">
                        {{ $site->name }}
                    </h2>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        Active Customer
                    </span>
                </div>
                <div class="space-x-2">
                     <a href="{{ route('sites.edit', [$currentOrganization->slug, $site->id]) }}" class="px-4 py-2 text-sm font-bold leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                        Edit Site
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div x-data="{ editing: false, hasNotes: {{ $site->notes ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between mb-4 mt-4">
            <div></div>
            <button @click="editing = true" x-show="!hasNotes && !editing" class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-gray-700 dark:text-blue-400">
                + Add Site Quick Note
            </button>
        </div>

        <!-- Quick Note Section -->
        <div x-show="hasNotes || editing" class="mb-8 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-yellow-400" x-cloak>
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300 flex items-center justify-between">
                <span class="flex items-center text-xs">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Site Quick Notes
                </span>
                <button @click="editing = !editing" class="text-xs font-medium text-blue-600 hover:text-blue-800 focus:outline-none">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Cancel</span>
                </button>
            </h4>

            <div x-show="!editing" class="ck-content text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                {!! $site->notes !!}
            </div>

            <form x-show="editing" action="{{ route('sites.notes.update', [$currentOrganization->slug, $site->id]) }}" method="POST">
                @csrf
                <textarea id="site_notes_editor" name="notes">{{ $site->notes }}</textarea>
                <div class="mt-3 flex justify-end space-x-2">
                    <button type="button" @click="editing = false" class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                        Discard
                    </button>
                    <button type="submit" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                        Save Site Notes
                    </button>
                </div>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    ClassicEditor
                        .create(document.querySelector('#site_notes_editor'), {
                            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo' ],
                            table: {
                                contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                            }
                        })
                        .catch(error => {
                            console.error(error);
                        });
                });
            </script>
        </div>
    </div>

    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-purple-500">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                Location Details
            </h4>
            <div class="grid gap-4 text-sm">
                 <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Address</span>
                    <span class="text-gray-700 dark:text-gray-300 text-right">
                        {{ $site->address }}<br>
                        {{ $site->city }}, {{ $site->state }} {{ $site->postcode }}
                    </span>
                </div>
                 <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Site Manager</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $site->site_manager ?? '-' }}</span>
                </div>
                 <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Timezone</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $site->timezone ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-blue-500">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9.5 9.5 0 0113.436 0m-17.678-4.243a13.5 13.5 0 0119.092 0"></path></svg>
                Network & Access
            </h4>
            <div class="grid gap-4 text-sm">
                <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Internet Provider</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $site->internet_provider ?? '-' }}</span>
                </div>
                <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Circuit ID</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $site->circuit_id ?? '-' }}</span>
                </div>
                <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                    <span class="text-gray-500">Alarm Code</span>
                    <span class="text-gray-700 dark:text-gray-300 font-mono">{{ $site->alarm_code ?? 'Not stored' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Documents -->
    <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-green-500 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-600 dark:text-gray-300 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Site Documents
            </h4>
            <a href="{{ route('documents.create', ['organization' => $currentOrganization->slug, 'documentable_id' => $site->id, 'documentable_type' => get_class($site)]) }}" class="text-xs px-2 py-1 bg-purple-600 text-white rounded hover:bg-purple-700">
                + Add Document
            </a>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">Document</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($site->documents as $doc)
                    <tr class="text-gray-700 dark:text-gray-400">
                        <td class="px-4 py-3 text-sm font-semibold">
                            <a href="{{ route('documents.show', [$currentOrganization->slug, $doc->id]) }}" class="hover:text-purple-600">{{ $doc->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-xs uppercase">
                            {{ $doc->is_upload ? 'File (' . strtoupper(last(explode('/', $doc->mime_type))) . ')' : 'Markdown' }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs">
                            @if($doc->is_upload)
                                <a href="{{ route('documents.download', [$currentOrganization->slug, $doc->id]) }}" class="text-blue-600 hover:underline">Download</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-3 text-xs text-gray-400 italic">No documents linked to this site.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-6 mb-8 lg:grid-cols-3">
        <!-- Site Assets -->
        <div class="lg:col-span-2 min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Assets at this Location
            </h4>
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($assets as $asset)
                        <tr class="text-gray-700 dark:text-gray-400">
                            <td class="px-4 py-3 text-sm font-semibold">
                                <a href="{{ route('assets.show', [$currentOrganization->slug, $asset->id]) }}" class="hover:text-purple-600">{{ $asset->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-xs uppercase">{{ $asset->type->name }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    {{ $asset->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-3 text-xs text-gray-400 italic">No assets assigned to this site.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Site Activity -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Recent Site Activity
            </h4>
            <div class="flow-root">
                <ul role="list" class="-mb-8 text-xs">
                    @forelse($recent_activity as $activity)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <span class="text-[8px] font-bold text-blue-700">{{ substr($activity->action, 0, 1) }}</span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ $activity->user->name ?? 'System' }} {{ $activity->action }} this site.
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-[10px] text-gray-500 uppercase">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <p class="text-gray-400 italic">No logs for this site.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
