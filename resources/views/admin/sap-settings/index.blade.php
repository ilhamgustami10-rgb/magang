<x-app-layout>
    <div class="flex flex-col gap-8 lg:flex-row">
        <aside class="w-64 shrink-0">@include('layouts.sidebar')</aside>
        <div class="flex-1 space-y-6">
        <header class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Pengaturan Bot SAP</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola konfigurasi koneksi SAP dan jadwal otomatisasi penarikan data.</p>
            </div>
        </header>

        @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 text-sm font-bold animate-pulse">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 p-4 bg-amber-50 text-amber-700 rounded-xl border border-amber-100 text-sm font-bold">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm font-bold">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.sap-settings.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Section 1: Kredensial SAP -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    1. Kredensial SAP
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="sapUser" class="block text-sm font-bold text-slate-700">Username SAP</label>
                        <input type="text" id="sapUser" name="sapUser" value="{{ old('sapUser', $settings['sapUser'] ?? '') }}" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                        @error('sapUser') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="sapPass" class="block text-sm font-bold text-slate-700">Password SAP</label>
                        <div class="relative">
                            <input type="password" id="sapPass" name="sapPass" placeholder="Kosongkan jika tidak ingin mengubah password"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 pr-12">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors" title="Tampilkan/Sembunyikan">
                                <svg id="eyeIcon" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span id="eyeText" class="hidden sm:inline">Tampilkan</span>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Kosongkan jika password SAP tidak ingin diubah.</p>
                        @error('sapPass') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Konfigurasi Export -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
                    2. Konfigurasi Export
                </h2>
                <div class="space-y-2">
                    <label for="exportFolder" class="block text-sm font-bold text-slate-700">Folder Export</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="exportFolder" name="exportFolder" value="{{ old('exportFolder', $settings['exportFolder'] ?? 'D:\Sap_export') }}" required
                            class="w-full pl-4 pr-3 py-3 bg-white border border-slate-300 text-slate-900 text-sm rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm">
                        <button type="button" onclick="openFolderModal()" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-2xl transition-colors shrink-0 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Pilih Folder
                        </button>
                        <button type="button" onclick="testFolder()" class="px-5 py-3 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold rounded-2xl transition-colors shrink-0 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Test Folder
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        💡 <b>Tips Hosting:</b> Jika Anda menggunakan server Cloud/Hosting, tombol Pilih Folder tidak dapat menelusuri komputer lokal Anda. <b>Ketikkan jalurnya secara manual</b> di kolom atas (contoh: <code class="bg-slate-100 text-pink-600 px-1 py-0.5 rounded">D:\Sap_export</code>).
                    </p>
                    <p class="text-xs text-slate-500">Folder di komputer/server tempat bot menyimpan file CSV hasil tarikan SAP.</p>
                    @error('exportFolder') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                    <p id="folderStatus" class="text-xs font-bold mt-2 hidden px-3 py-2 rounded-xl"></p>
                </div>
            </div>

            <!-- Section 3: Jadwal Otomatisasi -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    3. Jadwal Otomatisasi
                </h2>
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-slate-700">Jam Ambil Data Otomatis</label>
                    <div class="flex gap-3 items-center">
                        <select name="hour" id="hourSelect" class="w-24 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10" onchange="updatePreview()">
                            <option value="">Jam</option>
                            @for($i = 1; $i <= 24; $i++)
                                <option value="{{ $i }}" {{ old('hour', $hour ?? '') == $i ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                        <span class="font-black text-slate-400 text-lg">:</span>
                        <select name="minute" id="minuteSelect" class="w-24 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10" onchange="updatePreview()">
                            <option value="">Menit</option>
                            @for($i = 0; $i <= 59; $i++)
                                <option value="{{ $i }}" {{ (old('minute', $minute ?? '') !== '' && old('minute', $minute ?? '') == $i) ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                        <span class="text-sm font-bold text-slate-600 bg-slate-100 px-3 py-2 rounded-xl">WIB</span>
                    </div>
                    @error('hour') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                    @error('minute') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                    <div id="schedulePreview" class="text-sm text-indigo-700 font-semibold mt-3 bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span id="previewText">Silakan pilih jam dan menit.</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>

        <!-- Modal Pilih Folder -->
        <div id="folderModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[85vh] animate-in fade-in zoom-in-95 duration-200">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Pilih Folder Server
                    </h3>
                    <button type="button" onclick="closeFolderModal()" class="text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-4 flex gap-2 border-b border-slate-100 bg-white">
                    <input type="text" id="modalCurrentPath" readonly class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-700 focus:outline-none">
                    <button type="button" id="btnUp" onclick="goUp()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        Naik
                    </button>
                </div>
                <div class="p-2 overflow-y-auto flex-1 bg-slate-50/50" id="folderList">
                    <!-- Loading state -->
                    <div class="p-8 text-center text-slate-500">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memuat folder...
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100 bg-white flex justify-end gap-3">
                    <button type="button" onclick="closeFolderModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="button" onclick="selectCurrentFolder()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-colors">Pilih Folder Ini</button>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script>
        function togglePassword() {
            const input = document.getElementById('sapPass');
            const path = document.getElementById('eyePath');
            const text = document.getElementById('eyeText');
            if (input.type === 'password') {
                input.type = 'text';
                text.innerText = 'Sembunyikan';
                path.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21');
            } else {
                input.type = 'password';
                text.innerText = 'Tampilkan';
                path.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
            }
        }

        function updatePreview() {
            const h = document.getElementById('hourSelect').value;
            const m = document.getElementById('minuteSelect').value;
            const preview = document.getElementById('previewText');
            if (h && m !== "") {
                const hourStr = h.toString().padStart(2, '0');
                const minStr = m.toString().padStart(2, '0');
                preview.innerText = `Bot akan mengambil data otomatis setiap hari pukul ${hourStr}:${minStr} WIB.`;
                preview.parentElement.classList.remove('bg-amber-50', 'text-amber-700', 'border-amber-100');
                preview.parentElement.classList.add('bg-indigo-50', 'text-indigo-700', 'border-indigo-100');
            } else {
                preview.innerText = 'Silakan pilih jam dan menit agar jadwal aktif.';
                preview.parentElement.classList.remove('bg-indigo-50', 'text-indigo-700', 'border-indigo-100');
                preview.parentElement.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-100');
            }
        }

        // Initialize preview
        document.addEventListener('DOMContentLoaded', updatePreview);

        // --- Folder Picker Logic ---
        let modalParentPath = null;
        let currentLoadedPath = '';

        function openFolderModal() {
            let initialPath = document.getElementById('exportFolder').value || 'DRIVES';
            document.getElementById('folderModal').classList.remove('hidden');
            loadFolder(initialPath);
        }

        function closeFolderModal() {
            document.getElementById('folderModal').classList.add('hidden');
        }

        function loadFolder(path) {
            const listEl = document.getElementById('folderList');
            listEl.innerHTML = `<div class="p-8 text-center text-slate-500"><svg class="animate-spin h-8 w-8 mx-auto mb-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memuat folder...</div>`;
            
            let url = `{{ route('admin.sap-settings.browse', [], false) }}?path=${encodeURIComponent(path)}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        listEl.innerHTML = `<div class="p-6 text-center text-red-500 font-bold bg-red-50 rounded-xl m-2 border border-red-100">${data.error}</div>`;
                        // Coba load DRIVES sebagai fallback jika error
                        if (path !== 'DRIVES') {
                            setTimeout(() => loadFolder('DRIVES'), 1500);
                        }
                        return;
                    }
                    
                    document.getElementById('modalCurrentPath').value = data.current_path;
                    currentLoadedPath = data.current_path;
                    modalParentPath = data.parent_path;
                    
                    document.getElementById('btnUp').disabled = !modalParentPath;

                    if (data.directories.length === 0) {
                        listEl.innerHTML = `<div class="p-6 text-center text-slate-400 italic bg-white rounded-xl m-2 border border-slate-100">Tidak ada sub-folder.</div>`;
                        return;
                    }

                    let html = '';
                    data.directories.forEach(dir => {
                        // Double backslash escaping for inline onclick
                        const safePath = dir.path.replace(/\\/g, '\\\\');
                        html += `
                        <button type="button" onclick="loadFolder('${safePath}')" class="w-full text-left px-4 py-3 hover:bg-white hover:shadow-sm rounded-xl flex items-center gap-3 group transition-all border border-transparent hover:border-slate-200">
                            <svg class="w-6 h-6 text-amber-400 group-hover:text-amber-500 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">${dir.name}</span>
                        </button>`;
                    });
                    listEl.innerHTML = html;
                })
                .catch(err => {
                    listEl.innerHTML = `<div class="p-6 text-center text-red-500">Terjadi kesalahan koneksi.</div>`;
                });
        }

        function goUp() {
            if (modalParentPath) loadFolder(modalParentPath);
        }

        function selectCurrentFolder() {
            document.getElementById('exportFolder').value = currentLoadedPath;
            closeFolderModal();
            testFolder(); // Auto test setelah pilih
        }

        function testFolder() {
            const folder = document.getElementById('exportFolder').value;
            const statusEl = document.getElementById('folderStatus');
            
            if (!folder) {
                statusEl.innerText = 'Folder belum dipilih.';
                statusEl.className = 'text-xs font-bold mt-2 block px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-100';
                return;
            }

            statusEl.innerText = 'Mengetes folder...';
            statusEl.className = 'text-xs font-bold mt-2 block px-3 py-2 rounded-xl bg-slate-50 text-slate-500 border border-slate-200 animate-pulse';

            // Gunakan endpoint browse untuk tes keberadaan
            fetch(`{{ route('admin.sap-settings.browse', [], false) }}?path=${encodeURIComponent(folder)}`)
                .then(res => {
                    if (res.ok) {
                        statusEl.innerHTML = '<span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Folder terdeteksi di server ini (Valid).</span>';
                        statusEl.className = 'text-xs font-bold mt-2 block px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100';
                    } else {
                        statusEl.innerHTML = '<span class="flex items-center gap-1"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Server tidak dapat mendeteksi folder ini. Jika Anda yakin ini adalah folder di PC Lokal Anda, silakan abaikan pesan ini dan tekan Simpan.</span>';
                        statusEl.className = 'text-xs font-bold mt-2 block px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-100';
                    }
                }).catch(() => {
                    statusEl.innerText = 'Gagal mengecek folder.';
                    statusEl.className = 'text-xs font-bold mt-2 block px-3 py-2 rounded-xl bg-red-50 text-red-700 border border-red-100';
                });
        }
        </script>
    </div>
</x-app-layout>
