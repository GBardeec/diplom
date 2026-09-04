<?php

namespace App\Services;

use App\Models\Qualification;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Builder;

class RecommendationService
{
    public function recommend(array $filters): array
    {
        $skills = $filters['skills'] ?? [];
        $vacancies = $this->marketQuery($filters, $skills)->limit(500)->get();
        $qualifications = Qualification::orderBy('id')->get();
        $current = $this->currentLevel($filters, $vacancies, $qualifications);
        $opportunities = $this->opportunities($vacancies, $skills);
        $gaps = $this->skillGaps($vacancies, $skills);
        $next = $qualifications
            ->filter(fn ($item) => $this->levelRank($item) > $this->levelRank($current))
            ->sortBy(fn ($item) => $this->levelRank($item))
            ->first();
        $assessment = $this->assessment($filters, $current);
        $readiness = $this->readiness($filters, $next, $skills);

        return [
            'profile' => [
                'current_level' => $current->title,
                'basis' => 'Оценка по опыту и уровню самостоятельности',
                'skills_count' => count($skills),
            ],
            'opportunities' => $opportunities,
            'growth' => [
                'next_level' => $next?->title,
                'skills_to_build' => $gaps,
            ],
            'assessment' => $assessment,
            'readiness' => $readiness,
            'portfolio_evidence' => $this->portfolioEvidence($next, $gaps),
            'roadmap' => [
                ['title' => 'Закрепить текущий уровень', 'text' => 'Соберите примеры задач и проектов, которые подтверждают выбранные навыки.'],
                ['title' => 'Закрыть дефицит навыков', 'text' => $gaps ? 'Начните с: '.implode(', ', array_slice($gaps, 0, 3)).'.' : 'Продолжайте углублять выбранную специализацию.'],
                ['title' => 'Подготовить следующий переход', 'text' => $next ? "Ориентир следующего шага — {$next->title}." : 'Вы уже на верхнем доступном уровне модели.'],
            ],
            'meta' => ['total' => $vacancies->count(), 'message' => $skills ? 'Профиль составлен по вашим навыкам и данным вакансий.' : 'Добавьте навыки, чтобы сделать профиль точнее.'],
        ];
    }

    private function marketQuery(array $filters, array $skills): Builder
    {
        $query = Vacancy::query()->where('archived', false)->where('hidden', false)
            ->with(['category.group', 'qualification', 'salary', 'skills']);
        if (!empty($filters['category_id'])) $query->where('vacancy_category_id', $filters['category_id']);
        elseif (!empty($filters['group_id'])) $query->whereHas('category', fn (Builder $q) => $q->where('group_id', $filters['group_id']));
        if ($skills) $query->whereHas('skills', fn (Builder $q) => $q->whereIn('skills.id', $skills));
        return $query;
    }

    private function currentLevel(array $filters, $vacancies, $qualifications): Qualification
    {
        if (($filters['commercial_experience'] ?? null) === 'none') {
            return $qualifications->first(fn ($item) => strcasecmp($item->title, 'Intern') === 0) ?? $qualifications->first();
        }

        if (!empty($filters['grade_answers'])) {
            $average = array_sum($filters['grade_answers']) / count($filters['grade_answers']);
            $title = match (true) {
                $average < 0.8 => 'Junior',
                $average < 1.8 => 'Middle',
                $average < 2.7 => 'Senior',
                default => 'Lead',
            };

            return $qualifications->first(fn ($item) => strcasecmp($item->title, $title) === 0) ?? $qualifications->first();
        }

        if (!empty($filters['qualification_id']) && ($chosen = $qualifications->firstWhere('id', $filters['qualification_id']))) return $chosen;
        if (empty($filters['skills'])) return $qualifications->first();
        $bestId = $vacancies->groupBy('qualification_id')->sortByDesc(fn ($items) => $items->count())->keys()->first();
        return $qualifications->firstWhere('id', $bestId) ?? $qualifications->first();
    }

    private function levelRank(Qualification $qualification): int
    {
        return match (strtolower($qualification->title)) {
            'intern' => 1,
            'junior' => 2,
            'middle' => 3,
            'senior' => 4,
            'lead', 'head' => 5,
            default => 0,
        };
    }

