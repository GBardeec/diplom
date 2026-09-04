<?php

namespace Database\Seeders;

use App\Enums\HabrCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateVacancyCategoriesHierarchySeeder extends Seeder
{
    private array $idByExternalId = [];

    public function run(): void
    {
        // Загружаем существующие ID
        $categories = DB::table('vacancy_categories')->get();
        foreach ($categories as $cat) {
            $this->idByExternalId[$cat->external_id] = $cat->id;
        }

        $updates = $this->getHierarchyUpdates();

        foreach ($updates as $externalId => $data) {
            DB::table('vacancy_categories')
                ->where('external_id', $externalId)
                ->update([
                    'parent_id' => $data['parent_id'],
                    'level' => $data['level'],
                    'sort_order' => $data['sort_order'],
                    'updated_at' => now(),
                ]);
        }

        $this->command->info('✅ Обновлена иерархия ' . count($updates) . ' категорий');
    }

    private function getParentId(?HabrCategory $parent): ?int
    {
        if ($parent === null) {
            return null;
        }
        return $this->idByExternalId[$parent->value] ?? null;
    }

    private function getHierarchyUpdates(): array
    {
        return [
            // ==========================================
            // РАЗРАБОТКА (сохраняем вашу логику)
            // ==========================================
            HabrCategory::SOFTWARE_ARCHITECT->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 1,
            ],
            HabrCategory::DATABASE_ARCHITECT->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 2,
            ],
            HabrCategory::C1_ARCHITECT->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 3,
            ],

            // Ведущие специалисты (уровень 1)
            HabrCategory::SYSTEM_ENGINEER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::RELEASE_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 1,
                'sort_order' => 2,
            ],
            HabrCategory::DATABASE_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATABASE_ARCHITECT),
                'level' => 1,
                'sort_order' => 3,
            ],
            HabrCategory::C1_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::C1_ARCHITECT),
                'level' => 1,
                'sort_order' => 4,
            ],

            // Разработчики (уровень 2)
            HabrCategory::BACKEND_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::FRONTEND_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 2,
            ],
            HabrCategory::FULLSTACK_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 3,
            ],
            HabrCategory::MOBILE_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 4,
            ],
            HabrCategory::GAME_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 5,
            ],
            HabrCategory::DESKTOP_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 6,
            ],
            HabrCategory::ERP_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SOFTWARE_ARCHITECT),
                'level' => 2,
                'sort_order' => 7,
            ],
            HabrCategory::EMBEDDED_ENGINEER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SYSTEM_ENGINEER),
                'level' => 2,
                'sort_order' => 8,
            ],
            HabrCategory::HARDWARE_ENGINEER->value => [
                'parent_id' => $this->getParentId(HabrCategory::SYSTEM_ENGINEER),
                'level' => 2,
                'sort_order' => 9,
            ],

            // Узкие специалисты (уровень 3)
            HabrCategory::WEB_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::BACKEND_DEVELOPER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::APPLICATION_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::BACKEND_DEVELOPER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::HTML_CODER->value => [
                'parent_id' => $this->getParentId(HabrCategory::FRONTEND_DEVELOPER),
                'level' => 3,
                'sort_order' => 3,
            ],

            // ==========================================
            // ТЕСТИРОВАНИЕ
            // ==========================================
            HabrCategory::QA_DIRECTOR->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 1,
            ],
            HabrCategory::QA_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_DIRECTOR),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::QA_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_MANAGER),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::QA_ENGINEER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_MANAGER),
                'level' => 2,
                'sort_order' => 2,
            ],
            HabrCategory::AUTO_TESTER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_ENGINEER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::MANUAL_TESTER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_ENGINEER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::PERFORMANCE_TESTER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_ENGINEER),
                'level' => 3,
                'sort_order' => 3,
            ],
            HabrCategory::UX_TESTER->value => [
                'parent_id' => $this->getParentId(HabrCategory::QA_ENGINEER),
                'level' => 3,
                'sort_order' => 4,
            ],
            HabrCategory::QA_OTHER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 99,
            ],

            // ==========================================
            // АНАЛИТИКА
            // ==========================================
            HabrCategory::SYSTEMS_ANALYST->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 1,
            ],
            HabrCategory::DATA_SCIENTIST->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 2,
            ],
            HabrCategory::BUSINESS_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::SYSTEMS_ANALYST),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::PRODUCT_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::BUSINESS_ANALYST),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::SOFTWARE_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::SYSTEMS_ANALYST),
                'level' => 1,
                'sort_order' => 2,
            ],
            HabrCategory::C1_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::SYSTEMS_ANALYST),
                'level' => 1,
                'sort_order' => 3,
            ],
            HabrCategory::DATA_ENGINEER->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_SCIENTIST),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::DATA_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_SCIENTIST),
                'level' => 1,
                'sort_order' => 2,
            ],
            HabrCategory::BI_DEVELOPER->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_ENGINEER),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::WEB_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_ANALYST),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::MOBILE_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_ANALYST),
                'level' => 2,
                'sort_order' => 2,
            ],
            HabrCategory::GAME_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_ANALYST),
                'level' => 2,
                'sort_order' => 3,
            ],
            HabrCategory::UX_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::DATA_ANALYST),
                'level' => 2,
                'sort_order' => 4,
            ],
            HabrCategory::ANALYTICS_OTHER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 99,
            ],

            // ==========================================
            // ДИЗАЙН
            // ==========================================
            HabrCategory::ART_DIRECTOR->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 1,
            ],
            HabrCategory::PRODUCT_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::ART_DIRECTOR),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::UI_UX_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PRODUCT_DESIGNER),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::WEB_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::UI_UX_DESIGNER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::APP_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::UI_UX_DESIGNER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::GRAPHIC_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::ART_DIRECTOR),
                'level' => 1,
                'sort_order' => 2,
            ],
            HabrCategory::MOTION_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::GRAPHIC_DESIGNER),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::ANIMATOR_3D->value => [
                'parent_id' => $this->getParentId(HabrCategory::MOTION_DESIGNER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::MODELER_3D->value => [
                'parent_id' => $this->getParentId(HabrCategory::ANIMATOR_3D),
                'level' => 4,
                'sort_order' => 1,
            ],
            HabrCategory::FLASH_ANIMATOR->value => [
                'parent_id' => $this->getParentId(HabrCategory::MOTION_DESIGNER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::COMPUTER_GRAPHICS_ARTIST->value => [
                'parent_id' => $this->getParentId(HabrCategory::MOTION_DESIGNER),
                'level' => 3,
                'sort_order' => 3,
            ],
            HabrCategory::ILLUSTRATOR->value => [
                'parent_id' => $this->getParentId(HabrCategory::GRAPHIC_DESIGNER),
                'level' => 2,
                'sort_order' => 2,
            ],
            HabrCategory::GAME_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PRODUCT_DESIGNER),
                'level' => 2,
                'sort_order' => 3,
            ],
            HabrCategory::NARRATIVE_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::GAME_DESIGNER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::VUI_DESIGNER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PRODUCT_DESIGNER),
                'level' => 2,
                'sort_order' => 4,
            ],
            HabrCategory::DESIGN_OTHER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 99,
            ],

            // ==========================================
            // МЕНЕДЖМЕНТ
            // ==========================================
            HabrCategory::PROGRAM_MANAGER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 1,
            ],
            HabrCategory::PROJECT_DIRECTOR->value => [
                'parent_id' => $this->getParentId(HabrCategory::PROGRAM_MANAGER),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::PROJECT_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PROJECT_DIRECTOR),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::PRODUCT_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PROJECT_MANAGER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::PRODUCT_MARKETING->value => [
                'parent_id' => $this->getParentId(HabrCategory::PRODUCT_MANAGER),
                'level' => 4,
                'sort_order' => 1,
            ],
            HabrCategory::DELIVERY_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PROJECT_MANAGER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::SCRUM_MASTER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PROJECT_MANAGER),
                'level' => 3,
                'sort_order' => 3,
            ],
            HabrCategory::COMMUNITY_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::PRODUCT_MANAGER),
                'level' => 4,
                'sort_order' => 2,
            ],
            HabrCategory::MANAGEMENT_OTHER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 99,
            ],

            // ==========================================
            // ТОП-МЕНЕДЖМЕНТ
            // ==========================================
            HabrCategory::CEO->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::CTO->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 1],
            HabrCategory::CPO->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 2],
            HabrCategory::COO->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 3],
            HabrCategory::CFO->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 4],
            HabrCategory::CCO->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 5],
            HabrCategory::CIO->value => ['parent_id' => $this->getParentId(HabrCategory::CTO), 'level' => 2, 'sort_order' => 1],
            HabrCategory::HR_DIRECTOR->value => ['parent_id' => $this->getParentId(HabrCategory::CEO), 'level' => 1, 'sort_order' => 6],

            // ==========================================
            // HR
            // ==========================================
            HabrCategory::HR_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_DIRECTOR),
                'level' => 1,
                'sort_order' => 1,
            ],
            HabrCategory::RECRUITMENT_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_MANAGER),
                'level' => 2,
                'sort_order' => 1,
            ],
            HabrCategory::RECRUITMENT_DIRECTOR->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_DIRECTOR),
                'level' => 1,
                'sort_order' => 2,
            ],
            HabrCategory::HR_ANALYST->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_MANAGER),
                'level' => 2,
                'sort_order' => 2,
            ],
            HabrCategory::HR_TRAINING_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_MANAGER),
                'level' => 2,
                'sort_order' => 3,
            ],
            HabrCategory::HR_BRAND_MANAGER->value => [
                'parent_id' => $this->getParentId(HabrCategory::HR_MANAGER),
                'level' => 2,
                'sort_order' => 4,
            ],
            HabrCategory::RESEARCHER->value => [
                'parent_id' => $this->getParentId(HabrCategory::RECRUITMENT_MANAGER),
                'level' => 3,
                'sort_order' => 1,
            ],
            HabrCategory::SOURCER->value => [
                'parent_id' => $this->getParentId(HabrCategory::RECRUITMENT_MANAGER),
                'level' => 3,
                'sort_order' => 2,
            ],
            HabrCategory::HR_OTHER->value => [
                'parent_id' => null,
                'level' => 0,
                'sort_order' => 99,
            ],

            // ==========================================
            // Остальные категории (без изменений - parent = null)
            // ==========================================
            HabrCategory::DEVOPS->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::SYSTEM_ADMIN->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 2],
            HabrCategory::SERVER_ADMIN->value => ['parent_id' => $this->getParentId(HabrCategory::SYSTEM_ADMIN), 'level' => 1, 'sort_order' => 1],
            HabrCategory::NETWORK_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::SYSTEM_ADMIN), 'level' => 1, 'sort_order' => 2],
            HabrCategory::DBA->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 3],
            HabrCategory::SRE->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 4],
            HabrCategory::MLOPS->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 5],
            HabrCategory::SITE_ADMIN->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 6],
            HabrCategory::WIRELESS_ENGINEER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 7],
            HabrCategory::DATA_CENTER_ENGINEER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 8],
            HabrCategory::ADMIN_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::SUPPORT_DIRECTOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::SUPPORT_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::SUPPORT_DIRECTOR), 'level' => 1, 'sort_order' => 1],
            HabrCategory::SUPPORT_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::SUPPORT_MANAGER), 'level' => 2, 'sort_order' => 1],
            HabrCategory::SUPPORT_ANALYST->value => ['parent_id' => $this->getParentId(HabrCategory::SUPPORT_MANAGER), 'level' => 2, 'sort_order' => 2],
            HabrCategory::CUSTOMER_SERVICE_DIRECTOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 2],
            HabrCategory::CUSTOMER_SERVICE_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::CUSTOMER_SERVICE_DIRECTOR), 'level' => 1, 'sort_order' => 1],
            HabrCategory::MODERATOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 3],
            HabrCategory::SUPPORT_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::MARKETING_DIRECTOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::MARKETING_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_DIRECTOR), 'level' => 1, 'sort_order' => 1],
            HabrCategory::MARKETING_ANALYST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 1],
            HabrCategory::SEO_SPECIALIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 2],
            HabrCategory::SMM_SPECIALIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 3],
            HabrCategory::TARGETOLOGIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 4],
            HabrCategory::PR_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_DIRECTOR), 'level' => 1, 'sort_order' => 2],
            HabrCategory::DEVREL->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_DIRECTOR), 'level' => 1, 'sort_order' => 3],
            HabrCategory::PPC_SPECIALIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 5],
            HabrCategory::DIRECOLOGIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 6],
            HabrCategory::ORM_SPECIALIST->value => ['parent_id' => $this->getParentId(HabrCategory::MARKETING_MANAGER), 'level' => 2, 'sort_order' => 7],
            HabrCategory::MARKETING_OTHER->value => [ 'parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::CONTENT_DIRECTOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::CONTENT_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_DIRECTOR), 'level' => 1, 'sort_order' => 1],
            HabrCategory::CONTENT_WRITER->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_MANAGER), 'level' => 2, 'sort_order' => 1],
            HabrCategory::TECH_WRITER->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_MANAGER), 'level' => 2, 'sort_order' => 2],
            HabrCategory::EDITOR->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_MANAGER), 'level' => 2, 'sort_order' => 3],
            HabrCategory::UX_WRITER->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_MANAGER), 'level' => 2, 'sort_order' => 4],
            HabrCategory::COPYWRITER->value => ['parent_id' => $this->getParentId(HabrCategory::CONTENT_WRITER), 'level' => 3, 'sort_order' => 1],
            HabrCategory::CONTENT_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::SALES_DIRECTOR->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::SALES_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::SALES_DIRECTOR), 'level' => 1, 'sort_order' => 1],
            HabrCategory::ACCOUNT_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::SALES_MANAGER), 'level' => 2, 'sort_order' => 1],
            HabrCategory::ACCOUNT_DIRECTOR->value => ['parent_id' => $this->getParentId(HabrCategory::SALES_DIRECTOR), 'level' => 1, 'sort_order' => 2],
            HabrCategory::PRESALE_MANAGER->value => ['parent_id' => $this->getParentId(HabrCategory::SALES_MANAGER), 'level' => 2, 'sort_order' => 2],
            HabrCategory::PRESALE_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::PRESALE_MANAGER), 'level' => 3, 'sort_order' => 1],
            HabrCategory::SALES_ANALYST->value => ['parent_id' => $this->getParentId(HabrCategory::SALES_MANAGER), 'level' => 2, 'sort_order' => 3],
            HabrCategory::SALES_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::INFO_SEC_ARCHITECT->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::INFO_SEC_SPECIALIST->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_ARCHITECT), 'level' => 1, 'sort_order' => 1],
            HabrCategory::SECURITY_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 1],
            HabrCategory::PENTESTER->value => ['parent_id' => $this->getParentId(HabrCategory::SECURITY_ENGINEER), 'level' => 3, 'sort_order' => 1],
            HabrCategory::APPSEC_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 2],
            HabrCategory::SOC_ANALYST->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 3],
            HabrCategory::SECURITY_ADMIN->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 4],
            HabrCategory::REVERSE_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::SECURITY_ENGINEER), 'level' => 3, 'sort_order' => 2],
            HabrCategory::NLP_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 5],
            HabrCategory::ANTIFRAUD_ANALYST->value => ['parent_id' => $this->getParentId(HabrCategory::INFO_SEC_SPECIALIST), 'level' => 2, 'sort_order' => 6],
            HabrCategory::SECURITY_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::ML_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::DATA_SCIENTIST), 'level' => 1, 'sort_order' => 1],
            HabrCategory::CV_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::ML_ENGINEER), 'level' => 2, 'sort_order' => 1],
            HabrCategory::PROMPT_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::ML_ENGINEER), 'level' => 2, 'sort_order' => 2],
            HabrCategory::AI_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::OFFICE_MANAGER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::ACCOUNTANT->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 2],
            HabrCategory::LAWYER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 3],
            HabrCategory::OFFICE_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::ZEROCODER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::NOCODE_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],

            HabrCategory::CHIEF_PROJECT_ENGINEER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 1],
            HabrCategory::DESIGN_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 1],
            HabrCategory::MECHANICAL_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 2],
            HabrCategory::ELECTRONICS_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 3],
            HabrCategory::POWER_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 4],
            HabrCategory::ELECTRICAL_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 5],
            HabrCategory::QUALITY_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 6],
            HabrCategory::COMMISSIONING_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 7],
            HabrCategory::MAINTENANCE_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 8],
            HabrCategory::HSE_ENGINEER->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 9],
            HabrCategory::TECHNICAL_SUPERVISION->value => ['parent_id' => $this->getParentId(HabrCategory::CHIEF_PROJECT_ENGINEER), 'level' => 1, 'sort_order' => 10],
            HabrCategory::RESEARCH_SCIENTIST->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 2],
            HabrCategory::MANUFACTURING_OTHER->value => ['parent_id' => null, 'level' => 0, 'sort_order' => 99],
        ];
    }
}
