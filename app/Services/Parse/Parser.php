<?php

namespace App\Services\Parse;

use App\Services\Parse\DataProvider\SkillsDataProvider;
use App\Services\Parse\DataProvider\VacancyDataProvider;
use App\Services\Parse\Interfaces\ParserInterface;
use Illuminate\Support\Facades\DB;

class Parser implements ParserInterface
{
    public function __construct(protected SkillsDataProvider $skillsDataProvider, protected VacancyDataProvider $vacancyDataProvider)
    {

    }

    public function handle()
    {
        // Сначала полностью скачиваем данные. Сбой API до сохранения не
        // затрагивает БД.
        $skills = $this->skillsDataProvider->collect();
        $vacancies = $this->vacancyDataProvider->collectAllCategories();

        DB::transaction(function () use ($skills, $vacancies) {
            $this->skillsDataProvider->persist($skills);
            $this->vacancyDataProvider->persist($vacancies);
        }, 3);
    }
}
