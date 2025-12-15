<div>
    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-emerald-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-emerald-400 text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-rose-500/10 border border-rose-500/20 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-rose-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-rose-400 text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-indigo-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-indigo-400 text-sm">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-amber-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="text-amber-400 text-sm">{{ session('warning') }}</p>
            </div>
        </div>
    @endif
</div>
