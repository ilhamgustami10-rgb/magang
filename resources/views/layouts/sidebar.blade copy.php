{{-- resources/views/layouts/sidebar.blade.php --}}
<aside class="w-full lg:w-64 shrink-0">
    <div class="bg-white rounded-3xl border border-slate-100 p-4 shadow-sm space-y-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] px-4 mb-4">Main Menu</p>
        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.index') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="font-bold text-sm">Overview</span>
        </a>
        <a href="{{ route('admin.airlines.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.airlines.index') || request()->is('admin/airlines*') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 group' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ request()->routeIs('admin.airlines.index') || request()->is('admin/airlines*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            <span class="font-bold text-sm">Master Data Airline</span>
        </a>
        <hr class="my-4 border-slate-100">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('profile.edit') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="font-bold text-sm">My Profile</span>
        </a>
    </div>
</aside>