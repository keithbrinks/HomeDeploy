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
                        <p class="text-sm text-amber-200 mt-1">Connect your GitHub account to browse repositories or enter details manually.</p>
                        <a href="{{ route('auth.github') }}" class="inline-flex items-center mt-3 px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-sm rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            Connect GitHub
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

            <div class="flex justify-end">
                <a href="{{ route('dashboard') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium mr-3">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium">Create Site</button>
            </div>
        </form>
    </div>
</x-layouts.app>
