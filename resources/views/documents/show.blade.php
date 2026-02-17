<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                 <a href="{{ route('documents.index', $currentOrganization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Back
                </a>
                <span class="text-lg font-semibold">{{ $document->title }}</span>
            </div>
            <a href="{{ route('documents.edit', [$currentOrganization->slug, $document->id]) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 mb-8 md:grid-cols-4">
        <!-- Sidebar Metadata -->
        <div class="md:col-span-1 space-y-6">
            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h5 class="mb-3 font-semibold text-gray-600 dark:text-gray-300">Properties</h5>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Category</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $document->category ?? 'Uncategorized' }}</span>
                    </div>
                     <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Status</span>
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                'review' => 'bg-yellow-100 text-yellow-800',
                                'published' => 'bg-green-100 text-green-800',
                            ];
                            $status = $document->approval_status ?? 'draft';
                            $color = $statusColors[$status] ?? $statusColors['draft'];
                        @endphp
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full w-fit {{ $color }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Visibility</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $document->visibility_scope ?? 'Global' }}</span>
                    </div>
                     <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Version</span>
                        <span class="text-gray-700 dark:text-gray-300">v{{ $document->version ?? '1.0' }}</span>
                    </div>
                     <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Author</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $document->author ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h5 class="mb-3 font-semibold text-gray-600 dark:text-gray-300">Tags</h5>
                 <div class="flex flex-wrap gap-2">
                    @forelse($document->tags as $tag)
                        <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
                            {{ $tag->name }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-500">No tags</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="md:col-span-3">
             <div class="p-8 bg-white rounded-lg shadow-md dark:bg-gray-800 min-h-[500px]">
                <article class="prose dark:prose-invert max-w-none">
                    {!! $htmlContent !!}
                </article>
            </div>

            <!-- Related Items -->
            <div class="mt-8">
                <x-relationship-manager :model="$document" />
            </div>
        </div>
    </div>
</x-app-layout>
