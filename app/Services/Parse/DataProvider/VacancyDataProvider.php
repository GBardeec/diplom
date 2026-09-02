<?php

namespace App\Services\Parse\DataProvider;

use App\Models\VacancyCategory;
use App\Models\Vacancy;
use App\Models\Skill;
use App\Models\Location;
use App\Models\Division;
use App\Models\Qualification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VacancyDataProvider extends AbstractDataProvider
{
    protected string $endpoint = '/api/frontend/vacancies';
    protected ?VacancyCategory $currentCategory = null;

    /**
     * @throws Exception
     */
    public function get(array $additionalParams = []): void
    {
        $this->persist($this->collectAllCategories());
    }

    /** Загружает вакансии по всем категориям, не изменяя БД. */
    public function collectAllCategories(): array
    {
        $categories = VacancyCategory::all();

        if ($categories->isEmpty()) {
            echo "No categories found!\n";
            return [];
        }

        $vacancies = [];
        foreach ($categories as $category) {
            echo "Processing vacancies for category: {$category->title} (ID: {$category->external_id})\n";
            $this->currentCategory = $category;

            $vacancies = array_merge($vacancies, parent::collect([
                's[]' => $category->external_id,
            ]));
        }

        echo 'All categories downloaded: ' . count($vacancies) . " vacancies\n";

        return $vacancies;
    }

    /**
     * Подготовка данных для ОДНОЙ вакансии из ответа API
     */
    protected function prepareData(array $item): array
    {
        // Не обращаемся к БД во время скачивания: квалификация будет создана
        // внутри общей транзакции сохранения.
        $qualificationTitle = $item['qualification'] ??
            ($item['salaryQualification']['title'] ?? 'Не указано');

        return [
            // Основные данные вакансии
            'external_id' => $item['id'],
            'href' => $item['href'],
            'title' => trim($item['title']),
            'vacancy_category_id' => $this->currentCategory?->id,
            'is_marked' => $item['isMarked'] ?? false,
            'remote_work' => $item['remoteWork'] ?? false,
            'employment' => $item['employment'] ?? null,
            'qualification_title' => $qualificationTitle,
            'published_at' => isset($item['publishedDate']['date'])
                ? date('Y-m-d H:i:s', strtotime($item['publishedDate']['date']))
                : null,
            'published_title' => $item['publishedDate']['title'] ?? null,
            'archived' => $item['archived'] ?? false,
            'hidden' => $item['hidden'] ?? false,

            // Связанные данные для many-to-many связей
            'skills' => $this->prepareSkills($item['skills'] ?? []),
            'locations' => $this->prepareLocations($item['locations'] ?? []),
            'divisions' => $this->prepareDivisions($item['divisions'] ?? []),

            // Данные зарплаты
            'salary' => $this->prepareSalary($item['salary'] ?? [], $item['predictedSalary'] ?? null),
        ];
    }

    /**
     * Подготовка навыков
     */
    protected function prepareSkills(array $skills): array
    {
        $preparedSkills = [];
        foreach ($skills as $skill) {
            $preparedSkills[] = [
                'title' => $skill['title'],
                'alias' => $skill['title'],
            ];
        }
        return $preparedSkills;
    }

    /**
     * Подготовка локаций
     */
    protected function prepareLocations(array $locations): array
    {
        $preparedLocations = [];
        foreach ($locations as $location) {
            // Извлекаем external_id из href, если есть
            $externalId = null;
            if (isset($location['href']) && preg_match('/city_id=(\d+)/', $location['href'], $matches)) {
                $externalId = $matches[1];
            }

            $preparedLocations[] = [
                'external_id' => $externalId,
                'title' => $location['title'],
                'href' => $location['href'] ?? null,
            ];
        }
        return $preparedLocations;
    }

    /**
     * Подготовка дивизионов
     */
    protected function prepareDivisions(array $divisions): array
    {
        $preparedDivisions = [];
        foreach ($divisions as $division) {
            $preparedDivisions[] = [
                'title' => $division['title'],
                'href' => $division['href'] ?? null,
            ];
        }
        return $preparedDivisions;
    }

    /**
     * Подготовка данных зарплаты
     */
    protected function prepareSalary(array $salary, ?array $predictedSalary): array
    {
        // Используем predictedSalary если salary пустой
        $salaryData = $salary;
        if (empty($salaryData['from']) && empty($salaryData['to']) && $predictedSalary) {
            $salaryData = $predictedSalary;
        }

        return [
            'from' => $salaryData['from'] ?? null,
            'to' => $salaryData['to'] ?? null,
            'currency' => $salaryData['currency'] ?? null,
            'formatted' => $salaryData['formatted'] ?? null,
        ];
    }

    /**
     * Сохранение БАТЧА вакансий (массив вакансий)
     */
    protected function save(array $dataToSave): void
    {
        // $dataToSave - это массив подготовленных вакансий
        foreach ($dataToSave as $vacancyData) {
            try {
                DB::transaction(function () use ($vacancyData) {
                    // Извлекаем связанные данные
                    $skillsData = $vacancyData['skills'] ?? [];
                    $locationsData = $vacancyData['locations'] ?? [];
                    $divisionsData = $vacancyData['divisions'] ?? [];
                    $salaryData = $vacancyData['salary'] ?? [];

                    // Создаем копию без связанных данных
                    $vacancyDataForSave = $vacancyData;
                    unset($vacancyDataForSave['skills'], $vacancyDataForSave['locations'],
                        $vacancyDataForSave['divisions'], $vacancyDataForSave['salary']);

                    $qualification = Qualification::firstOrCreate([
                        'title' => $vacancyDataForSave['qualification_title'],
                    ]);
                    $vacancyDataForSave['qualification_id'] = $qualification->id;
                    unset($vacancyDataForSave['qualification_title']);

                    // Создаем или обновляем вакансию
                    $vacancy = Vacancy::updateOrCreate(
                        ['external_id' => $vacancyDataForSave['external_id']],
                        $vacancyDataForSave
                    );

                    // Обрабатываем навыки (many-to-many)
                    $this->syncSkills($vacancy, $skillsData);

                    // Обрабатываем локации (many-to-many)
                    $this->syncLocations($vacancy, $locationsData);

                    // Обрабатываем дивизионы (many-to-many)
//                    $this->syncDivisions($vacancy, $divisionsData);

                    // Обрабатываем зарплату (one-to-one)
                    $this->saveSalary($vacancy, $salaryData);
                });

                echo "Saved/Updated vacancy: {$vacancyData['title']} (ID: {$vacancyData['external_id']})\n";

            } catch (Exception $e) {
                Log::error('Failed to save vacancy', [
                    'external_id' => $vacancyData['external_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Синхронизация навыков
     */
    protected function syncSkills(Vacancy $vacancy, array $skills): void
    {
        if (empty($skills)) {
            $vacancy->skills()->sync([]);
            return;
        }

        $skillIds = [];
        foreach ($skills as $skillData) {
            $title = trim($skillData['title']);
            $skill = Skill::query()->firstOrCreate(
                ['title' => $title],
                // В API вакансии нет ID навыка. Нулевой внешний ID означает,
                // что навык будет дополнен при следующей выгрузке справочника.
                ['alias' => $skillData['alias'] ?? $title, 'external_id' => 0]
            );
            $skillIds[] = $skill->id;
        }
        $vacancy->skills()->sync($skillIds);
    }

    /**
     * Синхронизация локаций
     */
    protected function syncLocations(Vacancy $vacancy, array $locations): void
    {
        if (empty($locations)) {
            $vacancy->locations()->sync([]);
            return;
        }

        $locationIds = [];
        foreach ($locations as $locationData) {
            if ($locationData['external_id']) {
                $location = Location::updateOrCreate(
                    ['external_id' => $locationData['external_id']],
                    [
                        'title' => $locationData['title'],
                        'href' => $locationData['href']
                    ]
                );
            } else {
                // Если нет external_id, используем title как уникальный идентификатор
                $location = Location::updateOrCreate(
                    ['title' => $locationData['title']],
                    ['href' => $locationData['href']]
                );
            }
            $locationIds[] = $location->id;
        }
        $vacancy->locations()->sync($locationIds);
    }

    /**
     * Синхронизация дивизионов
     */
    protected function syncDivisions(Vacancy $vacancy, array $divisions): void
    {
        if (empty($divisions)) {
            $vacancy->divisions()->sync([]);
            return;
        }

        $divisionIds = [];
        foreach ($divisions as $divisionData) {
            $division = Division::updateOrCreate(
                ['title' => $divisionData['title']],
                ['href' => $divisionData['href'] ?? null]
            );
            $divisionIds[] = $division->id;
        }
        $vacancy->divisions()->sync($divisionIds);
    }

    /**
     * Сохранение зарплаты
     */
    protected function saveSalary(Vacancy $vacancy, array $salaryData): void
    {
        if (empty($salaryData['from']) && empty($salaryData['to']) && empty($salaryData['formatted'])) {
            // Пустой блок зарплаты в ответе не означает, что прежние данные
            // стали неверными: источник часто не публикует зарплату повторно.
            // Поэтому накопленные сведения не удаляем.
            return;
        }

        $vacancy->salary()->updateOrCreate(
            ['vacancy_id' => $vacancy->id],
            $salaryData
        );
    }

}
