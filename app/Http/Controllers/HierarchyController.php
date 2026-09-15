<?php

namespace App\Http\Controllers;

use App\Models\VacancyCategory;
use App\Models\VacancyGroup;
use App\Services\HierarchyCache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HierarchyController extends Controller
{
    public function index()
    {
        $data = HierarchyCache::remember(fn () => $this->buildHierarchyData());

        return Inertia::render('Hierarchy/Index', $data);
    }

    private function buildHierarchyData(): array
    {
        $categories = VacancyCategory::with(['parent'])
            ->withCount('vacancies')
            ->orderBy('market_level')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                // Получаем статистику по вакансиям для этой категории
                $stats = $this->getVacancyStats($category->id);

                return [
                    'id' => $category->id,
                    'external_id' => $category->external_id,
                    'title' => $category->title,
                    'alias' => $category->alias,
                    'description' => $category->description,
                    'level' => $category->level,
                    'market_level' => $category->market_level,
                    'market_salary_median' => $category->market_salary_median,
                    'market_salary_sample_size' => $category->market_salary_sample_size,
                    'parent_id' => $category->parent_id,
                    'group_id' => $category->group_id,
                    'sort_order' => $category->sort_order,
                    'vacancies_count' => $category->vacancies_count,
                    'locations_count' => $stats['locations_count'],
                    'grades_count' => $stats['grades_count'],
                    'salary_stats' => $stats['salary_stats'],
                    'top_skills' => $stats['top_skills'],
                    'top_skills_by_grade' => $stats['top_skills_by_grade'],
                    'top_locations' => $stats['top_locations'],
                    'top_locations_by_grade' => $stats['top_locations_by_grade'],
                'grades_distribution' => $stats['grades_distribution'],
                'employment_stats' => $stats['employment_stats'],
                'publication_timeline' => $stats['publication_timeline'],
                ];
            })
            ->values()
            ->all();

        $groups = VacancyGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'external_id' => $group->external_id,
                'title' => $group->title,
                'description' => $group->description,
            ];
        })->values()->all();

        return [
            'categories' => $categories,
            'groups' => $groups,
            'transitions' => $this->buildTransitionData(),
        ];
    }

    private function buildTransitionData(): array
    {
        $vacancyCounts = DB::table('vacancy_category_vacancy')
            ->join('vacancies', 'vacancies.id', '=', 'vacancy_category_vacancy.vacancy_id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->selectRaw('vacancy_category_vacancy.vacancy_category_id as category_id, COUNT(DISTINCT vacancies.id) as vacancies_count')
            ->groupBy('vacancy_category_vacancy.vacancy_category_id')
            ->pluck('vacancies_count', 'category_id');

        $skillsByCategory = DB::table('vacancy_category_vacancy')
            ->join('vacancies', 'vacancies.id', '=', 'vacancy_category_vacancy.vacancy_id')
            ->join('skill_vacancy', 'skill_vacancy.vacancy_id', '=', 'vacancies.id')
            ->join('skills', 'skills.id', '=', 'skill_vacancy.skill_id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->selectRaw('vacancy_category_vacancy.vacancy_category_id as category_id, skills.id as skill_id, skills.title, COUNT(DISTINCT vacancies.id) as vacancies_count')
            ->groupBy('vacancy_category_vacancy.vacancy_category_id', 'skills.id', 'skills.title')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($skills) => $skills->keyBy('skill_id'));

        return DB::table('career_transitions')
            ->join('vacancy_categories as source', 'source.id', '=', 'career_transitions.from_category_id')
            ->join('vacancy_categories as target', 'target.id', '=', 'career_transitions.to_category_id')
            ->select([
                'career_transitions.from_category_id',
                'career_transitions.to_category_id',
                'career_transitions.skills_similarity',
                'source.group_id',
            ])
            ->orderBy('source.group_id')
            ->orderBy('career_transitions.from_category_id')
            ->get()
            ->map(function ($transition) use ($skillsByCategory, $vacancyCounts) {
                $sourceSkills = $skillsByCategory->get($transition->from_category_id, collect());
                $targetSkills = $skillsByCategory->get($transition->to_category_id, collect());
                $targetVacancies = max(1, (int) ($vacancyCounts[$transition->to_category_id] ?? 0));

                $missingSkills = $targetSkills
                    ->reject(fn ($skill) => $sourceSkills->has($skill->skill_id))
                    ->sortByDesc('vacancies_count')
                    ->take(5)
                    ->map(fn ($skill) => [
                        'title' => $skill->title,
                        'percent' => (int) round($skill->vacancies_count / $targetVacancies * 100),
                    ])
                    ->values()
                    ->all();

                $commonSkills = $targetSkills
                    ->filter(fn ($skill) => $sourceSkills->has($skill->skill_id))
                    ->sortByDesc('vacancies_count')
                    ->take(5)
                    ->map(fn ($skill) => [
                        'title' => $skill->title,
                        'percent' => (int) round($skill->vacancies_count / $targetVacancies * 100),
                    ])
                    ->values()
                    ->all();

                return [
                    'from_category_id' => (int) $transition->from_category_id,
                    'to_category_id' => (int) $transition->to_category_id,
                    'group_id' => (int) $transition->group_id,
                    'similarity' => (int) round($transition->skills_similarity * 100),
                    'common_skills' => $commonSkills,
                    'missing_skills' => $missingSkills,
                ];
            })
            ->values()
            ->all();
    }

    private function getVacancyStats($categoryId)
    {
        // Получаем ID всех вакансий категории
        $vacancyIds = DB::table('vacancies')
            ->join('vacancy_category_vacancy', 'vacancy_category_vacancy.vacancy_id', '=', 'vacancies.id')
            ->where('vacancy_category_vacancy.vacancy_category_id', $categoryId)
            ->where('archived', false)
            ->where('hidden', false)
            ->pluck('vacancies.id');

        if ($vacancyIds->isEmpty()) {
            return [
                'locations_count' => 0,
                'grades_count' => 0,
                'salary_stats' => null,
                'top_skills' => [],
                'top_skills_by_grade' => [],
                'top_locations' => [],
                'top_locations_by_grade' => [],
                'grades_distribution' => [],
                'employment_stats' => [],
                'publication_timeline' => [],
            ];
        }

        // Статистика по зарплатам
        $salaryStats = DB::table('salaries')
            ->whereIn('vacancy_id', $vacancyIds)
            ->where('currency', 'rur')
            ->select(
                DB::raw('AVG(CASE
                WHEN `from` IS NOT NULL AND `to` IS NOT NULL THEN (`from` + `to`) / 2
                WHEN `from` IS NOT NULL THEN `from`
                WHEN `to` IS NOT NULL THEN `to`
            END) as avg_salary'),
                DB::raw('MIN(CASE
                WHEN `from` IS NOT NULL AND `to` IS NOT NULL THEN (`from` + `to`) / 2
                WHEN `from` IS NOT NULL THEN `from`
                WHEN `to` IS NOT NULL THEN `to`
            END) as min_salary'),
                DB::raw('MAX(CASE
                WHEN `from` IS NOT NULL AND `to` IS NOT NULL THEN (`from` + `to`) / 2
                WHEN `from` IS NOT NULL THEN `from`
                WHEN `to` IS NOT NULL THEN `to`
            END) as max_salary')
            )
            ->first();

        // Зарплаты по грейдам - исправлено: сначала считаем среднее между from и to
        $salaryByGradeData = DB::table('vacancies')
            ->join('salaries', 'vacancies.id', '=', 'salaries.vacancy_id')
            ->join('qualifications', 'vacancies.qualification_id', '=', 'qualifications.id')
            ->whereIn('vacancies.id', $vacancyIds)
            ->where('salaries.currency', 'rur')
            ->select(
                'qualifications.title as grade',
                DB::raw('AVG(CASE
                WHEN salaries.from IS NOT NULL AND salaries.to IS NOT NULL THEN (salaries.from + salaries.to) / 2
                WHEN salaries.from IS NOT NULL THEN salaries.from
                WHEN salaries.to IS NOT NULL THEN salaries.to
            END) as avg_salary'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('qualifications.id', 'qualifications.title')
            ->orderByRaw("
            CASE
                WHEN LOWER(qualifications.title) LIKE '%intern%' THEN 1
                WHEN LOWER(qualifications.title) LIKE '%junior%' THEN 2
                WHEN LOWER(qualifications.title) LIKE '%middle%' THEN 3
                WHEN LOWER(qualifications.title) LIKE '%senior%' THEN 4
                WHEN LOWER(qualifications.title) LIKE '%lead%' THEN 5
                ELSE 0
            END
        ")
            ->get();

        $salaryByGrade = [];
        foreach ($salaryByGradeData as $item) {
            $salaryByGrade[$item->grade] = [
                'avg' => round($item->avg_salary),
                'count' => $item->count
            ];
        }

        // Топ навыки
        $topSkills = DB::table('skill_vacancy')
            ->join('skills', 'skill_vacancy.skill_id', '=', 'skills.id')
            ->whereIn('skill_vacancy.vacancy_id', $vacancyIds)
            ->select('skills.id as skill_id', 'skills.title', DB::raw('COUNT(*) as count'))
            ->groupBy('skills.id', 'skills.title')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($skill) => [
                'skill_id' => (int) $skill->skill_id,
                'title' => $skill->title,
                'count' => (int) $skill->count,
                'percentage' => (int) round($skill->count / $vacancyIds->count() * 100),
            ])
            ->values()
            ->all();

        $topSkillsByGrade = DB::table('vacancies')
            ->join('qualifications', 'vacancies.qualification_id', '=', 'qualifications.id')
            ->join('skill_vacancy', 'vacancies.id', '=', 'skill_vacancy.vacancy_id')
            ->join('skills', 'skill_vacancy.skill_id', '=', 'skills.id')
            ->whereIn('vacancies.id', $vacancyIds)
            ->select('qualifications.id as grade_id', 'qualifications.title as grade_title', 'skills.id as skill_id', 'skills.title', DB::raw('COUNT(*) as count'))
            ->groupBy('qualifications.id', 'qualifications.title', 'skills.id', 'skills.title')
            ->orderBy('count', 'desc')
            ->get()
            ->groupBy('grade_id')
            ->map(function ($skills, $gradeId) use ($vacancyIds) {
                $gradeVacanciesCount = DB::table('vacancies')
                    ->whereIn('id', $vacancyIds)
                    ->where('qualification_id', $gradeId)
                    ->count();
                return [
                    'grade_id' => (int) $gradeId,
                    'title' => $skills->first()->grade_title,
                    'skills' => $skills->map(fn ($skill) => [
                        'skill_id' => $skill->skill_id,
                        'title' => $skill->title,
                        'count' => $skill->count,
                        'percentage' => $gradeVacanciesCount ? round($skill->count / $gradeVacanciesCount * 100) : 0,
                    ])->values()->all(),
                ];
            })
            ->sortBy(fn (array $grade) => $this->gradeSortOrder($grade['title']))
            ->values()
            ->all();

        // Топ локации
        $locationSalaryAverages = $this->locationSalaryAverages($vacancyIds);
        $topLocations = DB::table('location_vacancy')
            ->join('locations', 'location_vacancy.location_id', '=', 'locations.id')
            ->whereIn('location_vacancy.vacancy_id', $vacancyIds)
            ->select('locations.id as location_id', 'locations.title', DB::raw('COUNT(*) as count'))
            ->groupBy('locations.id', 'locations.title')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn ($location) => [
                'location_id' => (int) $location->location_id,
                'title' => $location->title,
                'count' => (int) $location->count,
                'percentage' => (int) round($location->count / $vacancyIds->count() * 100),
                'salary_avg' => $locationSalaryAverages[$location->location_id] ?? null,
            ])
            ->values()
            ->all();

        $topLocationsByGrade = DB::table('vacancies')
            ->join('qualifications', 'vacancies.qualification_id', '=', 'qualifications.id')
            ->join('location_vacancy', 'vacancies.id', '=', 'location_vacancy.vacancy_id')
            ->join('locations', 'location_vacancy.location_id', '=', 'locations.id')
            ->whereIn('vacancies.id', $vacancyIds)
            ->select('qualifications.id as grade_id', 'qualifications.title as grade_title', 'locations.id as location_id', 'locations.title', DB::raw('COUNT(*) as count'))
            ->groupBy('qualifications.id', 'qualifications.title', 'locations.id', 'locations.title')
            ->orderBy('count', 'desc')
            ->get()
            ->groupBy('grade_id')
            ->map(function ($locations, $gradeId) use ($vacancyIds) {
                $gradeVacanciesCount = DB::table('vacancies')
                    ->whereIn('id', $vacancyIds)
                    ->where('qualification_id', $gradeId)
                    ->count();
                $salaryAverages = $this->locationSalaryAverages($vacancyIds, (int) $gradeId);
                return [
                    'grade_id' => (int) $gradeId,
                    'title' => $locations->first()->grade_title,
                    'locations' => $locations->map(fn ($location) => [
                        'location_id' => $location->location_id,
                        'title' => $location->title,
                        'count' => $location->count,
                        'percentage' => $gradeVacanciesCount ? round($location->count / $gradeVacanciesCount * 100) : 0,
                        'salary_avg' => $salaryAverages[$location->location_id] ?? null,
                    ])->values()->all(),
                ];
            })
            ->sortBy(fn (array $grade) => $this->gradeSortOrder($grade['title']))
            ->values()
            ->all();

        // Распределение по грейдам
        $gradesDistribution = DB::table('vacancies')
            ->join('qualifications', 'vacancies.qualification_id', '=', 'qualifications.id')
            ->whereIn('vacancies.id', $vacancyIds)
            ->select(
                'qualifications.id as grade_id',
                'qualifications.title',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('qualifications.id', 'qualifications.title')
            ->orderByRaw("
        CASE
            WHEN LOWER(qualifications.title) LIKE '%Intern%' THEN 1
            WHEN LOWER(qualifications.title) LIKE '%Junior%' THEN 2
            WHEN LOWER(qualifications.title) LIKE '%Middle%' THEN 3
            WHEN LOWER(qualifications.title) LIKE '%Senior%' THEN 4
            WHEN LOWER(qualifications.title) LIKE '%Lead%' THEN 5
            ELSE 0
        END
    ")
            ->get()
            ->map(fn ($grade) => [
                'grade_id' => (int) $grade->grade_id,
                'title' => $grade->title,
                'count' => (int) $grade->count,
                'percentage' => (int) round($grade->count / $vacancyIds->count() * 100),
            ])
            ->values()
            ->all();

        // Статистика по формату работы
        $employmentStats = [];

        $vacancies = DB::table('vacancies')
            ->whereIn('id', $vacancyIds)
            ->select('employment', 'remote_work')
            ->get();

        foreach ($vacancies as $vacancy) {
            if ($vacancy->remote_work) {
                $employmentStats['remote'] =
                    ($employmentStats['remote'] ?? 0) + 1;
            }

            $type = $vacancy->employment ?: 'Не указано';

            $employmentStats[$type] =
                ($employmentStats[$type] ?? 0) + 1;
        }

        // Последние шесть месяцев относительно самой свежей публикации.
        // Пустые месяцы также включаем, чтобы была видна сезонность публикаций.
        $publicationRows = DB::table('vacancies')
            ->whereIn('id', $vacancyIds)
            ->whereNotNull('published_at')
            ->selectRaw("DATE_FORMAT(published_at, '%Y-%m-01') as published_date, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(published_at, '%Y-%m-01')")
            ->orderBy('published_date')
            ->get();

        $publicationTimeline = [];
        if ($publicationRows->isNotEmpty()) {
            $countsByDate = $publicationRows->pluck('count', 'published_date');
            $latestMonth = \Illuminate\Support\Carbon::parse($publicationRows->last()->published_date)->startOfMonth();

            $publicationTimeline = collect(range(5, 0))->map(function (int $offset) use ($latestMonth, $countsByDate) {
                $date = $latestMonth->copy()->subMonths($offset)->toDateString();
                return ['date' => $date, 'count' => (int) ($countsByDate[$date] ?? 0)];
            })->all();
        }

        // Количество уникальных компаний (если есть поле company_id)

        // Количество уникальных локаций
        $locationsCount = DB::table('location_vacancy')
            ->whereIn('vacancy_id', $vacancyIds)
            ->distinct('location_id')
            ->count('location_id');

        // Количество уникальных грейдов
        $gradesCount = DB::table('vacancies')
            ->whereIn('id', $vacancyIds)
            ->whereNotNull('qualification_id')
            ->distinct('qualification_id')
            ->count('qualification_id');

        return [
            'locations_count' => $locationsCount,
            'grades_count' => $gradesCount,
            'salary_stats' => [
                'avg_salary' => round($salaryStats->avg_salary ?? 0),
                'min_salary' => round($salaryStats->min_salary ?? 0),
                'max_salary' => round($salaryStats->max_salary ?? 0),
                'by_grade' => $salaryByGrade,
            ],
            'top_skills' => $topSkills,
            'top_skills_by_grade' => $topSkillsByGrade,
            'top_locations' => $topLocations,
            'top_locations_by_grade' => $topLocationsByGrade,
            'grades_distribution' => $gradesDistribution,
            'employment_stats' => $employmentStats,
            'publication_timeline' => $publicationTimeline,
        ];
    }

    private function gradeSortOrder(string $title): int
    {
        $grade = mb_strtolower($title);

        return match (true) {
            str_contains($grade, 'не указано') => 0,
            str_contains($grade, 'intern') => 1,
            str_contains($grade, 'junior') => 2,
            str_contains($grade, 'middle') => 3,
            str_contains($grade, 'senior') => 4,
            str_contains($grade, 'lead') => 5,
            default => 6,
        };
    }

    private function locationSalaryAverages($vacancyIds, ?int $gradeId = null): array
    {
        return DB::table('vacancies')
            ->join('location_vacancy', 'vacancies.id', '=', 'location_vacancy.vacancy_id')
            ->join('salaries', 'vacancies.id', '=', 'salaries.vacancy_id')
            ->whereIn('vacancies.id', $vacancyIds)
            ->where('salaries.currency', 'rur')
            ->where(function ($query) {
                $query->whereNotNull('salaries.from')->orWhereNotNull('salaries.to');
            })
            ->when($gradeId !== null, fn ($query) => $query->where('vacancies.qualification_id', $gradeId))
            ->selectRaw('location_vacancy.location_id, AVG(CASE WHEN salaries.`from` IS NOT NULL AND salaries.`to` IS NOT NULL THEN (salaries.`from` + salaries.`to`) / 2 WHEN salaries.`from` IS NOT NULL THEN salaries.`from` ELSE salaries.`to` END) as salary_avg')
            ->groupBy('location_vacancy.location_id')
            ->pluck('salary_avg', 'location_id')
            ->map(fn ($salary) => (int) round($salary))
            ->all();
    }
}
