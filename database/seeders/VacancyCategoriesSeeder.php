<?php

namespace Database\Seeders;

use App\Enums\HabrCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacancyCategoriesSeeder extends Seeder
{
    private array $internalGroupIds = [];
    private array $savedIds = [];

    public function run(): void
    {
        $this->loadGroupIds();

        $categories = $this->getCategoriesData();

        foreach ($categories as $cat) {
            $parentId = $cat['parent'] !== null ? ($this->savedIds[$cat['parent']->name] ?? null) : null;
            $externalGroupId = $this->getExternalGroupId($cat['case']);
            $internalGroupId = $this->internalGroupIds[$externalGroupId];

            DB::table('vacancy_categories')->updateOrInsert(
                ['external_id' => $cat['case']->value],
                [
                    'group_id' => $internalGroupId,
                    'external_id' => $cat['case']->value,
                    'title' => $cat['case']->label(),
                    'alias' => $cat['case']->name,
                    'description' => $this->getDescription($cat['case']),
                    'parent_id' => $parentId,
                    'level' => $cat['level'],
                    'sort_order' => $cat['sort_order'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $saved = DB::table('vacancy_categories')
                ->where('external_id', $cat['case']->value)
                ->first();

            $this->savedIds[$cat['case']->name] = $saved->id;
        }

        $this->command->info('✅ Добавлено ' . count($categories) . ' категорий');
    }

    private function loadGroupIds(): void
    {
        $groups = DB::table('vacancy_groups')->get();
        foreach ($groups as $group) {
            $this->internalGroupIds[$group->external_id] = $group->id;
        }
    }

    private function getExternalGroupId(HabrCategory $category): int
    {
        return match(true) {
            // group_id: 1 - Разработка
            in_array($category, [
                HabrCategory::BACKEND_DEVELOPER, HabrCategory::FRONTEND_DEVELOPER,
                HabrCategory::FULLSTACK_DEVELOPER, HabrCategory::WEB_DEVELOPER,
                HabrCategory::APPLICATION_DEVELOPER, HabrCategory::MOBILE_DEVELOPER,
                HabrCategory::RELEASE_MANAGER, HabrCategory::GAME_DEVELOPER,
                HabrCategory::DESKTOP_DEVELOPER, HabrCategory::DATABASE_DEVELOPER,
                HabrCategory::EMBEDDED_ENGINEER, HabrCategory::HTML_CODER,
                HabrCategory::C1_DEVELOPER, HabrCategory::SOFTWARE_ARCHITECT,
                HabrCategory::SYSTEM_ENGINEER, HabrCategory::ERP_DEVELOPER,
                HabrCategory::DATABASE_ARCHITECT, HabrCategory::HARDWARE_ENGINEER,
                HabrCategory::C1_ARCHITECT, HabrCategory::DEV_OTHER,
            ]) => 1,

            // group_id: 2 - Тестирование
            in_array($category, [
                HabrCategory::MANUAL_TESTER, HabrCategory::AUTO_TESTER,
                HabrCategory::QA_ENGINEER, HabrCategory::UX_TESTER,
                HabrCategory::PERFORMANCE_TESTER, HabrCategory::QA_ANALYST,
                HabrCategory::QA_MANAGER, HabrCategory::QA_DIRECTOR,
                HabrCategory::QA_OTHER,
            ]) => 2,

            // group_id: 3 - Администрирование
            in_array($category, [
                HabrCategory::DEVOPS, HabrCategory::SYSTEM_ADMIN,
                HabrCategory::SERVER_ADMIN, HabrCategory::WIRELESS_ENGINEER,
                HabrCategory::DBA, HabrCategory::DATA_CENTER_ENGINEER,
                HabrCategory::NETWORK_ENGINEER, HabrCategory::SITE_ADMIN,
                HabrCategory::MLOPS, HabrCategory::SRE, HabrCategory::ADMIN_OTHER,
            ]) => 3,

            // group_id: 4 - Дизайн
            in_array($category, [
                HabrCategory::PRODUCT_DESIGNER, HabrCategory::UI_UX_DESIGNER,
                HabrCategory::WEB_DESIGNER, HabrCategory::GRAPHIC_DESIGNER,
                HabrCategory::APP_DESIGNER, HabrCategory::ILLUSTRATOR,
                HabrCategory::GAME_DESIGNER, HabrCategory::NARRATIVE_DESIGNER,
                HabrCategory::MOTION_DESIGNER, HabrCategory::ANIMATOR_3D,
                HabrCategory::FLASH_ANIMATOR, HabrCategory::MODELER_3D,
                HabrCategory::COMPUTER_GRAPHICS_ARTIST, HabrCategory::VUI_DESIGNER,
                HabrCategory::ART_DIRECTOR, HabrCategory::DESIGN_OTHER,
            ]) => 4,

            // group_id: 5 - Менеджмент
            in_array($category, [
                HabrCategory::PROJECT_MANAGER, HabrCategory::PROJECT_DIRECTOR,
                HabrCategory::PRODUCT_MANAGER, HabrCategory::SCRUM_MASTER,
                HabrCategory::DELIVERY_MANAGER, HabrCategory::COMMUNITY_MANAGER,
                HabrCategory::PRODUCT_MARKETING, HabrCategory::PROGRAM_MANAGER,
                HabrCategory::MANAGEMENT_OTHER,
            ]) => 5,

            // group_id: 6 - Аналитика
            in_array($category, [
                HabrCategory::MOBILE_ANALYST, HabrCategory::SYSTEMS_ANALYST,
                HabrCategory::BUSINESS_ANALYST, HabrCategory::DATA_ANALYST,
                HabrCategory::UX_ANALYST, HabrCategory::GAME_ANALYST,
                HabrCategory::DATA_ENGINEER, HabrCategory::SOFTWARE_ANALYST,
                HabrCategory::PRODUCT_ANALYST, HabrCategory::BI_DEVELOPER,
                HabrCategory::WEB_ANALYST, HabrCategory::C1_ANALYST,
                HabrCategory::ANALYTICS_OTHER,
            ]) => 6,

            // group_id: 7 - Поддержка
            in_array($category, [
                HabrCategory::SUPPORT_ENGINEER, HabrCategory::SUPPORT_MANAGER,
                HabrCategory::SUPPORT_DIRECTOR, HabrCategory::SUPPORT_ANALYST,
                HabrCategory::CUSTOMER_SERVICE_MANAGER, HabrCategory::CUSTOMER_SERVICE_DIRECTOR,
                HabrCategory::MODERATOR, HabrCategory::SUPPORT_OTHER,
            ]) => 7,

            // group_id: 8 - Маркетинг
            in_array($category, [
                HabrCategory::MARKETING_MANAGER, HabrCategory::MARKETING_DIRECTOR,
                HabrCategory::MARKETING_ANALYST, HabrCategory::SEO_SPECIALIST,
                HabrCategory::SMM_SPECIALIST, HabrCategory::TARGETOLOGIST,
                HabrCategory::DEVREL, HabrCategory::PR_MANAGER,
                HabrCategory::PPC_SPECIALIST, HabrCategory::DIRECOLOGIST,
                HabrCategory::ORM_SPECIALIST, HabrCategory::MARKETING_OTHER,
            ]) => 8,

            // group_id: 9 - Продажи
            in_array($category, [
                HabrCategory::ACCOUNT_MANAGER, HabrCategory::ACCOUNT_DIRECTOR,
                HabrCategory::SALES_MANAGER, HabrCategory::PRESALE_ENGINEER,
                HabrCategory::SALES_DIRECTOR, HabrCategory::PRESALE_MANAGER,
                HabrCategory::SALES_ANALYST, HabrCategory::SALES_OTHER,
            ]) => 9,

            // group_id: 10 - Контент
            in_array($category, [
                HabrCategory::UX_WRITER, HabrCategory::TECH_WRITER,
                HabrCategory::CONTENT_WRITER, HabrCategory::CONTENT_MANAGER,
                HabrCategory::EDITOR, HabrCategory::CONTENT_DIRECTOR,
                HabrCategory::COPYWRITER, HabrCategory::CONTENT_OTHER,
            ]) => 10,

            // group_id: 11 - HR
            in_array($category, [
                HabrCategory::HR_MANAGER, HabrCategory::RECRUITMENT_MANAGER,
                HabrCategory::RESEARCHER, HabrCategory::SOURCER,
                HabrCategory::RECRUITMENT_DIRECTOR, HabrCategory::HR_ANALYST,
                HabrCategory::HR_TRAINING_MANAGER, HabrCategory::HR_BRAND_MANAGER,
                HabrCategory::HR_OTHER,
            ]) => 11,

            // group_id: 12 - Офис
            in_array($category, [
                HabrCategory::OFFICE_MANAGER, HabrCategory::ACCOUNTANT,
                HabrCategory::LAWYER, HabrCategory::OFFICE_OTHER,
            ]) => 12,

            // group_id: 13 - Информационная безопасность
            in_array($category, [
                HabrCategory::PENTESTER, HabrCategory::SECURITY_ADMIN,
                HabrCategory::SOC_ANALYST, HabrCategory::INFO_SEC_SPECIALIST,
                HabrCategory::REVERSE_ENGINEER, HabrCategory::APPSEC_ENGINEER,
                HabrCategory::SECURITY_ENGINEER, HabrCategory::NLP_ENGINEER,
                HabrCategory::ANTIFRAUD_ANALYST, HabrCategory::INFO_SEC_ARCHITECT,
                HabrCategory::SECURITY_OTHER,
            ]) => 13,

            // group_id: 14 - Искусственный интеллект
            in_array($category, [
                HabrCategory::DATA_SCIENTIST, HabrCategory::ML_ENGINEER,
                HabrCategory::CV_ENGINEER, HabrCategory::PROMPT_ENGINEER,
                HabrCategory::AI_OTHER,
            ]) => 14,

            // group_id: 15 - Зерокодинг
            in_array($category, [
                HabrCategory::ZEROCODER, HabrCategory::NOCODE_OTHER,
            ]) => 15,

            // group_id: 49 - Топ-менеджмент
            in_array($category, [
                HabrCategory::HR_DIRECTOR, HabrCategory::CPO,
                HabrCategory::CCO, HabrCategory::COO,
                HabrCategory::CIO, HabrCategory::CEO,
                HabrCategory::CFO, HabrCategory::CTO,
            ]) => 49,

            // group_id: 50 - Производство
            in_array($category, [
                HabrCategory::DESIGN_ENGINEER, HabrCategory::MECHANICAL_ENGINEER,
                HabrCategory::COMMISSIONING_ENGINEER, HabrCategory::QUALITY_ENGINEER,
                HabrCategory::HSE_ENGINEER, HabrCategory::MAINTENANCE_ENGINEER,
                HabrCategory::ELECTRONICS_ENGINEER, HabrCategory::POWER_ENGINEER,
                HabrCategory::RESEARCH_SCIENTIST, HabrCategory::ELECTRICAL_ENGINEER,
                HabrCategory::CHIEF_PROJECT_ENGINEER, HabrCategory::TECHNICAL_SUPERVISION,
                HabrCategory::MANUFACTURING_OTHER,
            ]) => 50,

            default => 1,
        };
    }

    private function getCategoriesData(): array
    {
        return [
            // ==========================================
            // РАЗРАБОТКА (логическая иерархия)
            // ==========================================
            // Уровень 0: Архитекторы (главные)
            ['case' => HabrCategory::SOFTWARE_ARCHITECT, 'parent' => null, 'level' => 0, 'title' => 'Архитектор ПО', 'sort_order' => 1],
            ['case' => HabrCategory::DATABASE_ARCHITECT, 'parent' => null, 'level' => 0, 'title' => 'Архитектор БД', 'sort_order' => 2],
            ['case' => HabrCategory::C1_ARCHITECT, 'parent' => null, 'level' => 0, 'title' => 'Архитектор 1С', 'sort_order' => 3],

            // Уровень 1: Ведущие / Ключевые специалисты (подчиняются архитекторам)
            ['case' => HabrCategory::SYSTEM_ENGINEER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 1, 'title' => 'Системный инженер', 'sort_order' => 1],
            ['case' => HabrCategory::RELEASE_MANAGER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 1, 'title' => 'Релиз-менеджер', 'sort_order' => 2],
            ['case' => HabrCategory::DATABASE_DEVELOPER, 'parent' => HabrCategory::DATABASE_ARCHITECT, 'level' => 1, 'title' => 'Разработчик БД', 'sort_order' => 3],
            ['case' => HabrCategory::C1_DEVELOPER, 'parent' => HabrCategory::C1_ARCHITECT, 'level' => 1, 'title' => 'Программист 1С', 'sort_order' => 4],

            // Уровень 2: Разработчики (подчиняются ведущим)
            ['case' => HabrCategory::BACKEND_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Бэкенд разработчик', 'sort_order' => 1],
            ['case' => HabrCategory::FRONTEND_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Фронтенд разработчик', 'sort_order' => 2],
            ['case' => HabrCategory::FULLSTACK_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Фулстек разработчик', 'sort_order' => 3],
            ['case' => HabrCategory::MOBILE_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Мобильный разработчик', 'sort_order' => 4],
            ['case' => HabrCategory::GAME_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Разработчик игр', 'sort_order' => 5],
            ['case' => HabrCategory::DESKTOP_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'Десктоп разработчик', 'sort_order' => 6],
            ['case' => HabrCategory::ERP_DEVELOPER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 2, 'title' => 'ERP-разработчик', 'sort_order' => 7],
            ['case' => HabrCategory::EMBEDDED_ENGINEER, 'parent' => HabrCategory::SYSTEM_ENGINEER, 'level' => 2, 'title' => 'Инженер встраиваемых систем', 'sort_order' => 8],
            ['case' => HabrCategory::HARDWARE_ENGINEER, 'parent' => HabrCategory::SYSTEM_ENGINEER, 'level' => 2, 'title' => 'Инженер электронных устройств', 'sort_order' => 9],

            // Уровень 3: Узкие специалисты (подчиняются разработчикам)
            ['case' => HabrCategory::WEB_DEVELOPER, 'parent' => HabrCategory::BACKEND_DEVELOPER, 'level' => 3, 'title' => 'Веб-разработчик', 'sort_order' => 1],
            ['case' => HabrCategory::APPLICATION_DEVELOPER, 'parent' => HabrCategory::BACKEND_DEVELOPER, 'level' => 3, 'title' => 'Разработчик приложений', 'sort_order' => 2],
            ['case' => HabrCategory::HTML_CODER, 'parent' => HabrCategory::FRONTEND_DEVELOPER, 'level' => 3, 'title' => 'HTML-верстальщик', 'sort_order' => 3],

            ['case' => HabrCategory::DEV_OTHER, 'parent' => HabrCategory::SOFTWARE_ARCHITECT, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // ТЕСТИРОВАНИЕ
            // ==========================================
            ['case' => HabrCategory::QA_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор по качеству', 'sort_order' => 1],
            ['case' => HabrCategory::QA_MANAGER, 'parent' => HabrCategory::QA_DIRECTOR, 'level' => 1, 'title' => 'Менеджер по качеству', 'sort_order' => 1],
            ['case' => HabrCategory::QA_ANALYST, 'parent' => HabrCategory::QA_MANAGER, 'level' => 2, 'title' => 'Аналитик качества', 'sort_order' => 1],
            ['case' => HabrCategory::QA_ENGINEER, 'parent' => HabrCategory::QA_MANAGER, 'level' => 2, 'title' => 'Инженер по качеству', 'sort_order' => 2],
            ['case' => HabrCategory::AUTO_TESTER, 'parent' => HabrCategory::QA_ENGINEER, 'level' => 3, 'title' => 'Автотестировщик', 'sort_order' => 1],
            ['case' => HabrCategory::MANUAL_TESTER, 'parent' => HabrCategory::QA_ENGINEER, 'level' => 3, 'title' => 'Ручной тестировщик', 'sort_order' => 2],
            ['case' => HabrCategory::PERFORMANCE_TESTER, 'parent' => HabrCategory::QA_ENGINEER, 'level' => 3, 'title' => 'Тестировщик производительности', 'sort_order' => 3],
            ['case' => HabrCategory::UX_TESTER, 'parent' => HabrCategory::QA_ENGINEER, 'level' => 3, 'title' => 'UX-тестировщик', 'sort_order' => 4],
            ['case' => HabrCategory::QA_OTHER, 'parent' => HabrCategory::QA_MANAGER, 'level' => 2, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // АНАЛИТИКА
            // ==========================================
            ['case' => HabrCategory::SYSTEMS_ANALYST, 'parent' => null, 'level' => 0, 'title' => 'Системный аналитик', 'sort_order' => 1],
            ['case' => HabrCategory::DATA_SCIENTIST, 'parent' => null, 'level' => 0, 'title' => 'Data Scientist', 'sort_order' => 2],
            ['case' => HabrCategory::BUSINESS_ANALYST, 'parent' => HabrCategory::SYSTEMS_ANALYST, 'level' => 1, 'title' => 'Бизнес-аналитик', 'sort_order' => 1],
            ['case' => HabrCategory::PRODUCT_ANALYST, 'parent' => HabrCategory::BUSINESS_ANALYST, 'level' => 2, 'title' => 'Продуктовый аналитик', 'sort_order' => 1],
            ['case' => HabrCategory::SOFTWARE_ANALYST, 'parent' => HabrCategory::SYSTEMS_ANALYST, 'level' => 1, 'title' => 'Программный аналитик', 'sort_order' => 2],
            ['case' => HabrCategory::C1_ANALYST, 'parent' => HabrCategory::SYSTEMS_ANALYST, 'level' => 1, 'title' => 'Аналитик 1С', 'sort_order' => 3],
            ['case' => HabrCategory::DATA_ENGINEER, 'parent' => HabrCategory::DATA_SCIENTIST, 'level' => 1, 'title' => 'Data Engineer', 'sort_order' => 1],
            ['case' => HabrCategory::DATA_ANALYST, 'parent' => HabrCategory::DATA_SCIENTIST, 'level' => 1, 'title' => 'Data Analyst', 'sort_order' => 2],
            ['case' => HabrCategory::BI_DEVELOPER, 'parent' => HabrCategory::DATA_ENGINEER, 'level' => 2, 'title' => 'BI-разработчик', 'sort_order' => 1],
            ['case' => HabrCategory::WEB_ANALYST, 'parent' => HabrCategory::DATA_ANALYST, 'level' => 2, 'title' => 'Веб-аналитик', 'sort_order' => 1],
            ['case' => HabrCategory::MOBILE_ANALYST, 'parent' => HabrCategory::DATA_ANALYST, 'level' => 2, 'title' => 'Аналитик мобильных приложений', 'sort_order' => 2],
            ['case' => HabrCategory::GAME_ANALYST, 'parent' => HabrCategory::DATA_ANALYST, 'level' => 2, 'title' => 'Гейм-аналитик', 'sort_order' => 3],
            ['case' => HabrCategory::UX_ANALYST, 'parent' => HabrCategory::DATA_ANALYST, 'level' => 2, 'title' => 'UX-аналитик', 'sort_order' => 4],
            ['case' => HabrCategory::ANALYTICS_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // ДИЗАЙН
            // ==========================================
            ['case' => HabrCategory::ART_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Арт-директор', 'sort_order' => 1],
            ['case' => HabrCategory::PRODUCT_DESIGNER, 'parent' => HabrCategory::ART_DIRECTOR, 'level' => 1, 'title' => 'Продуктовый дизайнер', 'sort_order' => 1],
            ['case' => HabrCategory::UI_UX_DESIGNER, 'parent' => HabrCategory::PRODUCT_DESIGNER, 'level' => 2, 'title' => 'UI/UX дизайнер', 'sort_order' => 1],
            ['case' => HabrCategory::WEB_DESIGNER, 'parent' => HabrCategory::UI_UX_DESIGNER, 'level' => 3, 'title' => 'Веб-дизайнер', 'sort_order' => 1],
            ['case' => HabrCategory::APP_DESIGNER, 'parent' => HabrCategory::UI_UX_DESIGNER, 'level' => 3, 'title' => 'Дизайнер приложений', 'sort_order' => 2],
            ['case' => HabrCategory::GRAPHIC_DESIGNER, 'parent' => HabrCategory::ART_DIRECTOR, 'level' => 1, 'title' => 'Графический дизайнер', 'sort_order' => 2],
            ['case' => HabrCategory::MOTION_DESIGNER, 'parent' => HabrCategory::GRAPHIC_DESIGNER, 'level' => 2, 'title' => 'Моушн-дизайнер', 'sort_order' => 1],
            ['case' => HabrCategory::ANIMATOR_3D, 'parent' => HabrCategory::MOTION_DESIGNER, 'level' => 3, 'title' => '3D-аниматор', 'sort_order' => 1],
            ['case' => HabrCategory::MODELER_3D, 'parent' => HabrCategory::ANIMATOR_3D, 'level' => 4, 'title' => '3D-моделлер', 'sort_order' => 1],
            ['case' => HabrCategory::FLASH_ANIMATOR, 'parent' => HabrCategory::MOTION_DESIGNER, 'level' => 3, 'title' => 'Flash-аниматор', 'sort_order' => 2],
            ['case' => HabrCategory::COMPUTER_GRAPHICS_ARTIST, 'parent' => HabrCategory::MOTION_DESIGNER, 'level' => 3, 'title' => 'Художник компьютерной графики', 'sort_order' => 3],
            ['case' => HabrCategory::ILLUSTRATOR, 'parent' => HabrCategory::GRAPHIC_DESIGNER, 'level' => 2, 'title' => 'Иллюстратор', 'sort_order' => 2],
            ['case' => HabrCategory::GAME_DESIGNER, 'parent' => HabrCategory::PRODUCT_DESIGNER, 'level' => 2, 'title' => 'Гейм-дизайнер', 'sort_order' => 3],
            ['case' => HabrCategory::NARRATIVE_DESIGNER, 'parent' => HabrCategory::GAME_DESIGNER, 'level' => 3, 'title' => 'Нарративный дизайнер', 'sort_order' => 1],
            ['case' => HabrCategory::VUI_DESIGNER, 'parent' => HabrCategory::PRODUCT_DESIGNER, 'level' => 2, 'title' => 'VUI-дизайнер', 'sort_order' => 4],
            ['case' => HabrCategory::DESIGN_OTHER, 'parent' => HabrCategory::ART_DIRECTOR, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // МЕНЕДЖМЕНТ
            // ==========================================
            ['case' => HabrCategory::PROGRAM_MANAGER, 'parent' => null, 'level' => 0, 'title' => 'Программный менеджер', 'sort_order' => 1],
            ['case' => HabrCategory::PROJECT_DIRECTOR, 'parent' => HabrCategory::PROGRAM_MANAGER, 'level' => 1, 'title' => 'Директор проектов', 'sort_order' => 1],
            ['case' => HabrCategory::PROJECT_MANAGER, 'parent' => HabrCategory::PROJECT_DIRECTOR, 'level' => 2, 'title' => 'Менеджер проектов', 'sort_order' => 1],
            ['case' => HabrCategory::PRODUCT_MANAGER, 'parent' => HabrCategory::PROJECT_MANAGER, 'level' => 3, 'title' => 'Менеджер продукта', 'sort_order' => 1],
            ['case' => HabrCategory::PRODUCT_MARKETING, 'parent' => HabrCategory::PRODUCT_MANAGER, 'level' => 4, 'title' => 'Продуктовый маркетолог', 'sort_order' => 1],
            ['case' => HabrCategory::DELIVERY_MANAGER, 'parent' => HabrCategory::PROJECT_MANAGER, 'level' => 3, 'title' => 'Деливери-менеджер', 'sort_order' => 2],
            ['case' => HabrCategory::SCRUM_MASTER, 'parent' => HabrCategory::PROJECT_MANAGER, 'level' => 3, 'title' => 'Scrum-мастер', 'sort_order' => 3],
            ['case' => HabrCategory::COMMUNITY_MANAGER, 'parent' => HabrCategory::PRODUCT_MANAGER, 'level' => 4, 'title' => 'Менеджер сообщества', 'sort_order' => 2],
            ['case' => HabrCategory::MANAGEMENT_OTHER, 'parent' => HabrCategory::PROGRAM_MANAGER, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // ТОП-МЕНЕДЖМЕНТ
            // ==========================================
            ['case' => HabrCategory::CEO, 'parent' => null, 'level' => 0, 'title' => 'Генеральный директор', 'sort_order' => 1],
            ['case' => HabrCategory::CTO, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Технический директор', 'sort_order' => 1],
            ['case' => HabrCategory::CPO, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Директор по продукту', 'sort_order' => 2],
            ['case' => HabrCategory::COO, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Исполнительный директор', 'sort_order' => 3],
            ['case' => HabrCategory::CFO, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Финансовый директор', 'sort_order' => 4],
            ['case' => HabrCategory::CCO, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Коммерческий директор', 'sort_order' => 5],
            ['case' => HabrCategory::CIO, 'parent' => HabrCategory::CTO, 'level' => 2, 'title' => 'Директор по информационным технологиям', 'sort_order' => 1],
            ['case' => HabrCategory::HR_DIRECTOR, 'parent' => HabrCategory::CEO, 'level' => 1, 'title' => 'Директор по персоналу', 'sort_order' => 6],

            // ==========================================
            // HR
            // ==========================================
            ['case' => HabrCategory::HR_MANAGER, 'parent' => HabrCategory::HR_DIRECTOR, 'level' => 1, 'title' => 'HR-менеджер', 'sort_order' => 1],
            ['case' => HabrCategory::RECRUITMENT_MANAGER, 'parent' => HabrCategory::HR_MANAGER, 'level' => 2, 'title' => 'Менеджер по найму', 'sort_order' => 1],
            ['case' => HabrCategory::RECRUITMENT_DIRECTOR, 'parent' => HabrCategory::HR_DIRECTOR, 'level' => 1, 'title' => 'Директор по найму', 'sort_order' => 2],
            ['case' => HabrCategory::HR_ANALYST, 'parent' => HabrCategory::HR_MANAGER, 'level' => 2, 'title' => 'HR-аналитик', 'sort_order' => 2],
            ['case' => HabrCategory::HR_TRAINING_MANAGER, 'parent' => HabrCategory::HR_MANAGER, 'level' => 2, 'title' => 'Менеджер по обучению', 'sort_order' => 3],
            ['case' => HabrCategory::HR_BRAND_MANAGER, 'parent' => HabrCategory::HR_MANAGER, 'level' => 2, 'title' => 'HR-бренд-менеджер', 'sort_order' => 4],
            ['case' => HabrCategory::RESEARCHER, 'parent' => HabrCategory::RECRUITMENT_MANAGER, 'level' => 3, 'title' => 'Ресечер', 'sort_order' => 1],
            ['case' => HabrCategory::SOURCER, 'parent' => HabrCategory::RECRUITMENT_MANAGER, 'level' => 3, 'title' => 'Сорсер', 'sort_order' => 2],
            ['case' => HabrCategory::HR_OTHER, 'parent' => HabrCategory::HR_MANAGER, 'level' => 2, 'title' => 'Другое', 'sort_order' => 99],

            // ==========================================
            // Остальные категории (без вложенности)
            // ==========================================
            // Администрирование
            ['case' => HabrCategory::DEVOPS, 'parent' => null, 'level' => 0, 'title' => 'DevOps-инженер', 'sort_order' => 1],
            ['case' => HabrCategory::SYSTEM_ADMIN, 'parent' => null, 'level' => 0, 'title' => 'Системный администратор', 'sort_order' => 2],
            ['case' => HabrCategory::SERVER_ADMIN, 'parent' => HabrCategory::SYSTEM_ADMIN, 'level' => 1, 'title' => 'Администратор серверов', 'sort_order' => 1],
            ['case' => HabrCategory::NETWORK_ENGINEER, 'parent' => HabrCategory::SYSTEM_ADMIN, 'level' => 1, 'title' => 'Сетевой инженер', 'sort_order' => 2],
            ['case' => HabrCategory::DBA, 'parent' => null, 'level' => 0, 'title' => 'Администратор баз данных', 'sort_order' => 3],
            ['case' => HabrCategory::SRE, 'parent' => null, 'level' => 0, 'title' => 'SRE-инженер', 'sort_order' => 4],
            ['case' => HabrCategory::MLOPS, 'parent' => null, 'level' => 0, 'title' => 'MLOps-инженер', 'sort_order' => 5],
            ['case' => HabrCategory::SITE_ADMIN, 'parent' => null, 'level' => 0, 'title' => 'Администратор сайта', 'sort_order' => 6],
            ['case' => HabrCategory::WIRELESS_ENGINEER, 'parent' => null, 'level' => 0, 'title' => 'Инженер по беспроводным системам', 'sort_order' => 7],
            ['case' => HabrCategory::DATA_CENTER_ENGINEER, 'parent' => null, 'level' => 0, 'title' => 'Инженер ЦОД', 'sort_order' => 8],
            ['case' => HabrCategory::ADMIN_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],

            // Поддержка
            ['case' => HabrCategory::SUPPORT_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор поддержки', 'sort_order' => 1],
            ['case' => HabrCategory::SUPPORT_MANAGER, 'parent' => HabrCategory::SUPPORT_DIRECTOR, 'level' => 1, 'title' => 'Менеджер поддержки', 'sort_order' => 1],
            ['case' => HabrCategory::SUPPORT_ENGINEER, 'parent' => HabrCategory::SUPPORT_MANAGER, 'level' => 2, 'title' => 'Инженер поддержки', 'sort_order' => 1],
            ['case' => HabrCategory::SUPPORT_ANALYST, 'parent' => HabrCategory::SUPPORT_MANAGER, 'level' => 2, 'title' => 'Аналитик поддержки', 'sort_order' => 2],
            ['case' => HabrCategory::CUSTOMER_SERVICE_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор по обслуживанию клиентов', 'sort_order' => 2],
            ['case' => HabrCategory::CUSTOMER_SERVICE_MANAGER, 'parent' => HabrCategory::CUSTOMER_SERVICE_DIRECTOR, 'level' => 1, 'title' => 'Менеджер по обслуживанию клиентов', 'sort_order' => 1],
            ['case' => HabrCategory::MODERATOR, 'parent' => null, 'level' => 0, 'title' => 'Модератор', 'sort_order' => 3],
            ['case' => HabrCategory::SUPPORT_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],

            // Маркетинг
            ['case' => HabrCategory::MARKETING_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор по маркетингу', 'sort_order' => 1],
            ['case' => HabrCategory::MARKETING_MANAGER, 'parent' => HabrCategory::MARKETING_DIRECTOR, 'level' => 1, 'title' => 'Маркетолог', 'sort_order' => 1],
            ['case' => HabrCategory::MARKETING_ANALYST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'Маркетинг-аналитик', 'sort_order' => 1],
            ['case' => HabrCategory::SEO_SPECIALIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'SEO-специалист', 'sort_order' => 2],
            ['case' => HabrCategory::SMM_SPECIALIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'SMM-менеджер', 'sort_order' => 3],
            ['case' => HabrCategory::TARGETOLOGIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'Таргетолог', 'sort_order' => 4],
            ['case' => HabrCategory::PR_MANAGER, 'parent' => HabrCategory::MARKETING_DIRECTOR, 'level' => 1, 'title' => 'PR-менеджер', 'sort_order' => 2],
            ['case' => HabrCategory::DEVREL, 'parent' => HabrCategory::MARKETING_DIRECTOR, 'level' => 1, 'title' => 'DevRel', 'sort_order' => 3],
            ['case' => HabrCategory::PPC_SPECIALIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'Контекстолог', 'sort_order' => 5],
            ['case' => HabrCategory::DIRECOLOGIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'Директолог', 'sort_order' => 6],
            ['case' => HabrCategory::ORM_SPECIALIST, 'parent' => HabrCategory::MARKETING_MANAGER, 'level' => 2, 'title' => 'ORM-специалист', 'sort_order' => 7],
            ['case' => HabrCategory::MARKETING_OTHER, 'parent' => HabrCategory::MARKETING_DIRECTOR, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // Контент
            ['case' => HabrCategory::CONTENT_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор по контенту', 'sort_order' => 1],
            ['case' => HabrCategory::CONTENT_MANAGER, 'parent' => HabrCategory::CONTENT_DIRECTOR, 'level' => 1, 'title' => 'Контент-менеджер', 'sort_order' => 1],
            ['case' => HabrCategory::CONTENT_WRITER, 'parent' => HabrCategory::CONTENT_MANAGER, 'level' => 2, 'title' => 'Копирайтер', 'sort_order' => 1],
            ['case' => HabrCategory::TECH_WRITER, 'parent' => HabrCategory::CONTENT_MANAGER, 'level' => 2, 'title' => 'Технический писатель', 'sort_order' => 2],
            ['case' => HabrCategory::EDITOR, 'parent' => HabrCategory::CONTENT_MANAGER, 'level' => 2, 'title' => 'Редактор', 'sort_order' => 3],
            ['case' => HabrCategory::UX_WRITER, 'parent' => HabrCategory::CONTENT_MANAGER, 'level' => 2, 'title' => 'UX-писатель', 'sort_order' => 4],
            ['case' => HabrCategory::COPYWRITER, 'parent' => HabrCategory::CONTENT_WRITER, 'level' => 3, 'title' => 'Копирайтер (продажи)', 'sort_order' => 1],
            ['case' => HabrCategory::CONTENT_OTHER, 'parent' => HabrCategory::CONTENT_DIRECTOR, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // Продажи
            ['case' => HabrCategory::SALES_DIRECTOR, 'parent' => null, 'level' => 0, 'title' => 'Директор по продажам', 'sort_order' => 1],
            ['case' => HabrCategory::SALES_MANAGER, 'parent' => HabrCategory::SALES_DIRECTOR, 'level' => 1, 'title' => 'Менеджер по продажам', 'sort_order' => 1],
            ['case' => HabrCategory::ACCOUNT_MANAGER, 'parent' => HabrCategory::SALES_MANAGER, 'level' => 2, 'title' => 'Аккаунт-менеджер', 'sort_order' => 1],
            ['case' => HabrCategory::ACCOUNT_DIRECTOR, 'parent' => HabrCategory::SALES_DIRECTOR, 'level' => 1, 'title' => 'Директор по работе с клиентами', 'sort_order' => 2],
            ['case' => HabrCategory::PRESALE_MANAGER, 'parent' => HabrCategory::SALES_MANAGER, 'level' => 2, 'title' => 'Пресейл-менеджер', 'sort_order' => 2],
            ['case' => HabrCategory::PRESALE_ENGINEER, 'parent' => HabrCategory::PRESALE_MANAGER, 'level' => 3, 'title' => 'Пресейл-инженер', 'sort_order' => 1],
            ['case' => HabrCategory::SALES_ANALYST, 'parent' => HabrCategory::SALES_MANAGER, 'level' => 2, 'title' => 'Аналитик продаж', 'sort_order' => 3],
            ['case' => HabrCategory::SALES_OTHER, 'parent' => HabrCategory::SALES_DIRECTOR, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // Инфобезопасность
            ['case' => HabrCategory::INFO_SEC_ARCHITECT, 'parent' => null, 'level' => 0, 'title' => 'Архитектор ИБ', 'sort_order' => 1],
            ['case' => HabrCategory::INFO_SEC_SPECIALIST, 'parent' => HabrCategory::INFO_SEC_ARCHITECT, 'level' => 1, 'title' => 'Специалист по ИБ', 'sort_order' => 1],
            ['case' => HabrCategory::SECURITY_ENGINEER, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'Инженер безопасности', 'sort_order' => 1],
            ['case' => HabrCategory::PENTESTER, 'parent' => HabrCategory::SECURITY_ENGINEER, 'level' => 3, 'title' => 'Пентестер', 'sort_order' => 1],
            ['case' => HabrCategory::APPSEC_ENGINEER, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'AppSec-инженер', 'sort_order' => 2],
            ['case' => HabrCategory::SOC_ANALYST, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'SOC-аналитик', 'sort_order' => 3],
            ['case' => HabrCategory::SECURITY_ADMIN, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'Администратор защиты', 'sort_order' => 4],
            ['case' => HabrCategory::REVERSE_ENGINEER, 'parent' => HabrCategory::SECURITY_ENGINEER, 'level' => 3, 'title' => 'Реверс-инженер', 'sort_order' => 2],
            ['case' => HabrCategory::NLP_ENGINEER, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'NLP-инженер', 'sort_order' => 5],
            ['case' => HabrCategory::ANTIFRAUD_ANALYST, 'parent' => HabrCategory::INFO_SEC_SPECIALIST, 'level' => 2, 'title' => 'Антифрод-аналитик', 'sort_order' => 6],
            ['case' => HabrCategory::SECURITY_OTHER, 'parent' => HabrCategory::INFO_SEC_ARCHITECT, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // Искусственный интеллект
            ['case' => HabrCategory::DATA_SCIENTIST, 'parent' => null, 'level' => 0, 'title' => 'Data Scientist', 'sort_order' => 1],
            ['case' => HabrCategory::ML_ENGINEER, 'parent' => HabrCategory::DATA_SCIENTIST, 'level' => 1, 'title' => 'ML-инженер', 'sort_order' => 1],
            ['case' => HabrCategory::CV_ENGINEER, 'parent' => HabrCategory::ML_ENGINEER, 'level' => 2, 'title' => 'CV-инженер', 'sort_order' => 1],
            ['case' => HabrCategory::PROMPT_ENGINEER, 'parent' => HabrCategory::ML_ENGINEER, 'level' => 2, 'title' => 'Промпт-инженер', 'sort_order' => 2],
            ['case' => HabrCategory::AI_OTHER, 'parent' => HabrCategory::DATA_SCIENTIST, 'level' => 1, 'title' => 'Другое', 'sort_order' => 99],

            // Офис
            ['case' => HabrCategory::OFFICE_MANAGER, 'parent' => null, 'level' => 0, 'title' => 'Офис-менеджер', 'sort_order' => 1],
            ['case' => HabrCategory::ACCOUNTANT, 'parent' => null, 'level' => 0, 'title' => 'Бухгалтер', 'sort_order' => 2],
            ['case' => HabrCategory::LAWYER, 'parent' => null, 'level' => 0, 'title' => 'Юрист', 'sort_order' => 3],
            ['case' => HabrCategory::OFFICE_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],

            // Зерокодинг
            ['case' => HabrCategory::ZEROCODER, 'parent' => null, 'level' => 0, 'title' => 'Зерокодер', 'sort_order' => 1],
            ['case' => HabrCategory::NOCODE_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],

            // Производство
            ['case' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'parent' => null, 'level' => 0, 'title' => 'Главный инженер проекта', 'sort_order' => 1],
            ['case' => HabrCategory::DESIGN_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер-конструктор', 'sort_order' => 1],
            ['case' => HabrCategory::MECHANICAL_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер-механик', 'sort_order' => 2],
            ['case' => HabrCategory::ELECTRONICS_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер-электронщик', 'sort_order' => 3],
            ['case' => HabrCategory::POWER_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер-энергетик', 'sort_order' => 4],
            ['case' => HabrCategory::ELECTRICAL_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер-электрик', 'sort_order' => 5],
            ['case' => HabrCategory::QUALITY_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер по качеству', 'sort_order' => 6],
            ['case' => HabrCategory::COMMISSIONING_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер ПНР', 'sort_order' => 7],
            ['case' => HabrCategory::MAINTENANCE_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер по эксплуатации', 'sort_order' => 8],
            ['case' => HabrCategory::HSE_ENGINEER, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер по охране труда', 'sort_order' => 9],
            ['case' => HabrCategory::TECHNICAL_SUPERVISION, 'parent' => HabrCategory::CHIEF_PROJECT_ENGINEER, 'level' => 1, 'title' => 'Инженер ПТО', 'sort_order' => 10],
            ['case' => HabrCategory::RESEARCH_SCIENTIST, 'parent' => null, 'level' => 0, 'title' => 'Научный сотрудник', 'sort_order' => 2],
            ['case' => HabrCategory::MANUFACTURING_OTHER, 'parent' => null, 'level' => 0, 'title' => 'Другое', 'sort_order' => 99],
        ];
    }

    private function getDescription(HabrCategory $category): string
    {
        return match($category) {
            // Разработка
            HabrCategory::SOFTWARE_ARCHITECT => 'Проектирует IT-архитектуру компании: выбирает технологии, стандарты и инструменты, которыми будут пользоваться все команды разработки',
            HabrCategory::DATABASE_ARCHITECT => 'Проектирует структуру баз данных, оптимизирует скорость запросов и следит за целостностью данных',
            HabrCategory::C1_ARCHITECT => 'Разрабатывает архитектуру конфигураций 1С: структуру справочников, документов, регистров и бизнес-процессов',
            HabrCategory::C1_DEVELOPER => 'Пишет код на встроенном языке 1С, дорабатывает конфигурации под задачи бизнеса, делает отчёты и обработки',
            HabrCategory::BACKEND_DEVELOPER => 'Разрабатывает серверную часть сайтов и приложений: API, бизнес-логику, работу с базами данных и интеграции',
            HabrCategory::FRONTEND_DEVELOPER => 'Создаёт интерфейсы сайтов и приложений: верстает макеты, пишет логику на JavaScript, настраивает взаимодействие с сервером',
            HabrCategory::FULLSTACK_DEVELOPER => 'Разрабатывает и серверную, и клиентскую части веб-приложений — от базы данных до интерфейса',
            HabrCategory::WEB_DEVELOPER => 'Создаёт веб-сайты и веб-приложения: программирует серверную логику, работает с базами данных и API',
            HabrCategory::MOBILE_DEVELOPER => 'Разрабатывает приложения для iOS, Android или кроссплатформенно (React Native, Flutter, Xamarin)',
            HabrCategory::GAME_DEVELOPER => 'Разрабатывает видеоигры: программирует игровую логику, физику, работу ИИ и интеграцию с движком (Unity, Unreal)',
            HabrCategory::DESKTOP_DEVELOPER => 'Создаёт программы для работы на компьютере (Windows, macOS, Linux) на C++, C#, Java или других языках',
            HabrCategory::DATABASE_DEVELOPER => 'Пишет и оптимизирует SQL-запросы, создаёт хранимые процедуры, функции и триггеры в базах данных',
            HabrCategory::SYSTEM_ENGINEER => 'Разрабатывает системное ПО, драйверы, прошивки и инструменты для работы с оборудованием',
            HabrCategory::EMBEDDED_ENGINEER => 'Пишет прошивки для микроконтроллеров, разрабатывает ПО для встраиваемых систем и устройств IoT',
            HabrCategory::ERP_DEVELOPER => 'Разрабатывает и дорабатывает ERP-системы (SAP, Oracle, 1С, Odoo) под бизнес-процессы компании',
            HabrCategory::RELEASE_MANAGER => 'Управляет процессом выпуска релизов: версионирование, сборка, деплой, координация команд',
            HabrCategory::HTML_CODER => 'Верстает макеты в HTML и CSS, делает адаптивную и кроссбраузерную вёрстку под все устройства',
            HabrCategory::HARDWARE_ENGINEER => 'Разрабатывает схемы устройств и печатные платы (PCB), выбирает компоненты, отлаживает прототипы',
            HabrCategory::APPLICATION_DEVELOPER => 'Разрабатывает прикладное ПО — настольные, мобильные и серверные приложения под конкретные задачи',
            HabrCategory::DEV_OTHER => 'Другие специальности в области разработки ПО (не вошедшие в основные категории)',

            // Тестирование
            HabrCategory::QA_DIRECTOR => 'Формирует стратегию контроля качества в компании, управляет QA-процессами и руководит командой тестирования',
            HabrCategory::QA_MANAGER => 'Управляет командой тестировщиков, распределяет задачи, внедряет процессы контроля качества',
            HabrCategory::QA_ENGINEER => 'Разрабатывает тестовые сценарии и стратегии тестирования, автоматизирует проверку качества продукта',
            HabrCategory::AUTO_TESTER => 'Пишет автотесты на Java/Python/JS, настраивает их запуск в CI/CD, поддерживает тестовую инфраструктуру',
            HabrCategory::MANUAL_TESTER => 'Тестирует сайты и приложения вручную, ищет баги, составляет чек-листы и тест-кейсы',
            HabrCategory::PERFORMANCE_TESTER => 'Проверяет приложения на нагрузку и стресс-тестирование, помогает найти узкие места в системе',
            HabrCategory::UX_TESTER => 'Тестирует удобство интерфейсов: оценивает понятность, интуитивность и удовлетворённость пользователя',
            HabrCategory::QA_ANALYST => 'Анализирует метрики качества, собирает статистику дефектов, выявляет слабые места в процессах',
            HabrCategory::QA_OTHER => 'Другие специальности в области тестирования и контроля качества',

            // Аналитика
            HabrCategory::SYSTEMS_ANALYST => 'Собирает и анализирует требования к системе, проектирует архитектуру и пишет техническую документацию',
            HabrCategory::BUSINESS_ANALYST => 'Анализирует бизнес-процессы компании, собирает требования от заказчиков и готовит решения для автоматизации',
            HabrCategory::DATA_SCIENTIST => 'Строит модели машинного обучения, прогнозирует тренды, находит закономерности в больших данных',
            HabrCategory::DATA_ENGINEER => 'Строит пайплайны данных (ETL/ELT), настраивает хранилища и обеспечивает качество данных для аналитики и ML',
            HabrCategory::DATA_ANALYST => 'Анализирует данные, строит дашборды, визуализирует метрики и помогает командам принимать решения',
            HabrCategory::PRODUCT_ANALYST => 'Анализирует продуктовые метрики (LTV, Retention, Churn), ставит A/B тесты, улучшает продукт',
            HabrCategory::BI_DEVELOPER => 'Разрабатывает отчёты и дашборды в BI-системах (Power BI, Tableau, Looker, Superset)',
            HabrCategory::WEB_ANALYST => 'Настраивает системы аналитики (GA, Yandex Метрика), считает конверсии, строит воронки продаж',
            HabrCategory::MOBILE_ANALYST => 'Анализирует метрики мобильных приложений: удержание, глубина экранов, доходность',
            HabrCategory::GAME_ANALYST => 'Анализирует игровую аналитику: поведение игроков, баланс монетизацию, проводит A/B тесты',
            HabrCategory::UX_ANALYST => 'Исследует поведение пользователей, проводит юзабилити-тесты и даёт рекомендации по интерфейсу',
            HabrCategory::SOFTWARE_ANALYST => 'Детально прорабатывает требования к ПО, пишет спецификации, тестирует реализацию',
            HabrCategory::C1_ANALYST => 'Анализирует бизнес-задачи и превращает их в технические требования для разработчиков 1С',
            HabrCategory::ANALYTICS_OTHER => 'Другие специальности в области аналитики и исследований данных',

            // Дизайн
            HabrCategory::ART_DIRECTOR => 'Управляет дизайн-командой, задаёт визуальный стиль и следит за качеством всех материалов',
            HabrCategory::PRODUCT_DESIGNER => 'Проектирует пользовательский опыт (UX) и интерфейсы: от исследования до готового макета',
            HabrCategory::UI_UX_DESIGNER => 'Создаёт удобные и красивые интерфейсы: прототипы, адаптивный дизайн, пользовательские сценарии',
            HabrCategory::WEB_DESIGNER => 'Дизайн веб-сайтов: создаёт макеты, продумывает навигацию, адаптирует под экраны',
            HabrCategory::APP_DESIGNER => 'Проектирует интерфейсы мобильных приложений под iOS/Android с учётом гайдлайнов платформ',
            HabrCategory::GRAPHIC_DESIGNER => 'Создаёт визуальный контент: логотипы, полиграфию, упаковку, рекламные материалы',
            HabrCategory::MOTION_DESIGNER => 'Создаёт анимацию, 2D/3D-ролики, моушн-графику и визуальные эффекты для видео',
            HabrCategory::ANIMATOR_3D => 'Оживляет 3D-модели: создаёт анимацию персонажей, объектов и камер в 3D-пакетах',
            HabrCategory::MODELER_3D => 'Создаёт 3D-модели персонажей, окружения, техники и предметов для игр или анимации',
            HabrCategory::ILLUSTRATOR => 'Рисует вручную или на планшете: персонажи, иллюстрации, комиксы, арты',
            HabrCategory::GAME_DESIGNER => 'Придумывает игровые механики, пишет дизайн-документы, балансирует сложность и экономику',
            HabrCategory::NARRATIVE_DESIGNER => 'Пишет сценарии, диалоги и лор для игр, создаёт ветвления и истории',
            HabrCategory::VUI_DESIGNER => 'Проектирует голосовые интерфейсы: сценарии диалогов, фразы, обработку намерений',
            HabrCategory::FLASH_ANIMATOR => 'Создаёт 2D-анимацию лендингов, баннеров, инфографики (устаревающая специализация)',
            HabrCategory::COMPUTER_GRAPHICS_ARTIST => 'Создаёт текстуры, материалы, лайтинг и шейдеры для 3D-сцен и игр',
            HabrCategory::DESIGN_OTHER => 'Другие специальности в области дизайна и визуального творчества',

            // Менеджмент
            HabrCategory::PROGRAM_MANAGER => 'Управляет портфелем взаимосвязанных проектов, синхронизирует команды и ресурсы',
            HabrCategory::PROJECT_DIRECTOR => 'Руководит портфелем проектов: ставит цели, управляет бюджетами и стратегией',
            HabrCategory::PROJECT_MANAGER => 'Ведёт IT-проекты: планирует задачи, управляет сроками, бюджетом и командой',
            HabrCategory::PRODUCT_MANAGER => 'Управляет продуктом: собирает требования, строит роадмап, улучшает метрики продукта',
            HabrCategory::PRODUCT_MARKETING => 'Выводит продукты на рынок, создаёт ценность, работает с позиционированием и аналитикой',
            HabrCategory::DELIVERY_MANAGER => 'Организует процесс поставки продукта: управляет релизами и коммуникацией с заказчиком',
            HabrCategory::SCRUM_MASTER => 'Обучает команду Scrum, фасилитирует мероприятия, устраняет препятствия',
            HabrCategory::COMMUNITY_MANAGER => 'Развивает и модерирует сообщество: организует активности, отвечает на вопросы',
            HabrCategory::MANAGEMENT_OTHER => 'Другие специальности в области управления проектами и процессами',

            // Топ-менеджмент
            HabrCategory::CEO => 'Управляет бизнес-стратегией, отвечает за прибыль, развитие и итоговые результаты компании',
            HabrCategory::CTO => 'Определяет техническую стратегию, выбирает архитектуру, управляет развитием технологий в компании',
            HabrCategory::CPO => 'Формирует продуктовую стратегию, управляет продуктовыми командами и роудмапом продуктов',
            HabrCategory::COO => 'Настраивает операционную деятельность: процессы, регламенты, эффективность работы компании',
            HabrCategory::CFO => 'Управляет финансами компании: бюджетами, отчётностью, денежными потоками и инвестициями',
            HabrCategory::CCO => 'Управляет коммерческим направлением: продажами, доходностью и развитием бизнеса',
            HabrCategory::CIO => 'Руководит цифровой трансформацией: внедряет IT-системы и автоматизирует бизнес-процессы',
            HabrCategory::HR_DIRECTOR => 'Отвечает за стратегическое управление персоналом: культуру, найм, обучение, удержание',

            // HR
            HabrCategory::HR_MANAGER => 'Управляет HR-процессами: подбор, адаптация, оценка, развитие корпоративной культуры',
            HabrCategory::RECRUITMENT_MANAGER => 'Организует и управляет подбором персонала, строит воронку найма от воронки до оффера',
            HabrCategory::RECRUITMENT_DIRECTOR => 'Руководит стратегией подбора, управляет командой рекрутёров и budget найма',
            HabrCategory::HR_ANALYST => 'Собирает и анализирует HR-метрики: текучесть, вовлечённость, эффективность подбора',
            HabrCategory::HR_TRAINING_MANAGER => 'Создаёт систему обучения: программы развития, тренинги, грейдирование',
            HabrCategory::HR_BRAND_MANAGER => 'Развивает HR-бренд: внешние и внутренние коммуникации, работа с соискателями',
            HabrCategory::RESEARCHER => 'Ищет и скринит кандидатов в пассивном рынке, строит карту поиска',
            HabrCategory::SOURCER => 'Находит контакты и привлекает редких специалистов через прямые каналы',
            HabrCategory::HR_OTHER => 'Другие специальности в области управления персоналом',

            // Остальные категории
            HabrCategory::DEVOPS => 'Автоматизирует деплой, настраивает CI/CD, управляет облаками и инфраструктурой',
            HabrCategory::SYSTEM_ADMIN => 'Поддерживает работу серверов, сетей и IT-инфраструктуры компании',
            HabrCategory::SERVER_ADMIN => 'Настраивает серверы: Linux/Windows, виртуализацию, резервное копирование',
            HabrCategory::NETWORK_ENGINEER => 'Проектирует и обслуживает компьютерные сети: роутеры, VLAN, VPN',
            HabrCategory::DBA => 'Администрирует базы данных: бэкапы, репликация, тюнинг производительности',
            HabrCategory::SRE => 'Обеспечивает надёжность и отказоустойчивость сервисов под высокими нагрузками',
            HabrCategory::MLOPS => 'Автоматизирует пайплайны ML: обучение, развертывание и мониторинг моделей в продакшене',
            HabrCategory::SITE_ADMIN => 'Загружает контент, управляет сайтом в админке, базами данных',
            HabrCategory::WIRELESS_ENGINEER => 'Проектирует и обслуживает Wi-Fi сети, точки доступа, кибербезопасность',
            HabrCategory::DATA_CENTER_ENGINEER => 'Управляет ЦОД: охлаждение, электрика, мониторинг, железо',
            HabrCategory::ADMIN_OTHER => 'Другие специальности в администрировании',

            HabrCategory::SUPPORT_DIRECTOR => 'Управляет техподдержкой: процессы, лиды, эскалация, отчёты',
            HabrCategory::SUPPORT_MANAGER => 'Руководит командой поддержки: KPI, графики, обучение',
            HabrCategory::SUPPORT_ENGINEER => 'Решает сложные технические проблемы пользователей, эскалирует разработчикам',
            HabrCategory::SUPPORT_ANALYST => 'Смотрит метрики поддержки: время ответа, CSI, нагрузка на тикеты',
            HabrCategory::CUSTOMER_SERVICE_DIRECTOR => 'Руководит стратегией клиентского сервиса: голос клиента, программу лояльности',
            HabrCategory::CUSTOMER_SERVICE_MANAGER => 'Организует процессы обслуживания клиентов, разрешает конфликты',
            HabrCategory::MODERATOR => 'Модерирует комментарии, чаты и форумы по правилам',
            HabrCategory::SUPPORT_OTHER => 'Другие специальности в поддержке',

            HabrCategory::MARKETING_DIRECTOR => 'Определяет маркетинговую стратегию: позиционирование, бюджеты, каналы',
            HabrCategory::MARKETING_MANAGER => 'Управляет маркетинговыми кампаниями: лиды, контент, аналитика',
            HabrCategory::MARKETING_ANALYST => 'Анализирует рекламные кампании: CPA, LTV, ROI, проводит A/B тесты',
            HabrCategory::SEO_SPECIALIST => 'Продвигает страницы в поисковиках: семантика, ссылки, трафик',
            HabrCategory::SMM_SPECIALIST => 'Ведёт социальные сети компаний: вовлечённость, аудитория, ответы',
            HabrCategory::TARGETOLOGIST => 'Запускает таргетинг в соцсетях: бюджеты, аудитории, интерес',
            HabrCategory::PR_MANAGER => 'Взаимодействует со СМИ: пресс-релизы, публикации, антикризис',
            HabrCategory::DEVREL => 'Строит доверие между продуктом и IT-сообществом: технический блог, митапы',
            HabrCategory::PPC_SPECIALIST => 'Настраивает Яндекс.Директ и Google Ads: ключевики, минус-слова',
            HabrCategory::DIRECOLOGIST => 'Работает с прямой рекламой: email-рассылки, post, личные сообщения',
            HabrCategory::ORM_SPECIALIST => 'Управляет онлайн-репутацией компании: отзывы, маркетинг в поиске',
            HabrCategory::MARKETING_OTHER => 'Другие маркетинговые специальности',

            HabrCategory::CONTENT_DIRECTOR => 'Руководит контент-отделом, задаёт tone-of-voice компании',
            HabrCategory::CONTENT_MANAGER => 'Управляет контент-планом: задания авторам, редактура, публикация',
            HabrCategory::CONTENT_WRITER => 'Пишет статьи, лонгриды, посты для корпоративных блогов',
            HabrCategory::TECH_WRITER => 'Пишет техническую документацию: руководства, API, инструкции',
            HabrCategory::EDITOR => 'Проверяет тексты на ошибки, связность, соответствие ToV',
            HabrCategory::UX_WRITER => 'Пишет тексты в интерфейсе: кнопки, уведомления, сообщения об ошибках',
            HabrCategory::COPYWRITER => 'Пишет продающие тексты: посадочные страницы, гипотезы на креативы',
            HabrCategory::CONTENT_OTHER => 'Другие контентные специальности',

            HabrCategory::SALES_DIRECTOR => 'Управляет продажами в целом: план, командой, системой мотивации',
            HabrCategory::SALES_MANAGER => 'Активно продаёт продукт компании, закрывает сделки',
            HabrCategory::ACCOUNT_MANAGER => 'Ведёт существующих клиентов: продление, cross-sell, допродажи',
            HabrCategory::ACCOUNT_DIRECTOR => 'Управляет ключевыми клиентами (департмент аккаунтов)',
            HabrCategory::PRESALE_MANAGER => 'Продаёт через технические консультации: демо, прототипы',
            HabrCategory::PRESALE_ENGINEER => 'Погружается в технические потребности клиента и демонстрирует интеграции',
            HabrCategory::SALES_ANALYST => 'Анализирует воронку продаж, прогнозирует планы, метрики',
            HabrCategory::SALES_OTHER => 'Другие специальности в продажах',

            HabrCategory::INFO_SEC_ARCHITECT => 'Проектирует архитектуру ИБ компании: политики, аудит, compliance',
            HabrCategory::INFO_SEC_SPECIALIST => 'Разрабатывает политики ИБ, проводит оценки рисков, обучает сотрудников',
            HabrCategory::SECURITY_ENGINEER => 'Внедряет средства ИБ: DLP, SIEM, IDS, шифрование',
            HabrCategory::PENTESTER => 'Проводит тесты на проникновение и оценку уязвимости систем с разрешения',
            HabrCategory::APPSEC_ENGINEER => 'Интегрирует безопасность в разработку: code review, SAST/DAST',
            HabrCategory::SOC_ANALYST => 'Мониторит уровень безопасности 24/7, реагирует на инциденты',
            HabrCategory::SECURITY_ADMIN => 'Администрирует системы ИБ: межсетевые экраны, антивирусы, VPN',
            HabrCategory::REVERSE_ENGINEER => 'Анализирует вредоносное ПО и бинарные файлы',
            HabrCategory::NLP_ENGINEER => 'Применяет NLP для задач безопасности: анализ логов, текстовых угроз',
            HabrCategory::ANTIFRAUD_ANALYST => 'Выявляет мошеннические действия по транзакциям, доходам',
            HabrCategory::SECURITY_OTHER => 'Другие специальности в информационной безопасности',

            HabrCategory::ML_ENGINEER => 'Разрабатывает и деплоит ML-модели: фичи, обучение, инференс',
            HabrCategory::CV_ENGINEER => 'Решает задачи компьютерного зрения: детекция, распознавание, трекинг',
            HabrCategory::PROMPT_ENGINEER => 'Создаёт промпты для LLM- и GenAI-задач',
            HabrCategory::AI_OTHER => 'Другие специальности в AI/ML',

            HabrCategory::OFFICE_MANAGER => 'Управляет офисом: канцтовары, техника, мероприятия, коммуникации',
            HabrCategory::ACCOUNTANT => 'Ведёт бухгалтерию: первичка, налоги, отчётность',
            HabrCategory::LAWYER => 'Готовит договоры, регистрирует права, следит за законодательством',
            HabrCategory::OFFICE_OTHER => 'Другие офисные специальности',

            HabrCategory::ZEROCODER => 'Создаёт сайты, чат-боты, приложения платформами no-code',
            HabrCategory::NOCODE_OTHER => 'Другие no-code специальности',

            HabrCategory::CHIEF_PROJECT_ENGINEER => 'Главный инженер проекта: проектная документация, сроки, контроль',
            HabrCategory::DESIGN_ENGINEER => 'Инженер-конструктор: чертежи, 3D-модели, CAD',
            HabrCategory::MECHANICAL_ENGINEER => 'Инженер-механик: узлы, детали, гидравлика, термодинамика',
            HabrCategory::ELECTRONICS_ENGINEER => 'Инженер-электронщик: схемы, компоненты, PCB, отладка',
            HabrCategory::POWER_ENGINEER => 'Инженер-энергетик: электрические сети, трансформаторы, системы передачи',
            HabrCategory::ELECTRICAL_ENGINEER => 'Инженер-электрик: проводка, освещение, заземление',
            HabrCategory::QUALITY_ENGINEER => 'Инженер по качеству: процессы, аудит, стандарты (ISO, ГОСТ)',
            HabrCategory::COMMISSIONING_ENGINEER => 'Инженер ПНР: пусконаладка оборудования, проверка схем',
            HabrCategory::MAINTENANCE_ENGINEER => 'Инженер по эксплуатации: обслуживание, ремонт, осмотры',
            HabrCategory::HSE_ENGINEER => 'Инженер по охране труда: безопасность, риск-менеджмент',
            HabrCategory::TECHNICAL_SUPERVISION => 'Инженер ПТО: стройконтроль, проверка смет',
            HabrCategory::RESEARCH_SCIENTIST => 'Научный сотрудник: гипотезы, эксперименты, научные работы',
            HabrCategory::MANUFACTURING_OTHER => 'Другие производственные специальности',
        };
    }}
