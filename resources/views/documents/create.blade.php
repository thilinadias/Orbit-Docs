<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>Create Document</span>
            <a href="{{ route('documents.index', $organization->slug) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                &larr; Back to Documents
            </a>
        </div>
    </x-slot>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <form action="{{ route('documents.store', $organization->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <input type="hidden" name="documentable_id" value="{{ request('documentable_id') }}">
            <input type="hidden" name="documentable_type" value="{{ request('documentable_type') }}">

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Title</span>
                    <input name="title" required class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Server Setup Guide" />
                </label>
            </div>

            <div x-data="{ mode: 'editor' }" class="mb-4">
                <div class="flex items-center gap-4 mb-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" x-model="mode" value="editor" class="text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Write Markdown</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" x-model="mode" value="upload" class="text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Upload File</span>
                    </label>
                </div>

                <div x-show="mode === 'editor'">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Content (Markdown)</span>
                        <textarea name="content" rows="15" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="# Introduction..."></textarea>
                    </label>
                </div>

                <div x-show="mode === 'upload'" class="p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Select File</span>
                        <input type="file" name="file" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" />
                        <p class="mt-1 text-xs text-gray-500">Maximum size: 10MB. Supports PDF, Images, etc.</p>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Save Document
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
