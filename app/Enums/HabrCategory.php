<?php

namespace App\Enums;

/**
 * Категории профессий Хабр Карьеры
 * Источник: https://career.habr.com
 */
enum HabrCategory: int
{
    // Разработка (group_id: 1)
    case BACKEND_DEVELOPER = 2;           // Бэкенд разработчик
    case FRONTEND_DEVELOPER = 3;          // Фронтенд разработчик
    case FULLSTACK_DEVELOPER = 4;         // Фулстек разработчик
    case WEB_DEVELOPER = 82;              // Веб-разработчик
    case APPLICATION_DEVELOPER = 72;      // Разработчик приложений
    case MOBILE_DEVELOPER = 5;            // Разработчик мобильных приложений
    case RELEASE_MANAGER = 75;            // Релиз менеджер
    case GAME_DEVELOPER = 6;              // Разработчик игр
    case DESKTOP_DEVELOPER = 1;           // Десктоп разработчик
    case DATABASE_DEVELOPER = 77;         // Разработчик баз данных
    case EMBEDDED_ENGINEER = 7;           // Инженер встраиваемых систем
    case HTML_CODER = 83;                 // HTML-верстальщик
    case C1_DEVELOPER = 84;               // Программист 1С
    case SOFTWARE_ARCHITECT = 73;         // Архитектор ПО
    case SYSTEM_ENGINEER = 8;             // Системный инженер
    case ERP_DEVELOPER = 85;              // ERP-программист
    case DATABASE_ARCHITECT = 86;         // Архитектор БД
    case HARDWARE_ENGINEER = 188;         // Инженер электронных устройств
    case C1_ARCHITECT = 178;              // Архитектор 1С
    case DEV_OTHER = 106;                 // Другое (Разработка)

    // Тестирование (group_id: 2)
    case MANUAL_TESTER = 12;              // Инженер по ручному тестированию
    case AUTO_TESTER = 10;                // Инженер по автоматизации тестирования
    case QA_ENGINEER = 13;                // Инженер по обеспечению качества
    case UX_TESTER = 87;                  // UX-тестировщик
    case PERFORMANCE_TESTER = 11;         // Инженер по производительности
    case QA_ANALYST = 14;                 // Аналитик по обеспечению качества
    case QA_MANAGER = 15;                 // Менеджер по обеспечению качества
    case QA_DIRECTOR = 16;                // Директор по обеспечению качества
    case QA_OTHER = 107;                  // Другое (Тестирование)

    // Аналитика (group_id: 6)
    case MOBILE_ANALYST = 98;             // Аналитик мобильных приложений
    case SYSTEMS_ANALYST = 41;            // Системный аналитик
    case BUSINESS_ANALYST = 42;           // Бизнес-аналитик
    case DATA_ANALYST = 43;               // Аналитик по данным
    case UX_ANALYST = 168;                // UX-аналитик
    case GAME_ANALYST = 99;               // Гейм-аналитик
    case DATA_ENGINEER = 76;              // Инженер по данным
    case SOFTWARE_ANALYST = 96;           // Программный аналитик
    case PRODUCT_ANALYST = 97;            // Продуктовый аналитик
    case BI_DEVELOPER = 95;               // BI-разработчик
    case WEB_ANALYST = 100;               // Веб-аналитик
    case C1_ANALYST = 133;                // Аналитик 1С
    case ANALYTICS_OTHER = 111;           // Другое (Аналитика)

    // Дизайн (group_id: 4)
    case PRODUCT_DESIGNER = 94;           // Продуктовый дизайнер
    case UI_UX_DESIGNER = 23;             // UI/UX дизайнер
    case WEB_DESIGNER = 24;               // Веб дизайнер
    case GRAPHIC_DESIGNER = 30;           // Графический дизайнер
    case APP_DESIGNER = 25;               // Дизайнер приложений
    case ILLUSTRATOR = 27;                // Дизайнер иллюстратор
    case GAME_DESIGNER = 26;              // Дизайнер игр
    case NARRATIVE_DESIGNER = 90;         // Нарративный дизайнер
    case MOTION_DESIGNER = 28;            // Моушен дизайнер
    case ANIMATOR_3D = 91;                // 3d-аниматор
    case FLASH_ANIMATOR = 92;             // Flash-аниматор
    case MODELER_3D = 29;                 // 3d моделлер
    case COMPUTER_GRAPHICS_ARTIST = 93;   // Художник компьютерной графики
    case VUI_DESIGNER = 122;              // VUI-дизайнер
    case ART_DIRECTOR = 31;               // Арт директор
    case DESIGN_OTHER = 109;              // Другое (Дизайн)

