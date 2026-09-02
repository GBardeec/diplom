<?php

namespace App\Services\Parse\DataProvider;

use App\Models\Skill;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillsDataProvider extends AbstractDataProvider
{
    protected string $endpoint = '/api/frontend/suggestions/skills';

    protected function prepareData(array $item): array
    {
        return [
            'external_id' => $item['value'],
            'title' => $item['title'],
            'alias' => $item['alias'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function save(array $dataToSave): void
    {
        try {
            foreach ($dataToSave as $skillData) {
                // external_id в исходной схеме не имеет уникального ограничения,
                // поэтому upsert MySQL мог добавлять дубликаты. Ищем сначала по
                // ID источника, затем по названию, и обновляем одну запись.
                $skill = Skill::query()
                    ->where('external_id', $skillData['external_id'])
                    ->first()
                    ?? Skill::query()
                        ->where('title', $skillData['title'])
                        ->first();

                if ($skill) {
                    $skill->update([
                        'external_id' => $skillData['external_id'],
                        'title' => $skillData['title'],
                        'alias' => $skillData['alias'],
                    ]);
                    continue;
                }

                Skill::query()->create($skillData);
            }
        } catch (Exception $e) {
            Log::error('Skills save failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
