<x-installer-layout>
    <div x-data="{ 
        running: false, 
        completed: false, 
        error: null,
        output: '',
        runMigrations() {
            this.running = true;
            this.error = null;
            this.output = 'Starting migrations...\n';
            
            fetch('{{ route('install.runMigrations') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text || response.statusText) });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.output += data.message + '\n';
                    this.completed = true;
                } else {
                    this.error = data.message;
                    this.output += 'Error: ' + data.message + '\n';
                }
            })
            .catch(err => {
                console.error(err);
                this.error = 'Migration failed. Check logs.';
                // Check if error is HTML (common with 500/504)
                if (err.message && err.message.trim().startsWith('<')) {
                     this.output += 'Server Error (Likely Timeout). Please check container logs.\n';
                } else {
                     this.output += 'System Error: ' + err.message + '\n';
                }
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

            <div class="bg-gray-900 rounded-md p-4 overflow-auto h-48">
                <pre class="text-xs text-green-400 font-mono" x-text="output"></pre>
            </div>

            <div class="flex justify-end">
                <div x-show="running" class="flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-500 text-sm">Processing...</span>
                </div>

                <a x-show="completed" href="{{ route('install.admin') }}" class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Next: Create Admin
                </a>

                <button x-show="error && !running" @click="runMigrations()" class="flex w-full justify-center rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Retry
                </button>
            </div>
        </div>
    </div>
</x-installer-layout>
