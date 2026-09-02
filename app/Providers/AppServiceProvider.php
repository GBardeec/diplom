<?php

namespace App\Providers;

use app\Services\Parse\DataProvider\AbstractDataProvider;
use App\Services\Parse\DataProvider\SkillsDataProvider;
use App\Services\Parse\DataProvider\VacancyDataProvider;
use App\Services\Parse\Interfaces\ParserInterface;
use App\Services\Parse\Parser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ParserInterface::class, Parser::class);

        $this->app->singleton(SkillsDataProvider::class, function ($app) {
            $client = new Client([
                'base_uri' => 'https://career.habr.com',
            ]);

            return new SkillsDataProvider($client);
        });

        $this->app->singleton(VacancyDataProvider::class, function ($app) {
            $client = new Client([
                'base_uri' => 'https://career.habr.com',
            ]);

            return new VacancyDataProvider($client);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
