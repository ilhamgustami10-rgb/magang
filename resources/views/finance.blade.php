<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Modern Map Styling */
        #map-jt { 
            height: 500px; 
            width: 100%; 
            border-radius: 2.5rem; 
            z-index: 10; 
            filter: grayscale(0.2) contrast(1.1);
            border: 8px solid white;
        }

        /* Custom Popup Styling */
        .leaflet-popup-content-wrapper {
            border-radius: 1.2rem;
            padding: 4px;
            box-shadow: 0 15px 20px -5px rgba(0,0,0,0.1);
        }

        /* Custom Scrollbar for Sidebar List */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        /* Org Chart Line Effect */
        .org-line { position: relative; }
        .org-line::after {
            content: '';
            position: absolute;
            top: -24px;
            left: 50%;
            width: 2px;
            height: 24px;
            background: #e2e8f0;
        }
    </style>

    <div class="p-6 lg:p-10 bg-[#F8FAFC] min-h-screen font-jakarta">
        
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-1">
                <span class="h-2 w-2 bg-indigo-600 rounded-full"></span>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">
                    Operational Network & Human Capital
                </p>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                Wilayah Operasional <span class="text-indigo-600">Jawa Timur</span>
            </h2>
            <div class="mt-3 h-1.5 w-20 bg-gradient-to-r from-indigo-600 to-sky-400 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16">
            
            <div class="lg:col-span-8 relative">
                <div id="map-jt" class="shadow-2xl shadow-slate-200/60"></div>
                
                <div class="absolute bottom-6 left-6 z-[100] bg-white/80 backdrop-blur-md p-5 rounded-[2rem] border border-white/50 shadow-xl max-w-[200px]">
                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Legenda Unit</h5>
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 bg-indigo-600 rounded-full ring-4 ring-indigo-50"></span>
                            <span class="text-[11px] font-bold text-slate-700">Kantor Utama</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 bg-sky-500 rounded-full ring-4 ring-sky-50"></span>
                            <span class="text-[11px] font-bold text-slate-700">Cabang Pembantu</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 bg-emerald-500 rounded-full ring-4 ring-emerald-50"></span>
                            <span class="text-[11px] font-bold text-slate-700">Unit Layanan</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 flex flex-col">
                <div class="bg-white rounded-[2.5rem] p-7 shadow-xl shadow-slate-200/40 border border-slate-100 flex-grow h-[500px] flex flex-col">
                    <div class="flex justify-between items-center mb-6 px-1">
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Status SDM</h4>
                            <p class="text-[10px] font-bold text-indigo-600 mt-1 uppercase">Live Monitoring</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-slate-900 leading-none">1,248</span>
                            <p class="text-[9px] font-bold text-slate-400 uppercase leading-none mt-1">Total Personel</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 overflow-y-auto pr-2 custom-scrollbar flex-grow">
                        
                        <div class="group p-4 bg-slate-50 hover:bg-indigo-600 rounded-[1.8rem] transition-all duration-300 cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 group-hover:bg-white animate-pulse"></div>
                                    <div>
                                        <span class="text-sm font-black text-slate-800 group-hover:text-white transition-colors">Surabaya</span>
                                        <p class="text-[10px] font-bold text-indigo-500 group-hover:text-indigo-200 uppercase">Kantor Pusat</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-slate-800 group-hover:text-white">540</span>
                                </div>
                            </div>
                        </div>

                        <div class="group p-4 bg-white border border-slate-100 hover:border-sky-200 rounded-[1.8rem] transition-all shadow-sm hover:shadow-md cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500"></div>
                                    <div>
                                        <span class="text-sm font-black text-slate-700">Malang</span>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Capem</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-slate-800">210</span>
                                </div>
                            </div>
                        </div>

                        <div class="group p-4 bg-rose-50 border border-rose-100 rounded-[1.8rem] transition-all cursor-pointer">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></div>
                                    <div>
                                        <span class="text-sm font-black text-slate-700">Bawean</span>
                                        <p class="text-[10px] font-bold text-rose-500 uppercase italic underline">Understaffed</p>
                                    </div>
                                </div>
                                <div class="text-right text-rose-600">
                                    <span class="text-base font-black">12</span>
                                </div>
                            </div>
                        </div>

                        </div>
                </div>
            </div>
        </div>

        <div class="pt-10">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Kepemimpinan Wilayah</h2>
                <p class="text-slate-400 text-sm font-medium mt-1">Struktur Koordinasi Manajemen Jawa Timur</p>
            </div>

            <div class="flex flex-col items-center">
                <div class="mb-16 relative">
                    <div class="bg-indigo-600 p-6 rounded-[2.2rem] shadow-2xl shadow-indigo-200 flex items-center gap-5 w-[320px] transform hover:-translate-y-1 transition-all">
                        <div class="h-16 w-16 bg-white/20 rounded-2xl flex-shrink-0 flex items-center justify-center text-white text-2xl font-black backdrop-blur-sm">RM</div>
                        <div>
                            <h5 class="font-black text-white text-lg leading-tight">Budi Santoso</h5>
                            <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest mt-1">Regional Manager</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative w-full max-w-5xl">
                    <div class="org-line flex flex-col items-center">
                        <div class="bg-white p-5 rounded-[1.8rem] shadow-lg border border-slate-50 flex items-center gap-4 w-[260px] hover:shadow-indigo-100 transition-all">
                            <div class="h-11 w-11 bg-slate-100 rounded-xl flex items-center justify-center text-indigo-600 font-black text-xs">BM</div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-sm">Andi Wijaya</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase">BM Surabaya</p>
                            </div>
                        </div>
                    </div>

                    <div class="org-line flex flex-col items-center">
                        <div class="bg-white p-5 rounded-[1.8rem] shadow-lg border border-slate-50 flex items-center gap-4 w-[260px] hover:shadow-indigo-100 transition-all">
                            <div class="h-11 w-11 bg-slate-100 rounded-xl flex items-center justify-center text-indigo-600 font-black text-xs">PM</div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-sm">Siti Aminah</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase">Pincapem Malang</p>
                            </div>
                        </div>
                    </div>

                    <div class="org-line flex flex-col items-center">
                        <div class="bg-white p-5 rounded-[1.8rem] shadow-lg border border-slate-50 flex items-center gap-4 w-[260px] hover:shadow-indigo-100 transition-all">
                            <div class="h-11 w-11 bg-slate-100 rounded-xl flex items-center justify-center text-indigo-600 font-black text-xs">UM</div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-sm">Rizky Pratama</h5>
                                <p class="text-[9px] font-black text-slate-400 uppercase">Ka. Unit Jember</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mapOptions = {
                zoomControl: true, 
                scrollWheelZoom: false,
                dragging: true 
            };

            var map = L.map('map-jt', mapOptions).setView([-7.6, 112.7], 8); 

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: ''
            }).addTo(map);

            var branchData = [
                { name: "Surabaya", type: "Kantor Pusat", coords: [-7.2575, 112.7521], color: "#4F46E5", staff: 540 },
                { name: "Malang", type: "Capem", coords: [-7.9666, 112.6326], color: "#0EA5E9", staff: 210 },
                { name: "Banyuwangi", type: "Capem", coords: [-8.2192, 114.3691], color: "#0EA5E9", staff: 85 },
                { name: "Sumenep", type: "Capem", coords: [-7.0084, 113.8621], color: "#0EA5E9", staff: 64 },
                { name: "Kediri", type: "Unit", coords: [-7.8480, 112.0178], color: "#10B981", staff: 42 },
                { name: "Jember", type: "Unit", coords: [-8.1724, 113.7007], color: "#10B981", staff: 38 },
                { name: "Bawean", type: "Unit", coords: [-5.8425, 112.6713], color: "#F43F5E", staff: 12 },
                { name: "Blora", type: "Unit", coords: [-6.9697, 111.4166], color: "#10B981", staff: 22 }
            ];

            branchData.forEach(function(loc) {
                var marker = L.circleMarker(loc.coords, {
                    radius: 10,
                    fillColor: loc.color,
                    color: "#fff",
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);

                marker.bindPopup(`
                    <div class="p-1 font-jakarta">
                        <p class="text-[9px] font-black text-indigo-600 uppercase">${loc.type}</p>
                        <h4 class="text-sm font-black text-slate-800 leading-tight">${loc.name}</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Total SDM: <span class="font-bold text-slate-700">${loc.staff}</span></p>
                    </div>
                `);
            });
        });
    </script>
</x-app-layout>