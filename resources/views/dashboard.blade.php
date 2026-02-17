<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                @if($organization->parent)
                <nav class="flex mb-1 text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-2">
                        <li>
                            <a href="{{ route('dashboard', $organization->parent->slug) }}" class="hover:text-purple-600 transition-colors">
                                {{ $organization->parent->name }}
                            </a>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-3 h-3 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <span class="text-gray-400">{{ $organization->name }}</span>
                        </li>
                    </ol>
                </nav>
                @endif
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ $organization->name }} Dashboard
                </h2>
            </div>
            <div x-data="{ hasNotes: {{ $organization->notes ? 'true' : 'false' }} }">
                <button @click="window.dispatchEvent(new CustomEvent('open-notes-editor'))" x-show="!hasNotes" class="px-3 py-1 text-xs font-medium text-purple-600 bg-purple-100 rounded-lg hover:bg-purple-200 dark:bg-gray-700 dark:text-purple-400">
                    + Add Quick Note
                </button>
            </div>
        </div>
    </x-slot>

    <div x-data="{ editing: false, hasNotes: {{ $organization->notes ? 'true' : 'false' }} }" @open-notes-editor.window="editing = true">
        <!-- Cards -->
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Assets</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $assets_count }}</p>
                </div>
            </div>
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Credentials</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $credentials_count }}</p>
                </div>
            </div>
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-l-4 border-red-500">
                <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Contacts</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $contacts_count }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 mb-8 lg:grid-cols-3">
            <!-- Quick Note Section (Auto-hides if empty and not editing) -->
            <div x-show="hasNotes || editing" class="lg:col-span-2 min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-yellow-400" x-cloak>
                <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300 flex items-center justify-between">
                    <span class="flex items-center text-xs">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Quick Notes
                    </span>
                    <div class="space-x-2">
                        <button @click="editing = !editing" class="text-xs font-medium text-purple-600 hover:text-purple-800 focus:outline-none">
                            <span x-show="!editing">Edit</span>
                            <span x-show="editing">Cancel</span>
                        </button>
                    </div>
                </h4>
                
                <div x-show="!editing" class="ck-content text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                    {!! $organization->notes !!}
                </div>

                <form x-show="editing" action="{{ route('organizations.notes.update', $organization->slug) }}" method="POST" id="notes-form">
                    @csrf
                    <textarea id="notes_editor" name="notes">{{ $organization->notes }}</textarea>
                    <div class="mt-3 flex justify-end space-x-2">
                        <button type="button" @click="editing = false" class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                            Discard
                        </button>
                        <button type="submit" class="px-3 py-1 text-xs font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                            Save Notes
                        </button>
                    </div>
                </form>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        ClassicEditor
                            .create(document.querySelector('#notes_editor'), {
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

            <!-- Asset Distribution -->
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                    Asset Distribution
                </h4>
                <div class="space-y-4">
                    @foreach($asset_distribution as $type)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600 dark:text-gray-400">{{ $type->name }}</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $type->count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                            @php $percentage = $assets_count > 0 ? ($type->count / $assets_count) * 100 : 0; @endphp
                            <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sub-Organizations Redesigned -->
        @if($sub_organizations->count() > 0)
        <div class="mb-8">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Sub-Organizations
            </h4>
            <div class="flex flex-wrap gap-8">
                @foreach($sub_organizations as $sub_org)
                <a href="{{ route('dashboard', $sub_org->slug) }}" class="flex flex-col items-center group w-24">
                    <div class="relative w-16 h-16 mb-2 flex items-center justify-center rounded-full bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 border-2 border-transparent group-hover:border-purple-500 transition-all duration-150 overflow-hidden shadow-sm">
                        @if($sub_org->logo)
                            <img src="{{ asset('storage/' . $sub_org->logo) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="font-bold text-xl uppercase tracking-tighter">{{ substr($sub_org->name, 0, 1) }}{{ substr(explode(' ', $sub_org->name)[1] ?? '-', 0, 1) }}</span>
                        @endif
                        <!-- Shield Icon indicator like the reference -->
                        <div class="absolute bottom-0 w-full h-2 bg-gray-400 dark:bg-gray-600 opacity-50"></div>
                    </div>
                    <p class="text-[10px] font-semibold text-center text-gray-700 dark:text-gray-300 group-hover:text-purple-600 leading-tight truncate w-full">
                        {{ $sub_org->name }}
                    </p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid gap-6 mb-8 lg:grid-cols-2">
            <!-- Site Summary Explorer -->
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-600 dark:text-gray-300">
                        Location Explorer
                    </h4>
                    <a href="{{ route('sites.index', $organization->slug) }}" class="text-xs text-purple-600 hover:underline">View All Locations</a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Site</th>
                                <th class="px-4 py-3">Assets</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($sites as $site)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                            @if($site->logo)
                                                <img class="object-cover w-full h-full rounded-full" src="{{ asset('storage/' . $site->logo) }}" alt="" loading="lazy" />
                                            @else
                                                <div class="flex items-center justify-center w-full h-full rounded-full bg-blue-100 text-blue-700 font-bold text-xs uppercase">
                                                    {{ substr($site->name, 0, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-xs">{{ $site->name }}</p>
                                            <p class="text-[10px] text-gray-600 dark:text-gray-400">{{ $site->city }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                        {{ $site->assets_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('sites.show', [$organization->slug, $site->id]) }}" class="text-purple-600 hover:text-purple-900">Details</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                    Recent Organization Activity
                </h4>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($recent_activity as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 ring-4 ring-white dark:ring-gray-800">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase">{{ substr($activity->action, 0, 1) }}</span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-medium text-gray-900 dark:text-gray-200">{{ $activity->user->name ?? 'System' }}</span>
                                                {{ $activity->action }}
                                                <span class="font-medium text-gray-900 dark:text-gray-200 text-[10px]">{{ $activity->subject->name ?? $activity->subject->title ?? 'item' }}</span>
                                            </p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-[10px] text-gray-500 dark:text-gray-400 uppercase">
                                            <time datetime="{{ $activity->created_at }}">{{ $activity->created_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @empty
                        <p class="text-sm text-gray-500 italic">No activity logs found.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Assets Table -->
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Recent Assets
        </h2>
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($recent_assets as $asset)
                        <tr class="text-gray-700 dark:text-gray-400">
                            <td class="px-4 py-3">
                                <div class="flex items-center text-sm">
                                    <div>
                                        <p class="font-semibold">{{ $asset->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $asset->type->name ?? 'Unknown' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    {{ $asset->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $asset->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-4 py-3" colspan="4">No assets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
