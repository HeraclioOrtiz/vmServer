<?php

namespace App\Http\Controllers\Gym\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Gym\WeeklyAssignment;
use App\Models\Gym\DailyAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MyPlanController extends Controller
{
    public function myWeek(Request $request)
    {
        $user = $request->user();
        $date = $request->date('date') ?: now()->toDateString();

        $assignment = WeeklyAssignment::with('days')
            ->where('user_id', $user->id)
            ->whereDate('week_start', '<=', $date)
            ->whereDate('week_end', '>=', $date)
            ->first();

        if (!$assignment) {
            return response()->json([
                'week_start' => null,
                'week_end' => null,
                'days' => [],
            ]);
        }

        $days = [];
        $daysMap = $assignment->days->keyBy('weekday');
        for ($w = 1; $w <= 7; $w++) {
            $day = $daysMap->get($w);
            $days[] = [
                'weekday' => $w,
                'date' => $day?->date?->toDateString(),
                'has_session' => $day !== null,
                'title' => $day?->title,
            ];
        }

        return response()->json([
            'week_start' => $assignment->week_start->toDateString(),
            'week_end' => $assignment->week_end->toDateString(),
            'days' => $days,
        ]);
    }

    public function myDay(Request $request)
    {
        $user = $request->user();
        $date = $request->input('date') ?: now()->toDateString();
        $weekday = Carbon::parse($date)->dayOfWeek ?: 7; // Sunday = 7

        try {
            $assignment = WeeklyAssignment::with('days.exercises')
                ->where('user_id', $user->id)
                ->whereDate('week_start', '<=', $date)
                ->whereDate('week_end', '>=', $date)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'date' => $date,
                    'title' => 'Sin entrenamiento',
                    'exercises' => [],
                    'message' => 'No hay asignación para esta fecha'
                ]);
            }

            // Buscar el día específico
            $day = $assignment->days->where('weekday', $weekday)->first();

            if (!$day) {
                return response()->json([
                    'date' => $date,
                    'title' => 'Día de descanso',
                    'exercises' => [],
                    'message' => 'No hay entrenamiento programado para este día'
                ]);
            }

            return response()->json([
                'date' => $date,
                'title' => $day->title ?? 'Entrenamiento',
                'notes' => $day->notes,
                'exercises' => $day->exercises->map(function ($ex) {
                    return [
                        'name' => $ex->name ?? 'Ejercicio',
                        'order' => $ex->order ?? 1,
                        'sets' => $ex->sets ?? 3,
                        'reps' => $ex->reps ?? '10-12',
                        'rest_seconds' => $ex->rest_seconds ?? 60,
                        'notes' => $ex->notes
                    ];
                })->values(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'date' => $date,
                'title' => 'Error',
                'exercises' => [],
                'message' => 'Error al obtener el entrenamiento del día',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function formatReps(?int $min, ?int $max): ?string
    {
        if ($min === null && $max === null) { return null; }
        if ($min !== null && $max !== null && $min !== $max) { return $min.'-'.$max; }
        return (string)($min ?? $max);
    }
}
