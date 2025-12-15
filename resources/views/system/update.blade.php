<x-layouts.app>
    <x-flash-messages />
    
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">System Update</h1>
            <p class="text-sm text-slate-400 mt-1">Check for and install HomeDeploy updates</p>
        </div>

        <div x-data="{
            checking: false,
            updateInfo: @js($updateInfo),
            async checkUpdates() {
                this.checking = true;
                try {
                    const response = await fetch('/system/update/check');
                    this.updateInfo = await response.json();
                } catch (error) {
                    console.error('Failed to check for updates:', error);
                } finally {
                    this.checking = false;
                }
            }
        }" class="space-y-6">
            
            <!-- Current Version Card -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Current Version</h2>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-slate-400 text-sm">Commit:</span>
                        <code class="ml-2 text-indigo-400 font-mono" x-text="updateInfo.currentCommit"></code>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <template x-if="updateInfo.hasUpdates">
                                <div class="flex items-center text-yellow-400">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="font-medium">Updates available</span>
                                </div>
                            </template>
                            
                            <template x-if="!updateInfo.hasUpdates && !updateInfo.error">
                                <div class="flex items-center text-green-400">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">Up to date</span>
                                </div>
                            </template>
                            
                            <template x-if="updateInfo.error">
                                <div class="flex items-center text-red-400">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium" x-text="updateInfo.error"></span>
                                </div>
                            </template>
                        </div>
                        
                        <button @click="checkUpdates()" 
                                :disabled="checking"
                                class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': checking }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span x-text="checking ? 'Checking...' : 'Check for Updates'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Available Updates Card -->
            <template x-if="updateInfo.hasUpdates">
                <div class="bg-slate-800 border border-yellow-700 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Available Updates</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-slate-400 text-sm">Latest Commit:</span>
                            <code class="ml-2 text-indigo-400 font-mono" x-text="updateInfo.remoteCommit"></code>
                        </div>
                        
                        <template x-if="updateInfo.commits && updateInfo.commits.length > 0">
                            <div>
                                <p class="text-slate-400 text-sm mb-2">Recent Changes:</p>
                                <div class="bg-slate-900 rounded-md p-4 space-y-1">
                                    <template x-for="commit in updateInfo.commits" :key="commit">
                                        <div class="text-sm text-slate-300 font-mono" x-text="commit"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <div class="bg-yellow-900/20 border border-yellow-700 rounded-md p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="text-sm text-yellow-200">
                                    <p class="font-medium mb-1">Before updating:</p>
                                    <ul class="list-disc list-inside space-y-1 text-yellow-300/90">
                                        <li>A backup of the current version will be created</li>
                                        <li>The update process may take a few minutes</li>
                                        <li>Your sites will continue running during the update</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <form action="{{ route('system.update.perform') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Install Update
                            </button>
                        </form>
                    </div>
                </div>
            </template>

            <!-- Documentation Card -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Update Information</h2>
                
                <div class="space-y-3 text-sm text-slate-300">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>HomeDeploy checks for updates from the main branch of the GitHub repository</p>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <p>Backup information is stored in storage/backups/ for rollback if needed</p>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <p>The update process runs composer install, database migrations, and cache clearing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
