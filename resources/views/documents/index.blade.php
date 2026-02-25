<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Documentation</span>
            <a href="{{ route('documents.create', $organization->slug) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                New Document
            </a>
        </div>
    </x-slot>

    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Last Updated</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($documents as $document)
                        <tr class="text-gray-700 dark:text-gray-400">
                        <td class="px-4 py-3">
                             <a href="{{ route('documents.show', [$organization->slug, $document->id]) }}" class="font-semibold hover:text-purple-600">
                                {{ $document->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $document->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center space-x-4 text-sm">
                                <a href="{{ route('documents.edit', [$organization->slug, $document->id]) }}" class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                </a>
                                @can('document.delete')
                                    <form action="{{ route('documents.destroy', [$organization->slug, $document->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-red-600 rounded-lg focus:outline-none" aria-label="Delete">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="3" class="px-4 py-3 text-center text-gray-500">No documents found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
            {{ $documents->links() }}
        </div>
    </div>
</x-app-layout>