    // Менеджмент (group_id: 5)
    case PROJECT_MANAGER = 32;            // Менеджер проекта
    case PROJECT_DIRECTOR = 33;           // Директор проекта
    case PRODUCT_MANAGER = 34;            // Менеджер продукта
    case SCRUM_MASTER = 119;              // Scrum-мастер
    case DELIVERY_MANAGER = 185;          // Деливери-менеджер
    case COMMUNITY_MANAGER = 36;          // Менеджер сообщества
    case PRODUCT_MARKETING = 186;         // Продуктовый маркетолог
    case PROGRAM_MANAGER = 37;            // Программный менеджер
    case MANAGEMENT_OTHER = 110;          // Другое (Менеджмент)

    // Информационная безопасность (group_id: 13)
    case PENTESTER = 78;                  // Пентестер
    case SECURITY_ADMIN = 21;             // Администратор защиты
    case SOC_ANALYST = 172;               // Аналитик SOC
    case INFO_SEC_SPECIALIST = 174;       // Специалист по ИБ
    case REVERSE_ENGINEER = 79;           // Специалист по реверс-инжинирингу
    case APPSEC_ENGINEER = 173;           // AppSec-инженер
    case SECURITY_ENGINEER = 80;          // Инженер по безопасности
    case NLP_ENGINEER = 176;              // NLP-инженер
    case ANTIFRAUD_ANALYST = 81;          // Антифрод аналитик
    case INFO_SEC_ARCHITECT = 182;        // Архитектор ИБ
    case SECURITY_OTHER = 118;            // Другое (ИБ)

    // Топ-менеджмент (group_id: 49)
    case HR_DIRECTOR = 63;                // Директор по персоналу
    case CPO = 35;                        // Директор по продукту (CPO)
    case CCO = 201;                       // Коммерческий директор (CCO)
    case COO = 38;                        // Исполнительный директор (COO)
    case CIO = 70;                        // Директор по информационным технологиям (CIO)
    case CEO = 39;                        // Генеральный директор (CEO)
    case CFO = 71;                        // Финансовый директор (CFO)
    case CTO = 9;                         // Технический директор (CTO)

    // Искусственный интеллект (group_id: 14)
    case DATA_SCIENTIST = 44;             // Ученый по данным
    case ML_ENGINEER = 125;               // ML разработчик
    case CV_ENGINEER = 177;               // Инженер по компьютерному зрению
    case PROMPT_ENGINEER = 175;           // Промпт-инженер
    case AI_OTHER = 126;                  // Другое (ИИ)

    // Поддержка (group_id: 7)
    case SUPPORT_ENGINEER = 49;           // Инженер технической поддержки
    case SUPPORT_MANAGER = 45;            // Менеджер технической поддержки
    case SUPPORT_DIRECTOR = 46;           // Директор технической поддержки
    case SUPPORT_ANALYST = 50;            // Аналитик технической поддержки
    case CUSTOMER_SERVICE_MANAGER = 47;   // Менеджер по обслуживанию клиентов
    case CUSTOMER_SERVICE_DIRECTOR = 48;  // Директор по обслуживанию клиентов
    case MODERATOR = 101;                 // Модератор
    case SUPPORT_OTHER = 112;             // Другое (Поддержка)

