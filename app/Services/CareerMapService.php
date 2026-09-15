<?php

namespace App\Services;

use App\Models\VacancyCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CareerMapService
{
    private const MIN_SALARY_SAMPLE = 3;
    private const MIN_SKILLS_SIMILARITY = 0.20;

    /**
     * Rebuilds the market career map from the active vacancy data.
     * Manual parent_id and level fields are intentionally not changed, so the
     * migration can be rolled back without losing the original scheme.
     */
    public function rebuild(): void
    {
        DB::transaction(function () {
            $categories = VacancyCategory::query()->get(['id', 'group_id']);
            $salaryValues = $this->salaryValues();

            VacancyCategory::query()->update([
                'market_level' => null,
                'market_salary_median' => null,
                'market_salary_sample_size' => 0,
            ]);

            $marketCategories = $this->assignMarketLevels($categories, $salaryValues);
            $this->storeTransitions($marketCategories);
        });

        HierarchyCache::forget();
    }

    private function salaryValues(): Collection
    {
        return DB::table('vacancies')
            ->join('vacancy_category_vacancy', 'vacancy_category_vacancy.vacancy_id', '=', 'vacancies.id')
            ->join('salaries', 'salaries.vacancy_id', '=', 'vacancies.id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->where('salaries.currency', 'rur')
            ->where(function ($query) {
                $query->whereNotNull('salaries.from')->orWhereNotNull('salaries.to');
            })
            ->selectRaw('vacancy_category_vacancy.vacancy_category_id as category_id, CASE WHEN salaries.`from` IS NOT NULL AND salaries.`to` IS NOT NULL THEN (salaries.`from` + salaries.`to`) / 2 WHEN salaries.`from` IS NOT NULL THEN salaries.`from` ELSE salaries.`to` END as value')
            ->get()
            ->groupBy('category_id')
            ->map(fn(Collection $items) => $items->pluck('value')->map(fn($value) => (int)$value)->sort()->values());
    }

    private function assignMarketLevels(Collection $categories, Collection $salaryValues): Collection
    {
        $stats = $categories->map(function (VacancyCategory $category) use ($salaryValues) {
            $values = $salaryValues->get($category->id, collect());
            $sampleSize = $values->count();

            return [
                'id' => $category->id,
                'group_id' => $category->group_id,
                'sample_size' => $sampleSize,
                'median' => $sampleSize >= self::MIN_SALARY_SAMPLE ? (int)round($this->median($values)) : null,
            ];
        })->filter(fn(array $item) => $item['median'] !== null);

        $marketCategories = collect();
        $stats->groupBy('group_id')->each(function (Collection $groupItems) use (&$marketCategories) {
            $ordered = $groupItems->sortBy('median')->values();
            $lastIndex = max(1, $ordered->count() - 1);

            $ordered->each(function (array $item, int $index) use ($lastIndex, &$marketCategories) {
                $level = $lastIndex <= 4
                    ? $index + 1
                    : (int)floor($index / $lastIndex * 4) + 1;
                VacancyCategory::query()->whereKey($item['id'])->update([
                    'market_level' => $level,
                    'market_salary_median' => $item['median'],
                    'market_salary_sample_size' => $item['sample_size'],
                ]);
                $item['market_level'] = $level;
                $item['market_salary_median'] = $item['median'];
                $marketCategories->push($item);
            });
        });

        return $marketCategories;
    }

    private function storeTransitions(Collection $marketCategories): void
    {
        DB::table('career_transitions')->delete();

        $skillsByCategory = DB::table('vacancies')
            ->join('vacancy_category_vacancy', 'vacancy_category_vacancy.vacancy_id', '=', 'vacancies.id')
            ->join('skill_vacancy', 'skill_vacancy.vacancy_id', '=', 'vacancies.id')
            ->where('vacancies.archived', false)
            ->where('vacancies.hidden', false)
            ->select('vacancy_category_vacancy.vacancy_category_id as category_id', 'skill_vacancy.skill_id')
            ->distinct()
            ->get()
            ->groupBy('category_id')
            ->map(fn(Collection $items) => $items->pluck('skill_id')->map(fn($id) => (int)$id)->all());

        $rows = [];
        $marketCategories->groupBy('group_id')->each(function (Collection $groupItems) use ($skillsByCategory, &$rows) {
            $groupItems->each(function (array $source) use ($groupItems, $skillsByCategory, &$rows) {
                $sourceSkills = $skillsByCategory->get($source['id'], []);
                if (!$sourceSkills) {
                    return;
                }

                $candidates = $groupItems
                    ->filter(fn(array $target) => $target['market_level'] === $source['market_level'] + 1)
                    ->map(function (array $target) use ($sourceSkills, $skillsByCategory) {
                        $targetSkills = $skillsByCategory->get($target['id'], []);
                        $intersection = count(array_intersect($sourceSkills, $targetSkills));
                        $union = count(array_unique(array_merge($sourceSkills, $targetSkills)));
                        $target['similarity'] = $union ? $intersection / $union : 0;
                        return $target;
                    })
                    ->filter(fn(array $target) => $target['similarity'] >= self::MIN_SKILLS_SIMILARITY)
                    ->sortBy([
                        ['market_level', 'asc'],
                        ['similarity', 'desc'],
                        ['market_salary_median', 'asc'],
                    ])
                    ->take(2);

                foreach ($candidates as $target) {
                    $rows[] = [
                        'from_category_id' => $source['id'],
                        'to_category_id' => $target['id'],
                        'skills_similarity' => round($target['similarity'], 4),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            });
        });

        if ($rows) {
            DB::table('career_transitions')->insert($rows);
        }
    }

    private function median(Collection $values): float
    {
        $count = $values->count();
        $middle = intdiv($count, 2);

        return $count % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
    }
}