    private function assessment(array $filters, Qualification $current): array
    {
        if (($filters['commercial_experience'] ?? null) === 'none') {
            return [
                'summary' => 'Вы на старте карьерного пути. Для первой роли важнее показать базу, практику и завершённые проекты.',
                'strengths' => ['Вы уже определили направление развития.', 'Готовы собрать профиль навыков для первой роли.'],
                'focus' => ['Закрепить базовые инструменты на учебном проекте.', 'Подготовить портфолио из одного-двух законченных работ.'],
            ];
        }

        $answers = $filters['grade_answers'] ?? [];
        $dimensions = [
            ['label' => 'Самостоятельность', 'strong' => 'самостоятельно ведёте задачи', 'focus' => 'брать больше самостоятельных задач'],
            ['label' => 'Ответственность', 'strong' => 'отвечаете за результат, а не только за процесс', 'focus' => 'расширять зону ответственности'],
            ['label' => 'Решения', 'strong' => 'умеете оценивать варианты и риски', 'focus' => 'обосновывать технические решения'],
            ['label' => 'Взаимодействие', 'strong' => 'помогаете коллегам и делитесь опытом', 'focus' => 'обсуждать решения и помогать коллегам'],
            ['label' => 'Влияние', 'strong' => 'влияете на подходы команды', 'focus' => 'предлагать улучшения процессов и подходов'],
        ];
        $strengths = [];
        $focus = [];

        foreach ($dimensions as $index => $dimension) {
            $answer = $answers[$index] ?? 0;
            if ($answer >= 2) $strengths[] = ucfirst($dimension['strong']).'.';
            if ($answer <= 1) $focus[] = ucfirst($dimension['focus']).'.';
        }

        return [
            'summary' => "По ответам о самостоятельности и ответственности вы ближе к уровню {$current->title}.",
            'strengths' => array_slice($strengths ?: ['У вас есть опыт, на который можно опереться при следующем переходе.'], 0, 3),
            'focus' => array_slice($focus ?: ['Продолжайте расширять влияние на решения и результат команды.'], 0, 3),
        ];
    }

    private function readiness(array $filters, ?Qualification $next, array $skillIds): array
    {
        if (!$next) return ['status' => 'Верхний уровень', 'summary' => 'В модели нет следующей ступени для сравнения.', 'matched' => 0, 'total' => 0];

        $vacancies = $this->marketQuery($filters, [])->where('qualification_id', $next->id)->limit(500)->get();
        $marketSkills = $vacancies->flatMap->skills->unique('id');
        $matched = $marketSkills->whereIn('id', $skillIds)->count();
        $total = $marketSkills->count();
        $ratio = $total ? $matched / $total : 0;
        $status = $ratio >= .6 ? 'Высокая' : ($ratio >= .3 ? 'Средняя' : 'Начальная');

        return [
            'status' => $status,
            'matched' => $matched,
            'total' => $total,
            'summary' => $total
                ? "Для уровня {$next->title} у вас совпадает {$matched} из {$total} наиболее частых навыков в подходящих вакансиях."
                : "Недостаточно вакансий уровня {$next->title}, чтобы оценить готовность по рынку.",
        ];
    }

    private function portfolioEvidence(?Qualification $next, array $gaps): array
    {
        $role = $next?->title ? "для уровня {$next->title}" : 'для следующей роли';
        $skills = implode(', ', array_slice($gaps, 0, 3));

        return [
            ['title' => 'Завершённый проект', 'text' => "Соберите небольшой, но законченный проект {$role}".($skills ? ", где примените {$skills}." : '.')],
            ['title' => 'Понятное описание решений', 'text' => 'Добавьте README: цель проекта, запуск, архитектура и ключевые технические решения.'],
            ['title' => 'Примеры опыта', 'text' => 'Подготовьте 2–3 истории: задача, ваше решение, результат и чему вы научились.'],
        ];
    }

    private function opportunities($vacancies, array $skillIds): array
    {
        return $vacancies->filter(fn ($item) => $item->category)
            ->groupBy('vacancy_category_id')->map(function ($items) use ($skillIds) {
                $sample = $items->first();
                $matched = $items->flatMap->skills->whereIn('id', $skillIds)->unique('id')->count();
                $marketSkills = $items->flatMap->skills->unique('id')->count();
                $salary = $items->pluck('salary')->filter()->map(fn ($item) => $item->from ?: $item->to)->filter()->avg();
                return ['category_id' => $sample->category->id, 'title' => $sample->category->title, 'group' => $sample->category->group?->title, 'fit' => $marketSkills ? min(100, round($matched / min($marketSkills, max(1, count($skillIds))) * 100)) : 0, 'vacancies_count' => $items->count(), 'salary' => $salary ? round($salary) : null];
            })->sortByDesc('fit')->take(3)->values()->all();
    }

    private function skillGaps($vacancies, array $skillIds): array
    {
        return $vacancies->flatMap->skills->reject(fn ($skill) => in_array($skill->id, $skillIds, true))
            ->countBy('id')->sortDesc()->take(5)->keys()->map(fn ($id) => optional($vacancies->flatMap->skills->firstWhere('id', $id))->title)->filter()->values()->all();
    }
}