    // Производство и строительство (group_id: 50)
    case DESIGN_ENGINEER = 189;           // Инженер-конструктор / Инженер-проектировщик
    case MECHANICAL_ENGINEER = 190;       // Сервисный инженер / Инженер-механик
    case COMMISSIONING_ENGINEER = 191;    // Инженер ПНР
    case QUALITY_ENGINEER = 192;          // Инженер по качеству
    case HSE_ENGINEER = 193;              // Инженер по охране труда и технике безопасности
    case MAINTENANCE_ENGINEER = 194;      // Инженер по эксплуатации
    case ELECTRONICS_ENGINEER = 195;      // Инженер-электронщик
    case POWER_ENGINEER = 196;            // Инженер-энергетик
    case RESEARCH_SCIENTIST = 197;        // Научный специалист, исследователь
    case ELECTRICAL_ENGINEER = 202;       // Инженер-электрик
    case CHIEF_PROJECT_ENGINEER = 198;    // Главный инженер проекта
    case TECHNICAL_SUPERVISION = 199;     // Инженер ПТО / Инженер-сметчик
    case MANUFACTURING_OTHER = 200;       // Другое (Производство)

    // Маркетинг (group_id: 8)
    case MARKETING_MANAGER = 51;          // Менеджер по маркетингу
    case MARKETING_DIRECTOR = 52;         // Директор по маркетингу
    case MARKETING_ANALYST = 53;          // Маркетинговый аналитик
    case SEO_SPECIALIST = 102;            // SEO-специалист
    case SMM_SPECIALIST = 103;            // SMM-специалист
    case TARGETOLOGIST = 104;             // Таргетолог
    case DEVREL = 120;                    // Деврел
    case PR_MANAGER = 121;                // PR-менеджер
    case PPC_SPECIALIST = 132;            // Контекстолог
    case DIRECOLOGIST = 131;              // Директолог
    case ORM_SPECIALIST = 179;            // ORM/SERM-специалист
    case MARKETING_OTHER = 113;           // Другое (Маркетинг)

    // Администрирование (group_id: 3)
    case DEVOPS = 22;                     // DevOps-инженер
    case SYSTEM_ADMIN = 17;               // Системный администратор
    case SERVER_ADMIN = 18;               // Администратор серверов
    case WIRELESS_ENGINEER = 183;         // Инженер по беспроводным системам
    case DBA = 19;                        // Администратор баз данных
    case DATA_CENTER_ENGINEER = 187;      // Инженер ЦОД
    case NETWORK_ENGINEER = 20;           // Сетевой инженер
    case SITE_ADMIN = 89;                 // Администратор сайта
    case MLOPS = 129;                     // MLOps-инженер
    case SRE = 130;                       // Инженер по доступности сервисов (SRE)
    case ADMIN_OTHER = 108;               // Другое (Администрирование)

    // Контент (group_id: 10)
    case UX_WRITER = 171;                 // UX-редактор
    case TECH_WRITER = 74;                // Технический писатель
    case CONTENT_WRITER = 59;             // Создатель контента
    case CONTENT_MANAGER = 60;            // Менеджер по контенту
    case EDITOR = 184;                    // Редактор
    case CONTENT_DIRECTOR = 61;           // Директор по контенту
    case COPYWRITER = 105;                // Копирайтер
    case CONTENT_OTHER = 115;             // Другое (Контент)

    // Продажи (group_id: 9)
    case ACCOUNT_MANAGER = 54;            // Менеджер по работе с клиентами
    case ACCOUNT_DIRECTOR = 55;           // Директор по работе с клиентами
    case SALES_MANAGER = 56;              // Менеджер по продажам
    case PRESALE_ENGINEER = 169;          // Пресейл инженер
    case SALES_DIRECTOR = 57;             // Директор по продажам
    case PRESALE_MANAGER = 170;           // Пресейл менеджер
    case SALES_ANALYST = 58;              // Аналитик продаж
    case SALES_OTHER = 114;               // Другое (Продажи)

    // HR (group_id: 11)
    case HR_MANAGER = 62;                 // Менеджер по персоналу
    case RECRUITMENT_MANAGER = 64;        // Менеджер по найму
    case RESEARCHER = 180;                // Ресечер
    case SOURCER = 181;                   // Сорсер
    case RECRUITMENT_DIRECTOR = 65;       // Директор по найму
    case HR_ANALYST = 66;                 // Аналитик по персоналу
    case HR_TRAINING_MANAGER = 123;       // Менеджер по обучению и развитию
    case HR_BRAND_MANAGER = 124;          // Менеджер по развитию HR-бренда
    case HR_OTHER = 116;                  // Другое (HR)

