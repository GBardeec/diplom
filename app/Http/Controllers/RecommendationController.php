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
            'popularSkillIds' => $this->popularSkillIds(),
            'popularSkillIdsByGroup' => $this->popularSkillIdsByGroup(),
            'categories' => VacancyCategory::orderBy('title')->get(['id', 'group_id', 'parent_id', 'title', 'market_level', 'market_salary_median', 'market_salary_sample_size']),
            'qualifications' => Qualification::orderBy('id')->get(['id', 'title']),
            'locations' => Location::orderBy('title')->get(['id', 'title']),
            'reportUuid' => $reportUuid,
        ]);
    }

    private function popularSkillIds(): array
    {
        return DB::table('skill_vacancy')
            ->join('vacancies', 'vacancies.id', '=', 'skill_vacancy.vacancy_id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->select('skill_vacancy.skill_id')
            ->groupBy('skill_vacancy.skill_id')
            ->orderByRaw('COUNT(DISTINCT skill_vacancy.vacancy_id) DESC')
            ->limit(12)
            ->pluck('skill_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function popularSkillIdsByGroup(): array
    {
        return DB::table('skill_vacancy')
            ->join('vacancies', 'vacancies.id', '=', 'skill_vacancy.vacancy_id')
            ->join('vacancy_category_vacancy', 'vacancy_category_vacancy.vacancy_id', '=', 'vacancies.id')
            ->join('vacancy_categories', 'vacancy_categories.id', '=', 'vacancy_category_vacancy.vacancy_category_id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->select('vacancy_categories.group_id', 'skill_vacancy.skill_id', DB::raw('COUNT(DISTINCT skill_vacancy.vacancy_id) as vacancies_count'))
            ->groupBy('vacancy_categories.group_id', 'skill_vacancy.skill_id')
            ->orderBy('vacancies_count', 'desc')
            ->get()
            ->groupBy('group_id')
            ->map(fn ($skills) => $skills->take(12)->pluck('skill_id')->map(fn ($id) => (int) $id)->values()->all())
            ->all();
    }
}
