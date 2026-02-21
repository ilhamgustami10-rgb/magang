<x-app-layout>
    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="w-full lg:w-64 shrink-0">
            <div class="bg-white rounded-3xl border border-slate-100 p-4 shadow-sm space-y-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] px-4 mb-4">Main Menu</p>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="font-bold text-sm">Overview</span>
                </a>

                <a href="{{ route('admin.airlines.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 transition-all group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span class="font-bold text-sm">Master Data Airline</span>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="font-bold text-sm">Manage Traffic</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m9-6a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold text-sm">Manage Finance</span>
                </a>

                <hr class="my-4 border-slate-100">

                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-bold text-sm">My Profile</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 space-y-8">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Admin Dashboard</h1>
                    <p class="text-slate-500 font-medium">Welcome back, {{ Auth::user()->name }}</p>
                </div>
                
                <button class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Report
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Traffic</p>
                    <h3 class="text-3xl font-black text-slate-800">24,512</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Monthly Revenue</p>
                    <h3 class="text-3xl font-black text-emerald-600">IDR 1.2B</h3>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Active Personnel</p>
                    <h3 class="text-3xl font-black text-blue-600">128</h3>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="font-bold text-slate-800">Recent System Logs</h3>
                </div>
                <div class="p-8 text-center text-slate-400 text-sm italic">
                    No recent activity logs found.
                </div>
            </div>
        </div>

    </div>
</x-app-layout>