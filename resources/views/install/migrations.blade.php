<x-installer-layout>
    <div x-data="{
        started: false,
        completed: false,
        error: null,
        errorDetail: null,
        output: '',
        pollTimer: null,

        startMigrations() {
            this.started = true;
            this.error = null;
            this.errorDetail = null;
            this.output = 'Starting database setup...\nThis may take 1-2 minutes on first run.\n';

            // Fire-and-forget POST to start the background process
            fetch('{{ route('install.runMigrations') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    this.output += 'Background process started.\nPolling for status...\n';
                    this.pollTimer = setInterval(() => this.checkStatus(), 3000);
                } else {
                    this.error = 'Failed to start migrations.';
                    this.errorDetail = data.message || 'Unknown error';
                    this.output += '\n✗ ' + (data.message || 'Unknown error') + '\n';
                    this.started = false;
                }
            }).catch(err => {
                this.error = 'Failed to start migrations.';
                this.errorDetail = err.message;
                this.output += '\n✗ ' + err.message + '\n';
                this.started = false;
            });
        },

        checkStatus() {
            fetch('{{ route('install.migrationStatus') }}')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'done') {
                        clearInterval(this.pollTimer);
                        this.output += '\n✓ Migrations complete.\n✓ Database seeded.\n\nSetup finished!\n';
                        this.completed = true;
                        this.started = false;
                    } else if (data.status === 'error') {
                        clearInterval(this.pollTimer);
                        this.error = data.step || 'Migration failed';
                        this.errorDetail = data.message || 'Check container logs for details.';
                        this.output += '\n✗ ' + (data.step || 'Error') + '\n' + (data.message || '') + '\n';
                        this.started = false;
                    } else if (data.status === 'running') {
                        this.output += '⏳ ' + (data.step || 'Processing...') + '\n';
                    }
                })
                .catch(() => {
                    // Network blip — don't stop polling, just log it
                    this.output += '⏳ Checking...\n';
                });
        }
    }" x-init="startMigrations()">

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">System Setup</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Setting up the database tables and initial data.</p>
            </div>

            {{-- Terminal output --}}
            <div class="bg-gray-900 rounded-md p-4 overflow-auto h-48" x-ref="terminal">
                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap" x-text="output"
                     x-effect="$nextTick(() => $refs.terminal.scrollTop = $refs.terminal.scrollHeight)"></pre>
            </div>

            {{-- Error detail box --}}
            <div x-show="errorDetail" x-cloak class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300" x-text="error"></h3>
                        <p class="text-xs mt-1 text-red-700 dark:text-red-400 font-mono break-all" x-text="errorDetail"></p>
                        <p class="text-xs mt-2 text-red-600 dark:text-red-500">
                            Run <code class="bg-red-100 dark:bg-red-900 px-1 rounded">docker logs orbitdocs-app</code>
                            and <code class="bg-red-100 dark:bg-red-900 px-1 rounded">cat /tmp/migrate_output.txt</code> inside the container for more detail.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                {{-- Spinner --}}
                <div x-show="started && !completed && !error" class="flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Running in background — please wait...</span>
                </div>

                {{-- Next button --}}
                <a x-show="completed" x-cloak
                   href="{{ route('install.admin') }}"
                   class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Next: Create Admin Account →
                </a>

                {{-- Retry button --}}
                <button x-show="error && !started" x-cloak
                        @click="error = null; errorDetail = null; output = ''; startMigrations()"
                        class="flex w-full justify-center rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Retry Setup
                </button>
            </div>
        </div>
    </div>
</x-installer-layout>
