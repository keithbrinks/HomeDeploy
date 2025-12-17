<x-layouts.app>
    <x-flash-messages />
    
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Settings</h1>
            <p class="text-sm text-slate-400 mt-1">Configure HomeDeploy integrations and services</p>
        </div>

        <!-- GitHub OAuth Configuration -->
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-white">GitHub Integration</h2>
                    <p class="text-sm text-slate-400 mt-1">Connect GitHub to browse repositories and enable auto-deployments</p>
                </div>
                @if($settings->isGithubConnected())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Connected
                    </span>
                @elseif($settings->hasGithubOAuth())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">
                        OAuth Configured
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                        Not Configured
                    </span>
                @endif
            </div>

            @if($settings->hasGithubToken())
                <!-- Connected Status -->
                <div class="bg-emerald-500/10 border border-emerald-500/50 rounded-md p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-emerald-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-emerald-300">GitHub Connected</h3>
                            <p class="text-sm text-emerald-200 mt-1">Account: <strong>{{ $settings->github_user }}</strong></p>
                            <p class="text-xs text-emerald-300/70 mt-2">You can now browse your repositories when creating sites.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Instructions -->
            <div class="bg-slate-900 rounded-md p-4 mb-6">
                <h3 class="text-sm font-medium text-white mb-3">Setup Instructions</h3>
                <ol class="space-y-2 text-sm text-slate-300">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">1</span>
                        <span>Go to <a href="https://github.com/settings/developers" target="_blank" class="text-indigo-400 hover:text-indigo-300 underline">GitHub Developer Settings</a></span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">2</span>
                        <span>Click <strong>"New OAuth App"</strong></span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">3</span>
                        <div>
                            <span>Fill in the application details:</span>
                            <ul class="mt-1 ml-4 space-y-1 text-xs text-slate-400">
                                <li>• Application name: <code class="text-indigo-400">HomeDeploy</code></li>
                                <li>• Homepage URL: <code class="text-indigo-400">{{ config('app.url') }}</code></li>
                                <li>• Callback URL: <code class="text-indigo-400">{{ config('app.url') }}/auth/github/callback</code></li>
                            </ul>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">4</span>
                        <span>Copy the <strong>Client ID</strong> and generate a new <strong>Client Secret</strong></span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">5</span>
                        <span>Paste them in the form below</span>
                    </li>
                </ol>
            </div>

            <!-- Configuration Form -->
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label for="github_client_id" class="block text-sm font-medium text-slate-300 mb-2">Client ID</label>
                    <input type="text" name="github_client_id" id="github_client_id" 
                           value="{{ old('github_client_id', $settings->github_client_id) }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           placeholder="Iv1.a1b2c3d4e5f6g7h8">
                    @error('github_client_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="github_client_secret" class="block text-sm font-medium text-slate-300 mb-2">Client Secret</label>
                    <input type="password" name="github_client_secret" id="github_client_secret"
                           value="{{ old('github_client_secret') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           placeholder="{{ $settings->github_client_secret ? 'Leave blank to keep current secret' : 'Enter your client secret' }}">
                    @error('github_client_secret')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @if($settings->github_client_secret)
                        <p class="mt-1 text-xs text-slate-500">Leave blank to keep the current secret</p>
                    @endif
                </div>
                
                <div>
                    <label for="github_redirect_uri" class="block text-sm font-medium text-slate-300 mb-2">Redirect URI</label>
                    <input type="url" name="github_redirect_uri" id="github_redirect_uri"
                           value="{{ old('github_redirect_uri', $settings->github_redirect_uri ?: config('app.url') . '/auth/github/callback') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           placeholder="{{ config('app.url') }}/auth/github/callback">
                    @error('github_redirect_uri')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-end space-x-3 pt-4">
                    @if($settings->hasGithubOAuth() && !$settings->hasGithubToken())
                        <a href="{{ route('auth.github') }}" 
                           class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Connect GitHub Account
                        </a>
                    @endif
                    @if($settings->hasGithubOAuth())
                        <a href="{{ route('settings.test-github') }}" 
                           class="text-sm text-slate-400 hover:text-white transition-colors">
                            Test Connection
                        </a>
                    @endif
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Server Configuration -->
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-2">Server Configuration</h2>
            <p class="text-sm text-slate-400 mb-6">Basic server settings for your deployment environment</p>
            
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Server IP -->
                <div>
                    <label for="server_ip" class="block text-sm font-medium text-slate-300 mb-2">Server IP Address</label>
                    <div class="flex gap-2">
                        <input type="text" name="server_ip" id="server_ip" 
                               value="{{ old('server_ip', $settings->server_ip) }}"
                               class="flex-1 bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="192.168.1.106">
                        <button type="button" 
                                onclick="detectServerIp()"
                                class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap">
                            Auto-Detect
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Your server's IP address for direct access</p>
                </div>
                
                <!-- Base Domain -->
                <div>
                    <label for="base_domain" class="block text-sm font-medium text-slate-300 mb-2">Base Domain</label>
                    <input type="text" name="base_domain" id="base_domain"
                           value="{{ old('base_domain', $settings->base_domain) }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="example.com">
                    <p class="mt-1 text-xs text-slate-500">Your main domain - sites will be created as <code class="text-indigo-400">sitename.example.com</code></p>
                </div>
                
                <div class="flex justify-end pt-4 border-t border-slate-700">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Cloudflare Tunnel -->
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-white">Cloudflare Tunnel</h2>
                    <p class="text-sm text-slate-400 mt-1">Expose your server to the internet securely without port forwarding</p>
                </div>
                @if($settings->cloudflare_tunnel_enabled)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                        Inactive
                    </span>
                @endif
            </div>

            <div class="bg-slate-900 rounded-md p-4 mb-6">
                <h3 class="text-sm font-medium text-white mb-3">Setup Instructions</h3>
                <ol class="space-y-3 text-sm text-slate-300">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">1</span>
                        <div class="flex-1">
                            <p class="mb-1">Install cloudflared on your server:</p>
                            <code class="text-xs bg-slate-800 px-2 py-1 rounded block">wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb && sudo dpkg -i cloudflared-linux-amd64.deb</code>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">2</span>
                        <div class="flex-1">
                            <p class="mb-1">Login to Cloudflare (opens browser):</p>
                            <code class="text-xs bg-slate-800 px-2 py-1 rounded block">sudo cloudflared tunnel login</code>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">3</span>
                        <div class="flex-1">
                            <p class="mb-1">Create a tunnel named "homedeploy":</p>
                            <code class="text-xs bg-slate-800 px-2 py-1 rounded block">sudo cloudflared tunnel create homedeploy</code>
                            <p class="text-xs text-slate-500 mt-1">This will create <code class="text-indigo-400">/root/.cloudflared/&lt;TUNNEL_ID&gt;.json</code></p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">4</span>
                        <div class="flex-1">
                            <p class="mb-1">Get the tunnel ID (save this for below):</p>
                            <code class="text-xs bg-slate-800 px-2 py-1 rounded block">sudo cloudflared tunnel list</code>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">5</span>
                        <div class="flex-1">
                            <p class="mb-1">Copy the entire contents of the tunnel credentials file:</p>
                            <code class="text-xs bg-slate-800 px-2 py-1 rounded block">sudo cat /root/.cloudflared/&lt;TUNNEL_ID&gt;.json</code>
                            <p class="text-xs text-slate-500 mt-1">Paste the entire JSON content in the Tunnel Token field below</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 mt-0.5">6</span>
                        <div class="flex-1">
                            <p>Enter the Tunnel ID and credentials below, save configuration, then click "Start Tunnel"</p>
                        </div>
                    </li>
                </ol>
                <div class="mt-4 p-3 bg-amber-500/10 border border-amber-500/20 rounded">
                    <p class="text-xs text-amber-300">
                        <strong>Note:</strong> HomeDeploy will automatically create the config file and systemd service. You only need to install cloudflared and create the tunnel manually.
                    </p>
                </div>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label for="cloudflare_tunnel_id" class="block text-sm font-medium text-slate-300 mb-2">Tunnel ID</label>
                    <input type="text" name="cloudflare_tunnel_id" id="cloudflare_tunnel_id"
                           value="{{ old('cloudflare_tunnel_id', $settings->cloudflare_tunnel_id) }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                    <p class="mt-1 text-xs text-slate-500">From the output of <code class="text-indigo-400">cloudflared tunnel list</code></p>
                </div>
                
                <div>
                    <label for="cloudflare_tunnel_token" class="block text-sm font-medium text-slate-300 mb-2">Tunnel Credentials (JSON)</label>
                    <textarea name="cloudflare_tunnel_token" id="cloudflare_tunnel_token" rows="6"
                           class="w-full bg-slate-900 border border-slate-700 rounded-md px-4 py-2 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-xs"
                           placeholder='{"AccountTag":"...","TunnelSecret":"...","TunnelID":"..."}'>{{ old('cloudflare_tunnel_token', $settings->cloudflare_tunnel_token) }}</textarea>
                    @if($settings->cloudflare_tunnel_token)
                        <p class="mt-1 text-xs text-slate-500">Current credentials are saved (edit to update)</p>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4">
                    @if($settings->hasCloudflare())
                        <div class="flex gap-2">
                            @if(!$settings->cloudflare_tunnel_enabled)
                                <form action="{{ route('settings.tunnel.start') }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Start Cloudflare Tunnel?')"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                        Start Tunnel
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('settings.tunnel.stop') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Stop Cloudflare Tunnel?')"
                                            class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                        Stop Tunnel
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors ml-auto">
                        Save Tunnel Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function detectServerIp() {
            try {
                const response = await fetch('https://api.ipify.org?format=json');
                const data = await response.json();
                document.getElementById('server_ip').value = data.ip;
            } catch (error) {
                alert('Failed to detect IP address');
            }
        }
    </script>
</x-layouts.app>
