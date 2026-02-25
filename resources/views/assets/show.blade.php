<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('assets.index', $currentOrganization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Back
                </a>
                <span class="text-lg font-semibold">{{ $asset->name }}</span>
            </div>
            <a href="{{ route('assets.edit', [$currentOrganization->slug, $asset->id]) }}" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Details
            </h4>
            <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Column 1 -->
                    <div class="space-y-2">
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Type:</span>
                            <span>{{ $asset->type->name }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Manufacturer:</span>
                            <span>{{ $asset->manufacturer ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Model:</span>
                            <span>{{ $asset->model ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Serial:</span>
                            <span>{{ $asset->serial_number ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Asset Tag:</span>
                            <span>{{ $asset->asset_tag ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Assigned To:</span>
                            <span>{{ $asset->assigned_to ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-2">
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Site:</span>
                            <span>{{ $asset->site->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">IP Address:</span>
                            <span>{{ $asset->ip_address ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">MAC Address:</span>
                            <span>{{ $asset->mac_address ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">OS Version:</span>
                            <span>{{ $asset->os_version ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">Monitoring:</span>
                            <span>{{ $asset->monitoring_enabled ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                            <span class="font-semibold">RMM Agent:</span>
                            <span>{{ $asset->rmm_agent_installed ? 'Installed' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4 mt-4">
                     <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                        <span class="font-semibold">Purchase Date:</span>
                        <span>{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}</span>
                    </div>
                     <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                        <span class="font-semibold">Warranty Expiry:</span>
                        <span>{{ $asset->warranty_expiration_date ? $asset->warranty_expiration_date->format('Y-m-d') : '-' }}</span>
                    </div>
                     <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                        <span class="font-semibold">End of Life:</span>
                        <span>{{ $asset->end_of_life ? $asset->end_of_life->format('Y-m-d') : '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b dark:border-gray-700 pb-1">
                        <span class="font-semibold">Status:</span>
                        <span class="px-2 py-0.5 font-semibold leading-tight rounded-full 
                            {{ $asset->status === 'active' ? 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100' : '' }}
                            {{ $asset->status === 'broken' ? 'text-red-700 bg-red-100 dark:bg-red-700 dark:text-red-100' : '' }}
                            {{ $asset->status === 'archived' ? 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-100' : '' }}
                        ">
                            {{ ucfirst($asset->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if($asset->notes)
            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                <div>
                    <h2 class="text-gray-700 dark:text-gray-400 text-sm font-bold">Notes</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ $asset->notes ?? 'N/A' }}</p>
                </div>
            </div>
            @endif

            <!-- Custom Fields -->
            @if($asset->values->count() > 0)
            <div class="mt-6 border-t pt-6 dark:border-gray-700">
                <h4 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">
                    {{ $asset->type->name }} Details
                </h4>
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach($asset->values as $value)
                    <div>
                        <h2 class="text-gray-700 dark:text-gray-400 text-sm font-bold">{{ $value->field->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-300">{{ $value->value }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <x-relationship-manager :model="$asset" :organization="$currentOrganization" :show-form="false" />
        </div>
        
        <div class="space-y-6">
            <!-- Asset Documents -->
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 border-t-4 border-green-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-600 dark:text-gray-300 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Asset Documents
                    </h4>
                    <a href="{{ route('documents.create', ['organization' => $currentOrganization->slug, 'documentable_id' => $asset->id, 'documentable_type' => get_class($asset)]) }}" class="text-xs px-2 py-1 bg-purple-600 text-white rounded hover:bg-purple-700">
                        + Add Document
                    </a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Document</th>
                                <th class="px-3 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($asset->documents as $doc)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm font-semibold">
                                    <a href="{{ route('documents.show', [$currentOrganization->slug, $doc->id]) }}" class="hover:text-purple-600">{{ $doc->title }}</a>
                                </td>
                                <td class="px-3 py-3 text-right text-xs">
                                    @if($doc->is_upload)
                                        <a href="{{ route('documents.download', [$currentOrganization->slug, $doc->id]) }}" class="text-blue-600 hover:underline">Download</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-3 text-xs text-gray-400 italic">No documents linked to this asset.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-relationship-manager :model="$asset" :organization="$currentOrganization" :show-list="false" />
        </div>
    </div>
</x-app-layout>
