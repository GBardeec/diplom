<?php

namespace App\Console\Commands;

use App\Enums\HabrCategory;
use App\Services\Parse\Interfaces\ParserInterface;
use GuzzleHttp\Client;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:test')]
#[Description('Полная атомарная выгрузка вакансий и навыков из API Хабр Карьеры без очистки базы')]
class Test extends Command
{
    public function handle(ParserInterface $parser)
    {
        $this->info('Сначала загружаю все данные, затем сохраню их одной транзакцией…');
        $parser->handle();
        $this->info('Импорт завершён. Новые данные добавлены, существующие обновлены, прежние не удалялись.');

//        $page = 1;
//        $allSkills = [];
//        $client = new Client();
//
//
//        while (true) {
//            $response = $client->get('https://career.habr.com/api/frontend/suggestions/skills', [
//                'query' => [
//                    'page' => $page,
//                    'per_page' => 50,
//                ]
//            ]);
//
//            $data = json_decode($response->getBody(), true);
//            $skills = $data['list'] ?? [];
//
//            if (empty($skills)) break;
//
//            $allSkills = array_merge($allSkills, $skills);
//            dd($allSkills);
//            $page++;
//        }
//
//        print_r($allSkills);
//
//
//
//
//
//
//
//        $category = HabrCategory::BACKEND_DEVELOPER;
//
//        $this->info('🔍 Поиск вакансий по категории: ' . $category->label());
//        $this->info('🏷️ ID категории: ' . $category->value);
//        $this->newLine();
//
//        $client = new Client();
//
//        $response = $client->get('https://career.habr.com/api/frontend/vacancies', [
//            'query' => [
//                's[]' => $category->value,
//                'page' => 1,
//                'per_page' => 10,
//                'sort' => 'date',
//            ],
//            'headers' => [
//                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
//                'Accept' => 'application/json',
//            ]
//        ]);
//
//        $data = json_decode($response->getBody(), true);
//        $vacancies = $data['list'] ?? [];
//        dd($vacancies);
//
//        $client = new Client();
//
//        $response = $client->get('https://career.habr.com/api/frontend/vacancies', [
//            'query' => [
//                'q' => 'php',
//                'sort' => 'relevance',
//                'type' => 'all',
//                'currency' => 'RUR',
//                'page' => 1,
//                'per_page' => 20
//            ]
//        ]);
//
//        $data = json_decode($response->getBody(), true);
//    dd($data);
    }
}
