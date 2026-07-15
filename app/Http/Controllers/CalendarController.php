<?php

namespace App\Http\Controllers;

use App\Models\ExerciseLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    const START_OF_WEEK = Carbon::MONDAY;
    const END_OF_WEEK = Carbon::SUNDAY;

    public function index(Request $request)
    {
        $month = $request->query('month');
        $date = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : Carbon::now()->startOfMonth();

        $startOfGrid = $date->copy()->startOfMonth()->startOfWeek(self::START_OF_WEEK);
        $endOfGrid = $date->copy()->endOfMonth()->endOfWeek(self::END_OF_WEEK);

        $days = [];
        for ($cursor = $startOfGrid->copy(); $cursor <= $endOfGrid; $cursor->addDay()) {
            $days[] = $cursor->copy();
        }

        $weekdayLabels = [];
        $labelCursor = Carbon::now()->startOfWeek(self::START_OF_WEEK);
        for ($i = 0; $i < 7; $i++) {
            $weekdayLabels[] = $labelCursor->copy()->addDays($i)->format('D');
        }

        $logs = ExerciseLog::whereBetween('date', [
            $startOfGrid->format('Y-m-d'),
            $endOfGrid->format('Y-m-d'),
        ])
            ->get()
            ->keyBy(fn($log) => $log->date->format('Y-m-d'));

        $monthLogs = ExerciseLog::whereBetween('date', [
            $date->copy()->startOfMonth()->format('Y-m-d'),
            $date->copy()->endOfMonth()->format('Y-m-d'),
        ])
            ->get();

        $totalMinutes = $monthLogs->sum('minutes');
        $totalKilometers = $monthLogs->sum('kilometers');
        $hasEntriesThisMonth = $monthLogs->isNotEmpty();
        $firstLogDate = ExerciseLog::min('date');
        $firstLogDate = $firstLogDate ? Carbon::parse($firstLogDate) : null;

        return view('calendar', [
            'days' => $days,
            'weekdayLabels' => $weekdayLabels,
            'currentMonth' => $date,
            'today' => Carbon::today(),
            'prevMonth' => $date->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $date->copy()->addMonth()->format('Y-m'),
            'logs' => $logs,
            'firstLogDate' => $firstLogDate,
            'totalMinutes' => $totalMinutes,
            'totalKilometers' => $totalKilometers,
            'hasEntriesThisMonth' => $hasEntriesThisMonth,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'minutes' => ['required', 'integer', 'min:0'],
            'kilometers' => ['required', 'numeric', 'min:0'],
            'month' => ['nullable', 'string'],
        ]);

        ExerciseLog::updateOrCreate(
            ['date' => $validated['date']],
            [
                'minutes' => $validated['minutes'],
                'kilometers' => $validated['kilometers'],
            ]
        );

        return redirect('/?month=' . ($validated['month'] ?? now()->format('Y-m')));
    }
}
