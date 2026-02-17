<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <span>Edit Document: {{ $document->title }}</span>
            <a href="{{ route('documents.show', [$organization->slug, $document->id]) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                Cancel
            </a>
        </div>
    </x-slot>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <form action="{{ route('documents.update', [$organization->slug, $document->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Title</span>
                    <input name="title" value="{{ $document->title }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Content (Markdown)</span>
                    <textarea name="content" rows="15" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">{{ $document->content }}</textarea>
                </label>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-2">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Status</span>
                    <select name="approval_status" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                        <option value="draft" {{ $document->approval_status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="review" {{ $document->approval_status === 'review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="published" {{ $document->approval_status === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Tags (comma separated)</span>
                    <input name="tags" value="{{ $document->tags->pluck('name')->implode(', ') }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="guide, server, network" />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Category</span>
                    <input name="category" value="{{ $document->category }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="e.g. Documentation, API" />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Author</span>
                    <input name="author" value="{{ $document->author }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Author Name" />
                </label>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Update Document
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
