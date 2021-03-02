<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InitTables extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // skip the migration if there are another migrations
        // It means this migration was already applied
        $migrations = DB::select('SELECT * FROM migrations LIMIT 1');
        if (!empty($migrations)) {
            return;
        }
        $oldMigrationTable = DB::select("SHOW TABLES LIKE 'schema_migrations'");
        if (!empty($oldMigrationTable)) {
            return;
        }

        DB::beginTransaction();

        try {
            app("db")->getPdo()->exec($this->getSql());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();
    }

    private function getSql()
    {
        return <<<SQL
            CREATE TABLE `accounts` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `number` varchar(255) NOT NULL,
              `type_id` int(10) UNSIGNED DEFAULT NULL,
              `user_id` varchar(36) DEFAULT NULL,
              `description` text,
              `is_active` tinyint(1) DEFAULT NULL,
              `balance` decimal(36,18) DEFAULT NULL,
              `allow_withdrawals` tinyint(1) DEFAULT NULL,
              `allow_deposits` tinyint(1) DEFAULT NULL,
              `maturity_date` datetime DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `payout_day` int(2) UNSIGNED DEFAULT NULL,
              `interest_account_id` bigint(20) UNSIGNED DEFAULT NULL,
              `available_amount` decimal(36,18) DEFAULT NULL,
              `initial_balance` decimal(36,18) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `account_types` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `currency_code` varchar(64) NOT NULL,
              `balance_fee_amount` decimal(36,18) DEFAULT NULL,
              `balance_charge_day` int(2) UNSIGNED DEFAULT NULL,
              `balance_limit_amount` decimal(36,18) DEFAULT NULL,
              `credit_limit_amount` decimal(36,18) DEFAULT NULL,
              `credit_annual_interest_rate` decimal(36,18) DEFAULT NULL,
              `credit_payout_method_id` int(10) UNSIGNED DEFAULT NULL,
              `credit_charge_period_id` int(10) UNSIGNED DEFAULT NULL,
              `credit_charge_day` int(10) UNSIGNED DEFAULT NULL,
              `credit_charge_month` int(2) UNSIGNED DEFAULT NULL,
              `deposit_annual_interest_rate` decimal(36,18) DEFAULT NULL,
              `deposit_payout_method_id` int(10) UNSIGNED DEFAULT NULL,
              `deposit_payout_period_id` int(10) UNSIGNED DEFAULT NULL,
              `deposit_payout_day` int(2) UNSIGNED DEFAULT NULL,
              `deposit_payout_month` int(2) UNSIGNED DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `code` varchar(255) DEFAULT NULL,
              `auto_number_generation` tinyint(1) DEFAULT NULL,
              `number_prefix` varchar(64) DEFAULT NULL,
              `monthly_maintenance_fee` decimal(36,18) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `balance_snapshots` (
              `id` int(10) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `balance_type_id` int(10) UNSIGNED NOT NULL,
              `balance_id` int(10) UNSIGNED DEFAULT NULL,
              `user_id` varchar(128) DEFAULT NULL,
              `snapshot` varchar(255) NOT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `balance_types` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(128) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            INSERT INTO `balance_types` (`id`, `name`) VALUES
            (1, 'account'),
            (2, 'card'),
            (3, 'revenue_account');

            CREATE TABLE `bank_details` (
              `id` int(10) UNSIGNED NOT NULL,
              `swift_code` varchar(255) NOT NULL,
              `bank_name` varchar(255) NOT NULL,
              `address` varchar(255) NOT NULL,
              `location` varchar(255) NOT NULL,
              `country_id` int(10) UNSIGNED NOT NULL,
              `aba_number` varchar(255) DEFAULT NULL,
              `iban` varchar(255) DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `beneficiary_customers` (
              `id` int(10) UNSIGNED NOT NULL,
              `account_name` varchar(255) NOT NULL,
              `address` varchar(255) NOT NULL,
              `iban` varchar(255) NOT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `cards` (
              `id` int(10) UNSIGNED NOT NULL,
              `number` varchar(255) NOT NULL,
              `status` varchar(255) NOT NULL,
              `card_type_id` int(10) UNSIGNED NOT NULL,
              `balance` decimal(36,18) NOT NULL,
              `user_id` varchar(36) NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `expiration_year` int(11) NOT NULL,
              `expiration_month` int(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `card_types` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `currency_code` varchar(64) NOT NULL,
              `icon_id` bigint(20) UNSIGNED DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `card_type_category_id` int(10) UNSIGNED DEFAULT NULL,
              `card_type_format_id` int(10) UNSIGNED DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `card_type_categories` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(128) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            INSERT INTO `card_type_categories` (`id`, `name`) VALUES
            (1, 'Manual'),
            (2, 'Integrated');

            CREATE TABLE `card_type_formats` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(128) NOT NULL,
              `code` varchar(64) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            INSERT INTO `card_type_formats` (`id`, `name`, `code`) VALUES
            (1, 'Alphanumeric', 'alphanumeric'),
            (2, '16 numeric digits', 'sixteen_numeric');

            CREATE TABLE `countries` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) NOT NULL,
              `code` varchar(2) NOT NULL,
              `code3` varchar(3) NOT NULL,
              `code_numeric` varchar(3) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            INSERT INTO `countries` (`id`, `name`, `code`, `code3`, `code_numeric`) VALUES
            (1, 'Afghanistan', 'AF', 'AFG', '004'),
            (2, 'Aland Islands', 'AX', 'ALA', '248'),
            (3, 'Albania', 'AL', 'ALB', '008'),
            (4, 'Algeria', 'DZ', 'DZA', '012'),
            (5, 'American Samoa', 'AS', 'ASM', '016'),
            (6, 'Andorra', 'AD', 'AND', '020'),
            (7, 'Angola', 'AO', 'AGO', '024'),
            (8, 'Anguilla', 'AI', 'AIA', '660'),
            (9, 'Antarctica', 'AQ', 'ATA', '010'),
            (10, 'Antigua and Barbuda', 'AG', 'ATG', '028'),
            (11, 'Argentina', 'AR', 'ARG', '032'),
            (12, 'Armenia', 'AM', 'ARM', '051'),
            (13, 'Aruba', 'AW', 'ABW', '533'),
            (14, 'Australia', 'AU', 'AUS', '036'),
            (15, 'Austria', 'AT', 'AUT', '040'),
            (16, 'Azerbaijan', 'AZ', 'AZE', '031'),
            (17, 'Bahamas', 'BS', 'BHS', '044'),
            (18, 'Bahrain', 'BH', 'BHR', '048'),
            (19, 'Bangladesh', 'BD', 'BGD', '050'),
            (20, 'Barbados', 'BB', 'BRB', '052'),
            (21, 'Belarus', 'BY', 'BLR', '112'),
            (22, 'Belgium', 'BE', 'BEL', '056'),
            (23, 'Belize', 'BZ', 'BLZ', '084'),
            (24, 'Benin', 'BJ', 'BEN', '204'),
            (25, 'Bermuda', 'BM', 'BMU', '060'),
            (26, 'Bhutan', 'BT', 'BTN', '064'),
            (27, 'Bolivia', 'BO', 'BOL', '068'),
            (28, 'Bosnia and Herzegovina', 'BA', 'BIH', '070'),
            (29, 'Botswana', 'BW', 'BWA', '072'),
            (30, 'Bouvet Island', 'BV', 'BVT', '074'),
            (31, 'Brazil', 'BR', 'BRA', '076'),
            (32, 'British Virgin Islands', 'VG', 'VGB', '092'),
            (33, 'British Indian Ocean Territory', 'IO', 'IOT', '086'),
            (34, 'Brunei Darussalam', 'BN', 'BRN', '096'),
            (35, 'Bulgaria', 'BG', 'BGR', '100'),
            (36, 'Burkina Faso', 'BF', 'BFA', '854'),
            (37, 'Burundi', 'BI', 'BDI', '108'),
            (38, 'Cambodia', 'KH', 'KHM', '116'),
            (39, 'Cameroon', 'CM', 'CMR', '120'),
            (40, 'Canada', 'CA', 'CAN', '124'),
            (41, 'Cape Verde', 'CV', 'CPV', '132'),
            (42, 'Cayman Islands', 'KY', 'CYM', '136'),
            (43, 'Central African Republic', 'CF', 'CAF', '140'),
            (44, 'Chad', 'TD', 'TCD', '148'),
            (45, 'Chile', 'CL', 'CHL', '152'),
            (46, 'China', 'CN', 'CHN', '156'),
            (47, 'Hong Kong, SAR China', 'HK', 'HKG', '344'),
            (48, 'Macao, SAR China', 'MO', 'MAC', '446'),
            (49, 'Christmas Island', 'CX', 'CXR', '162'),
            (50, 'Cocos (Keeling) Islands', 'CC', 'CCK', '166'),
            (51, 'Colombia', 'CO', 'COL', '170'),
            (52, 'Comoros', 'KM', 'COM', '174'),
            (53, 'Congo (Brazzaville)', 'CG', 'COG', '178'),
            (54, 'Congo, (Kinshasa)', 'CD', 'COD', '180'),
            (55, 'Cook Islands', 'CK', 'COK', '184'),
            (56, 'Costa Rica', 'CR', 'CRI', '188'),
            (57, "Côte d\'Ivoire", 'CI', 'CIV', '384'),
            (58, 'Croatia', 'HR', 'HRV', '191'),
            (59, 'Cuba', 'CU', 'CUB', '192'),
            (60, 'Cyprus', 'CY', 'CYP', '196'),
            (61, 'Czech Republic', 'CZ', 'CZE', '203'),
            (62, 'Denmark', 'DK', 'DNK', '208'),
            (63, 'Djibouti', 'DJ', 'DJI', '262'),
            (64, 'Dominica', 'DM', 'DMA', '212'),
            (65, 'Dominican Republic', 'DO', 'DOM', '214'),
            (66, 'Ecuador', 'EC', 'ECU', '218'),
            (67, 'Egypt', 'EG', 'EGY', '818'),
            (68, 'El Salvador', 'SV', 'SLV', '222'),
            (69, 'Equatorial Guinea', 'GQ', 'GNQ', '226'),
            (70, 'Eritrea', 'ER', 'ERI', '232'),
            (71, 'Estonia', 'EE', 'EST', '233'),
            (72, 'Ethiopia', 'ET', 'ETH', '231'),
            (73, 'Falkland Islands (Malvinas)', 'FK', 'FLK', '238'),
            (74, 'Faroe Islands', 'FO', 'FRO', '234'),
            (75, 'Fiji', 'FJ', 'FJI', '242'),
            (76, 'Finland', 'FI', 'FIN', '246'),
            (77, 'France', 'FR', 'FRA', '250'),
            (78, 'French Guiana', 'GF', 'GUF', '254'),
            (79, 'French Polynesia', 'PF', 'PYF', '258'),
            (80, 'French Southern Territories', 'TF', 'ATF', '260'),
            (81, 'Gabon', 'GA', 'GAB', '266'),
            (82, 'Gambia', 'GM', 'GMB', '270'),
            (83, 'Georgia', 'GE', 'GEO', '268'),
            (84, 'Germany', 'DE', 'DEU', '276'),
            (85, 'Ghana', 'GH', 'GHA', '288'),
            (86, 'Gibraltar', 'GI', 'GIB', '292'),
            (87, 'Greece', 'GR', 'GRC', '300'),
            (88, 'Greenland', 'GL', 'GRL', '304'),
            (89, 'Grenada', 'GD', 'GRD', '308'),
            (90, 'Guadeloupe', 'GP', 'GLP', '312'),
            (91, 'Guam', 'GU', 'GUM', '316'),
            (92, 'Guatemala', 'GT', 'GTM', '320'),
            (93, 'Guernsey', 'GG', 'GGY', '831'),
            (94, 'Guinea', 'GN', 'GIN', '324'),
            (95, 'Guinea-Bissau', 'GW', 'GNB', '624'),
            (96, 'Guyana', 'GY', 'GUY', '328'),
            (97, 'Haiti', 'HT', 'HTI', '332'),
            (98, 'Heard and Mcdonald Islands', 'HM', 'HMD', '334'),
            (99, 'Holy See (Vatican City State)', 'VA', 'VAT', '336'),
            (100, 'Honduras', 'HN', 'HND', '340'),
            (101, 'Hungary', 'HU', 'HUN', '348'),
            (102, 'Iceland', 'IS', 'ISL', '352'),
            (103, 'India', 'IN', 'IND', '356'),
            (104, 'Indonesia', 'ID', 'IDN', '360'),
            (105, 'Iran, Islamic Republic of', 'IR', 'IRN', '364'),
            (106, 'Iraq', 'IQ', 'IRQ', '368'),
            (107, 'Ireland', 'IE', 'IRL', '372'),
            (108, 'Isle of Man', 'IM', 'IMN', '833'),
            (109, 'Israel', 'IL', 'ISR', '376'),
            (110, 'Italy', 'IT', 'ITA', '380'),
            (111, 'Jamaica', 'JM', 'JAM', '388'),
            (112, 'Japan', 'JP', 'JPN', '392'),
            (113, 'Jersey', 'JE', 'JEY', '832'),
            (114, 'Jordan', 'JO', 'JOR', '400'),
            (115, 'Kazakhstan', 'KZ', 'KAZ', '398'),
            (116, 'Kenya', 'KE', 'KEN', '404'),
            (117, 'Kiribati', 'KI', 'KIR', '296'),
            (118, 'Korea (North)', 'KP', 'PRK', '408'),
            (119, 'Korea (South)', 'KR', 'KOR', '410'),
            (120, 'Kuwait', 'KW', 'KWT', '414'),
            (121, 'Kyrgyzstan', 'KG', 'KGZ', '417'),
            (122, 'Lao PDR', 'LA', 'LAO', '418'),
            (123, 'Latvia', 'LV', 'LVA', '428'),
            (124, 'Lebanon', 'LB', 'LBN', '422'),
            (125, 'Lesotho', 'LS', 'LSO', '426'),
            (126, 'Liberia', 'LR', 'LBR', '430'),
            (127, 'Libya', 'LY', 'LBY', '434'),
            (128, 'Liechtenstein', 'LI', 'LIE', '438'),
            (129, 'Lithuania', 'LT', 'LTU', '440'),
            (130, 'Luxembourg', 'LU', 'LUX', '442'),
            (131, 'Macedonia, Republic of', 'MK', 'MKD', '807'),
            (132, 'Madagascar', 'MG', 'MDG', '450'),
            (133, 'Malawi', 'MW', 'MWI', '454'),
            (134, 'Malaysia', 'MY', 'MYS', '458'),
            (135, 'Maldives', 'MV', 'MDV', '462'),
            (136, 'Mali', 'ML', 'MLI', '466'),
            (137, 'Malta', 'MT', 'MLT', '470'),
            (138, 'Marshall Islands', 'MH', 'MHL', '584'),
            (139, 'Martinique', 'MQ', 'MTQ', '474'),
            (140, 'Mauritania', 'MR', 'MRT', '478'),
            (141, 'Mauritius', 'MU', 'MUS', '480'),
            (142, 'Mayotte', 'YT', 'MYT', '175'),
            (143, 'Mexico', 'MX', 'MEX', '484'),
            (144, 'Micronesia, Federated States of', 'FM', 'FSM', '583'),
            (145, 'Moldova', 'MD', 'MDA', '498'),
            (146, 'Monaco', 'MC', 'MCO', '492'),
            (147, 'Mongolia', 'MN', 'MNG', '496'),
            (148, 'Montenegro', 'ME', 'MNE', '499'),
            (149, 'Montserrat', 'MS', 'MSR', '500'),
            (150, 'Morocco', 'MA', 'MAR', '504'),
            (151, 'Mozambique', 'MZ', 'MOZ', '508'),
            (152, 'Myanmar', 'MM', 'MMR', '104'),
            (153, 'Namibia', 'NA', 'NAM', '516'),
            (154, 'Nauru', 'NR', 'NRU', '520'),
            (155, 'Nepal', 'NP', 'NPL', '524'),
            (156, 'Netherlands', 'NL', 'NLD', '528'),
            (157, 'Netherlands Antilles', 'AN', 'ANT', '530'),
            (158, 'New Caledonia', 'NC', 'NCL', '540'),
            (159, 'New Zealand', 'NZ', 'NZL', '554'),
            (160, 'Nicaragua', 'NI', 'NIC', '558'),
            (161, 'Niger', 'NE', 'NER', '562'),
            (162, 'Nigeria', 'NG', 'NGA', '566'),
            (163, 'Niue', 'NU', 'NIU', '570'),
            (164, 'Norfolk Island', 'NF', 'NFK', '574'),
            (165, 'Northern Mariana Islands', 'MP', 'MNP', '580'),
            (166, 'Norway', 'NO', 'NOR', '578'),
            (167, 'Oman', 'OM', 'OMN', '512'),
            (168, 'Pakistan', 'PK', 'PAK', '586'),
            (169, 'Palau', 'PW', 'PLW', '585'),
            (170, 'Palestinian Territory', 'PS', 'PSE', '275'),
            (171, 'Panama', 'PA', 'PAN', '591'),
            (172, 'Papua New Guinea', 'PG', 'PNG', '598'),
            (173, 'Paraguay', 'PY', 'PRY', '600'),
            (174, 'Peru', 'PE', 'PER', '604'),
            (175, 'Philippines', 'PH', 'PHL', '608'),
            (176, 'Pitcairn', 'PN', 'PCN', '612'),
            (177, 'Poland', 'PL', 'POL', '616'),
            (178, 'Portugal', 'PT', 'PRT', '620'),
            (179, 'Puerto Rico', 'PR', 'PRI', '630'),
            (180, 'Qatar', 'QA', 'QAT', '634'),
            (181, 'Réunion', 'RE', 'REU', '638'),
            (182, 'Romania', 'RO', 'ROU', '642'),
            (183, 'Russian Federation', 'RU', 'RUS', '643'),
            (184, 'Rwanda', 'RW', 'RWA', '646'),
            (185, 'Saint-Barthélemy', 'BL', 'BLM', '652'),
            (186, 'Saint Helena', 'SH', 'SHN', '654'),
            (187, 'Saint Kitts and Nevis', 'KN', 'KNA', '659'),
            (188, 'Saint Lucia', 'LC', 'LCA', '662'),
            (189, 'Saint-Martin (French part)', 'MF', 'MAF', '663'),
            (190, 'Saint Pierre and Miquelon', 'PM', 'SPM', '666'),
            (191, 'Saint Vincent and Grenadines', 'VC', 'VCT', '670'),
            (192, 'Samoa', 'WS', 'WSM', '882'),
            (193, 'San Marino', 'SM', 'SMR', '674'),
            (194, 'Sao Tome and Principe', 'ST', 'STP', '678'),
            (195, 'Saudi Arabia', 'SA', 'SAU', '682'),
            (196, 'Senegal', 'SN', 'SEN', '686'),
            (197, 'Serbia', 'RS', 'SRB', '688'),
            (198, 'Seychelles', 'SC', 'SYC', '690'),
            (199, 'Sierra Leone', 'SL', 'SLE', '694'),
            (200, 'Singapore', 'SG', 'SGP', '702'),
            (201, 'Slovakia', 'SK', 'SVK', '703'),
            (202, 'Slovenia', 'SI', 'SVN', '705'),
            (203, 'Solomon Islands', 'SB', 'SLB', '090'),
            (204, 'Somalia', 'SO', 'SOM', '706'),
            (205, 'South Africa', 'ZA', 'ZAF', '710'),
            (206, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', '239'),
            (207, 'South Sudan', 'SS', 'SSD', '728'),
            (208, 'Spain', 'ES', 'ESP', '724'),
            (209, 'Sri Lanka', 'LK', 'LKA', '144'),
            (210, 'Sudan', 'SD', 'SDN', '736'),
            (211, 'Suriname', 'SR', 'SUR', '740'),
            (212, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM', '744'),
            (213, 'Swaziland', 'SZ', 'SWZ', '748'),
            (214, 'Sweden', 'SE', 'SWE', '752'),
            (215, 'Switzerland', 'CH', 'CHE', '756'),
            (216, 'Syrian Arab Republic (Syria)', 'SY', 'SYR', '760'),
            (217, 'Taiwan, Republic of China', 'TW', 'TWN', '158'),
            (218, 'Tajikistan', 'TJ', 'TJK', '762'),
            (219, 'Tanzania, United Republic of', 'TZ', 'TZA', '834'),
            (220, 'Thailand', 'TH', 'THA', '764'),
            (221, 'Timor-Leste', 'TL', 'TLS', '626'),
            (222, 'Togo', 'TG', 'TGO', '768'),
            (223, 'Tokelau', 'TK', 'TKL', '772'),
            (224, 'Tonga', 'TO', 'TON', '776'),
            (225, 'Trinidad and Tobago', 'TT', 'TTO', '780'),
            (226, 'Tunisia', 'TN', 'TUN', '788'),
            (227, 'Turkey', 'TR', 'TUR', '792'),
            (228, 'Turkmenistan', 'TM', 'TKM', '795'),
            (229, 'Turks and Caicos Islands', 'TC', 'TCA', '796'),
            (230, 'Tuvalu', 'TV', 'TUV', '798'),
            (231, 'Uganda', 'UG', 'UGA', '800'),
            (232, 'Ukraine', 'UA', 'UKR', '804'),
            (233, 'United Arab Emirates', 'AE', 'ARE', '784'),
            (234, 'United Kingdom', 'GB', 'GBR', '826'),
            (235, 'United States of America', 'US', 'USA', '840'),
            (236, 'US Minor Outlying Islands', 'UM', 'UMI', '581'),
            (237, 'Uruguay', 'UY', 'URY', '858'),
            (238, 'Uzbekistan', 'UZ', 'UZB', '860'),
            (239, 'Vanuatu', 'VU', 'VUT', '548'),
            (240, 'Venezuela (Bolivarian Republic)', 'VE', 'VEN', '862'),
            (241, 'Viet Nam', 'VN', 'VNM', '704'),
            (242, 'Virgin Islands, US', 'VI', 'VIR', '850'),
            (243, 'Wallis and Futuna Islands', 'WF', 'WLF', '876'),
            (244, 'Western Sahara', 'EH', 'ESH', '732'),
            (245, 'Yemen', 'YE', 'YEM', '887'),
            (246, 'Zambia', 'ZM', 'ZMB', '894'),
            (247, 'Zimbabwe', 'ZW', 'ZWE', '716'),
            (248, 'Curacao', 'CW', 'CUW', '531');

            CREATE TABLE `iwt_bank_accounts` (
              `id` int(10) UNSIGNED NOT NULL,
              `currency_code` varchar(64) NOT NULL,
              `is_iwt_enabled` tinyint(1) NOT NULL,
              `beneficiary_bank_details_id` int(10) UNSIGNED DEFAULT NULL,
              `intermediary_bank_details_id` int(10) UNSIGNED DEFAULT NULL,
              `beneficiary_customer_id` int(10) UNSIGNED DEFAULT NULL,
              `additional_instructions` varchar(255) NOT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            -- --------------------------------------------------------

            --
            -- table `payment_periods`
            --

            CREATE TABLE `payment_periods` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- data of the table `payment_periods`
            --

            INSERT INTO `payment_periods` (`id`, `name`, `created_at`, `updated_at`) VALUES
            (1, 'Monthly', NULL, NULL),
            (2, 'Quarterly', NULL, NULL),
            (3, 'Bi-Annually', NULL, NULL),
            (4, 'Annually', NULL, NULL);

            -- --------------------------------------------------------

            --
            -- table `payout_methods`
            --

            CREATE TABLE `payout_methods` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `method` varchar(64) NOT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- data of the table `payout_methods`
            --

            INSERT INTO `payout_methods` (`id`, `name`, `method`, `created_at`, `updated_at`) VALUES
            (1, 'Daily interest calculation', 'daily', NULL, '2019-07-30 16:19:23');

            -- --------------------------------------------------------

            --
            -- table `requests`
            --

            CREATE TABLE `requests` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `is_initiated_by_admin` tinyint(1) NOT NULL,
              `is_initiated_by_system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
              `is_visible` tinyint(1) NOT NULL DEFAULT '1',
              `user_id` varchar(36) NOT NULL,
              `status` varchar(50) NOT NULL,
              `subject` varchar(64) NOT NULL,
              `base_currency_code` varchar(64) NOT NULL,
              `reference_currency_code` varchar(64) NOT NULL,
              `amount` decimal(36,18) NOT NULL,
              `rate` decimal(36,18) NOT NULL,
              `description` text,
              `cancellation_reason` varchar(255) DEFAULT NULL,
              `metadata` text,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `status_changed_at` timestamp NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_ca`
            --

            CREATE TABLE `request_data_ca` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `revenue_account_id` int(10) UNSIGNED DEFAULT NULL,
              `destination_account_id` bigint(20) UNSIGNED NOT NULL,
              `debit_from_revenue_account` tinyint(1) DEFAULT NULL,
              `apply_iwt_fee` tinyint(1) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_cft`
            --

            CREATE TABLE `request_data_cft` (
              `id` int(10) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `source_account_id` bigint(20) UNSIGNED NOT NULL,
              `destination_card_id` int(10) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_da`
            --

            CREATE TABLE `request_data_da` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `revenue_account_id` int(10) UNSIGNED DEFAULT NULL,
              `source_account_id` bigint(20) UNSIGNED NOT NULL,
              `credit_to_revenue_account` tinyint(1) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_dra`
            --

            CREATE TABLE `request_data_dra` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `revenue_account_id` int(10) UNSIGNED DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_owt`
            --

            CREATE TABLE `request_data_owt` (
              `id` int(11) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `source_account_id` bigint(20) UNSIGNED NOT NULL,
              `destination_currency_code` varchar(64) NOT NULL,
              `bank_details_id` int(10) UNSIGNED NOT NULL,
              `beneficiary_customer_id` int(10) UNSIGNED NOT NULL,
              `intermediary_bank_details_id` int(10) UNSIGNED DEFAULT NULL,
              `ref_message` varchar(255) NOT NULL,
              `fee_id` int(10) UNSIGNED DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_tba`
            --

            CREATE TABLE `request_data_tba` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `source_account_id` bigint(20) UNSIGNED NOT NULL,
              `destination_account_id` bigint(20) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_data_tbu`
            --

            CREATE TABLE `request_data_tbu` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `source_account_id` bigint(20) UNSIGNED NOT NULL,
              `destination_account_id` bigint(20) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `request_subjects`
            --

            CREATE TABLE `request_subjects` (
              `subject` varchar(64) NOT NULL,
              `description` varchar(255) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- data of the table `request_subjects`
            --

            INSERT INTO `request_subjects` (`subject`, `description`) VALUES
            ('CA', 'Credit Account'),
            ('CFT', 'Card Funding Transfer'),
            ('DA', 'Debit Account'),
            ('DRA', 'Deduct Revenue Account'),
            ('IWT', 'Incoming Wire Transfer'),
            ('OWT', 'Outgoing Wire Transfer'),
            ('TBA', 'Transfer Between Accounts'),
            ('TBU', 'Transfer Between Users');

            -- --------------------------------------------------------

            --
            -- table `request_templates`
            --

            CREATE TABLE `request_templates` (
              `id` int(11) NOT NULL,
              `name` varchar(64) NOT NULL,
              `request_subject` varchar(64) NOT NULL,
              `user_id` varchar(255) NOT NULL,
              `data` text NOT NULL,
              `created_at` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `revenue_accounts`
            --

            CREATE TABLE `revenue_accounts` (
              `id` int(10) UNSIGNED NOT NULL,
              `balance` decimal(36,18) DEFAULT NULL,
              `available_amount` decimal(36,18) DEFAULT NULL,
              `currency_code` varchar(64) DEFAULT NULL,
              `is_default` tinyint(1) DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `scheduled_transactions`
            --

            CREATE TABLE `scheduled_transactions` (
              `id` int(10) UNSIGNED NOT NULL,
              `reason` enum('maintenance_fee','limit_balance_fee','credit_line_fee','interest_generation') NOT NULL,
              `account_id` bigint(20) UNSIGNED NOT NULL,
              `amount` decimal(36,18) NOT NULL,
              `status` enum('pending','executed') NOT NULL,
              `request_id` bigint(20) UNSIGNED DEFAULT NULL,
              `scheduled_date` datetime NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `scheduled_transaction_logs`
            --

            CREATE TABLE `scheduled_transaction_logs` (
              `id` int(10) UNSIGNED NOT NULL,
              `scheduled_transaction_id` int(10) UNSIGNED NOT NULL,
              `amount` decimal(36,18) NOT NULL,
              `created_at` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `schema_migrations`
            --

            CREATE TABLE `schema_migrations` (
              `version` bigint(20) NOT NULL,
              `dirty` tinyint(1) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- data of the table `schema_migrations`
            --

            INSERT INTO `schema_migrations` (`version`, `dirty`) VALUES
            (20190911090434, 0);

            -- --------------------------------------------------------

            --
            -- table `settings`
            --

            CREATE TABLE `settings` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(128) NOT NULL,
              `value` varchar(255) NOT NULL,
              `description` varchar(255) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `updated_at` timestamp NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- data of the table `settings`
            --

            INSERT INTO `settings` (`id`, `name`, `value`, `description`, `created_at`, `updated_at`) VALUES
            (1, 'tba_action_required', 'true', 'Transfer between accounts. Administrator will receive notification about transfer and will have to execute/cancel it.', '2019-08-22 16:27:01', '2019-10-24 18:47:43'),
            (2, 'tba_tan_required', 'false', 'Transfer between accounts. User will have to enter TAN for transfer to proceed.', '2019-08-22 16:26:55', '2019-10-24 18:47:43'),
            (3, 'tbu_action_required', 'true', 'Transfer between users. Administrator will receive notification about transfer and will have to execute/cancel it.', '2019-10-29 13:33:15', '2019-10-24 18:47:43'),
            (4, 'tbu_tan_required', 'false', 'Transfer between users. User will have to enter TAN for transfer to proceed.', '2018-09-21 13:15:16', '2019-10-24 18:47:43'),
            (5, 'owt_action_required', 'true', 'Outgoing wire transfer. Administrator will receive notification about transfer and will have to execute/cancel it.', '2018-11-11 09:50:39', NULL),
            (6, 'owt_tan_required', 'false', 'Outgoing wire transfer. User will have to enter TAN for transfer to proceed.', '2019-08-22 16:27:42', '2019-10-24 18:47:43'),
            (7, 'cft_action_required', 'true', 'Card funding transfer. Administrator will receive notification about transfer and will have to execute/cancel it.', '2018-09-10 12:52:04', NULL),
            (8, 'cft_tan_required', 'false', 'Card funding transfer. User will have to enter TAN for transfer to proceed.', '2019-05-03 10:08:53', '2019-10-24 18:47:43'),
            (9, 'tan_generate_qty', '10', 'Number of tans to generate. Standard TANs Qty.', '2018-09-10 12:52:48', '2019-10-25 06:59:34'),
            (10, 'tan_generate_trigger_qty', '5', 'Number of remaining tans to trigger generation. TANs Qty Remaining Limit.', '2018-09-10 12:52:53', '2019-10-25 06:59:34'),
            (11, 'tan_message_subject', 'TANs', 'Message subject', '2019-10-29 13:35:00', '2019-10-25 06:59:34'),
            (12, 'tan_message_content', 'Please, copy or print this message, since it is only going to be shown once. \r\nYour TANs: \r\n[Tan]', '', '2019-10-29 13:35:20', '2019-10-25 06:59:34');

            -- --------------------------------------------------------

            --
            -- table `tans`
            --

            CREATE TABLE `tans` (
              `id` int(11) UNSIGNED NOT NULL,
              `tan` varchar(128) NOT NULL,
              `uid` varchar(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `tan_subscribers`
            --

            CREATE TABLE `tan_subscribers` (
              `id` int(10) UNSIGNED NOT NULL,
              `uid` varchar(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `transactions`
            --

            CREATE TABLE `transactions` (
              `id` bigint(20) UNSIGNED NOT NULL,
              `request_id` bigint(20) UNSIGNED NOT NULL,
              `description` text,
              `status` varchar(50) NOT NULL,
              `amount` decimal(36,18) NOT NULL,
              `balance_snapshot` decimal(36,18) DEFAULT NULL,
              `type` enum('account','card','revenue','fee') NOT NULL,
              `account_id` bigint(20) UNSIGNED DEFAULT NULL,
              `card_id` int(10) UNSIGNED DEFAULT NULL,
              `revenue_account_id` int(10) UNSIGNED DEFAULT NULL,
              `purpose` varchar(64) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `show_amount` decimal(36,18) DEFAULT NULL,
              `is_visible` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
              `show_balance_snapshot` decimal(36,18) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `transfer_fees`
            --

            CREATE TABLE `transfer_fees` (
              `id` int(10) UNSIGNED NOT NULL,
              `name` varchar(255) NOT NULL,
              `request_subject` varchar(64) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `updated_at` timestamp NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `transfer_fees_parameters`
            --

            CREATE TABLE `transfer_fees_parameters` (
              `id` int(10) UNSIGNED NOT NULL,
              `transfer_fee_id` int(10) UNSIGNED NOT NULL,
              `currency_code` varchar(32) NOT NULL,
              `base` decimal(36,18) UNSIGNED DEFAULT NULL,
              `min` decimal(36,18) UNSIGNED DEFAULT NULL,
              `percent` decimal(20,8) UNSIGNED DEFAULT NULL,
              `max` decimal(36,18) UNSIGNED DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- --------------------------------------------------------

            --
            -- table `transfer_fees_user_groups`
            --

            CREATE TABLE `transfer_fees_user_groups` (
              `transfer_fee_id` int(10) UNSIGNED NOT NULL,
              `user_group_id` int(10) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            --
            -- indexes of the table `accounts`
            --
            ALTER TABLE `accounts`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `number` (`number`),
              ADD KEY `account_type_index` (`type_id`),
              ADD KEY `fk_interest_account` (`interest_account_id`);

            --
            -- indexes of the table `account_types`
            --
            ALTER TABLE `account_types`
              ADD PRIMARY KEY (`id`),
              ADD KEY `credit_payout_method_index` (`credit_payout_method_id`),
              ADD KEY `credit_charge_period_index` (`credit_payout_method_id`),
              ADD KEY `FK_account_type_credit_charge_period` (`credit_charge_period_id`),
              ADD KEY `deposit_payout_method_index` (`deposit_payout_method_id`),
              ADD KEY `deposit_charge_period_index` (`deposit_payout_method_id`),
              ADD KEY `FK_account_type_deposit_charge_period` (`deposit_payout_period_id`);

            --
            -- indexes of the table `balance_snapshots`
            --
            ALTER TABLE `balance_snapshots`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_BALANCE_SNAPSHOTS_TO_BALANCE_TYPES_idx` (`balance_type_id`),
              ADD KEY `FK_BALANCE_SNAPSHOTS_TO_REQUESTS_idx` (`request_id`);

            --
            -- indexes of the table `balance_types`
            --
            ALTER TABLE `balance_types`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `name_UNIQUE` (`name`);

            --
            -- indexes of the table `bank_details`
            --
            ALTER TABLE `bank_details`
              ADD PRIMARY KEY (`id`),
              ADD KEY `bank_details_country_index` (`country_id`);

            --
            -- indexes of the table `beneficiary_customers`
            --
            ALTER TABLE `beneficiary_customers`
              ADD PRIMARY KEY (`id`);

            --
            -- indexes of the table `cards`
            --
            ALTER TABLE `cards`
              ADD PRIMARY KEY (`id`),
              ADD KEY `card_type_index` (`card_type_id`);

            --
            -- indexes of the table `card_types`
            --
            ALTER TABLE `card_types`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_CARD_TYPES_TO_CARD_TYPE_CATEGORIES_idx` (`card_type_category_id`),
              ADD KEY `FK_CARD_TYPES_TO_CARD_TYPE_FORMATS_idx` (`card_type_format_id`);

            --
            -- indexes of the table `card_type_categories`
            --
            ALTER TABLE `card_type_categories`
              ADD PRIMARY KEY (`id`);

            --
            -- indexes of the table `card_type_formats`
            --
            ALTER TABLE `card_type_formats`
              ADD PRIMARY KEY (`id`);

            --
            -- indexes of the table `countries`
            --
            ALTER TABLE `countries`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `code_UNIQUE` (`code`),
              ADD UNIQUE KEY `code3_UNIQUE` (`code3`),
              ADD UNIQUE KEY `code_numeric_UNIQUE` (`code_numeric`);

            --
            -- indexes of the table `iwt_bank_accounts`
            --
            ALTER TABLE `iwt_bank_accounts`
              ADD PRIMARY KEY (`id`),
              ADD KEY `iwt_bank_account_beneficiary_bank_details_index` (`beneficiary_bank_details_id`),
              ADD KEY `iwt_bank_account_intermediary_bank_details_index` (`intermediary_bank_details_id`),
              ADD KEY `iwt_bank_account_beneficiary_customer_index` (`beneficiary_customer_id`);

            --
            -- indexes of the table `payment_periods`
            --
            ALTER TABLE `payment_periods`
              ADD PRIMARY KEY (`id`);

            --
            -- indexes of the table `payout_methods`
            --
            ALTER TABLE `payout_methods`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `method_UNIQUE` (`method`);

            --
            -- indexes of the table `requests`
            --
            ALTER TABLE `requests`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_REQUESTS_TO_REQUEST_SUBJECTS_idx` (`subject`);

            --
            -- indexes of the table `request_data_ca`
            --
            ALTER TABLE `request_data_ca`
              ADD PRIMARY KEY (`id`),
              ADD KEY `fk_request_data_ca_to_revenue_accounts_idx` (`revenue_account_id`),
              ADD KEY `fk_request_data_ca_to_accounts_idx` (`destination_account_id`),
              ADD KEY `fk_request_data_data_ca_to_requests_idx` (`request_id`);

            --
            -- indexes of the table `request_data_cft`
            --
            ALTER TABLE `request_data_cft`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_REQUEST_DATA_CFT_TO_REQUESTS_idx` (`request_id`),
              ADD KEY `FK_REQUEST_DATA_CFT_TO_ACCOUNTS_idx` (`source_account_id`),
              ADD KEY `FK_REQUEST_DATA_CFT_TO_CARDS_idx` (`destination_card_id`);

            --
            -- indexes of the table `request_data_da`
            --
            ALTER TABLE `request_data_da`
              ADD PRIMARY KEY (`id`),
              ADD KEY `fk_request_data_da_to_revenue_accounts_idx` (`revenue_account_id`),
              ADD KEY `fk_request_data_da_to_accounts_idx` (`source_account_id`),
              ADD KEY `fk_request_data_da_to_requests_idx` (`request_id`);

            --
            -- indexes of the table `request_data_dra`
            --
            ALTER TABLE `request_data_dra`
              ADD PRIMARY KEY (`id`),
              ADD KEY `fk_request_data_dra_to_revenue_accounts_idx` (`revenue_account_id`),
              ADD KEY `fk_request_data_dra_to_requests_idx` (`request_id`);

            --
            -- indexes of the table `request_data_owt`
            --
            ALTER TABLE `request_data_owt`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_REQUEST_DATA_OWT_TO_ACCOUNTS_idx` (`source_account_id`),
              ADD KEY `FK_REQUEST_DATA_OWT_TO_BANK_DETAILS_idx` (`bank_details_id`),
              ADD KEY `FK_REQUEST_DATA_OWT_TO_BENEFICIARY_CUSTOMERS_idx` (`beneficiary_customer_id`),
              ADD KEY `FK_REQUEST_DATA_OWT_BANK_DETAILS2_idx` (`intermediary_bank_details_id`),
              ADD KEY `FK_REQUEST_DATA_OWT_TO_REQUESTS_idx` (`request_id`);

            --
            -- indexes of the table `request_data_tba`
            --
            ALTER TABLE `request_data_tba`
              ADD PRIMARY KEY (`id`),
              ADD KEY `fk_request_data_tba_to_accounts1_idx` (`source_account_id`),
              ADD KEY `fk_request_data_tba_to_accounts2_idx` (`destination_account_id`),
              ADD KEY `fk_request_data_tba_to_requests_idx` (`request_id`);

            --
            -- indexes of the table `request_data_tbu`
            --
            ALTER TABLE `request_data_tbu`
              ADD PRIMARY KEY (`id`),
              ADD KEY `fk_request_data_tbu_to_requests_idx` (`request_id`),
              ADD KEY `fk_request_data_tbu_to_accounts_idx` (`source_account_id`),
              ADD KEY `fk_request_data_tbu_accounts2_idx` (`destination_account_id`);

            --
            -- indexes of the table `request_subjects`
            --
            ALTER TABLE `request_subjects`
              ADD PRIMARY KEY (`subject`);

            --
            -- indexes of the table `request_templates`
            --
            ALTER TABLE `request_templates`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `name_UNIQUE` (`name`,`request_subject`,`user_id`),
              ADD KEY `FK_REQUSET_TEMPLATES_TO_REQUEST_SUBJECTS_idx` (`request_subject`);

            --
            -- indexes of the table `revenue_accounts`
            --
            ALTER TABLE `revenue_accounts`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `currency_id_is_defailt` (`currency_code`,`is_default`);

            --
            -- indexes of the table `scheduled_transactions`
            --
            ALTER TABLE `scheduled_transactions`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_SCHEDULED_TRANSACTIONS_TO_ACCOUNTS_idx` (`account_id`),
              ADD KEY `FK_SCHEDULED_TRANSACTIONS_TO_REQUESTS_idx` (`request_id`);

            --
            -- indexes of the table `scheduled_transaction_logs`
            --
            ALTER TABLE `scheduled_transaction_logs`
              ADD PRIMARY KEY (`id`),
              ADD KEY `FK_SCHEDULED_TRANSACTION_LOGS_TO_SCHEDULED_TRANSACTIONS_idx` (`scheduled_transaction_id`);

            --
            -- indexes of the table `schema_migrations`
            --
            ALTER TABLE `schema_migrations`
              ADD PRIMARY KEY (`version`);

            --
            -- indexes of the table `settings`
            --
            ALTER TABLE `settings`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `name_UNIQUE` (`name`);

            --
            -- indexes of the table `tans`
            --
            ALTER TABLE `tans`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `tan_UNIQUE` (`tan`),
              ADD KEY `uid_idx` (`uid`);

            --
            -- indexes of the table `tan_subscribers`
            --
            ALTER TABLE `tan_subscribers`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `user_id_UNIQUE` (`uid`);

            --
            -- indexes of the table `transactions`
            --
            ALTER TABLE `transactions`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `purpose_UNIQUE` (`purpose`,`request_id`),
              ADD KEY `request_index` (`request_id`),
              ADD KEY `FK_transaction_to_accounts_idx` (`account_id`),
              ADD KEY `FK_transaction_to_revenue_accounts_idx` (`revenue_account_id`),
              ADD KEY `FK_TRANSACTIONS_TO_CARDS_idx` (`card_id`);

            --
            -- indexes of the table `transfer_fees`
            --
            ALTER TABLE `transfer_fees`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `name_subject_UNIQUE` (`name`,`request_subject`);

            --
            -- indexes of the table `transfer_fees_parameters`
            --
            ALTER TABLE `transfer_fees_parameters`
              ADD PRIMARY KEY (`id`),
              ADD UNIQUE KEY `transfer_fee_id_UNIQUE` (`transfer_fee_id`,`currency_code`);

            --
            -- indexes of the table `transfer_fees_user_groups`
            --
            ALTER TABLE `transfer_fees_user_groups`
              ADD KEY `FK_TRANSFER_FEES_USER_GROUPS_TO_TRANSFER_FEES_idx` (`transfer_fee_id`);

            --
            -- AUTO_INCREMENT for of the table `accounts`
            --
            ALTER TABLE `accounts`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371;

            --
            -- AUTO_INCREMENT for of the table `account_types`
            --
            ALTER TABLE `account_types`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

            --
            -- AUTO_INCREMENT for of the table `balance_snapshots`
            --
            ALTER TABLE `balance_snapshots`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2758;

            --
            -- AUTO_INCREMENT for of the table `balance_types`
            --
            ALTER TABLE `balance_types`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

            --
            -- AUTO_INCREMENT for of the table `bank_details`
            --
            ALTER TABLE `bank_details`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=451;

            --
            -- AUTO_INCREMENT for of the table `beneficiary_customers`
            --
            ALTER TABLE `beneficiary_customers`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=394;

            --
            -- AUTO_INCREMENT for of the table `cards`
            --
            ALTER TABLE `cards`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

            --
            -- AUTO_INCREMENT for of the table `card_types`
            --
            ALTER TABLE `card_types`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

            --
            -- AUTO_INCREMENT for of the table `card_type_categories`
            --
            ALTER TABLE `card_type_categories`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

            --
            -- AUTO_INCREMENT for of the table `card_type_formats`
            --
            ALTER TABLE `card_type_formats`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

            --
            -- AUTO_INCREMENT for of the table `countries`
            --
            ALTER TABLE `countries`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

            --
            -- AUTO_INCREMENT for of the table `iwt_bank_accounts`
            --
            ALTER TABLE `iwt_bank_accounts`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

            --
            -- AUTO_INCREMENT for of the table `payment_periods`
            --
            ALTER TABLE `payment_periods`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

            --
            -- AUTO_INCREMENT for of the table `payout_methods`
            --
            ALTER TABLE `payout_methods`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

            --
            -- AUTO_INCREMENT for of the table `requests`
            --
            ALTER TABLE `requests`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132871;

            --
            -- AUTO_INCREMENT for of the table `request_data_ca`
            --
            ALTER TABLE `request_data_ca`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39971;

            --
            -- AUTO_INCREMENT for of the table `request_data_cft`
            --
            ALTER TABLE `request_data_cft`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

            --
            -- AUTO_INCREMENT for of the table `request_data_da`
            --
            ALTER TABLE `request_data_da`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90879;

            --
            -- AUTO_INCREMENT for of the table `request_data_dra`
            --
            ALTER TABLE `request_data_dra`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

            --
            -- AUTO_INCREMENT for of the table `request_data_owt`
            --
            ALTER TABLE `request_data_owt`
              MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=344;

            --
            -- AUTO_INCREMENT for of the table `request_data_tba`
            --
            ALTER TABLE `request_data_tba`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=629;

            --
            -- AUTO_INCREMENT for of the table `request_data_tbu`
            --
            ALTER TABLE `request_data_tbu`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

            --
            -- AUTO_INCREMENT for of the table `request_templates`
            --
            ALTER TABLE `request_templates`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

            --
            -- AUTO_INCREMENT for of the table `revenue_accounts`
            --
            ALTER TABLE `revenue_accounts`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

            --
            -- AUTO_INCREMENT for of the table `scheduled_transactions`
            --
            ALTER TABLE `scheduled_transactions`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=497;

            --
            -- AUTO_INCREMENT for of the table `scheduled_transaction_logs`
            --
            ALTER TABLE `scheduled_transaction_logs`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11586;

            --
            -- AUTO_INCREMENT for of the table `settings`
            --
            ALTER TABLE `settings`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

            --
            -- AUTO_INCREMENT for of the table `tans`
            --
            ALTER TABLE `tans`
              MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4685;

            --
            -- AUTO_INCREMENT for of the table `tan_subscribers`
            --
            ALTER TABLE `tan_subscribers`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

            --
            -- AUTO_INCREMENT for of the table `transactions`
            --
            ALTER TABLE `transactions`
              MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5393;

            --
            -- AUTO_INCREMENT for of the table `transfer_fees`
            --
            ALTER TABLE `transfer_fees`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

            --
            -- AUTO_INCREMENT for of the table `transfer_fees_parameters`
            --
            ALTER TABLE `transfer_fees_parameters`
              MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

            --
            -- constrains for the table `accounts`
            --
            ALTER TABLE `accounts`
              ADD CONSTRAINT `FK_account_account_types` FOREIGN KEY (`type_id`) REFERENCES `account_types` (`id`),
              ADD CONSTRAINT `fk_interest_account` FOREIGN KEY (`interest_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

            --
            -- constrains for the table `account_types`
            --
            ALTER TABLE `account_types`
              ADD CONSTRAINT `FK_account_type_credit_charge_period` FOREIGN KEY (`credit_charge_period_id`) REFERENCES `payment_periods` (`id`),
              ADD CONSTRAINT `FK_account_type_credit_payout_method` FOREIGN KEY (`credit_payout_method_id`) REFERENCES `payout_methods` (`id`),
              ADD CONSTRAINT `FK_account_type_deposit_charge_period` FOREIGN KEY (`deposit_payout_period_id`) REFERENCES `payment_periods` (`id`),
              ADD CONSTRAINT `FK_account_type_deposit_payout_method` FOREIGN KEY (`deposit_payout_method_id`) REFERENCES `payout_methods` (`id`);

            --
            -- constrains for the table `balance_snapshots`
            --
            ALTER TABLE `balance_snapshots`
              ADD CONSTRAINT `FK_BALANCE_SNAPSHOTS_TO_BALANCE_TYPES` FOREIGN KEY (`balance_type_id`) REFERENCES `balance_types` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_BALANCE_SNAPSHOTS_TO_REQUESTS` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `bank_details`
            --
            ALTER TABLE `bank_details`
              ADD CONSTRAINT `FK_bank_details_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

            --
            -- constrains for the table `cards`
            --
            ALTER TABLE `cards`
              ADD CONSTRAINT `FK_cards_card_types` FOREIGN KEY (`card_type_id`) REFERENCES `card_types` (`id`);

            --
            -- constrains for the table `card_types`
            --
            ALTER TABLE `card_types`
              ADD CONSTRAINT `FK_CARD_TYPES_TO_CARD_TYPE_CATEGORIES` FOREIGN KEY (`card_type_category_id`) REFERENCES `card_type_categories` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_CARD_TYPES_TO_CARD_TYPE_FORMATS` FOREIGN KEY (`card_type_format_id`) REFERENCES `card_type_formats` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `iwt_bank_accounts`
            --
            ALTER TABLE `iwt_bank_accounts`
              ADD CONSTRAINT `FK_iwt_bank_account_beneficiary_bank_details` FOREIGN KEY (`beneficiary_bank_details_id`) REFERENCES `bank_details` (`id`),
              ADD CONSTRAINT `FK_iwt_bank_account_beneficiary_customer` FOREIGN KEY (`beneficiary_customer_id`) REFERENCES `beneficiary_customers` (`id`),
              ADD CONSTRAINT `FK_iwt_bank_account_intermediary_bank_details` FOREIGN KEY (`intermediary_bank_details_id`) REFERENCES `bank_details` (`id`);

            --
            -- constrains for the table `requests`
            --
            ALTER TABLE `requests`
              ADD CONSTRAINT `FK_REQUESTS_TO_REQUEST_SUBJECTS` FOREIGN KEY (`subject`) REFERENCES `request_subjects` (`subject`) ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_ca`
            --
            ALTER TABLE `request_data_ca`
              ADD CONSTRAINT `fk_request_data_ca_to_accounts_idx` FOREIGN KEY (`destination_account_id`) REFERENCES `accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_request_data_ca_to_requests_idx` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_ca_to_revenue_accounts_idx` FOREIGN KEY (`revenue_account_id`) REFERENCES `revenue_accounts` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_cft`
            --
            ALTER TABLE `request_data_cft`
              ADD CONSTRAINT `FK_REQUEST_DATA_CFT_TO_ACCOUNTS` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_CFT_TO_CARDS` FOREIGN KEY (`destination_card_id`) REFERENCES `cards` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_CFT_TO_REQUESTS` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_da`
            --
            ALTER TABLE `request_data_da`
              ADD CONSTRAINT `fk_request_data_da_to_accounts_idx` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_request_data_da_to_requests_idx` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_da_to_revenue_accounts_idx` FOREIGN KEY (`revenue_account_id`) REFERENCES `revenue_accounts` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_dra`
            --
            ALTER TABLE `request_data_dra`
              ADD CONSTRAINT `fk_request_data_dra_to_requests_idx` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_dra_to_revenue_accounts_idx` FOREIGN KEY (`revenue_account_id`) REFERENCES `revenue_accounts` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_owt`
            --
            ALTER TABLE `request_data_owt`
              ADD CONSTRAINT `FK_REQUEST_DATA_OWT_BANK_DETAILS2` FOREIGN KEY (`intermediary_bank_details_id`) REFERENCES `bank_details` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_OWT_TO_ACCOUNTS` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_OWT_TO_BANK_DETAILS` FOREIGN KEY (`bank_details_id`) REFERENCES `bank_details` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_OWT_TO_BENEFICIARY_CUSTOMERS` FOREIGN KEY (`beneficiary_customer_id`) REFERENCES `beneficiary_customers` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_REQUEST_DATA_OWT_TO_REQUESTS` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_tba`
            --
            ALTER TABLE `request_data_tba`
              ADD CONSTRAINT `fk_request_data_tba_to_accounts1` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_tba_to_accounts2` FOREIGN KEY (`destination_account_id`) REFERENCES `accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_request_data_tba_to_requests` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `request_data_tbu`
            --
            ALTER TABLE `request_data_tbu`
              ADD CONSTRAINT `fk_request_data_tbu_accounts2` FOREIGN KEY (`destination_account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_tbu_to_accounts` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `fk_request_data_tbu_to_requests` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `request_templates`
            --
            ALTER TABLE `request_templates`
              ADD CONSTRAINT `FK_REQUSET_TEMPLATES_TO_REQUEST_SUBJECTS` FOREIGN KEY (`request_subject`) REFERENCES `request_subjects` (`subject`) ON UPDATE CASCADE;

            --
            -- constrains for the table `scheduled_transactions`
            --
            ALTER TABLE `scheduled_transactions`
              ADD CONSTRAINT `FK_SCHEDULED_TRANSACTIONS_TO_ACCOUNTS` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_SCHEDULED_TRANSACTIONS_TO_REQUESTS` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON UPDATE CASCADE;

            --
            -- constrains for the table `scheduled_transaction_logs`
            --
            ALTER TABLE `scheduled_transaction_logs`
              ADD CONSTRAINT `FK_SCHEDULED_TRANSACTION_LOGS_TO_SCHEDULED_TRANSACTIONS` FOREIGN KEY (`scheduled_transaction_id`) REFERENCES `scheduled_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `transactions`
            --
            ALTER TABLE `transactions`
              ADD CONSTRAINT `FK_TRANSACTIONS_TO_CARDS` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_transaction_to_accounts` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
              ADD CONSTRAINT `FK_transaction_to_revenue_accounts` FOREIGN KEY (`revenue_account_id`) REFERENCES `revenue_accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `FK_transactions_requests` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`);

            --
            -- constrains for the table `transfer_fees_parameters`
            --
            ALTER TABLE `transfer_fees_parameters`
              ADD CONSTRAINT `FK_TRANSFER_FEES_PARAMETERS_TO_TRANSFER_FEES` FOREIGN KEY (`transfer_fee_id`) REFERENCES `transfer_fees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

            --
            -- constrains for the table `transfer_fees_user_groups`
            --
            ALTER TABLE `transfer_fees_user_groups`
              ADD CONSTRAINT `FK_TRANSFER_FEES_USER_GROUPS_TO_TRANSFER_FEES` FOREIGN KEY (`transfer_fee_id`) REFERENCES `transfer_fees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;
    }
}
