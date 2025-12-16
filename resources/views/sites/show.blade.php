<x-layouts.app>
    <x-flash-messages />
    
    <div class="flex justify-between items-center mb-8" x-data="{
        polling: false,
        latestDeploymentId: {{ $site->deployments->first()?->id ?? 'null' }},
        async pollLogs() {
            if (!this.latestDeploymentId || !this.polling) return;
            
            try {
                const response = await fetch('/api/deployments/' + this.latestDeploymentId + '/logs');
                const data = await response.json();
                
                // Update log output
                const logEl = document.getElementById('deployment-log');
                if (logEl) {
                    logEl.textContent = data.output || 'Waiting for logs...';
                    logEl.scrollTop = logEl.scrollHeight; // Auto-scroll to bottom
                }
                
                // Update status badge
                const statusEl = document.getElementById('deployment-status');
                if (statusEl) {
                    statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    statusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' +
                        (data.status === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 
                         data.status === 'failed' ? 'bg-rose-500/10 text-rose-400' :
                         data.status === 'running' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700 text-slate-300');
                }
                
                // Stop polling if deployment is complete
                if (data.status === 'success' || data.status === 'failed') {
                    this.polling = false;
                    clearInterval(this.pollInterval);
                }
            } catch (error) {
                console.error('Failed to fetch logs:', error);
            }
        },
        startPolling() {
            this.polling = true;
            this.pollInterval = setInterval(() => this.pollLogs(), 2000); // Poll every 2 seconds
            this.pollLogs(); // Initial fetch
        }
    }" x-init="if ({{ ($site->deployments->first()?->status === 'running' || $site->deployments->first()?->status === 'pending') ? 'true' : 'false' }}) startPolling()">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $site->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $site->repo_url }} ({{ $site->branch }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('sites.build-commands.edit', $site) }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                Build Commands
            </a>
            <a href="{{ route('cloudflare.edit', $site) }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                Cloudflare
            </a>
            <form action="{{ route('sites.nginx.generate', $site) }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Nginx Config
                </button>
            </form>
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
                    <div class="flex items-center space-x-3">
                        @if($site->deployments->isNotEmpty())
                            <span class="text-xs font-mono text-slate-500">{{ $site->deployments->first()->created_at->diffForHumans() }}</span>
                            <span id="deployment-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $site->deployments->first()->status === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 
                                   ($site->deployments->first()->status === 'failed' ? 'bg-rose-500/10 text-rose-400' : 
                                    ($site->deployments->first()->status === 'running' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700 text-slate-300')) }}">
                                {{ ucfirst($site->deployments->first()->status) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-0 bg-black">
                    @if($site->deployments->isNotEmpty())
                        <pre id="deployment-log" class="text-xs font-mono text-green-400 p-4 overflow-x-auto h-96">{{ $site->deployments->first()->output ?? 'Waiting for logs...' }}</pre>
                    @else
                        <div class="p-8 text-center text-slate-500">No deployments yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: History & Config -->
        <div class="space-y-6">
            <!-- Environment Variables -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden" x-data="{ showAddForm: false, editingEnvFile: false, envFileContent: '' }">
                <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">Environment Variables</h3>
                    <div class="flex space-x-2">
                        <button @click="editingEnvFile = !editingEnvFile; showAddForm = false" class="text-sm text-slate-400 hover:text-white">
                            <span x-text="editingEnvFile ? 'Cancel' : 'Edit .env'"></span>
                        </button>
                        <button @click="showAddForm = !showAddForm; editingEnvFile = false" class="text-sm text-indigo-400 hover:text-indigo-300">
                            <span x-text="showAddForm ? 'Cancel' : '+ Add'"></span>
                        </button>
                    </div>
                </div>
                
                <div x-show="editingEnvFile" class="px-6 py-4 border-b border-slate-800 bg-slate-800/50">
                    <form action="{{ route('sites.env-file.update', $site) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Environment File (.env)</label>
                            <textarea name="content" rows="15" class="block w-full rounded-md bg-slate-900 border-slate-700 text-white text-sm font-mono placeholder-slate-400" placeholder="APP_NAME=MyApp&#10;APP_ENV=production&#10;APP_DEBUG=false&#10;...">{{ old('content', $site->getEnvFileContent() ?? '') }}</textarea>
                            <p class="text-xs text-slate-500 mt-2">Edit the entire .env file for this site. Changes are saved to {{ $site->deploy_path }}/.env</p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-2 rounded-md text-sm font-medium">
                            Save .env File
                        </button>
                    </form>
                </div>
                
                <div x-show="showAddForm" class="px-6 py-4 border-b border-slate-800 bg-slate-800/50">
                    <form action="{{ route('sites.env.store', $site) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <input type="text" name="key" placeholder="Variable Name (e.g., APP_ENV)" required class="block w-full rounded-md bg-slate-700 border-slate-600 text-white text-sm placeholder-slate-400">
                        </div>
                        <div>
                            <textarea name="value" rows="2" placeholder="Value" required class="block w-full rounded-md bg-slate-700 border-slate-600 text-white text-sm placeholder-slate-400"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-md text-sm font-medium">
                            Add Variable
                        </button>
                    </form>
                </div>

                <ul class="divide-y divide-slate-800 max-h-64 overflow-y-auto">
                    @forelse($site->environmentVariables as $env)
                        <li class="px-6 py-3 hover:bg-slate-800/50 transition-colors group">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-white font-mono">{{ $env->key }}</p>
                                    <p class="text-xs text-slate-400 font-mono mt-1 truncate">{{ Str::limit($env->value, 40) }}</p>
                                </div>
                                <form action="{{ route('sites.env.destroy', [$site, $env]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this variable?')" class="text-rose-400 hover:text-rose-300 text-xs">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-center text-sm text-slate-500">No environment variables set</li>
                    @endforelse
                </ul>
            </div>

            <!-- Database Management -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800">
                    <h3 class="text-lg font-medium text-white">Database</h3>
                </div>
                
                @if(session('database_credentials'))
                    <div class="px-6 py-4 bg-emerald-500/10 border-b border-emerald-500/20">
                        <p class="text-sm font-medium text-emerald-400 mb-2">Database created! Save these credentials:</p>
                        <div class="space-y-1 font-mono text-xs">
                            <p class="text-slate-300"><span class="text-slate-500">Database:</span> {{ session('database_credentials')['database'] }}</p>
                            <p class="text-slate-300"><span class="text-slate-500">Username:</span> {{ session('database_credentials')['username'] }}</p>
                            <p class="text-slate-300"><span class="text-slate-500">Password:</span> {{ session('database_credentials')['password'] }}</p>
                            <p class="text-slate-300"><span class="text-slate-500">Host:</span> {{ session('database_credentials')['host'] }}</p>
                        </div>
                    </div>
                @endif

                <div class="px-6 py-4">
                    @if($site->database_name)
                        <div class="space-y-3">
                            <div class="bg-slate-800/50 rounded p-3">
                                <p class="text-xs text-slate-400 mb-1">Database Name</p>
                                <p class="text-sm font-mono text-white">{{ $site->database_name }}</p>
                            </div>
                            <div class="bg-slate-800/50 rounded p-3">
                                <p class="text-xs text-slate-400 mb-1">Username</p>
                                <p class="text-sm font-mono text-white">{{ $site->database_username }}</p>
                            </div>
                            <form action="{{ route('sites.database.destroy', $site) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('This will permanently delete the database and all its data. Continue?')"
                                        class="w-full bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Drop Database
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('sites.database.create', $site) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                Create Database
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-slate-500 text-center">Auto-generates database, user, and password</p>
                    @endif
                </div>
            </div>

            <!-- GitHub Webhook -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden" x-data="{ 
                showWebhook: false,
                webhookUrl: '{{ route('webhook.handle', $site) }}',
                webhookSecret: '{{ $site->webhook_secret ?? '' }}',
                async regenerateSecret() {
                    if (!confirm('This will invalidate the existing webhook. Continue?')) return;
                    
                    const response = await fetch('{{ route('sites.webhook.regenerate', $site) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    this.webhookSecret = data.secret;
                    this.webhookUrl = data.webhook_url;
                }
            }">
                <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white">GitHub Webhook</h3>
                    <button @click="showWebhook = !showWebhook" class="text-sm text-indigo-400 hover:text-indigo-300">
                        <span x-text="showWebhook ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                
                <div x-show="showWebhook" class="px-6 py-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Webhook URL</label>
                        <input type="text" :value="webhookUrl" readonly class="block w-full rounded-md bg-slate-800 border-slate-700 text-white text-xs font-mono">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Secret</label>
                        <div class="flex space-x-2">
                            <input type="text" x-model="webhookSecret" readonly class="flex-1 block rounded-md bg-slate-800 border-slate-700 text-white text-xs font-mono">
                            <button @click="regenerateSecret()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs rounded-md">
                                Regenerate
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-slate-500">Configure this webhook in your GitHub repository settings to enable auto-deploy on push.</p>
                </div>
            </div>

            <!-- Deployment History -->
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
                                    @if($deployment->commit_hash)
                                        <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ substr($deployment->commit_hash, 0, 7) }}</p>
                                    @endif
                                    @if($deployment->commit_message)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($deployment->commit_message, 50) }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $deployment->status === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 
                                           ($deployment->status === 'failed' ? 'bg-rose-500/10 text-rose-400' : 'bg-amber-500/10 text-amber-400') }}">
                                        {{ ucfirst($deployment->status) }}
                                    </span>
                                    @if($deployment->status === 'success')
                                        <form method="POST" action="{{ route('deployments.rollback', $deployment) }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                                                    onclick="return confirm('Rollback to this deployment?')">
                                                Rollback
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-layouts.app>
