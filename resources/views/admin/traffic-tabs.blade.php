<nav class="flex flex-wrap gap-2 border-b border-slate-200 pb-4" aria-label="Traffic">
    <a href="{{ route('admin.airlines.index') }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ request()->routeIs('admin.airlines.*') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">Airline</a>
    <a href="{{ route('admin.traffic.index') }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ request()->routeIs('admin.traffic.*') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">Traffic Movement</a>
    <a href="{{ route('admin.enroutes.index') }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ request()->routeIs('admin.enroutes.*') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">Enroute</a>
    <a href="{{ route('admin.terminals.index') }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ request()->routeIs('admin.terminals.*') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">Terminal</a>
</nav>