    // Офис (group_id: 12)
    case OFFICE_MANAGER = 67;             // Офис менеджер
    case ACCOUNTANT = 68;                 // Бухгалтер
    case LAWYER = 69;                     // Юрист
    case OFFICE_OTHER = 117;              // Другое (Офис)

    // Зерокодинг (group_id: 15)
    case ZEROCODER = 127;                 // Зерокодер
    case NOCODE_OTHER = 128;              // Другое (Зерокодинг)

    // Метод для получения названия категории на русском
    public function label(): string
    {
        return match($this) {
            self::BACKEND_DEVELOPER => 'Бэкенд разработчик',
            self::FRONTEND_DEVELOPER => 'Фронтенд разработчик',
            self::FULLSTACK_DEVELOPER => 'Фулстек разработчик',
            self::WEB_DEVELOPER => 'Веб-разработчик',
            self::APPLICATION_DEVELOPER => 'Разработчик приложений',
            self::MOBILE_DEVELOPER => 'Разработчик мобильных приложений',
            self::RELEASE_MANAGER => 'Релиз менеджер',
            self::GAME_DEVELOPER => 'Разработчик игр',
            self::DESKTOP_DEVELOPER => 'Десктоп разработчик',
            self::DATABASE_DEVELOPER => 'Разработчик баз данных',
            self::EMBEDDED_ENGINEER => 'Инженер встраиваемых систем',
            self::HTML_CODER => 'HTML-верстальщик',
            self::C1_DEVELOPER => 'Программист 1С',
            self::SOFTWARE_ARCHITECT => 'Архитектор ПО',
            self::SYSTEM_ENGINEER => 'Системный инженер',
            self::ERP_DEVELOPER => 'ERP-программист',
            self::DATABASE_ARCHITECT => 'Архитектор БД',
            self::HARDWARE_ENGINEER => 'Инженер электронных устройств',
            self::C1_ARCHITECT => 'Архитектор 1С',
            self::DEV_OTHER => 'Другое (Разработка)',
            self::MANUAL_TESTER => 'Инженер по ручному тестированию',
            self::AUTO_TESTER => 'Инженер по автоматизации тестирования',
            self::QA_ENGINEER => 'Инженер по обеспечению качества',
            self::UX_TESTER => 'UX-тестировщик',
            self::PERFORMANCE_TESTER => 'Инженер по производительности',
            self::QA_ANALYST => 'Аналитик по обеспечению качества',
            self::QA_MANAGER => 'Менеджер по обеспечению качества',
            self::QA_DIRECTOR => 'Директор по обеспечению качества',
            self::QA_OTHER => 'Другое (Тестирование)',
            self::MOBILE_ANALYST => 'Аналитик мобильных приложений',
            self::SYSTEMS_ANALYST => 'Системный аналитик',
            self::BUSINESS_ANALYST => 'Бизнес-аналитик',
            self::DATA_ANALYST => 'Аналитик по данным',
            self::UX_ANALYST => 'UX-аналитик',
            self::GAME_ANALYST => 'Гейм-аналитик',
            self::DATA_ENGINEER => 'Инженер по данным',
            self::SOFTWARE_ANALYST => 'Программный аналитик',
            self::PRODUCT_ANALYST => 'Продуктовый аналитик',
            self::BI_DEVELOPER => 'BI-разработчик',
            self::WEB_ANALYST => 'Веб-аналитик',
            self::C1_ANALYST => 'Аналитик 1С',
            self::ANALYTICS_OTHER => 'Другое (Аналитика)',
            self::PRODUCT_DESIGNER => 'Продуктовый дизайнер',
            self::UI_UX_DESIGNER => 'UI/UX дизайнер',
            self::WEB_DESIGNER => 'Веб дизайнер',
            self::GRAPHIC_DESIGNER => 'Графический дизайнер',
            self::APP_DESIGNER => 'Дизайнер приложений',
            self::ILLUSTRATOR => 'Дизайнер иллюстратор',
            self::GAME_DESIGNER => 'Дизайнер игр',
            self::NARRATIVE_DESIGNER => 'Нарративный дизайнер',
            self::MOTION_DESIGNER => 'Моушен дизайнер',
            self::ANIMATOR_3D => '3d-аниматор',
            self::FLASH_ANIMATOR => 'Flash-аниматор',
            self::MODELER_3D => '3d моделлер',
            self::COMPUTER_GRAPHICS_ARTIST => 'Художник компьютерной графики',
            self::VUI_DESIGNER => 'VUI-дизайнер',
            self::ART_DIRECTOR => 'Арт директор',
            self::DESIGN_OTHER => 'Другое (Дизайн)',
            self::PROJECT_MANAGER => 'Менеджер проекта',
            self::PROJECT_DIRECTOR => 'Директор проекта',
            self::PRODUCT_MANAGER => 'Менеджер продукта',
            self::SCRUM_MASTER => 'Scrum-мастер',
            self::DELIVERY_MANAGER => 'Деливери-менеджер',
            self::COMMUNITY_MANAGER => 'Менеджер сообщества',
            self::PRODUCT_MARKETING => 'Продуктовый маркетолог',
            self::PROGRAM_MANAGER => 'Программный менеджер',
            self::MANAGEMENT_OTHER => 'Другое (Менеджмент)',
            self::PENTESTER => 'Пентестер',
            self::SECURITY_ADMIN => 'Администратор защиты',
            self::SOC_ANALYST => 'Аналитик SOC',
            self::INFO_SEC_SPECIALIST => 'Специалист по ИБ',
            self::REVERSE_ENGINEER => 'Специалист по реверс-инжинирингу',
            self::APPSEC_ENGINEER => 'AppSec-инженер',
            self::SECURITY_ENGINEER => 'Инженер по безопасности',
            self::NLP_ENGINEER => 'NLP-инженер',
            self::ANTIFRAUD_ANALYST => 'Антифрод аналитик',
            self::INFO_SEC_ARCHITECT => 'Архитектор ИБ',
            self::SECURITY_OTHER => 'Другое (ИБ)',
            self::HR_DIRECTOR => 'Директор по персоналу',
            self::CPO => 'Директор по продукту (CPO)',
            self::CCO => 'Коммерческий директор (CCO)',
            self::COO => 'Исполнительный директор (COO)',
            self::CIO => 'Директор по информационным технологиям (CIO)',
            self::CEO => 'Генеральный директор (CEO)',
            self::CFO => 'Финансовый директор (CFO)',
            self::CTO => 'Технический директор (CTO)',
            self::DATA_SCIENTIST => 'Ученый по данным',
            self::ML_ENGINEER => 'ML разработчик',
            self::CV_ENGINEER => 'Инженер по компьютерному зрению',
            self::PROMPT_ENGINEER => 'Промпт-инженер',
            self::AI_OTHER => 'Другое (ИИ)',
            self::SUPPORT_ENGINEER => 'Инженер технической поддержки',
            self::SUPPORT_MANAGER => 'Менеджер технической поддержки',
            self::SUPPORT_DIRECTOR => 'Директор технической поддержки',
            self::SUPPORT_ANALYST => 'Аналитик технической поддержки',
            self::CUSTOMER_SERVICE_MANAGER => 'Менеджер по обслуживанию клиентов',
            self::CUSTOMER_SERVICE_DIRECTOR => 'Директор по обслуживанию клиентов',
            self::MODERATOR => 'Модератор',
            self::SUPPORT_OTHER => 'Другое (Поддержка)',
            self::DESIGN_ENGINEER => 'Инженер-конструктор / Инженер-проектировщик',
            self::MECHANICAL_ENGINEER => 'Сервисный инженер / Инженер-механик',
            self::COMMISSIONING_ENGINEER => 'Инженер ПНР',
            self::QUALITY_ENGINEER => 'Инженер по качеству',
            self::HSE_ENGINEER => 'Инженер по охране труда и технике безопасности',
            self::MAINTENANCE_ENGINEER => 'Инженер по эксплуатации',
            self::ELECTRONICS_ENGINEER => 'Инженер-электронщик',
            self::POWER_ENGINEER => 'Инженер-энергетик',
            self::RESEARCH_SCIENTIST => 'Научный специалист, исследователь',
            self::ELECTRICAL_ENGINEER => 'Инженер-электрик',
            self::CHIEF_PROJECT_ENGINEER => 'Главный инженер проекта',
            self::TECHNICAL_SUPERVISION => 'Инженер ПТО / Инженер-сметчик',
            self::MANUFACTURING_OTHER => 'Другое (Производство)',
            self::MARKETING_MANAGER => 'Менеджер по маркетингу',
            self::MARKETING_DIRECTOR => 'Директор по маркетингу',
            self::MARKETING_ANALYST => 'Маркетинговый аналитик',
            self::SEO_SPECIALIST => 'SEO-специалист',
            self::SMM_SPECIALIST => 'SMM-специалист',
            self::TARGETOLOGIST => 'Таргетолог',
            self::DEVREL => 'Деврел',
            self::PR_MANAGER => 'PR-менеджер',
            self::PPC_SPECIALIST => 'Контекстолог',
            self::DIRECOLOGIST => 'Директолог',
            self::ORM_SPECIALIST => 'ORM/SERM-специалист',
            self::MARKETING_OTHER => 'Другое (Маркетинг)',
            self::DEVOPS => 'DevOps-инженер',
            self::SYSTEM_ADMIN => 'Системный администратор',
            self::SERVER_ADMIN => 'Администратор серверов',
            self::WIRELESS_ENGINEER => 'Инженер по беспроводным системам',
            self::DBA => 'Администратор баз данных',
            self::DATA_CENTER_ENGINEER => 'Инженер ЦОД',
            self::NETWORK_ENGINEER => 'Сетевой инженер',
            self::SITE_ADMIN => 'Администратор сайта',
            self::MLOPS => 'MLOps-инженер',
            self::SRE => 'Инженер по доступности сервисов (SRE)',
            self::ADMIN_OTHER => 'Другое (Администрирование)',
            self::UX_WRITER => 'UX-редактор',
            self::TECH_WRITER => 'Технический писатель',
            self::CONTENT_WRITER => 'Создатель контента',
            self::CONTENT_MANAGER => 'Менеджер по контенту',
            self::EDITOR => 'Редактор',
            self::CONTENT_DIRECTOR => 'Директор по контенту',
            self::COPYWRITER => 'Копирайтер',
            self::CONTENT_OTHER => 'Другое (Контент)',
            self::ACCOUNT_MANAGER => 'Менеджер по работе с клиентами',
            self::ACCOUNT_DIRECTOR => 'Директор по работе с клиентами',
            self::SALES_MANAGER => 'Менеджер по продажам',
            self::PRESALE_ENGINEER => 'Пресейл инженер',
            self::SALES_DIRECTOR => 'Директор по продажам',
            self::PRESALE_MANAGER => 'Пресейл менеджер',
            self::SALES_ANALYST => 'Аналитик продаж',
            self::SALES_OTHER => 'Другое (Продажи)',
            self::HR_MANAGER => 'Менеджер по персоналу',
            self::RECRUITMENT_MANAGER => 'Менеджер по найму',
            self::RESEARCHER => 'Ресечер',
            self::SOURCER => 'Сорсер',
            self::RECRUITMENT_DIRECTOR => 'Директор по найму',
            self::HR_ANALYST => 'Аналитик по персоналу',
            self::HR_TRAINING_MANAGER => 'Менеджер по обучению и развитию',
            self::HR_BRAND_MANAGER => 'Менеджер по развитию HR-бренда',
            self::HR_OTHER => 'Другое (HR)',
            self::OFFICE_MANAGER => 'Офис менеджер',
            self::ACCOUNTANT => 'Бухгалтер',
            self::LAWYER => 'Юрист',
            self::OFFICE_OTHER => 'Другое (Офис)',
            self::ZEROCODER => 'Зерокодер',
            self::NOCODE_OTHER => 'Другое (Зерокодинг)',
        };
    }
}
