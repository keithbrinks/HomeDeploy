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
                    <h2 class="text-lg font-semibold text-white">GitHub OAuth</h2>
                    <p class="text-sm text-slate-400 mt-1">Connect GitHub to browse repositories and enable auto-deployments</p>
                </div>
                @if($settings->hasGithubOAuth())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Configured
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                        Not Configured
                    </span>
                @endif
            </div>

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

        <!-- Future Settings Sections -->
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 opacity-50">
            <h2 class="text-lg font-semibold text-white mb-2">Additional Integrations</h2>
            <p class="text-sm text-slate-400">More integrations coming soon...</p>
        </div>
    </div>
</x-layouts.app>
