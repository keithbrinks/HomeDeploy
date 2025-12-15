<x-layouts.app>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-white mb-6">Add New Site</h1>

        <form action="{{ route('sites.store') }}" method="POST" class="space-y-6 bg-slate-900 p-6 rounded-lg border border-slate-800">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Site Name</label>
                <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="repo_url" class="block text-sm font-medium text-slate-300">Repository URL</label>
                <input type="url" name="repo_url" id="repo_url" required placeholder="https://github.com/user/repo.git" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="branch" class="block text-sm font-medium text-slate-300">Branch</label>
                <input type="text" name="branch" id="branch" value="main" required class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="deploy_path" class="block text-sm font-medium text-slate-300">Deployment Path</label>
                <input type="text" name="deploy_path" id="deploy_path" required placeholder="/var/www/mysite" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div class="flex justify-end">
                <a href="{{ route('dashboard') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium mr-3">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium">Create Site</button>
            </div>
        </form>
    </div>
</x-layouts.app>
