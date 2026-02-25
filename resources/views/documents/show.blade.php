<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                 <a href="{{ route('documents.index', $currentOrganization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Back
                </a>
                <span class="text-lg font-semibold">{{ $document->title }}</span>
            </div>
            <div class="flex items-center space-x-2">
                @if($document->is_upload)
                    <a href="{{ route('documents.download', [$currentOrganization->slug, $document->id]) }}" class="px-4 py-2 text-sm font-medium leading-5 text-purple-600 bg-purple-100 border border-transparent rounded-lg hover:bg-purple-200 focus:outline-none">
                        Download
                    </a>
                @endif
                <a href="{{ route('documents.edit', [$currentOrganization->slug, $document->id]) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 mb-8 md:grid-cols-4">
        <!-- Sidebar Metadata -->
        <div class="md:col-span-1 space-y-6">
            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h5 class="mb-3 font-semibold text-gray-600 dark:text-gray-300">Properties</h5>
                <div class="space-y-3 text-sm">
                    @if($document->is_upload)
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">File Name</span>
                            <span class="text-gray-700 dark:text-gray-300 overflow-hidden text-ellipsis">{{ $document->file_name }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Size</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ number_format($document->file_size / 1024 / 1024, 2) }} MB</span>
                        </div>
                    @endif
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
                    @if($document->documentable)
                        <div class="flex flex-col border-t dark:border-gray-700 pt-2 mt-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Linked to</span>
                            <a href="{{ route($document->documentable_type === 'App\Models\Site' ? 'sites.show' : 'assets.show', [$currentOrganization->slug, $document->documentable_id]) }}" class="text-purple-600 hover:underline">
                                {{ $document->documentable->name }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h5 class="mb-3 font-semibold text-gray-600 dark:text-gray-300">Tags</h5>
                 <div class="flex flex-wrap gap-2">
                    @forelse ($document->tags as $tag)
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
             <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800 min-h-[600px]">
                @if($document->is_upload)
                    <div class="h-full">
                        @if($document->isImage())
                            <div class="flex justify-center items-center">
                                <img src="{{ route('documents.preview', [$currentOrganization->slug, $document->id]) }}" class="max-w-full h-auto rounded-lg shadow-sm" alt="{{ $document->file_name }}">
                            </div>
                        @elseif($document->isPdf())
                            <iframe src="{{ route('documents.preview', [$currentOrganization->slug, $document->id]) }}" class="w-full h-[800px] border-none rounded-lg" title="{{ $document->file_name }}"></iframe>
                        @elseif($document->isWord())
                            <div id="docx-container" class="w-full h-[800px] overflow-auto border-none rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                                <div id="docx-render"></div>
                            </div>
                            <script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
                            <script src="https://unpkg.com/docx-preview/dist/docx-preview.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const container = document.getElementById("docx-render");
                                    const url = "{{ route('documents.preview', [$currentOrganization->slug, $document->id]) }}";
                                    
                                    fetch(url)
                                        .then(response => response.blob())
                                        .then(blob => {
                                            docx.renderAsync(blob, container)
                                                .then(x => console.log("docx: finished"));
                                        })
                                        .catch(err => {
                                            console.error("docx preview error:", err);
                                            container.innerHTML = `<div class="p-8 text-center text-red-500">Failed to load document preview.</div>`;
                                        });
                                });
                            </script>
                        @else
                            <div class="flex flex-col items-center justify-center h-[500px] text-gray-500">
                                <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <p class="text-lg font-semibold">Preview not available for this file type.</p>
                                <a href="{{ route('documents.download', [$currentOrganization->slug, $document->id]) }}" class="mt-4 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                    Download to view
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <article class="prose dark:prose-invert max-w-none p-4">
                        {!! $htmlContent !!}
                    </article>
                @endif
            </div>

            <!-- Related Items -->
            <div class="mt-8">
                <x-relationship-manager :model="$document" />
            </div>
        </div>
    </div>
</x-app-layout>
