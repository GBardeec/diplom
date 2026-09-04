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
        $next = $qualifications
            ->filter(fn ($item) => $this->levelRank($item) > $this->levelRank($current))
            ->sortBy(fn ($item) => $this->levelRank($item))
            ->first();
        $nextVacancies = $next
            ? $this->marketQuery($filters, [])->where('qualification_id', $next->id)->limit(500)->get()
            : collect();
        $opportunities = $this->opportunities($vacancies, $skills);
        $gaps = $this->skillGaps($nextVacancies, $skills);
        $assessment = $this->assessment($filters, $current);

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
            'portfolio_evidence' => $this->portfolioEvidence($current, $next, $gaps),
            'roadmap' => $this->roadmap($current, $next, $gaps),
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

    private function portfolioEvidence(Qualification $current, ?Qualification $next, array $gaps): array
    {
        $skills = implode(', ', array_column(array_slice($gaps, 0, 3), 'title'));
        $level = strtolower($current->title);

        if ($level === 'intern') {
            return [
                ['title' => 'Базовый проект', 'text' => 'Соберите один законченный проект по выбранному направлению, а не набор разрозненных учебных упражнений.'],
                ['title' => 'Понятный код и запуск', 'text' => 'Добавьте README: задача проекта, стек, запуск и несколько скриншотов или примеров работы.'],
                ['title' => 'Готовность к стажировке', 'text' => 'Подготовьте рассказ о том, чему научились, где застревали и как нашли решение.'],
            ];
        }

        if ($level === 'junior') {
            return [
                ['title' => 'Задача в реальном контексте', 'text' => 'Покажите функцию или мини-проект с понятными требованиями, ограничениями и результатом.'],
                ['title' => 'Качество работы', 'text' => 'Подтвердите базовую инженерную культуру: тесты, читаемый код, документация и работа с обратной связью.'],
                ['title' => 'Рост самостоятельности', 'text' => 'Подготовьте пример задачи, которую вы сначала разобрали, а затем довели до результата с минимальной поддержкой.'],
            ];
        }

        if ($level === 'middle') {
            return [
                ['title' => 'Сложная задача целиком', 'text' => 'Опишите задачу, которую довели от постановки до результата: ограничения, решения и измеримый эффект.'],
                ['title' => 'Техническое решение', 'text' => 'Покажите пример выбора между вариантами: что сравнивали, почему выбрали подход и какие риски учли.'],
                ['title' => 'Влияние на команду', 'text' => 'Подготовьте пример: код-ревью, помощь коллеге, улучшение процесса или договорённости команды.'],
            ];
        }

        if ($level === 'senior') {
            return [
                ['title' => 'Решение для продукта', 'text' => 'Покажите, как ваше решение повлияло на качество, скорость разработки, надёжность или бизнес-результат.'],
                ['title' => 'Архитектурный компромисс', 'text' => 'Опишите значимое техническое решение, альтернативы и последствия выбранного подхода.'],
                ['title' => 'Техническое лидерство', 'text' => 'Подготовьте пример наставничества, выстраивания технических договорённостей или развития коллег.'],
            ];
        }

        if (in_array($level, ['lead', 'head'], true)) {
            return [
                ['title' => 'Результат команды', 'text' => 'Покажите, как команда достигла результата: цель, приоритеты, риски и измеримый итог.'],
                ['title' => 'Развитие людей', 'text' => 'Подготовьте пример найма, наставничества, обратной связи или роста сотрудника.'],
                ['title' => 'Управленческое решение', 'text' => 'Опишите, как вы распределили работу, сняли блокер или изменили процесс ради результата команды.'],
            ];
        }

        return [
            ['title' => 'Завершённый проект', 'text' => 'Соберите небольшой, но законченный проект'.($skills ? ", где примените {$skills}." : '.')],
            ['title' => 'Понятное описание решений', 'text' => 'Добавьте README: цель проекта, запуск, архитектура и ключевые технические решения.'],
            ['title' => 'Примеры опыта', 'text' => 'Подготовьте 2-3 истории: задача, ваше решение, результат и чему вы научились.'],
        ];
    }

    private function roadmap(Qualification $current, ?Qualification $next, array $gaps): array
    {
        $skills = implode(', ', array_column(array_slice($gaps, 0, 3), 'title'));
        $nextTitle = $next?->title;

        return match (strtolower($current->title)) {
            'intern' => [
                ['title' => 'Собрать базу', 'text' => $skills ? "Освойте на практике: {$skills}." : 'Закрепите базовые инструменты выбранного направления.'],
                ['title' => 'Сделать законченный проект', 'text' => 'Доведите один проект до результата и оформите его так, чтобы его можно было показать работодателю.'],
                ['title' => 'Выйти на первую роль', 'text' => $nextTitle ? "Откликайтесь на стажировки и позиции {$nextTitle}, опираясь на проект и понятный рассказ о нём." : 'Начните искать первую практику или стажировку.'],
            ],
            'junior' => [
                ['title' => 'Укрепить самостоятельность', 'text' => 'Берите задачи с понятным результатом, сначала формулируйте план решения, затем запрашивайте точечную обратную связь.'],
                ['title' => 'Закрыть важные пробелы', 'text' => $skills ? "В первую очередь разберите: {$skills}." : 'Углубите ключевые инструменты своего направления.'],
                ['title' => 'Подготовиться к Middle', 'text' => $nextTitle ? "Соберите 2-3 примера задач, которые вы самостоятельно довели до результата для перехода к {$nextTitle}." : 'Соберите примеры самостоятельной работы.'],
            ],
            'middle' => [
                ['title' => 'Расширить ответственность', 'text' => 'Возьмите сложную задачу или часть продукта целиком: от уточнения требований до результата и поддержки после запуска.'],
                ['title' => 'Усилить инженерные решения', 'text' => $skills ? "Примените в реальной задаче: {$skills}." : 'Тренируйтесь сравнивать варианты решений, учитывать риски и фиксировать договорённости.'],
                ['title' => 'Подготовиться к Senior', 'text' => $nextTitle ? "Покажите влияние шире одной задачи: технические решения, помощь коллегам и улучшение процесса - это основа перехода к {$nextTitle}." : 'Покажите влияние шире одной задачи.'],
            ],
            'senior' => [
                ['title' => 'Вести направление', 'text' => 'Выберите техническую или продуктовую область и отвечайте за её результат, а не только за отдельные задачи.'],
                ['title' => 'Умножать эффект команды', 'text' => 'Создавайте договорённости, развивайте коллег и устраняйте системные причины повторяющихся проблем.'],
                ['title' => 'Подготовиться к лидерству', 'text' => $nextTitle ? "Если интересен переход к {$nextTitle}, возьмите часть планирования, приоритизации и координации людей." : 'Сфокусируйтесь на устойчивом техническом лидерстве.'],
            ],
            'lead', 'head' => [
                ['title' => 'Уточнить фокус команды', 'text' => 'Свяжите цели команды с продуктовым результатом, определите приоритеты и критерии успеха.'],
                ['title' => 'Развивать людей и процесс', 'text' => 'Регулярно работайте с обратной связью, ростом сотрудников, наймом и устранением организационных блокеров.'],
                ['title' => 'Масштабировать влияние', 'text' => 'Улучшайте взаимодействие между командами и создавайте условия, при которых результат не зависит от одного человека.'],
            ],
            default => [
                ['title' => 'Закрепить текущий уровень', 'text' => 'Соберите примеры задач и проектов, которые подтверждают ваш опыт.'],
                ['title' => 'Закрыть дефицит навыков', 'text' => $skills ? "Начните с: {$skills}." : 'Продолжайте углублять выбранную специализацию.'],
                ['title' => 'Подготовить следующий переход', 'text' => $nextTitle ? "Ориентир следующего шага - {$nextTitle}." : 'Вы уже на верхнем доступном уровне модели.'],
            ],
        };
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
        $total = $vacancies->count();
        if (!$total) return [];

        return $vacancies->flatMap->skills
            ->reject(fn ($skill) => in_array($skill->id, $skillIds, true))
            ->groupBy('id')
            ->sortByDesc(fn ($items) => $items->count())
            ->take(5)
            ->map(fn ($items) => [
                'title' => $items->first()->title,
                'percent' => (int) round($items->count() / $total * 100),
                'vacancies_count' => $items->count(),
            ])
            ->values()
            ->all();
    }
}
