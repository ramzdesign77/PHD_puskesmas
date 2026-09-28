<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - E-Klinik Sanitasi Puskesmas</title>

    <style>
      @layer base {
        html, body { margin: 0; padding: 0; overscroll-behavior: none; }
      }
      ::-webkit-scrollbar { display: none; }
    </style>

    <!-- Tailwind CSS (Gunakan CDN untuk development, pindahkan config ke tailwind.config.js untuk production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "on-surface": "#1e1b19",
              background: "#fff8f5",
              primary: "#760009",
              // ... (warna lain dari tema Anda)
            },
            fontFamily: {
              "body-md": ["Plus Jakarta Sans"],
            },
          },
        },
      };
    </script>

    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
</head>

<body class="bg-[#FBFBFB] font-body-md text-on-surface antialiased min-h-screen">
    <main class="w-full min-h-screen">
      <div class="flex flex-col w-full">
        <div class="relative w-full min-h-screen grid grid-cols-1 lg:grid-cols-2 overflow-hidden bg-[#FBFBFB]">

          <!-- LEFT PANEL: Gambar & Branding -->
          <aside class="relative hidden lg:flex flex-col justify-between p-12 xl:p-16 text-white overflow-hidden shadow-2xl select-none">
            <img alt="Laboratorium Pengujian Kualitas Air Sanitasi Puskesmas" class="absolute inset-0 w-full h-full object-cover object-center" src="https://lh3.googleusercontent.com/aida/AEtjO1WndaQXC4TLZJ62dQceNesMNDHZXWSvFvajkXhLBuvJeNLO_QPQTtZczssNFiwx2NViNZyMRGzDyBkw2kuyqeRKtfJ0J5-5Xzi-v9Pqa_ONyXsNVpc2W5zFHebJapj0mX9k_9lXarvXLTFzLClzVvQnUB5kzoAszHbQn_7-pUQK2uW6yEwi_cNrTCMrIQAbefEp7QxyFp3e4OvbyXeOkCXbCFsTG1UmJAokwZ7X4_9AELP7ui5y5qJT6bwd" />
            <div class="absolute inset-0 bg-gradient-to-br from-[#420b0d]/95 via-[#760009]/90 to-[#991b1b]/85 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#2b0005]/95 via-transparent to-[#420b0d]/70"></div>

            <!-- Top Branding -->
            <div class="relative z-10">
              <div class="inline-flex items-center gap-3.5">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md text-white border border-white/20 shadow-sm">
                  <span class="material-symbols-outlined text-2xl">water_drop</span>
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-xl font-bold tracking-tight text-white">E-Klinik Sanitasi</span>
                  </div>
                  <p class="text-xs text-white/80 tracking-wide font-medium">UPTD Puskesmas Sumbersari</p>
                </div>
              </div>
            </div>

            <!-- Middle Editorial Content -->
            <div class="relative z-10 my-auto py-10 max-w-lg space-y-6">
              <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-white text-xs font-semibold tracking-wider uppercase border border-white/15">
                <span class="w-2 h-2 rounded-full bg-red-300 animate-pulse"></span>
                Surveilans Digital Lingkungan
              </div>
              <h1 class="text-4xl xl:text-5xl font-bold text-white tracking-tight leading-[1.18]">
                Pengawasan Kualitas Air &amp; Sanitasi Lingkungan
              </h1>
              <p class="text-base text-white/85 leading-relaxed font-normal">
                Sistem integrasi inspeksi kesehatan lingkungan terpadu, uji reservoir berkala, dan pemantauan mikrobiologis air bersih wilayah kerja Puskesmas.
              </p>
              <div class="pt-2">
                <div class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-left">
                  <span class="material-symbols-outlined text-xl text-red-200">verified_user</span>
                  <div>
                    <p class="text-sm font-semibold text-white leading-snug">Akses Terenkripsi &amp; Terintegrasi PKAM</p>
                    <p class="text-xs text-white/75 font-normal">Kemenkes RI &amp; Dinas Kesehatan Kabupaten Jember</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom Footer Metadata -->
            <div class="relative z-10 pt-6 border-t border-white/15 flex items-center justify-between text-white/70 text-xs tracking-wide">
              <span>Kluster 4 Pencegahan &amp; Penyehatan Lingkungan</span>
              <span>v4.2 Enterprise</span>
            </div>
          </aside>

          <!-- RIGHT PANEL: Form Login -->
          <section class="flex flex-col justify-between p-6 sm:p-10 lg:p-14 xl:p-16 bg-[#FBFBFB] min-h-screen">
            <!-- Top bar mobile -->
            <div class="w-full flex items-center justify-between">
              <div class="flex lg:hidden items-center gap-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#760009] text-white">
                  <span class="material-symbols-outlined text-lg">water_drop</span>
                </div>
                <span class="font-bold text-[#760009] text-base">E-Klinik Sanitasi</span>
              </div>
              <div class="ml-auto flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-stone-200 text-stone-600 text-xs font-semibold shadow-xs">
                  <span class="w-2 h-2 rounded-full bg-[#991b1b] animate-pulse"></span>
                  Server PKAM Online
                </span>
              </div>
            </div>

            <!-- Form Content -->
            <div class="w-full max-w-md mx-auto my-auto py-8">
              <div class="space-y-2 mb-8 text-left">
                <h2 class="text-3xl font-bold text-stone-900 tracking-tight">Masuk ke Akun</h2>
                <p class="text-stone-500 text-sm">Portal Resmi E-Klinik Sanitasi Puskesmas</p>
              </div>

              <!-- FORM LARAVEL MULAI DI SINI -->
              <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5" onsubmit="event.preventDefault(); handleLoginSubmit();">
                @csrf

                <!-- Pesan Error Global (jika ada error validasi dari Laravel) -->
                @if ($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('error'))
                  <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                    {{ session('error') }}
                  </div>
                @endif

                @if (session('success'))
                  <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ session('success') }}
                  </div>
                @endif

                <!-- NIP / Username Field -->
                <div class="space-y-1.5 text-left">
                  <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider" for="usernameInput">
                    NIP / Username Kedinasan
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                      <span class="material-symbols-outlined text-lg">person</span>
                    </div>
                    <input class="block w-full pl-11 pr-4 py-3.5 bg-white text-stone-900 placeholder:text-stone-400 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-[#991b1b]/20 border border-stone-200 focus:border-[#991b1b] shadow-xs transition"
                           id="usernameInput"
                           name="username"
                           placeholder="Contoh: 19890412 201402 1 003"
                           required
                           type="text"
                           value="{{ old('username') }}" />
                  </div>
                  <p class="text-xs text-stone-400">Demo lokal: <strong>admin</strong>, <strong>petugas</strong>, atau <strong>masyarakat</strong> dengan kata sandi <strong>password</strong>.</p>
                </div>

                <!-- Password Field -->
                <div class="space-y-1.5 text-left">
                  <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider" for="passwordInput">
                    Kata Sandi
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                      <span class="material-symbols-outlined text-lg">lock</span>
                    </div>
                    <input class="block w-full pl-11 pr-11 py-3.5 bg-white text-stone-900 placeholder:text-stone-400 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-[#991b1b]/20 border border-stone-200 focus:border-[#991b1b] shadow-xs transition"
                           id="passwordInput"
                           name="password"
                           placeholder="Masukkan kata sandi..."
                           required
                           type="password" />
                    <button aria-label="Tampilkan atau sembunyikan kata sandi" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-stone-700 transition" id="togglePasswordBtn" onclick="togglePasswordVisibility()" type="button">
                      <span class="material-symbols-outlined text-lg" id="passwordToggleIcon">visibility</span>
                    </button>
                  </div>
                </div>

                <!-- Utilities row -->
                <div class="flex items-center justify-between pt-1 text-sm">
                  <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    {{-- <input class="w-4 h-4 rounded text-[#991b1b] accent-[#991b1b] cursor-pointer border-stone-300" id="rememberMe" name="remember" type="checkbox" /> --}}
                    {{-- <span class="text-stone-600 font-medium text-xs sm:text-sm">Ingat Saya</span> --}}
                  </label>

                  @if (Route::has('password.request'))
                  <a class="text-[#991b1b] hover:text-[#7f1d1d] font-semibold text-xs sm:text-sm hover:underline transition" href="{{ route('password.request') }}">
                    Lupa Kata Sandi?
                  </a>
                  @else
                  {{-- <a class="text-[#991b1b] hover:text-[#7f1d1d] font-semibold text-xs sm:text-sm hover:underline transition" href="javascript:void(0)" onclick="handleForgotPassword()">
                    Lupa Kata Sandi?
                  </a> --}}
                  @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                  <button class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-gradient-to-r from-[#760009] to-[#991b1b] hover:from-[#5c0007] hover:to-[#7f1d1d] text-white text-sm font-semibold tracking-wide shadow-md hover:shadow-lg active:scale-[0.99] transition" id="submitBtn" type="submit">
                    <span>Login</span>
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                  </button>
                </div>
              </form>

              <!-- Security trust note -->
              <div class="pt-6 mt-6 border-t border-stone-200/80 flex items-center justify-center gap-2 text-stone-500 text-xs">
                <span class="material-symbols-outlined text-[#991b1b] text-base">shield</span>
                <span>Sistem Resmi UPTD Puskesmas • Akses Terotentikasi</span>
              </div>
            </div>

            <!-- Subtle Technical Footer -->
            <div class="w-full pt-4 border-t border-stone-200/70 flex flex-col sm:flex-row items-center justify-between text-stone-500 text-xs gap-3">
              <span>© 2026 E-Klinik Sanitasi Puskesmas. Terintegrasi SISDMK &amp; PKAM.</span>
              <div class="flex items-center gap-3">
                {{-- <a class="hover:text-[#991b1b] transition" href="javascript:void(0)">Panduan SOP IKL</a>
                <span>|</span>
                <a class="hover:text-[#991b1b] transition" href="javascript:void(0)">Helpdesk Dinkes Jember</a> --}}
              </div>
            </div>
          </section>
        </div>
      </div>

      <script>
        function togglePasswordVisibility() {
          const passwordField = document.getElementById("passwordInput");
          const toggleIcon = document.getElementById("passwordToggleIcon");
          if (!passwordField || !toggleIcon) return;
          if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.textContent = "visibility_off";
          } else {
            passwordField.type = "password";
            toggleIcon.textContent = "visibility";
          }
        }

        // Modifikasi fungsi submit agar menjalankan animasi lalu submit form sesungguhnya ke Laravel
        function handleLoginSubmit() {
          const form = document.getElementById("loginForm");
          const btn = document.getElementById("submitBtn");
          if (!btn || !form) return;

          btn.disabled = true;
          btn.innerHTML = '<span class="material-symbols-outlined text-body-lg animate-spin">progress_activity</span><span>Memverifikasi Kredensial...</span>';

          // Jeda sebentar untuk menampilkan animasi sebelum mengirim POST request
          setTimeout(() => {
            form.submit();
          }, 600);
        }

        function handleForgotPassword() {
          alert("Silakan hubungi Administrator IT Dinas Kesehatan Kabupaten Jember atau Subbag Kepegawaian Puskesmas untuk reset kata sandi akun kedinasan.");
        }
      </script>
    </main>
</body>
</html>
