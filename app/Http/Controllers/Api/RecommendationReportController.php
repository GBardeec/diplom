<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecommendationReport;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecommendationReportController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    public function store(Request $request)
    {
        $input = $this->validated($request);
        $report = RecommendationReport::create([
            'report_uuid' => (string) Str::uuid(),
            // Отчёт открывается по уникальной ссылке; дополнительная привязка
            // к браузерной сессии для него не нужна.
            'session_id' => (string) Str::uuid(),
            'input_data' => $input,
            'view_count' => 1,
            'last_viewed_at' => now(),
        ]);

        return response()->json(['data' => $this->payload($report, $this->recommendations->recommend($input))], 201);
    }

    public function show(string $reportUuid)
    {
        $report = RecommendationReport::where('report_uuid', $reportUuid)->firstOrFail();
        $report->increment('view_count');
        $report->update(['last_viewed_at' => now()]);
        return response()->json(['data' => $this->payload($report, $this->recommendations->recommend($report->input_data))]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'skills' => ['nullable', 'array', 'max:50'], 'skills.*' => ['integer', 'exists:skills,id'],
            'group_id' => ['nullable', 'integer', 'exists:vacancy_groups,id'],
            'category_id' => ['nullable', 'integer', 'exists:vacancy_categories,id'],
            'qualification_id' => ['nullable', 'integer', 'exists:qualifications,id'],
            'city_id' => ['nullable', 'integer', 'exists:locations,id'],
            'remote_only' => ['nullable', 'boolean'],
            'employment' => ['nullable', 'in:full_time,part_time,project,internship'],
            'salary_from' => ['nullable', 'integer', 'min:0'],
            'salary_to' => ['nullable', 'integer', 'min:0', 'gte:salary_from'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]) + ['limit' => 20];
    }

    private function payload(RecommendationReport $report, array $result): array
    {
        return $result + [
            'report_uuid' => $report->report_uuid,
            'report_url' => url('/recommendations/'.$report->report_uuid),
            'input_data' => $report->input_data,
        ];
    }
}
