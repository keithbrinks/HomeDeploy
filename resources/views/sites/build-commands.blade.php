<x-layouts.app>
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Build Commands</h1>
                    <p class="mt-2 text-slate-400">Configure the commands that run during deployment for {{ $site->name }}</p>
                </div>
                <a href="{{ route('sites.show', $site) }}" class="text-slate-400 hover:text-white transition-colors">
                    ← Back to Site
                </a>
            </div>
        </div>

        <x-flash-messages />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <form action="{{ route('sites.build-commands.update', $site) }}" method="POST" x-data="{
                    commands: {{ json_encode($site->build_commands ?? ['']) }},
                    draggedIndex: null,
                    addCommand() {
                        this.commands.push('');
                    },
                    removeCommand(index) {
                        this.commands.splice(index, 1);
                    },
                    loadPreset(commands) {
                        this.commands = commands;
                    },
                    moveUp(index) {
                        if (index > 0) {
                            [this.commands[index], this.commands[index - 1]] = [this.commands[index - 1], this.commands[index]];
                        }
                    },
                    moveDown(index) {
                        if (index < this.commands.length - 1) {
                            [this.commands[index], this.commands[index + 1]] = [this.commands[index + 1], this.commands[index]];
                        }
                    }
                }" class="bg-slate-900 rounded-lg border border-slate-800 p-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4 mb-6">
                        <template x-for="(command, index) in commands" :key="index">
                            <div class="flex gap-2 items-center">
                                <!-- Reorder buttons -->
                                <div class="flex flex-col gap-1">
                                    <button 
                                        type="button" 
                                        @click="moveUp(index)"
                                        :disabled="index === 0"
                                        :class="index === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:text-indigo-400'"
                                        class="text-slate-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="moveDown(index)"
                                        :disabled="index === commands.length - 1"
                                        :class="index === commands.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:text-indigo-400'"
                                        class="text-slate-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex-shrink-0 w-8 h-10 flex items-center justify-center text-slate-500 font-mono text-sm">
                                    <span x-text="index + 1"></span>.
                                </div>
                                <input 
                                    type="text" 
                                    :name="'commands[' + index + ']'" 
                                    x-model="commands[index]"
                                    placeholder="e.g., npm install"
                                    required
                                    class="flex-1 rounded-md bg-slate-800 border-slate-700 text-white font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <button 
                                    type="button" 
                                    @click="removeCommand(index)"
                                    x-show="commands.length > 1"
                                    class="flex-shrink-0 px-3 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-md transition-colors"
                                    aria-label="Remove command">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                        <button 
                            type="button" 
                            @click="addCommand()"
                            class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Command
                        </button>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('sites.show', $site) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-md text-sm font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-md text-sm font-medium transition-colors">
                                Save Commands
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Presets Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 rounded-lg border border-slate-800 p-6">
                    <h3 class="text-lg font-medium text-white mb-4">Common Presets</h3>
                    <p class="text-xs text-slate-400 mb-4">Click a preset to load its commands</p>
                    
                    <div class="space-y-3">
                        @foreach($commonPresets as $name => $commands)
                            <button 
                                type="button"
                                @click="loadPreset({{ json_encode($commands) }})"
                                class="w-full text-left px-4 py-3 bg-slate-800 hover:bg-slate-700 rounded-md transition-colors group">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-white">{{ $name }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ count($commands) }} commands</p>
                                    </div>
                                    <svg class="w-5 h-5 text-slate-500 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-indigo-300 mb-1">Execution Order</h4>
                            <p class="text-xs text-indigo-200/80">Commands run sequentially during deployment. If any command fails, the deployment stops.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
