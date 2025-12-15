<x-layouts.app>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $site->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $site->repo_url }} ({{ $site->branch }})</p>
        </div>
        <div class="flex space-x-3">
            <form action="{{ route('sites.deploy', $site) }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Deploy Now
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content: Latest Deployment Log -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">Latest Deployment</h3>
                    @if($site->deployments->isNotEmpty())
                        <span class="text-xs font-mono text-slate-500">{{ $site->deployments->first()->created_at->diffForHumans() }}</span>
                    @endif
                </div>
                <div class="p-0 bg-black">
                    @if($site->deployments->isNotEmpty())
                        <pre class="text-xs font-mono text-green-400 p-4 overflow-x-auto h-96">{{ $site->deployments->first()->output ?? 'Waiting for logs...' }}</pre>
                    @else
                        <div class="p-8 text-center text-slate-500">No deployments yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: History & Config -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800">
                    <h3 class="text-lg font-medium text-white">Deployment History</h3>
                </div>
                <ul class="divide-y divide-slate-800">
                    @foreach($site->deployments->take(5) as $deployment)
                        <li class="px-6 py-4 hover:bg-slate-800/50 transition-colors">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-white">
                                        #{{ $deployment->id }} 
                                        <span class="text-slate-500 text-xs ml-2">{{ $deployment->created_at->format('M d, H:i') }}</span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $deployment->deployed_by }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $deployment->status === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 
                                       ($deployment->status === 'failed' ? 'bg-rose-500/10 text-rose-400' : 'bg-amber-500/10 text-amber-400') }}">
                                    {{ ucfirst($deployment->status) }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>
