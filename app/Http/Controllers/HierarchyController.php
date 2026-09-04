<?php

namespace App\Http\Controllers;

use App\Models\VacancyCategory;
use App\Models\VacancyGroup;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HierarchyController extends Controller
{
    public function index()
    {
        $categories = VacancyCategory::with(['parent'])
            ->withCount('vacancies')
            ->orderBy('level')
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
                    'parent_id' => $category->parent_id,
                    'group_id' => $category->group_id,
                    'sort_order' => $category->sort_order,
                    'vacancies_count' => $category->vacancies_count,
                    'locations_count' => $stats['locations_count'],
                    'grades_count' => $stats['grades_count'],
                    'salary_stats' => $stats['salary_stats'],
                    'top_skills' => $stats['top_skills'],
                    'top_locations' => $stats['top_locations'],
                'grades_distribution' => $stats['grades_distribution'],
                'employment_stats' => $stats['employment_stats'],
                'publication_timeline' => $stats['publication_timeline'],
                ];
            });

        $groups = VacancyGroup::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'external_id' => $group->external_id,
                'title' => $group->title,
                'description' => $group->description,
            ];
        });

        return Inertia::render('Hierarchy/Index', [
            'categories' => $categories,
            'groups' => $groups,
        ]);
    }

    private function getVacancyStats($categoryId)
    {
        // Получаем ID всех вакансий категории
        $vacancyIds = DB::table('vacancies')
            ->where('vacancy_category_id', $categoryId)
            ->where('archived', false)
            ->where('hidden', false)
            ->pluck('id');

        if ($vacancyIds->isEmpty()) {
            return [
                'locations_count' => 0,
                'grades_count' => 0,
                'salary_stats' => null,
                'top_skills' => [],
                'top_locations' => [],
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
            ->map(function ($skill) use ($vacancyIds) {
                $skill->percentage = round(($skill->count / $vacancyIds->count()) * 100);
                return $skill;
            });

        // Топ локации
        $topLocations = DB::table('location_vacancy')
            ->join('locations', 'location_vacancy.location_id', '=', 'locations.id')
            ->whereIn('location_vacancy.vacancy_id', $vacancyIds)
            ->select('locations.id as location_id', 'locations.title', DB::raw('COUNT(*) as count'))
            ->groupBy('locations.id', 'locations.title')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($location) use ($vacancyIds) {
                $location->percentage = round(($location->count / $vacancyIds->count()) * 100);
                return $location;
            });

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
            ->map(function ($grade) use ($vacancyIds) {
                $grade->percentage = round(($grade->count / $vacancyIds->count()) * 100);
                return $grade;
            });

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

        // Последние 14 календарных дней относительно самой свежей публикации.
        // Пустые дни также включаем, чтобы график показывал реальную динамику.
        $publicationRows = DB::table('vacancies')
            ->whereIn('id', $vacancyIds)
            ->whereNotNull('published_at')
            ->selectRaw('DATE(published_at) as published_date, COUNT(*) as count')
            ->groupByRaw('DATE(published_at)')
            ->orderBy('published_date')
            ->get();

        $publicationTimeline = [];
        if ($publicationRows->isNotEmpty()) {
            $countsByDate = $publicationRows->pluck('count', 'published_date');
            $latestDate = \Illuminate\Support\Carbon::parse($publicationRows->last()->published_date);

            $publicationTimeline = collect(range(13, 0))->map(function (int $offset) use ($latestDate, $countsByDate) {
                $date = $latestDate->copy()->subDays($offset)->toDateString();
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
            'top_locations' => $topLocations,
            'grades_distribution' => $gradesDistribution,
            'employment_stats' => $employmentStats,
            'publication_timeline' => $publicationTimeline,
        ];
    }}
