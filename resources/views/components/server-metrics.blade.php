<div x-data="{
    metrics: {
        cpu: 0,
        memory: { percent: 0, used: '0 MB', total: '0 MB' },
        disk: { percent: 0, used: '0 GB', total: '0 GB' },
        uptime: '0m',
        load_average: { '1min': 0, '5min': 0, '15min': 0 }
    },
    loading: true,
    error: false,
    async fetchMetrics() {
        try {
            const response = await fetch('/api/server-metrics');
            if (!response.ok) throw new Error('Failed to fetch metrics');
            this.metrics = await response.json();
            this.error = false;
        } catch (error) {
            console.error('Error fetching server metrics:', error);
            this.error = true;
        } finally {
            this.loading = false;
        }
    },
    getColorClass(percent) {
        if (percent < 60) return 'bg-green-500';
        if (percent < 80) return 'bg-yellow-500';
        return 'bg-red-500';
    }
}" x-init="
    fetchMetrics();
    setInterval(() => fetchMetrics(), 5000);
" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    
    <!-- CPU Usage -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-slate-400">CPU Usage</h3>
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
            </svg>
        </div>
        
        <template x-if="!loading">
            <div>
                <div class="text-3xl font-bold text-white mb-2" x-text="metrics.cpu + '%'"></div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500" 
                         :class="getColorClass(metrics.cpu)"
                         :style="'width: ' + metrics.cpu + '%'"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Load: <span x-text="metrics.load_average['1min']"></span> / 
                    <span x-text="metrics.load_average['5min']"></span> / 
                    <span x-text="metrics.load_average['15min']"></span>
                </div>
            </div>
        </template>
        
        <template x-if="loading">
            <div class="text-center text-slate-400">
                <div class="animate-spin h-8 w-8 border-4 border-slate-600 border-t-indigo-500 rounded-full mx-auto"></div>
            </div>
        </template>
    </div>

    <!-- Memory Usage -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-slate-400">Memory Usage</h3>
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
        </div>
        
        <template x-if="!loading">
            <div>
                <div class="text-3xl font-bold text-white mb-2" x-text="metrics.memory.percent + '%'"></div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500" 
                         :class="getColorClass(metrics.memory.percent)"
                         :style="'width: ' + metrics.memory.percent + '%'"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <span x-text="metrics.memory.used"></span> / <span x-text="metrics.memory.total"></span>
                </div>
            </div>
        </template>
        
        <template x-if="loading">
            <div class="text-center text-slate-400">
                <div class="animate-spin h-8 w-8 border-4 border-slate-600 border-t-indigo-500 rounded-full mx-auto"></div>
            </div>
        </template>
    </div>

    <!-- Disk Usage -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-slate-400">Disk Usage</h3>
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
        </div>
        
        <template x-if="!loading">
            <div>
                <div class="text-3xl font-bold text-white mb-2" x-text="metrics.disk.percent + '%'"></div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500" 
                         :class="getColorClass(metrics.disk.percent)"
                         :style="'width: ' + metrics.disk.percent + '%'"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <span x-text="metrics.disk.used"></span> / <span x-text="metrics.disk.total"></span>
                </div>
            </div>
        </template>
        
        <template x-if="loading">
            <div class="text-center text-slate-400">
                <div class="animate-spin h-8 w-8 border-4 border-slate-600 border-t-indigo-500 rounded-full mx-auto"></div>
            </div>
        </template>
    </div>

    <!-- System Uptime -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-slate-400">System Uptime</h3>
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <template x-if="!loading">
            <div>
                <div class="text-3xl font-bold text-white mb-2" x-text="metrics.uptime"></div>
                <div class="text-xs text-slate-400">Server running</div>
            </div>
        </template>
        
        <template x-if="loading">
            <div class="text-center text-slate-400">
                <div class="animate-spin h-8 w-8 border-4 border-slate-600 border-t-indigo-500 rounded-full mx-auto"></div>
            </div>
        </template>
    </div>

    <!-- Error State -->
    <template x-if="error">
        <div class="col-span-full bg-red-900/20 border border-red-700 rounded-lg p-4">
            <p class="text-red-400 text-sm">Failed to load server metrics. Retrying...</p>
        </div>
    </template>
</div>
