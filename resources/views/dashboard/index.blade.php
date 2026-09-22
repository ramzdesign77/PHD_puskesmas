@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data kesehatan lingkungan hari ini')

@section('content')
<div class="space-y-6 fade-in">
 <!-- CONTENT WRAPPER -->
      <main class="p-5 flex flex-col gap-4 flex-1">
        <!-- TOOLBAR & FILTER STRIP -->
        <section
          class="bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-wrap items-center justify-between gap-3"
        >
          <!-- Filter Dropdowns -->
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Wilayah -->
            <div class="relative">
              <div
                class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[12px] font-medium text-slate-700"
              >
                <span
                  class="material-symbols-outlined text-[16px] text-secondary"
                  >location_on</span
                >
                <select
                  class="bg-transparent border-none outline-none font-medium cursor-pointer pr-4 text-[12px] focus:ring-0 p-0 text-slate-800"
                >
                  <option>Kecamatan Sumbersari (7 Kelurahan)</option>
                  <option>Kel. Sumbersari (6 Titik Kritis)</option>
                  <option>Kel. Kebonsari</option>
                  <option>Kel. Tegalgede</option>
                  <option>Kel. Kranjingan</option>
                  <option>Kel. Antirogo</option>
                  <option>Kel. Wirolegi</option>
                  <option>Kel. Karangrejo</option>
                </select>
              </div>
            </div>
            <!-- Tingkat Risiko -->
            <div class="relative">
              <div
                class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[12px] font-medium text-slate-700"
              >
                <span
                  class="material-symbols-outlined text-[16px] text-slate-400"
                  >filter_list</span
                >
                <select
                  class="bg-transparent border-none outline-none font-medium cursor-pointer pr-4 text-[12px] focus:ring-0 p-0 text-slate-800"
                >
                  <option>Semua Tingkat Risiko</option>
                  <option>🔴 Risiko Tinggi (TMS)</option>
                  <option>🟡 Risiko Sedang (Waspada)</option>
                  <option>🔵 Aman (Laik Minum)</option>
                </select>
              </div>
            </div>
            <!-- Periode Waktu -->
            <div class="relative">
              <div
                class="flex items-center gap-1.5 pl-3 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[12px] font-medium text-slate-700"
              >
                <span
                  class="material-symbols-outlined text-[16px] text-slate-400"
                  >calendar_today</span
                >
                <select
                  class="bg-transparent border-none outline-none font-medium cursor-pointer pr-4 text-[12px] focus:ring-0 p-0 text-slate-800"
                >
                  <option>Oktober 2026 (Real-time)</option>
                  <option>September 2026</option>
                  <option>Agustus 2026</option>
                  <option>Triwulan III 2026</option>
                </select>
              </div>
            </div>
          </div>
          <!-- Right Side of Toolbar: Layer Toggles & Refresh -->
          <div class="flex items-center gap-2">
            <!-- View Modes -->
            <div
              class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200/80 text-[11px] font-semibold text-slate-600"
            >
              <button
                class="px-2.5 py-1 rounded bg-white text-primary shadow-xs font-bold"
                type="button"
              >
                Vektor GIS
              </button>
              <button
                class="px-2.5 py-1 rounded hover:text-slate-900 transition-colors"
                type="button"
              >
                Satelit
              </button>
              <button
                class="px-2.5 py-1 rounded hover:text-slate-900 transition-colors"
                type="button"
              >
                Heatmap E. Coli
              </button>
            </div>
            <!-- Refresh Button -->
            <button
              class="h-8 px-3 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[12px] font-medium transition-colors flex items-center gap-1.5 shadow-xs"
              id="btn-refresh-map"
              type="button"
            >
              <span
                class="material-symbols-outlined text-[16px]"
                id="refresh-icon"
                >sync</span
              >
              <span>Segarkan</span>
            </button>
          </div>
        </section>
        <!-- MAIN 70 / 30 GIS SPLIT GRID --><div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
          <!-- ==================== LEFT: 70% GIS INTERACTIVE MAP CANVAS (8 cols) ==================== -->
          <div class="lg:col-span-8 flex flex-col gap-3">
            <!-- MAP CONTAINER -->
            <div
              class="relative w-full h-[740px] rounded-xl overflow-hidden border border-slate-200 bg-[#f1f5f9] shadow-sm select-none"
            >
              <!-- SVG Map Vector Canvas -->
              <div class="absolute inset-0 z-0 bg-[#eaf2f8] overflow-hidden">
                <svg
                  class="w-full h-full"
                  preserveaspectratio="xMidYMid slice"
                  viewbox="0 0 1000 740"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <defs>
                    <!-- Base GIS Grid -->
                    <pattern
                      height="36"
                      id="gisPattern"
                      patternunits="userSpaceOnUse"
                      width="36"
                    >
                      <path
                        d="M 36 0 L 0 0 0 36"
                        fill="none"
                        stroke="#d5e3ee"
                        stroke-width="0.75"
                      ></path>
                    </pattern>
                    <!-- Hatch Patterns for Risk Zones -->
                    <pattern
                      height="8"
                      id="hatchRed"
                      patterntransform="rotate(45 0 0)"
                      patternunits="userSpaceOnUse"
                      width="8"
                    >
                      <line
                        opacity="0.3"
                        stroke="#ef4444"
                        stroke-width="1.2"
                        x1="0"
                        x2="0"
                        y1="0"
                        y2="8"
                      ></line>
                    </pattern>
                    <pattern
                      height="8"
                      id="hatchAmber"
                      patterntransform="rotate(45 0 0)"
                      patternunits="userSpaceOnUse"
                      width="8"
                    >
                      <line
                        opacity="0.25"
                        stroke="#f59e0b"
                        stroke-width="1.2"
                        x1="0"
                        x2="0"
                        y1="0"
                        y2="8"
                      ></line>
                    </pattern>
                    <!-- Filter Shadows -->
                    <filter
                      height="140%"
                      id="shadowMarker"
                      width="140%"
                      x="-20%"
                      y="-20%"
                    >
                      <fedropshadow
                        dx="0"
                        dy="2"
                        flood-color="#0f172a"
                        flood-opacity="0.2"
                        stddeviation="3"
                      ></fedropshadow>
                    </filter>
                    <filter
                      height="160%"
                      id="glowActiveRed"
                      width="160%"
                      x="-30%"
                      y="-30%"
                    >
                      <fedropshadow
                        dx="0"
                        dy="2"
                        flood-color="#ef4444"
                        flood-opacity="0.4"
                        stddeviation="5"
                      ></fedropshadow>
                    </filter>
                  </defs>
                  <!-- 1. Grid Background -->
                  <rect
                    fill="url(#gisPattern)"
                    height="740"
                    width="1000"
                  ></rect>
                  <!-- 2. Contour Lines -->
                  <path
                    d="M -50 160 Q 250 120 500 220 T 1050 180"
                    fill="none"
                    stroke="#d4e2ed"
                    stroke-dasharray="3,3"
                    stroke-width="1"
                  ></path>
                  <path
                    d="M -50 380 Q 280 340 580 430 T 1050 400"
                    fill="none"
                    stroke="#d4e2ed"
                    stroke-dasharray="3,3"
                    stroke-width="1"
                  ></path>
                  <path
                    d="M 120 740 Q 420 560 740 640 T 1050 580"
                    fill="none"
                    stroke="#d4e2ed"
                    stroke-dasharray="3,3"
                    stroke-width="1"
                  ></path>
                  <!-- 3. Major Water Body: Sungai Bedadung -->
                  <!-- River Area polygon -->
                  <path
                    d="M -20 220 C 180 240 260 350 380 340 C 510 330 560 450 640 520 C 720 590 850 610 1020 560 L 1020 595 C 840 645 700 625 620 545 C 540 475 490 365 370 375 C 240 385 160 275 -20 250 Z"
                    fill="#0284c7"
                    fill-opacity="0.14"
                  ></path>
                  <!-- River Center Line -->
                  <path
                    d="M -20 235 C 180 255 260 360 380 355 C 500 345 550 460 630 530 C 710 605 845 620 1020 575"
                    fill="none"
                    opacity="0.75"
                    stroke="#0284c7"
                    stroke-linecap="round"
                    stroke-width="4.5"
                  ></path>
                  <!-- River Label -->
                  <text
                    fill="#0369a1"
                    font-family="Poppins"
                    font-size="10"
                    font-weight="700"
                    letter-spacing="1.5"
                    opacity="0.8"
                    transform="rotate(-6 710 580)"
                    x="710"
                    y="580"
                  >
                    DAS SUNGAI BEDADUNG (SEKTOR SUMBERSARI)
                  </text>
                  <!-- Secondary irrigation branch -->
                  <path
                    d="M 380 355 Q 440 240 515 170 Q 570 120 620 20"
                    fill="none"
                    opacity="0.65"
                    stroke="#38bdf8"
                    stroke-dasharray="4,2"
                    stroke-width="1.8"
                  ></path>
                  <path
                    d="M 630 530 Q 520 630 480 740"
                    fill="none"
                    opacity="0.65"
                    stroke="#38bdf8"
                    stroke-dasharray="4,2"
                    stroke-width="1.8"
                  ></path>
                  <!-- 4. Road Networks -->
                  <!-- Arterial roads -->
                  <path
                    d="M 0 390 L 1000 350"
                    fill="none"
                    opacity="0.8"
                    stroke="#cbd5e1"
                    stroke-width="5"
                  ></path>
                  <path
                    d="M 0 390 L 1000 350"
                    fill="none"
                    stroke="#ffffff"
                    stroke-width="2.5"
                  ></path>
                  <path
                    d="M 520 0 L 460 740"
                    fill="none"
                    opacity="0.8"
                    stroke="#cbd5e1"
                    stroke-width="4.5"
                  ></path>
                  <path
                    d="M 520 0 L 460 740"
                    fill="none"
                    stroke="#ffffff"
                    stroke-width="2"
                  ></path>
                  <!-- Collector roads -->
                  <path
                    d="M 120 40 Q 220 180 310 370 Q 380 520 420 740"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="2.5"
                  ></path>
                  <path
                    d="M 280 370 Q 520 440 820 350 Q 920 320 1000 240"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="2.5"
                  ></path>
                  <!-- 5. Village Boundary Polygons -->
                  <!-- Zone 1: Kel. Sumbersari (High Risk - Crimson Red 15%) -->
                  <g
                    class="cursor-pointer transition-opacity hover:opacity-90"
                    id="poly-sumbersari"
                  >
                    <polygon
                      fill="#ef4444"
                      fill-opacity="0.14"
                      points="190,170 420,160 495,300 430,500 250,520 170,380"
                      stroke="#ef4444"
                      stroke-dasharray="6,4"
                      stroke-width="2"
                    ></polygon>
                    <polygon
                      fill="url(#hatchRed)"
                      points="190,170 420,160 495,300 430,500 250,520 170,380"
                    ></polygon>
                    <!-- Boundary Label Pill -->
                    <rect
                      fill="#ffffff"
                      filter="url(#shadowMarker)"
                      height="22"
                      rx="4"
                      width="165"
                      x="210"
                      y="210"
                    ></rect>
                    <circle cx="222" cy="221" fill="#ef4444" r="3.5"></circle>
                    <text
                      fill="#ba1a1a"
                      font-family="Poppins"
                      font-size="10"
                      font-weight="700"
                      x="232"
                      y="225"
                    >
                      KEL. SUMBERSARI (KRITIS)
                    </text>
                  </g>
                  <!-- Zone 2: Kel. Kebonsari (Moderate Risk - Amber 15%) -->
                  <g
                    class="cursor-pointer transition-opacity hover:opacity-90"
                    id="poly-kebonsari"
                  >
                    <polygon
                      fill="#f59e0b"
                      fill-opacity="0.12"
                      points="440,150 740,120 810,310 610,430 470,340 480,230"
                      stroke="#f59e0b"
                      stroke-dasharray="6,4"
                      stroke-width="1.8"
                    ></polygon>
                    <polygon
                      fill="url(#hatchAmber)"
                      points="440,150 740,120 810,310 610,430 470,340 480,230"
                    ></polygon>
                    <!-- Boundary Label Pill -->
                    <rect
                      fill="#ffffff"
                      filter="url(#shadowMarker)"
                      height="22"
                      rx="4"
                      width="145"
                      x="570"
                      y="180"
                    ></rect>
                    <circle cx="582" cy="191" fill="#f59e0b" r="3.5"></circle>
                    <text
                      fill="#b45309"
                      font-family="Poppins"
                      font-size="10"
                      font-weight="700"
                      x="592"
                      y="195"
                    >
                      KEL. KEBONSARI (SEDANG)
                    </text>
                  </g>
                  <!-- Zone 3: Kel. Tegalgede (Low Risk / Aman - Royal Blue 15%) -->
                  <g
                    class="cursor-pointer transition-opacity hover:opacity-90"
                    id="poly-tegalgede"
                  >
                    <polygon
                      fill="#2563eb"
                      fill-opacity="0.10"
                      points="150,410 370,420 440,670 200,710 100,570"
                      stroke="#2563eb"
                      stroke-dasharray="6,4"
                      stroke-width="1.8"
                    ></polygon>
                    <!-- Boundary Label Pill -->
                    <rect
                      fill="#ffffff"
                      filter="url(#shadowMarker)"
                      height="22"
                      rx="4"
                      width="155"
                      x="170"
                      y="590"
                    ></rect>
                    <circle cx="182" cy="601" fill="#2563eb" r="3.5"></circle>
                    <text
                      fill="#1e3a8a"
                      font-family="Poppins"
                      font-size="10"
                      font-weight="700"
                      x="192"
                      y="605"
                    >
                      KEL. TEGALGEDE (AMAN)
                    </text>
                  </g>
                  <!-- Zone 4: Kel. Kranjingan, Antirogo, Wirolegi Subtle Boundaries -->
                  <polygon
                    fill="#64748b"
                    fill-opacity="0.05"
                    points="450,440 680,440 780,680 460,700"
                    stroke="#94a3b8"
                    stroke-dasharray="4,4"
                    stroke-width="1.2"
                  ></polygon>
                  <text
                    fill="#64748b"
                    font-family="Poppins"
                    font-size="9.5"
                    font-weight="600"
                    x="530"
                    y="620"
                  >
                    KEL. KRANJINGAN
                  </text>
                  <polygon
                    fill="#64748b"
                    fill-opacity="0.05"
                    points="750,130 980,110 970,360 820,320"
                    stroke="#94a3b8"
                    stroke-dasharray="4,4"
                    stroke-width="1.2"
                  ></polygon>
                  <text
                    fill="#64748b"
                    font-family="Poppins"
                    font-size="9.5"
                    font-weight="600"
                    x="840"
                    y="220"
                  >
                    KEL. ANTIROGO
                  </text>
                  <!-- 6. Scattered Pin Markers (Zero Green Policy) -->
                  <!-- Royal Blue Pins (Aman) -->
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(230, 640)"
                  >
                    <circle cx="0" cy="0" fill="#2563eb" r="5.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(320, 610)"
                  >
                    <circle cx="0" cy="0" fill="#2563eb" r="5.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(280, 500)"
                  >
                    <circle cx="0" cy="0" fill="#2563eb" r="5.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(710, 230)"
                  >
                    <circle cx="0" cy="0" fill="#2563eb" r="5.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(580, 650)"
                  >
                    <circle cx="0" cy="0" fill="#2563eb" r="5.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <!-- Amber Gold Pins (Sedang) -->
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(560, 250)"
                  >
                    <circle cx="0" cy="0" fill="#f59e0b" r="6"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(680, 300)"
                  >
                    <circle cx="0" cy="0" fill="#f59e0b" r="6"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(510, 380)"
                  >
                    <circle cx="0" cy="0" fill="#f59e0b" r="6"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(760, 410)"
                  >
                    <circle cx="0" cy="0" fill="#f59e0b" r="6"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2"></circle>
                  </g>
                  <!-- Red Pins (Kritis) -->
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(240, 330)"
                  >
                    <circle cx="0" cy="0" fill="#ef4444" r="6.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2.5"></circle>
                  </g>
                  <g
                    filter="url(#shadowMarker)"
                    transform="translate(380, 400)"
                  >
                    <circle cx="0" cy="0" fill="#ef4444" r="6.5"></circle>
                    <circle cx="0" cy="0" fill="#ffffff" r="2.5"></circle>
                  </g>
                  <!-- FOCAL HIGHLIGHTED PIN (Gumuk Kerang / RT 02 RW 05 Sumbersari) -->
                  <g transform="translate(315, 330)">
                    <!-- Pulsing Radar Animation -->
                    <circle cx="0" cy="0" fill="#ef4444" opacity="0.2" r="22">
                      <animate
                        attributename="r"
                        dur="2.2s"
                        repeatcount="indefinite"
                        values="8;28;8"
                      ></animate>
                      <animate
                        attributename="opacity"
                        dur="2.2s"
                        repeatcount="indefinite"
                        values="0.45;0;0.45"
                      ></animate>
                    </circle>
                    <circle
                      cx="0"
                      cy="0"
                      fill="#ef4444"
                      opacity="0.3"
                      r="14"
                    ></circle>
                    <!-- Active Pin Shape -->
                    <path
                      d="M 0 0 C -6 -6 -8 -15 0 -22 C 8 -15 6 -6 0 0 Z"
                      fill="#ef4444"
                      filter="url(#glowActiveRed)"
                    ></path>
                    <circle cx="0" cy="-14" fill="#ffffff" r="3.5"></circle>
                  </g>
                  <!-- Compass Rose / North Indicator -->
                  <g transform="translate(950, 45)">
                    <circle
                      cx="0"
                      cy="0"
                      fill="#ffffff"
                      filter="url(#shadowMarker)"
                      opacity="0.95"
                      r="18"
                    ></circle>
                    <polygon
                      fill="#00236f"
                      points="0,-13 4,0 0,-3 -4,0"
                    ></polygon>
                    <polygon
                      fill="#cbd5e1"
                      points="0,13 4,0 0,3 -4,0"
                    ></polygon>
                    <text
                      fill="#00236f"
                      font-family="Poppins"
                      font-size="9"
                      font-weight="800"
                      x="-3.5"
                      y="-15"
                    >
                      U
                    </text>
                  </g>
                </svg>
              </div>
              <!-- FLOATING LEAFLET-STYLE MAP CONTROLS (Top Right) -->
              <div class="absolute top-4 right-4 z-10 flex flex-col gap-2">
                <div
                  class="bg-white/95 backdrop-blur-md rounded-lg shadow-sm border border-slate-200 overflow-hidden flex flex-col"
                >
                  <button
                    class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 transition-colors"
                    title="Perbesar Peta"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >add</span
                    >
                  </button>
                  <div class="h-px w-full bg-slate-200"></div>
                  <button
                    class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 transition-colors"
                    title="Perkecil Peta"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >remove</span
                    >
                  </button>
                </div>
                <div
                  class="bg-white/95 backdrop-blur-md rounded-lg shadow-sm border border-slate-200 overflow-hidden flex flex-col"
                >
                  <button
                    class="w-8 h-8 flex items-center justify-center text-primary hover:bg-slate-100 transition-colors"
                    title="Lapisan Tematik"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >layers</span
                    >
                  </button>
                  <div class="h-px w-full bg-slate-200"></div>
                  <button
                    class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 transition-colors"
                    title="Pusatkan Wilayah"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >my_location</span
                    >
                  </button>
                  <div class="h-px w-full bg-slate-200"></div>
                  <button
                    class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-slate-100 transition-colors"
                    title="Layar Penuh"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >fullscreen</span
                    >
                  </button>
                </div>
              </div>
              <!-- FLOATING ACTIVE PIN DETAIL POPUP MODAL (Compact & Anchored Gracefully) -->
              <div
                class="absolute z-20 top-20 left-1/4 sm:left-[28%] w-80 max-w-[90%] bg-white/95 backdrop-blur-md rounded-xl shadow-lg border border-slate-200 p-4 transition-all"
                id="gis-popup-modal"
              >
                <div
                  class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5"
                >
                  <div>
                    <div class="flex items-center gap-1.5 mb-1">
                      <span
                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 tracking-wide"
                        >🔴 Kritis • Klorinasi Segera</span
                      >
                    </div>
                    <h4
                      class="font-bold text-[13px] text-slate-900 leading-tight"
                    >
                      Kel. Sumbersari • RT 02 / RW 05
                    </h4>
                    <p class="text-[11px] text-secondary font-medium mt-0.5">
                      Lingkungan Gumuk Kerang (DAS Bedadung)
                    </p>
                  </div>
                  <button
                    class="text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors"
                    onclick="
                      document
                        .getElementById('gis-popup-modal')
                        .classList.toggle('hidden')
                    "
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[16px]"
                      >close</span
                    >
                  </button>
                </div>
                <!-- Metrics inside Popup -->
                <div class="py-2.5 space-y-2">
                  <div
                    class="flex items-center justify-between text-[11px] bg-red-50/70 p-2 rounded-lg border border-red-100 text-red-800"
                  >
                    <span class="flex items-center gap-1 font-medium">
                      <span
                        class="material-symbols-outlined text-[15px] text-red-600"
                        >report_problem</span
                      >
                      14 Keluhan Warga
                    </span>
                    <span class="font-bold">Air Keruh &amp; Berbau</span>
                  </div>
                  <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <div
                      class="bg-slate-50 p-2 rounded-lg border border-slate-100"
                    >
                      <span class="text-slate-500 text-[10px] block"
                        >Skor IKL Sanitasi</span
                      >
                      <span class="font-bold text-red-600 text-[12px]"
                        >4 / 5 (Tinggi)</span
                      >
                    </div>
                    <div
                      class="bg-slate-50 p-2 rounded-lg border border-slate-100"
                    >
                      <span class="text-slate-500 text-[10px] block"
                        >Bakteri E. Coli</span
                      >
                      <span class="font-bold text-red-600 text-[12px]"
                        >64 CFU / 100ml</span
                      >
                    </div>
                  </div>
                  <div
                    class="flex items-center gap-1.5 text-[10px] text-slate-500 pt-0.5"
                  >
                    <span class="material-symbols-outlined text-[13px]"
                      >person</span
                    >
                    <span
                      >Sanitarian:
                      <strong class="text-slate-700"
                        >Dimas Prasetyo, S.Tr.Kes</strong
                      ></span
                    >
                  </div>
                </div>
                <!-- Actions inside Popup -->
                <div
                  class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100"
                >
                  <button
                    class="h-8 rounded-lg bg-red-600 text-white hover:bg-red-700 text-[11px] font-semibold transition-colors flex items-center justify-center gap-1 shadow-xs"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[14px]"
                      >medication</span
                    >
                    <span>Klorinasi Cepat</span>
                  </button>
                  <button
                    class="h-8 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition-colors flex items-center justify-center gap-1"
                    type="button"
                  >
                    <span class="material-symbols-outlined text-[14px]"
                      >description</span
                    >
                    <span>Detail IKL &amp; Lab</span>
                  </button>
                </div>
              </div>
              <!-- FLOATING MINIMALIST LEGEND (Bottom Left) -->
              <div
                class="absolute bottom-4 left-4 z-10 bg-white/95 backdrop-blur-md rounded-xl p-3 border border-slate-200 shadow-sm max-w-xs text-[11px]"
              >
                <div
                  class="flex items-center justify-between font-bold text-slate-800 text-[11px] mb-2"
                >
                  <span>Legenda Risiko Kualitas Air</span>
                  <span class="text-[10px] text-slate-400 font-normal font-mono"
                    >Permenkes 2/2023</span
                  >
                </div>
                <div class="space-y-1.5">
                  <div class="flex items-center gap-2">
                    <span
                      class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"
                    ></span>
                    <span class="text-slate-700"
                      ><strong class="text-red-700"
                        >Risiko Tinggi / Kritis:</strong
                      >
                      &gt;50 CFU / 100ml (TMS)</span
                    >
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      class="w-3 h-3 rounded-full bg-amber-500 flex-shrink-0"
                    ></span>
                    <span class="text-slate-700"
                      ><strong class="text-amber-700">Risiko Sedang:</strong> 1
                      - 50 CFU (Perlu Monitor)</span
                    >
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      class="w-3 h-3 rounded-full bg-blue-600 flex-shrink-0"
                    ></span>
                    <span class="text-slate-700"
                      ><strong class="text-blue-800">Aman / Laik:</strong> 0 CFU
                      (Sesuai Baku Mutu)</span
                    >
                  </div>
                </div>
              </div>
              <!-- FLOATING REAL-TIME COORDINATES STRIP (Bottom Right) -->
              <div
                class="absolute bottom-4 right-4 z-10 hidden sm:flex items-center gap-3 px-3 py-1.5 rounded-lg bg-white/95 backdrop-blur-md border border-slate-200 text-[10px] font-mono text-slate-600 shadow-xs"
              >
                <span
                  >Lat:
                  <strong class="text-slate-900 font-semibold"
                    >-8.1725</strong
                  ></span
                >
                <span>•</span>
                <span
                  >Long:
                  <strong class="text-slate-900 font-semibold"
                    >113.7180</strong
                  ></span
                >
                <span>•</span>
                <span class="text-secondary font-semibold">Datum: WGS84</span>
                <span>•</span>
                <span class="flex items-center gap-1 text-slate-500"
                  ><span
                    class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"
                  ></span>
                  Live Sync</span
                >
              </div>
            </div>
            <!-- MAP TIMELINE BAR & QUICK METRICS -->
            <div
              class="bg-white rounded-xl border border-slate-200 p-2.5 shadow-xs flex flex-wrap items-center justify-between gap-2 text-[12px]"
            >
              <div class="flex items-center gap-2">
                <span class="font-bold text-primary flex items-center gap-1">
                  <span
                    class="material-symbols-outlined text-[18px] text-secondary"
                    >timeline</span
                  >
                  Simulasi Spasial Kejadian:
                </span>
                <div class="flex items-center gap-1">
                  <button
                    class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 hover:bg-slate-200 text-[11px] font-medium"
                    type="button"
                  >
                    H-7
                  </button>
                  <button
                    class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 hover:bg-slate-200 text-[11px] font-medium"
                    type="button"
                  >
                    H-3
                  </button>
                  <button
                    class="px-2.5 py-0.5 rounded bg-primary text-white text-[11px] font-semibold shadow-xs"
                    type="button"
                  >
                    Real-time Saat Ini
                  </button>
                </div>
              </div>
              <div class="flex items-center gap-4 text-[11px] text-slate-600">
                <span class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-primary"></span>
                  <span>15 Depot Air Minum (DAM) Terdata</span>
                </span>
                <span class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-red-500"></span>
                  <span>3 Rembesan Limbah Aktif</span>
                </span>
              </div>
            </div>
          </div>
          <!-- ==================== RIGHT: 30% ANALYTICS & OPERATIONS PANEL (4 cols) ==================== -->
          <aside class="lg:col-span-4 flex flex-col gap-4">
            <!-- 2x2 METRIC KPI GRID (Zero Green Palette) -->
            <div class="grid grid-cols-2 gap-3">
              <!-- Total Titik -->
              <div
                class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                    >Total Titik</span
                  >
                  <div
                    class="w-7 h-7 rounded-lg bg-blue-50 text-primary flex items-center justify-center"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >pin_drop</span
                    >
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-2xl font-bold text-slate-900 tracking-tight">
                    168
                  </div>
                  <div class="text-[11px] text-secondary font-medium">
                    Titik Geotag Aktif
                  </div>
                </div>
              </div>
              <!-- Titik Risiko Tinggi -->
              <div
                class="bg-white p-3.5 rounded-xl border border-red-200 shadow-xs flex flex-col justify-between"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="text-[11px] font-semibold uppercase tracking-wider text-red-600"
                    >Risiko Tinggi</span
                  >
                  <div
                    class="w-7 h-7 rounded-lg bg-red-100 text-red-600 flex items-center justify-center"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >emergency</span
                    >
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-2xl font-bold text-red-600 tracking-tight">
                    14
                  </div>
                  <div class="text-[11px] text-red-700 font-medium">
                    Kritis / TMS
                  </div>
                </div>
              </div>
              <!-- Titik Risiko Sedang -->
              <div
                class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-xs flex flex-col justify-between"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="text-[11px] font-semibold uppercase tracking-wider text-amber-700"
                    >Risiko Sedang</span
                  >
                  <div
                    class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >warning</span
                    >
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-2xl font-bold text-amber-600 tracking-tight">
                    48
                  </div>
                  <div class="text-[11px] text-amber-700 font-medium">
                    Dalam Pengawasan
                  </div>
                </div>
              </div>
              <!-- Titik Aman -->
              <div
                class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="text-[11px] font-semibold uppercase tracking-wider text-blue-700"
                    >Kualitas Aman</span
                  >
                  <div
                    class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center"
                  >
                    <span class="material-symbols-outlined text-[18px]"
                      >verified</span
                    >
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-2xl font-bold text-blue-700 tracking-tight">
                    106
                  </div>
                  <div class="text-[11px] text-blue-600 font-medium">
                    63% Laik Minum
                  </div>
                </div>
              </div>
            </div>
            <!-- VILLAGE RISK RANKING CARD -->
            <div
              class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col gap-3"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3
                    class="font-bold text-[13px] text-slate-900 leading-tight"
                  >
                    Peringkat Kerawanan Air Bersih
                  </h3>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    Rasio sampel IKL melebihi ambang batas
                  </p>
                </div>
                <span
                  class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold"
                  >7 Kelurahan</span
                >
              </div>
              <!-- Village Rows -->
              <div class="flex flex-col gap-2 pt-1">
                <!-- Item 1: Sumbersari -->
                <div
                  class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex flex-col gap-1.5 hover:bg-slate-100/80 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span
                        class="w-5 h-5 rounded bg-red-600 text-white font-mono text-[10px] font-bold flex items-center justify-center"
                        >1</span
                      >
                      <span class="font-bold text-[12px] text-slate-900"
                        >Kel. Sumbersari</span
                      >
                    </div>
                    <button
                      class="px-2 py-0.5 rounded bg-white border border-slate-200 text-secondary hover:bg-secondary hover:text-white text-[10px] font-semibold transition-all shadow-2xs flex items-center gap-1"
                      type="button"
                    >
                      <span class="material-symbols-outlined text-[12px]"
                        >my_location</span
                      >
                      <span>Fokus</span>
                    </button>
                  </div>
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="text-red-600 font-semibold"
                      >6 Titik Kritis</span
                    >
                    <span class="text-amber-600">14 Sedang</span>
                    <span class="text-blue-600">18 Aman</span>
                  </div>
                  <div
                    class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden flex"
                  >
                    <div class="h-full bg-red-500" style="width: 38%"></div>
                    <div class="h-full bg-amber-500" style="width: 34%"></div>
                    <div class="h-full bg-blue-600" style="width: 28%"></div>
                  </div>
                </div>
                <!-- Item 2: Kebonsari -->
                <div
                  class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex flex-col gap-1.5 hover:bg-slate-100/80 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span
                        class="w-5 h-5 rounded bg-amber-500 text-white font-mono text-[10px] font-bold flex items-center justify-center"
                        >2</span
                      >
                      <span class="font-bold text-[12px] text-slate-900"
                        >Kel. Kebonsari</span
                      >
                    </div>
                    <button
                      class="px-2 py-0.5 rounded bg-white border border-slate-200 text-secondary hover:bg-secondary hover:text-white text-[10px] font-semibold transition-all shadow-2xs flex items-center gap-1"
                      type="button"
                    >
                      <span class="material-symbols-outlined text-[12px]"
                        >my_location</span
                      >
                      <span>Fokus</span>
                    </button>
                  </div>
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="text-red-600 font-semibold"
                      >4 Titik Kritis</span
                    >
                    <span class="text-amber-600">12 Sedang</span>
                    <span class="text-blue-600">22 Aman</span>
                  </div>
                  <div
                    class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden flex"
                  >
                    <div class="h-full bg-red-500" style="width: 22%"></div>
                    <div class="h-full bg-amber-500" style="width: 38%"></div>
                    <div class="h-full bg-blue-600" style="width: 40%"></div>
                  </div>
                </div>
                <!-- Item 3: Tegalgede -->
                <div
                  class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex flex-col gap-1.5 hover:bg-slate-100/80 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span
                        class="w-5 h-5 rounded bg-blue-600 text-white font-mono text-[10px] font-bold flex items-center justify-center"
                        >3</span
                      >
                      <span class="font-bold text-[12px] text-slate-900"
                        >Kel. Tegalgede</span
                      >
                    </div>
                    <button
                      class="px-2 py-0.5 rounded bg-white border border-slate-200 text-secondary hover:bg-secondary hover:text-white text-[10px] font-semibold transition-all shadow-2xs flex items-center gap-1"
                      type="button"
                    >
                      <span class="material-symbols-outlined text-[12px]"
                        >my_location</span
                      >
                      <span>Fokus</span>
                    </button>
                  </div>
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="text-red-600 font-semibold"
                      >3 Titik Kritis</span
                    >
                    <span class="text-amber-600">10 Sedang</span>
                    <span class="text-blue-600">20 Aman</span>
                  </div>
                  <div
                    class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden flex"
                  >
                    <div class="h-full bg-red-500" style="width: 15%"></div>
                    <div class="h-full bg-amber-500" style="width: 35%"></div>
                    <div class="h-full bg-blue-600" style="width: 50%"></div>
                  </div>
                </div>
              </div>
            </div>
            <!-- URGENT ACTION RECOMMENDATION CARD -->
            <div
              class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs border-l-4 border-l-primary flex flex-col gap-2.5"
            >
              <div class="flex items-start gap-2.5">
                <div
                  class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0"
                >
                  <span class="material-symbols-outlined text-[20px]"
                    >crisis_alert</span
                  >
                </div>
                <div>
                  <h4
                    class="font-bold text-[12px] text-slate-900 leading-tight"
                  >
                    Rekomendasi Respons Cepat
                  </h4>
                  <p class="text-[11px] text-slate-500">
                    Prioritas Penanganan Sektor Kritis
                  </p>
                </div>
              </div>
              <p
                class="text-[11px] text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100"
              >
                <strong class="text-red-600">Kelurahan Sumbersari</strong>
                mendesak memerlukan intervensi
                <strong class="text-primary font-semibold"
                  >35 Kaleng Kaporit</strong
                >
                &amp; penugasan <strong>2 Sanitarian</strong> akibat rembesan
                saluran drainase di Lingkungan Gumuk Kerang.
              </p>
              <div class="grid grid-cols-2 gap-2 pt-1">
                <button
                  class="h-8 rounded-lg bg-primary text-white hover:bg-primary-container text-[11px] font-semibold transition-colors flex items-center justify-center gap-1 shadow-xs"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[14px]"
                    >person_add</span
                  >
                  <span>+ Jadwal Petugas</span>
                </button>
                <button
                  class="h-8 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition-colors flex items-center justify-center gap-1"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[14px]"
                    >inventory_2</span
                  >
                  <span>Disposisi Kaporit</span>
                </button>
              </div>
            </div>
            <!-- RECENT SPATIAL ALERTS FEED -->
            <div
              class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col gap-2.5"
            >
              <div class="flex items-center justify-between">
                <h4
                  class="font-bold text-[12px] text-slate-900 flex items-center gap-1.5"
                >
                  <span
                    class="material-symbols-outlined text-secondary text-[16px]"
                    >sensors</span
                  >
                  <span>Laporan Spasial Terkini</span>
                </h4>
                <a
                  class="text-[11px] text-secondary hover:underline font-semibold"
                  href="#"
                  >Lihat Semua</a
                >
              </div>
              <div class="divide-y divide-slate-100">
                <!-- Item 1 -->
                <div class="py-2 flex items-start gap-2">
                  <span
                    class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"
                  ></span>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between text-[11px]">
                      <span class="font-bold text-slate-800 truncate"
                        >Kader Posyandu (Kebonsari)</span
                      >
                      <span class="text-[10px] text-slate-400 font-mono"
                        >10m lalu</span
                      >
                    </div>
                    <p class="text-[11px] text-slate-500 truncate">
                      Air sumur keruh &amp; berminyak RT 03/RW 08
                    </p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span class="text-[9px] font-mono text-slate-400"
                        >-8.1812, 113.7214</span
                      >
                      <span
                        class="text-[9px] text-red-600 font-semibold uppercase"
                        >Belum Ditangani</span
                      >
                    </div>
                  </div>
                </div>
                <!-- Item 2 -->
                <div class="py-2 flex items-start gap-2">
                  <span
                    class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"
                  ></span>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between text-[11px]">
                      <span class="font-bold text-slate-800 truncate"
                        >Ketua RW 05 (Tegalgede)</span
                      >
                      <span class="text-[10px] text-slate-400 font-mono"
                        >25m lalu</span
                      >
                    </div>
                    <p class="text-[11px] text-slate-500 truncate">
                      Depot DAM Jl. Mastrip minta uji petik berkala
                    </p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span class="text-[9px] font-mono text-slate-400"
                        >-8.1695, 113.7198</span
                      >
                      <span
                        class="text-[9px] text-amber-700 font-semibold uppercase"
                        >Terjadwal IKL</span
                      >
                    </div>
                  </div>
                </div>
                <!-- Item 3 -->
                <div class="py-2 flex items-start gap-2">
                  <span
                    class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 flex-shrink-0"
                  ></span>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between text-[11px]">
                      <span class="font-bold text-slate-800 truncate"
                        >Labkesda Jember</span
                      >
                      <span class="text-[10px] text-slate-400 font-mono"
                        >1j lalu</span
                      >
                    </div>
                    <p class="text-[11px] text-slate-500 truncate">
                      Hasil coliform PAM Kranjingan memenuhi baku mutu
                    </p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span class="text-[9px] font-mono text-slate-400"
                        >-8.1740, 113.7155</span
                      >
                      <span
                        class="text-[9px] text-blue-700 font-semibold uppercase"
                        >Terverifikasi Lab</span
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </main>
</div>

<script>
      document.addEventListener("DOMContentLoaded", () => {
        const refreshBtn = document.getElementById("btn-refresh-map");
        const refreshIcon = document.getElementById("refresh-icon");
        const modal = document.getElementById("gis-popup-modal");

        if (refreshBtn && refreshIcon) {
          refreshBtn.addEventListener("click", () => {
            refreshIcon.classList.add("animate-spin");
            setTimeout(() => {
              refreshIcon.classList.remove("animate-spin");
              if (modal) modal.classList.remove("hidden");
            }, 600);
          });
        }
      });
    </script>
@endsection
