<div class="bg-white rounded-3xl border border-slate-100 p-4 shadow-sm space-y-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] px-4 mb-4">Main Menu</p>
        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.index') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="font-bold text-sm">Overview</span>
        </a>
        <a href="{{ route('admin.airlines.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.airlines.index') || request()->is('admin/airlines*') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
            <path strokeLinecap="round" strokeLinejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
            </svg>
            <span class="font-bold text-sm">Master Data Airline</span>
        </a>
        <a href="{{ route('admin.traffic.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.traffic.index') || request()->is('admin/traffic*') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="currentColor" strokeLinecap="round" strokeLinejoin="round" className="icon icon-tabler icons-tabler-outline icon-tabler-plane-tilt"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14.5 6.5l3 -2.9a2.05 2.05 0 0 1 2.9 2.9l-2.9 3l2.5 7.5l-2.5 2.55l-3.5 -6.55l-3 3v3l-2 2l-1.5 -4.5l-4.5 -1.5l2 -2h3l3 -3l-6.5 -3.5l2.5 -2.5l7.5 2.5" /></svg>
            <span class="font-bold text-sm">Traffic Movement</span>
        </a>
        <a href="{{ route('admin.enroutes.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.enroutes.index') || request()->is('admin/enroutes*') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className="icon icon-tabler icons-tabler-outline icon-tabler-route-square"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17h4v4h-4l0 -4" /><path d="M17 3h4v4h-4l0 -4" /><path d="M11 19h5.5a3.5 3.5 0 0 0 0 -7h-8a3.5 3.5 0 0 1 0 -7h4.5" /></svg>
            <span class="font-bold text-sm">Enroute</span>
        </a>
        <a href="{{ route('admin.terminals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.terminals.index') || request()->is('admin/terminals*') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className="icon icon-tabler icons-tabler-outline icon-tabler-air-traffic-control"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 3h2" /><path d="M12 3v3" /><path d="M5.998 6h12.004a2 2 0 0 1 1.916 2.575l-1.8 6a2 2 0 0 1 -1.916 1.425h-8.404a2 2 0 0 1 -1.916 -1.425l-1.8 -6a2 2 0 0 1 1.916 -2.575" /><path d="M8.5 6l1.5 10v5" /><path d="M15.5 6l-1.5 10v5" /></svg>            <span class="font-bold text-sm">Terminal</span>
        </a>
        <hr class="my-4 border-slate-100">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('profile.edit') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="font-bold text-sm">My Profile</span>
        </a>
    </div>