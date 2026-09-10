<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Qualification;
use App\Models\Skill;
use App\Models\VacancyCategory;
use App\Models\VacancyGroup;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    public function index()
    {
        return $this->page();
    }

    public function show(string $reportUuid)
    {
        return $this->page($reportUuid);
    }

    private function page(?string $reportUuid = null)
    {
        return Inertia::render('Recommendations/Index', [
            'skills' => Skill::orderBy('title')->get(['id', 'title']),
            'groups' => VacancyGroup::orderBy('sort_order')->get(['id', 'title']),
            'categories' => VacancyCategory::orderBy('title')->get(['id', 'group_id', 'parent_id', 'title', 'market_level', 'market_salary_median', 'market_salary_sample_size']),
            'qualifications' => Qualification::orderBy('id')->get(['id', 'title']),
            'locations' => Location::orderBy('title')->get(['id', 'title']),
            'transitions' => DB::table('career_transitions')->get(['from_category_id', 'to_category_id'])->map(fn ($transition) => [
                'from' => $transition->from_category_id,
                'to' => $transition->to_category_id,
            ]),
            'reportUuid' => $reportUuid,
        ]);
    }
}
