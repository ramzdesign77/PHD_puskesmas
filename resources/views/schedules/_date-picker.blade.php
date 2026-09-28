@php($calendarId = 'schedule-calendar-'.\Illuminate\Support\Str::uuid())

<div class="relative w-full min-w-52" data-schedule-calendar
    data-dates-url="{{ route('schedules.unavailable-dates') }}" data-except-id="{{ $exceptId ?? '' }}">
    <input type="hidden" name="tanggal_kunjungan" data-date-value>
    <button type="button" data-calendar-toggle aria-expanded="false" aria-controls="{{ $calendarId }}"
        class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
        <span data-calendar-selected>Pilih petugas terlebih dahulu</span>
        <i class="fas fa-calendar-alt text-gray-400" aria-hidden="true"></i>
    </button>

    <div id="{{ $calendarId }}" data-calendar-popup hidden
        class="absolute left-0 top-full z-30 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 shadow-lg">
        <div class="mb-3 flex items-center justify-between">
            <button type="button" data-calendar-prev aria-label="Bulan sebelumnya"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <p data-calendar-month class="text-sm font-semibold text-gray-800"></p>
            <button type="button" data-calendar-next aria-label="Bulan berikutnya"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
        <div class="mb-1 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-gray-400" aria-hidden="true">
            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
        </div>
        <div data-calendar-grid class="grid grid-cols-7 gap-1" role="grid"></div>
    </div>
    <p data-calendar-message class="mt-1 text-xs text-gray-500" role="status" aria-live="polite"></p>
    @error('tanggal_kunjungan')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
