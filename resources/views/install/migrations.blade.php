<x-installer-layout>
    <div x-data="{
        started: false,
        completed: false,
        error: null,
        errorDetail: null,
        output: '',
        progress: 0,
        currentStep: 'Initializing...',
        pollTimer: null,
        showTerminal: false,

        startMigrations() {
            this.started = true;
            this.error = null;
            this.errorDetail = null;
            this.progress = 5;
            this.currentStep = 'Starting background process...';

            fetch('{{ route('install.runMigrations') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    this.pollTimer = setInterval(() => this.checkStatus(), 2000);
                } else {
                    this.error = 'Failed to start migrations.';
                    this.errorDetail = data.message || 'Unknown error';
                    this.started = false;
                }
            }).catch(err => {
                this.error = 'Failed to start migrations.';
                this.errorDetail = err.message;
                this.started = false;
            });
        },

        checkStatus() {
            fetch('{{ route('install.migrationStatus') }}')
                .then(r => r.json())
                .then(data => {
                    if (data.progress) this.progress = data.progress;
                    if (data.step) this.currentStep = data.step;
                    
                    if (data.status === 'done') {
                        clearInterval(this.pollTimer);
                        this.progress = 100;
                        this.currentStep = 'Setup Complete!';
                        this.completed = true;
                        this.started = false;
                    } else if (data.status === 'error') {
                        clearInterval(this.pollTimer);
                        this.error = data.step || 'Migration failed';
                        this.errorDetail = data.message || 'Check container logs for details.';
                        this.started = false;
                    }
                })
                .catch(() => {
                    // Network blip — don't stop polling
                });
        }
    }" x-init="startMigrations()">

        <div class="space-y-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">System Setup</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="currentStep"></p>
            </div>

            {{-- Progress Bar Container --}}
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400" 
                              x-text="started ? 'In Progress' : (completed ? 'Success' : 'Ready')">
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-indigo-600 dark:text-indigo-400" x-text="progress + '%'"></span>
                    </div>
                </div>
                <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-gray-200 dark:bg-gray-700">
                    <div :style="'width: ' + progress + '%'" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-500 ease-out">
                    </div>
                </div>
            </div>

            {{-- Terminal output (Collapsible) --}}
            <div x-cloak>
                <button @click="showTerminal = !showTerminal" class="text-xs text-gray-400 hover:text-gray-600 flex items-center space-x-1 outline-none">
                   <svg :class="showTerminal ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" /></svg>
                   <span>View advanced logs</span>
                </button>
                <div x-show="showTerminal" x-collapse class="mt-4 bg-black rounded-lg p-4 overflow-auto h-48 border border-gray-800 shadow-inner">
                    <pre class="text-[10px] text-green-500 font-mono whitespace-pre-wrap leading-relaxed">
> Polling system status...
> Database: Connected
> Progress: <span x-text="progress + '%'"></span>
> Status: <span x-text="currentStep"></span>
                    </pre>
                </div>
            </div>

            {{-- Error detail box --}}
            <div x-show="errorDetail" x-cloak class="rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300" x-text="error"></h3>
                        <p class="text-xs mt-1 text-red-700 dark:text-red-400 font-mono bg-red-100/50 p-2 rounded mt-2" x-text="errorDetail"></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col space-y-4">
                {{-- Next button --}}
                <a x-show="completed" x-cloak
                   href="{{ route('install.admin') }}"
                   class="flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    Next: Create Admin Account →
                </a>

                {{-- Retry button --}}
                <button x-show="error && !started" x-cloak
                        @click="error = null; errorDetail = null; startMigrations()"
                        class="flex w-full justify-center rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all">
                    Retry Setup
                </button>
            </div>
        </div>
    </div>
</x-installer-layout>
