<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacancyGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['external_id' => 1, 'title' => 'Разработка', 'description' => 'Создание и поддержка программного обеспечения', 'sort_order' => 1],
            ['external_id' => 2, 'title' => 'Тестирование', 'description' => 'Контроль качества и тестирование продуктов', 'sort_order' => 2],
            ['external_id' => 3, 'title' => 'Администрирование', 'description' => 'Управление IT-инфраструктурой и сетями', 'sort_order' => 3],
            ['external_id' => 4, 'title' => 'Дизайн', 'description' => 'Визуальное оформление и пользовательские интерфейсы', 'sort_order' => 4],
            ['external_id' => 5, 'title' => 'Менеджмент', 'description' => 'Управление проектами, продуктами и командами', 'sort_order' => 5],
            ['external_id' => 6, 'title' => 'Аналитика', 'description' => 'Анализ данных и бизнес-процессов', 'sort_order' => 6],
            ['external_id' => 7, 'title' => 'Поддержка', 'description' => 'Техническая поддержка пользователей', 'sort_order' => 7],
            ['external_id' => 8, 'title' => 'Маркетинг', 'description' => 'Продвижение и реклама продуктов', 'sort_order' => 8],
            ['external_id' => 9, 'title' => 'Продажи', 'description' => 'Продажи и работа с клиентами', 'sort_order' => 9],
            ['external_id' => 10, 'title' => 'Контент', 'description' => 'Создание и управление контентом', 'sort_order' => 10],
            ['external_id' => 11, 'title' => 'HR', 'description' => 'Управление персоналом и подбор', 'sort_order' => 11],
            ['external_id' => 12, 'title' => 'Офис', 'description' => 'Административная поддержка', 'sort_order' => 12],
            ['external_id' => 13, 'title' => 'Информационная безопасность', 'description' => 'Защита данных и систем', 'sort_order' => 13],
            ['external_id' => 14, 'title' => 'Искусственный интеллект', 'description' => 'ML и AI разработка', 'sort_order' => 14],
            ['external_id' => 15, 'title' => 'Зерокодинг', 'description' => 'No-code разработка', 'sort_order' => 15],
            ['external_id' => 49, 'title' => 'Топ-менеджмент', 'description' => 'Стратегическое руководство компанией', 'sort_order' => 16],
            ['external_id' => 50, 'title' => 'Производство', 'description' => 'Промышленное производство и инженерия', 'sort_order' => 17],
        ];

        foreach ($groups as $group) {
            DB::table('vacancy_groups')->updateOrInsert(
                ['external_id' => $group['external_id']],
                [
                    'title' => $group['title'],
                    'description' => $group['description'],
                    'sort_order' => $group['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Добавлено ' . count($groups) . ' групп');
    }
}
