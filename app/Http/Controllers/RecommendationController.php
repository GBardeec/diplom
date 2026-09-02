<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Qualification;
use App\Models\Skill;
use App\Models\VacancyCategory;
use App\Models\VacancyGroup;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    public function index()
    {
        return $this->page();
    }

    public function show(string $reportUuid)
    {
        return $this->page($reportUuid);
    }

    private function page(?string $reportUuid = null)
    {
        return Inertia::render('Recommendations/Index', [
            'skills' => Skill::orderBy('title')->get(['id', 'title']),
            'groups' => VacancyGroup::orderBy('sort_order')->get(['id', 'title']),
            'categories' => VacancyCategory::orderBy('title')->get(['id', 'group_id', 'parent_id', 'title']),
            'qualifications' => Qualification::orderBy('id')->get(['id', 'title']),
            'locations' => Location::orderBy('title')->get(['id', 'title']),
            'reportUuid' => $reportUuid,
        ]);
    }
}
