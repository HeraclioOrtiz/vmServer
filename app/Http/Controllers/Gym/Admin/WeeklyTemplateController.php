<?php

namespace App\Http\Controllers\Gym\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\StoreWeeklyTemplateRequest;
use App\Http\Requests\Gym\UpdateWeeklyTemplateRequest;
use App\Models\Gym\WeeklyTemplate;
use App\Models\Gym\WeeklyTemplateDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeeklyTemplateController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $query = WeeklyTemplate::query();
        if ($q) {
            $query->where('title', 'like', "%$q%");
        }
        if ($split = $request->string('split')->toString()) {
            $query->where('split', $split);
        }
        if ($goal = $request->string('goal')->toString()) {
            $query->where('goal', $goal);
        }
        if ($dpw = $request->integer('days_per_week')) {
            $query->where('days_per_week', $dpw);
        }
        $perPage = (int) $request->integer('per_page', 20);
        return response()->json($query->orderByDesc('is_preset')->orderBy('title')->paginate($perPage));
    }

    public function show(WeeklyTemplate $weeklyTemplate)
    {
        $weeklyTemplate->load('days');
        return response()->json($weeklyTemplate);
    }

    public function store(StoreWeeklyTemplateRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $tpl = WeeklyTemplate::create([
                'created_by' => $request->user()->id ?? null,
                'title' => $data['title'],
                'goal' => $data['goal'] ?? null,
                'split' => $data['split'] ?? null,
                'days_per_week' => $data['days_per_week'] ?? 3,
                'tags' => $data['tags'] ?? [],
                'is_preset' => false,
            ]);

            foreach (($data['days'] ?? []) as $d) {
                WeeklyTemplateDay::create([
                    'weekly_template_id' => $tpl->id,
                    'weekday' => $d['weekday'],
                    'daily_template_id' => $d['daily_template_id'] ?? null,
                ]);
            }

            $tpl->load('days');
            return response()->json($tpl, 201);
        });
    }

    public function update(UpdateWeeklyTemplateRequest $request, WeeklyTemplate $weeklyTemplate)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $weeklyTemplate) {
            $weeklyTemplate->update([
                'title' => $data['title'] ?? $weeklyTemplate->title,
                'goal' => $data['goal'] ?? $weeklyTemplate->goal,
                'split' => $data['split'] ?? $weeklyTemplate->split,
                'days_per_week' => $data['days_per_week'] ?? $weeklyTemplate->days_per_week,
                'tags' => $data['tags'] ?? $weeklyTemplate->tags,
            ]);

            if (array_key_exists('days', $data)) {
                $weeklyTemplate->days()->delete();
                foreach ($data['days'] as $d) {
                    WeeklyTemplateDay::create([
                        'weekly_template_id' => $weeklyTemplate->id,
                        'weekday' => $d['weekday'],
                        'daily_template_id' => $d['daily_template_id'] ?? null,
                    ]);
                }
            }

            $weeklyTemplate->load('days');
            return response()->json($weeklyTemplate);
        });
    }

    public function destroy(WeeklyTemplate $weeklyTemplate)
    {
        $weeklyTemplate->delete();
        return response()->noContent();
    }

    public function duplicate(WeeklyTemplate $weeklyTemplate)
    {
        $duplicated = $weeklyTemplate->replicate();
        $duplicated->title = $weeklyTemplate->title . ' (Copia)';
        $duplicated->is_preset = false;
        $duplicated->save();
        
        // Duplicar días de la semana
        foreach ($weeklyTemplate->days as $day) {
            $newDay = $day->replicate();
            $newDay->weekly_template_id = $duplicated->id;
            $newDay->save();
        }
        
        return response()->json($duplicated->load('days'), 201);
    }
}
