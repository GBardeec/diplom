<?php

namespace App\Enums;

enum HHCategory: int
{
    case BI_ANALYST = 156;
    case DATA_ANALYST = 160;
    case ANALYST = 10;
    case ART_DIRECTOR = 12;
    case BUSINESS_ANALYST = 150;
    case GAME_DESIGNER = 25;
    case DATA_SCIENTIST = 165;
    case DESIGNER = 34;
    case CIO = 36;
    case PRODUCT_MANAGER = 73;
    case METHODOLOGIST = 155;
    case DEVELOPER = 96;
    case PRODUCT_ANALYST = 164;
    case DEVELOPMENT_TEAM_LEAD = 104;
    case ANALYTICS_HEAD = 157;
    case PROJECT_MANAGER = 107;
    case NETWORK_ENGINEER = 112;
    case SYSTEM_ADMINISTRATOR = 113;
    case SYSTEM_ANALYST = 148;
    case SYSTEM_ENGINEER = 114;
    case INFORMATION_SECURITY_SPECIALIST = 116;
    case TECHNICAL_SUPPORT_SPECIALIST = 121;
    case TESTER = 124;
    case CTO = 125;
    case TECHNICAL_WRITER = 126;

    public function label(): string
    {
        return match($this) {
            self::BI_ANALYST => 'BI-аналитик, аналитик данных',
            self::DATA_ANALYST => 'DevOps-инженер',
            self::ANALYST => 'Аналитик',
            self::ART_DIRECTOR => 'Арт-директор, креативный директор',
            self::BUSINESS_ANALYST => 'Бизнес-аналитик',
            self::GAME_DESIGNER => 'Гейм-дизайнер',
            self::DATA_SCIENTIST => 'Дата-сайентист',
            self::DESIGNER => 'Дизайнер, художник',
            self::CIO => 'Директор по информационным технологиям (CIO)',
            self::PRODUCT_MANAGER => 'Менеджер продукта',
            self::METHODOLOGIST => 'Методолог',
            self::DEVELOPER => 'Программист, разработчик',
            self::PRODUCT_ANALYST => 'Продуктовый аналитик',
            self::DEVELOPMENT_TEAM_LEAD => 'Руководитель группы разработки',
            self::ANALYTICS_HEAD => 'Руководитель отдела аналитики',
            self::PROJECT_MANAGER => 'Руководитель проектов',
            self::NETWORK_ENGINEER => 'Сетевой инженер',
            self::SYSTEM_ADMINISTRATOR => 'Системный администратор',
            self::SYSTEM_ANALYST => 'Системный аналитик',
            self::SYSTEM_ENGINEER => 'Системный инженер',
            self::INFORMATION_SECURITY_SPECIALIST => 'Специалист по информационной безопасности',
            self::TECHNICAL_SUPPORT_SPECIALIST => 'Специалист технической поддержки',
            self::TESTER => 'Тестировщик',
            self::CTO => 'Технический директор (CTO)',
            self::TECHNICAL_WRITER => 'Технический писатель',
        };
    }

    public function getName(): string
    {
        return $this->label();
    }
}
