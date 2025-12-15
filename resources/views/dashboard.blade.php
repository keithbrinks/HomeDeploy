<x-layouts.app>
    <x-flash-messages />
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            @if(!Auth::user()->github_token)
                <p class="text-sm text-slate-400 mt-1">
                    <a href="{{ route('auth.github') }}" class="text-indigo-400 hover:text-indigo-300">Connect GitHub</a> to browse repositories
                </p>
            @endif
        </div>
        <a href="{{ route('sites.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
            New Site
        </a>
    </div>

    <!-- Server Metrics -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-white mb-4">Server Resources</h2>
        <x-server-metrics />
    </div>

    <h2 class="text-lg font-semibold text-white mb-4">Your Sites</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($sites as $site)
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden hover:border-slate-700 transition-colors">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-white">{{ $site->name }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ $site->repo_url }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $site->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700 text-slate-300' }}">
                            {{ ucfirst($site->status) }}
                        </span>
                    </div>
                    
                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Last Deployment</span>
                            <span class="text-slate-300">
                                {{ $site->deployments->last()?->created_at->diffForHumans() ?? 'Never' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-900/50 px-6 py-4 border-t border-slate-800 flex justify-between items-center">
                    <span class="text-xs text-slate-500 font-mono">{{ $site->branch }}</span>
                    <a href="{{ route('sites.show', $site) }}" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Manage &rarr;</a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-slate-900/50 rounded-lg border border-slate-800 border-dashed">
                <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-white">No sites deployed</h3>
                <p class="mt-1 text-sm text-slate-400">Get started by creating a new deployment.</p>
                <div class="mt-6">
                    <a href="{{ route('sites.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Site
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-layouts.app>
