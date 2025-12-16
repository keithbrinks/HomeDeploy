<x-layouts.app>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-white mb-6">Add New Site</h1>

        <x-flash-messages />

        @if(!$hasGithub)
            <div class="mb-6 bg-amber-500/10 border border-amber-500/50 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-amber-300">GitHub Not Connected</h3>
                        <p class="text-sm text-amber-200 mt-1">Connect GitHub in Settings to browse repositories, or enter details manually below.</p>
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center mt-3 px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-sm rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Go to Settings
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('sites.store') }}" method="POST" x-data="{
            selectedRepo: null,
            branches: [],
            loadingBranches: false,
            async selectRepo(repo) {
                this.selectedRepo = repo;
                $refs.nameInput.value = repo.name;
                $refs.repoUrlInput.value = repo.clone_url;
                $refs.deployPathInput.value = '/var/www/' + repo.name;
                
                // Fetch branches
                this.loadingBranches = true;
                this.branches = [];
                
                const [owner, repoName] = repo.full_name.split('/');
                
                try {
                    const response = await fetch('{{ route('api.github.branches') }}?owner=' + owner + '&repo=' + repoName);
                    const data = await response.json();
                    this.branches = data.branches || [];
                    
                    // Pre-select default branch
                    const defaultBranch = this.branches.find(b => b.name === repo.default_branch);
                    if (defaultBranch) {
                        $refs.branchInput.value = defaultBranch.name;
                    }
                } catch (error) {
                    console.error('Failed to fetch branches:', error);
                } finally {
                    this.loadingBranches = false;
                }
            }
        }" class="space-y-6 bg-slate-900 p-6 rounded-lg border border-slate-800">
            @csrf

            @if($hasGithub && count($repositories) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Select Repository</label>
                    <div class="max-h-60 overflow-y-auto border border-slate-700 rounded-md bg-slate-800">
                        @foreach($repositories as $repo)
                            <button type="button" 
                                @click="selectRepo({{ json_encode($repo) }})"
                                :class="selectedRepo?.full_name === '{{ $repo['full_name'] }}' ? 'bg-indigo-600/20 border-indigo-500' : 'hover:bg-slate-700'"
                                class="w-full text-left px-4 py-3 border-b border-slate-700 last:border-b-0 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $repo['full_name'] }}</p>
                                        @if($repo['description'])
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $repo['description'] }}</p>
                                        @endif
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded {{ $repo['private'] ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700 text-slate-400' }}">
                                        {{ $repo['private'] ? 'Private' : 'Public' }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Or fill in the details manually below</p>
                </div>
            @endif

            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Site Name</label>
                <input type="text" name="name" id="name" x-ref="nameInput" required class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="repo_url" class="block text-sm font-medium text-slate-300">Repository URL</label>
                <input type="url" name="repo_url" id="repo_url" x-ref="repoUrlInput" required placeholder="https://github.com/user/repo.git" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="branch" class="block text-sm font-medium text-slate-300">Branch</label>
                <template x-if="branches.length > 0">
                    <select name="branch" id="branch" x-ref="branchInput" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <template x-for="branch in branches" :key="branch.name">
                            <option :value="branch.name" x-text="branch.name"></option>
                        </template>
                    </select>
                </template>
                <template x-if="branches.length === 0">
                    <input type="text" name="branch" id="branch" x-ref="branchInput" value="main" required class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </template>
                <p x-show="loadingBranches" class="text-xs text-slate-500 mt-1">Loading branches...</p>
            </div>

            <div>
                <label for="deploy_path" class="block text-sm font-medium text-slate-300">Deployment Path</label>
                <input type="text" name="deploy_path" id="deploy_path" x-ref="deployPathInput" required placeholder="/var/www/mysite" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Domain Configuration -->
            <div x-data="{
                strategy: 'subdomain',
                customDomain: '',
                siteName: '',
                updatePreview() {
                    this.siteName = $refs.nameInput?.value || 'sitename';
                }
            }" @input.window="updatePreview()">
                <label class="block text-sm font-medium text-slate-300 mb-3">Domain Configuration</label>
                
                <div class="space-y-3">
                    <!-- Subdomain -->
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                           :class="strategy === 'subdomain' ? 'border-indigo-500 bg-indigo-500/10' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" name="domain_strategy" value="subdomain" x-model="strategy" class="mt-1 text-indigo-600" {{ $settings->base_domain ? '' : 'disabled' }}>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-white">Subdomain (Recommended)</p>
                            @if($settings->base_domain)
                                <p class="text-xs text-slate-400 mt-1">Access via: <span class="font-mono" x-text="`${siteName}.{{ $settings->base_domain }}`"></span></p>
                            @else
                                <p class="text-xs text-amber-400 mt-1">Set base domain in Settings first</p>
                            @endif
                        </div>
                    </label>

                    <!-- Custom Domain -->
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                           :class="strategy === 'custom' ? 'border-indigo-500 bg-indigo-500/10' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" name="domain_strategy" value="custom" x-model="strategy" class="mt-1 text-indigo-600">
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-white mb-2">Custom Domain</p>
                            <input type="text" 
                                   name="domain" 
                                   x-model="customDomain"
                                   :disabled="strategy !== 'custom'"
                                   placeholder="example.com"
                                   class="w-full rounded-md bg-slate-800 border-slate-700 text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <p class="text-xs text-slate-500 mt-1">Configure DNS to point to {{ $settings->server_ip ?: 'your server IP' }}</p>
                        </div>
                    </label>
                </div>

                <div class="mt-4 p-3 bg-slate-800 rounded-md border border-slate-700">
                    <p class="text-xs font-medium text-slate-400 mb-1">Site will be accessible at:</p>
                    <p class="text-sm font-mono text-white" x-text="
                        strategy === 'subdomain' ? `${siteName}.{{ $settings->base_domain ?: 'domain.com' }}` :
                        strategy === 'custom' ? (customDomain || 'Enter custom domain') : ''
                    "></p>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('dashboard') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium mr-3">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium">Create Site</button>
            </div>
        </form>
    </div>
</x-layouts.app>
