<x-installer-layout>
    <div x-data="{
        running: false,
        completed: false,
        error: null,
        errorDetail: null,
        output: '',
        runMigrations() {
            this.running = true;
            this.error = null;
            this.errorDetail = null;
            this.output = 'Starting database setup...\n';
            this.output += 'This may take up to 2 minutes on first run.\n';

            fetch('{{ route('install.runMigrations') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        // Try to parse as JSON first (Laravel JSON error response)
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || text);
                        } catch(e) {
                            if (e.message === text || e.message.startsWith('{')) throw e;
                            // It was HTML (502/504 gateway error)
                            throw new Error('Gateway error (' + response.status + '). The server took too long to respond. Please check container logs and click Retry.');
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.output += '\n✓ Migrations complete.\n';
                    this.output += '✓ Roles & permissions seeded.\n';
                    this.output += '\nSetup complete!\n';
                    this.completed = true;
                } else {
                    this.error = 'Setup failed.';
                    this.errorDetail = data.message;
                    this.output += '\n✗ Error: ' + data.message + '\n';
                }
            })
            .catch(err => {
                this.error = 'Setup failed.';
                this.errorDetail = err.message;
                this.output += '\n✗ ' + err.message + '\n';
            })
            .finally(() => {
                this.running = false;
            });
        }
    }" x-init="runMigrations()">

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">System Setup</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Setting up the database tables and initial data.</p>
            </div>

            {{-- Terminal output --}}
            <div class="bg-gray-900 rounded-md p-4 overflow-auto h-48">
                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap" x-text="output"></pre>
            </div>

            {{-- Error detail box --}}
            <div x-show="errorDetail" class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Error Details</h3>
                        <p class="text-xs mt-1 text-red-700 dark:text-red-400 font-mono break-all" x-text="errorDetail"></p>
                        <p class="text-xs mt-2 text-red-600 dark:text-red-500">Run <code class="bg-red-100 dark:bg-red-900 px-1 rounded">docker logs orbitdocs-app</code> for more detail.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                {{-- Spinner --}}
                <div x-show="running" class="flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Processing — please wait...</span>
                </div>

                {{-- Next button --}}
                <a x-show="completed"
                   href="{{ route('install.admin') }}"
                   class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Next: Create Admin Account →
                </a>

                {{-- Retry button --}}
                <button x-show="error && !running"
                        @click="runMigrations()"
                        class="flex w-full justify-center rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Retry Setup
                </button>
            </div>
        </div>
    </div>
</x-installer-layout>
