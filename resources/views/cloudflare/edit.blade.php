<x-layouts.app>
    <x-flash-messages />
    
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('sites.show', $site) }}" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-white">Cloudflare Tunnel</h1>
            </div>
            <p class="text-sm text-slate-400">Configure Cloudflare Tunnel for {{ $site->name }}</p>
        </div>

        @if(!$config)
            <!-- Setup Instructions -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-white mb-4">Setup Instructions</h2>
                
                <div class="space-y-4 text-sm text-slate-300">
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-3">1</span>
                        <div>
                            <p class="font-medium mb-1">Install cloudflared</p>
                            <code class="block bg-slate-900 px-3 py-2 rounded text-xs font-mono mt-2">
                                curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb && sudo dpkg -i cloudflared.deb
                            </code>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-3">2</span>
                        <div>
                            <p class="font-medium mb-1">Create a tunnel in Cloudflare Dashboard</p>
                            <p class="text-slate-400 text-xs">Go to Zero Trust → Networks → Tunnels → Create a Tunnel</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-3">3</span>
                        <div>
                            <p class="font-medium mb-1">Copy the tunnel details</p>
                            <p class="text-slate-400 text-xs">You'll need: Tunnel ID, Tunnel Name, Tunnel Token, and Account ID</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-3">4</span>
                        <div>
                            <p class="font-medium mb-1">Fill in the form below</p>
                            <p class="text-slate-400 text-xs">Configure your tunnel to point to this site</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Configuration Form -->
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-white mb-6">Tunnel Configuration</h2>
            
            <form action="{{ route('cloudflare.store', $site) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tunnel_id" class="block text-sm font-medium text-slate-300 mb-2">Tunnel ID</label>
                        <input type="text" name="tunnel_id" id="tunnel_id" 
                               value="{{ old('tunnel_id', $config?->tunnel_id) }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                               required>
                        @error('tunnel_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="tunnel_name" class="block text-sm font-medium text-slate-300 mb-2">Tunnel Name</label>
                        <input type="text" name="tunnel_name" id="tunnel_name"
                               value="{{ old('tunnel_name', $config?->tunnel_name) }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="my-tunnel"
                               required>
                        @error('tunnel_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="tunnel_token" class="block text-sm font-medium text-slate-300 mb-2">Tunnel Token</label>
                    <textarea name="tunnel_token" id="tunnel_token" rows="3"
                              class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                              placeholder="eyJhIjoiYWJjZGVmLi4u"
                              required>{{ old('tunnel_token', $config?->tunnel_token ? '••••••••' : '') }}</textarea>
                    @error('tunnel_token')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="account_id" class="block text-sm font-medium text-slate-300 mb-2">Account ID</label>
                    <input type="text" name="account_id" id="account_id"
                           value="{{ old('account_id', $config?->account_id) }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                           required>
                    @error('account_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="hostname" class="block text-sm font-medium text-slate-300 mb-2">Public Hostname</label>
                        <input type="text" name="hostname" id="hostname"
                               value="{{ old('hostname', $config?->hostname) }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="myapp.example.com"
                               required>
                        @error('hostname')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">The public domain for your tunnel</p>
                    </div>
                    
                    <div>
                        <label for="service_url" class="block text-sm font-medium text-slate-300 mb-2">Service URL</label>
                        <input type="text" name="service_url" id="service_url"
                               value="{{ old('service_url', $config?->service_url ?? 'http://localhost:' . $site->port) }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="http://localhost:{{ $site->port }}">
                        @error('service_url')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">Local service to expose (default: http://localhost:{{ $site->port }})</p>
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4">
                    @if($config)
                        <button type="button" 
                                onclick="if(confirm('Delete this tunnel configuration?')) { document.getElementById('delete-form').submit(); }"
                                class="text-red-400 hover:text-red-300 text-sm font-medium">
                            Delete Configuration
                        </button>
                    @else
                        <div></div>
                    @endif
                    
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        {{ $config ? 'Update Configuration' : 'Save Configuration' }}
                    </button>
                </div>
            </form>
        </div>
        
        @if($config)
            <!-- Tunnel Control -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mt-6">
                <h2 class="text-lg font-semibold text-white mb-4">Tunnel Control</h2>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-300">
                            Status: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config->enabled ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700 text-slate-300' }}">
                                {{ $config->enabled ? 'Running' : 'Stopped' }}
                            </span>
                        </p>
                        @if($config->enabled)
                            <p class="text-xs text-slate-400 mt-1">Tunnel is active at: <a href="https://{{ $config->hostname }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">{{ $config->hostname }}</a></p>
                        @endif
                    </div>
                    
                    <div class="flex space-x-3">
                        @if($config->enabled)
                            <form action="{{ route('cloudflare.stop', $site) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Stop Tunnel
                                </button>
                            </form>
                        @else
                            <form action="{{ route('cloudflare.start', $site) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Start Tunnel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Delete Form (hidden) -->
            <form id="delete-form" action="{{ route('cloudflare.destroy', $site) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</x-layouts.app>
