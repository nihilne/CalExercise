<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CalExercise</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen bg-slate-950 font-sans p-6 flex flex-col">

    <div class="flex-1 min-h-0 flex flex-col bg-slate-900 text-slate-200 rounded-2xl border border-slate-800 shadow-sm overflow-hidden">

        <div class="grid grid-cols-3 items-center px-6 py-3 border-b border-slate-800 shrink-0">
            <div class="justify-self-start">
                <a href="?month={{ Illuminate\Support\Carbon::now()->format('Y-m') }}"
                    class="px-4 py-1.5 rounded-full border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition">
                    Today
                </a>
            </div>
            <div class="flex items-center justify-center gap-4">
                <a href="?month={{ $prevMonth }}"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-xl font-semibold text-slate-100">{{ $currentMonth->format('F Y') }}</h1>
                <a href="?month={{ $nextMonth }}"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div></div>
        </div>

        <div class="grid grid-cols-7 border-b border-slate-800 shrink-0">
            @foreach ($weekdayLabels as $wd)
            <div class="text-center text-xs font-semibold text-slate-500 tracking-wide py-2 uppercase">
                {{ $wd }}
            </div>
            @endforeach
        </div>

        <div class="flex-1 grid grid-cols-7 auto-rows-fr">
            @foreach ($days as $day)
            @php
            $dateStr = $day->format('Y-m-d');
            $log = $logs[$dateStr] ?? null;
            $trackingStarted = $firstLogDate && $day->greaterThanOrEqualTo($firstLogDate);
            $isFuture = $day->greaterThan($today);
            @endphp
            <div
                @if (!$isFuture)
                data-date="{{ $dateStr }}"
                data-minutes="{{ $log?->minutes ?? '' }}"
                data-km="{{ $log?->kilometers ?? '' }}"
                onclick="openDayModal(this)"
                @endif
                @class([ 'border-r border-b border-slate-800 p-2 flex flex-col transition' , 'cursor-pointer'=> !$isFuture,
                'cursor-default' => $isFuture,
                'text-slate-600' => !$day->isSameMonth($currentMonth),
                'today-ring rounded-lg' => $day->isSameDay($today),
                'bg-emerald-500/25 hover:bg-emerald-500/35' => $trackingStarted && $log,
                'bg-orange-500/25 hover:bg-orange-500/35' => $trackingStarted && !$log && $day->lessThan($today),
                'hover:bg-slate-800/60' => !$trackingStarted && !$isFuture,
                ])
                >
                <div @class([ 'w-7 h-7 flex items-center justify-center rounded-full text-sm font-medium' , 'text-white font-bold'=> $day->isSameDay($today),
                    ])>
                    {{ $day->day }}
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <div class="shrink-0 flex items-center justify-center gap-8 px-6 pt-6 text-sm">
        <div class="text-slate-400">
            Total Minutes: <span class="text-slate-100 font-semibold">{{ $hasEntriesThisMonth ? $totalMinutes : '—' }}</span>
        </div>
        <div class="text-slate-400">
            Total Kilometers: <span class="text-slate-100 font-semibold">{{ $hasEntriesThisMonth ? number_format($totalKilometers, 2) : '—' }}</span>
        </div>
    </div>

    <dialog id="dayModal" class="fixed m-auto rounded-2xl bg-slate-900 text-slate-200 p-6 w-80 border border-slate-800 backdrop:bg-black/60">
        <h2 id="modalDateLabel" class="text-lg font-semibold mb-4"></h2>
        <div id="viewMode">
            <div class="mb-2">
                <span class="text-slate-400 text-sm">Minutes:</span>
                <span id="viewMinutes" class="ml-2 font-medium"></span>
            </div>
            <div class="mb-6">
                <span class="text-slate-400 text-sm">Kilometers:</span>
                <span id="viewKm" class="ml-2 font-medium"></span>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('dayModal').close()"
                    class="px-4 py-1.5 rounded-full border border-slate-700 text-sm hover:bg-slate-800 transition cursor-pointer">
                    Close
                </button>
                <button type="button" onclick="switchToEditMode()"
                    class="px-4 py-1.5 rounded-full bg-violet-600 text-white text-sm hover:bg-violet-500 transition cursor-pointer">
                    Edit
                </button>
            </div>
        </div>
        <form id="editMode" method="POST" action="{{ route('log.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="date" id="modalDate">
            <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
            <label class="block text-sm mb-1 text-slate-400">Minutes</label>
            <input type="number" name="minutes" id="modalMinutes" min="1" required
                class="w-full mb-1 rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            @error('minutes')
            <p class="text-xs text-red-400 mb-3">{{ $message }}</p>
            @enderror
            <label class="block text-sm mb-1 text-slate-400">Kilometers</label>
            <input type="number" name="kilometers" id="modalKm" min="0.01" step="0.01" required
                class="w-full mb-6 rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            @error('kilometers')
            <p class="text-xs text-red-400 mb-3">{{ $message }}</p>
            @enderror
            <div class="flex justify-end gap-2">
                <button type="button" onclick="handleCancel()"
                    class="px-4 py-1.5 rounded-full border border-slate-700 text-sm hover:bg-slate-800 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-1.5 rounded-full bg-violet-600 text-white text-sm hover:bg-violet-500 transition cursor-pointer">
                    Save
                </button>
            </div>
        </form>
    </dialog>

</body>

</html>