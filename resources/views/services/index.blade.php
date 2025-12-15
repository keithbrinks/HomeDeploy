<x-layouts.app>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Service Management</h1>
            <p class="mt-2 text-slate-400">Manage system services for your server</p>
        </div>

        <x-flash-messages />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(['nginx', 'mysql', 'redis-server', 'php8.2-fpm', 'php8.3-fpm'] as $service)
                <div class="bg-slate-800/50 rounded-lg border border-slate-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">
                            {{ str_replace('-', ' ', ucfirst($service)) }}
                        </h3>
                        <span class="w-3 h-3 rounded-full bg-slate-600" title="Status unknown"></span>
                    </div>
                    
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('services.restart', $service) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition-colors"
                                    onclick="return confirm('Restart {{ $service }}?')">
                                Restart
                            </button>
                        </form>
                        
                        <a href="{{ route('services.status', $service) }}" 
                           class="block w-full px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-md text-sm font-medium text-center transition-colors">
                            Check Status
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
            <p class="text-amber-400 text-sm">
                <strong>Note:</strong> Restarting services requires sudo access. Ensure the www-data user is configured in sudoers for systemctl commands.
            </p>
        </div>
    </div>
</x-layouts.app>
