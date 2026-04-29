<?php
/**
 * Routine Data — Metropolitan University
 * Department of Software Engineering
 * Routine 2026 (Version 9/29/3[026])
 *
 * Structure: $routineData[day][batch] = array of slot entries
 * Each slot: [time_slot_index, course, room, faculty]
 * Time slots: 0=9:00-10:15, 1=10:15-11:30, 2=11:30-12:45, 3=1:00-2:15, 4=2:15-3:30, 5=3:30-4:45
 */

return [
    'version' => 'Routine 2026 (Version 9/29/3026)',
    'department' => 'Department of Software Engineering',
    'university' => 'Metropolitan University',

    'time_slots' => [
        ['start' => '9:00 am',  'end' => '10:15 am', 'label' => '9:00 am to 10:15 am'],
        ['start' => '10:15 am', 'end' => '11:30 am', 'label' => '10:15 am to 11:30 am'],
        ['start' => '11:30 am', 'end' => '12:45 pm', 'label' => '11:30 am to 12:45 pm'],
        ['start' => '1:00 pm',  'end' => '2:15 pm',  'label' => '1:00 pm to 2:15 pm'],
        ['start' => '2:15 pm',  'end' => '3:30 pm',  'label' => '2:15 pm to 3:30 pm'],
        ['start' => '3:30 pm',  'end' => '4:45 pm',  'label' => '3:30 pm to 4:45 pm'],
    ],

    'days' => ['SUN', 'MON', 'TUES', 'WED', 'THU'],

    'batches' => [
        '3rd (16)', '4th (24)', '5th (42)', '6th (35)',
        '7th (43)', '8th (41)', '9th (44)', '10th (35)', '11th (40)',
    ],

    // Color codes for courses (for visual grouping)
    'colors' => [
        'SWE' => '#611494ff',  // light purple
        'GED' => '#6062c4ff',  // light pink
        'MAT' => '#795926ff',  // light orange
        'PHY' => '#a9acadff',  // light blue
        'CSE' => '#e8f5e9',  // light green
        'default' => '#aa9a0ca2',
    ],

    // Routine data: day => batch => [[slot_index, course, room, faculty], ...]
    'schedule' => [
        'SUN' => [
            '3rd (16)' => [
                [2, 'SWE-431 SPM', '', 'NSC'],
                [3, 'SWE-403 ES & IOT', '109', 'MAK'],
            ],
            '4th (24)' => [
                [2, 'SWE-431 SPM', 'EXTN-1', 'NSC'],
                [3, 'SWE-403 ES & IOT', '', ''],
            ],
            '5th (42)' => [
                [2, 'SWE-431 SPM', 'EXTN-1', 'NSC'],
                [4, 'SWE-432', '203', 'NHN'],
                [5, 'SWE-313 CN', '301', 'NHN'],
            ],
            '6th (35)' => [
                [0, 'SWE-321 UI/UX', '101', 'DD'],
                [1, 'SWE-321 UI/UX', '101', 'DD'],
                [2, 'SWE-122 WP', '101', 'LN'],
                [3, 'SWE-122 WP', '101', 'LN'],
            ],
            '7th (43)' => [
                [0, 'SWE-202 SD', '306', 'FA'],
                [1, 'SWE-202 SD', '', ''],
                [4, 'MAT-211 NA', '305', 'RP'],
            ],
            '8th (41)' => [
                [0, 'SWE-231 DSE', 'EXTN-1', 'NSC'],
                [1, 'SWE-111 ICSE', 'EXTN-1', 'NHN'],
            ],
            '9th (44)' => [
                [2, 'SWE-235 MIS', '105', 'WIC'],
                [4, 'SWE-216 DLD Lab & DA', '109', 'AAC'],
                [5, 'SWE-122 SP', '101', 'IAC'],
            ],
            '10th (35)' => [
                [2, 'PHY-111 BP', '307', 'RD'],
                [4, 'MAT-112 LADE', '101', 'FA'],
                [5, 'SWE-122 SP', '101', 'IAC'],
            ],
            '11th (40)' => [
                [0, 'MAT-111 DM', '104', 'SAT'],
                [1, 'GED-101 BS', 'GL-1', 'SA'],
            ],
        ],

        'MON' => [
            '3rd (16)' => [],
            '4th (24)' => [
                [2, 'SWE-461 ES & EIT Lab', '106', 'MAK'],
                [3, 'SWE-461 ES & IOT Lab', '106', 'MAK'],
                [4, 'GED-403 ED', '305', 'MSC'],
            ],
            '5th (42)' => [],
            '6th (35)' => [
                [1, 'SWE-211 BSP', 'EXTN 1', 'MMZ'],
                [2, 'SWE-115 AI', '203', 'AAC'],
            ],
            '7th (43)' => [
                [0, 'SWE-226 CP', '101', 'AAC'],
                [1, 'SWE-226 CP', '101', 'AAC'],
                [2, 'SWE-213 SASP Lab', 'EXTN 1', 'NSC'],
                [4, 'SWE-226 DBMS LAB', '101', 'FA'],
            ],
            '8th (41)' => [
                [0, 'SWE-231 DSE', 'EXTN-1', 'NSC'],
                [1, 'SWE-111 ALGO', '101', 'NSC'],
            ],
            '9th (44)' => [],
            '10th (35)' => [
                [0, 'SWE-121 SP', '104', 'IAC'],
                [2, 'PHY-111 BP', '307', 'RD'],
            ],
            '11th (40)' => [],
        ],

        'TUES' => [
            '3rd (16)' => [
                [2, 'SWE-431 SPM', 'EXTN-1', 'NSC'],
            ],
            '4th (24)' => [
                [4, 'GED-403 ED', '206', 'WSC'],
                [5, 'SWE-461 SC', 'EXTN 1', 'AAC'],
            ],
            '5th (42)' => [
                [2, 'SWE-431 SPM', '', ''],
                [4, 'SWE-317 ML', '301', 'NHN'],
                [5, 'SWE-313 CN', '401', 'NHN'],
            ],
            '6th (35)' => [
                [2, 'SWE-316 AI Lab', '301', 'AAC'],
                [3, 'SWE-316 AI Lab', '301', 'AAC'],
            ],
            '7th (43)' => [],
            '8th (41)' => [
                [0, 'SWE-231 DSE', 'EXTN 1', ''],
                [1, 'SWE-226 DMS', '', ''],
                [2, 'SWE-124 DS Lab', '101', 'IAC'],
                [3, 'SWE-115 AI Lab', '', ''],
            ],
            '9th (44)' => [
                [0, 'SWE-124 DS', '201', 'IAC'],
                [1, 'SWE-124 DS Lab', '101', 'IAC'],
                [4, 'SWE-121 SP', 'EXTN-1', 'IAC'],
            ],
            '10th (35)' => [
                [1, 'MAT-112 LADE', 'EXTN-1', 'WSC'],
            ],
            '11th (40)' => [
                [0, 'GED-101 BS', '107', 'SA'],
                [1, 'MAT-111 DM', '501', 'NAT'],
                [2, 'SWE-131 ISE', '105', 'WIC'],
            ],
        ],

        'WED' => [
            '3rd (16)' => [
                [4, 'SWE-401 DMT', '101', 'MKB'],
                [5, 'SWE-401 DMT', '101', 'MKB'],
            ],
            '4th (24)' => [
                [4, 'SWE-401 DMT', '101', 'MKB'],
                [5, 'SWE-401 DMT', '101', 'MKB'],
            ],
            '5th (42)' => [],
            '6th (35)' => [],
            '7th (43)' => [
                [2, 'SWE-234 SASP Lab', '101', 'NSC'],
                [3, 'SWE-234 AADP Lab', '101', 'NSC'],
            ],
            '8th (41)' => [
                [0, 'SWE-102 PPD', '101', 'IAC'],
                [1, 'SWE-102 PPD', '101', 'IAC'],
                [3, 'SWE-121 ES', '305', 'DAC'],
                [4, 'SWE-215 HLD', '304', 'AAC'],
            ],
            '9th (44)' => [],
            '10th (35)' => [
                [0, 'MAT-112 LADE', 'EXTN-1', 'WSC'],
                [1, 'PHY-111 BP', 'EXTN 1', 'RD'],
                [3, 'MAT-112 LADE', '', ''],
                [4, 'SWE-131 ISE', '105', 'MSC'],
            ],
            '11th (40)' => [
                [1, 'GED-101 ESL1', '', ''],
            ],
        ],

        'THU' => [
            '3rd (16)' => [
                [4, 'SWE-210 ML Lab', '101', 'NHN'],
                [5, 'SWE-210 ML Lab', '101', 'NHN'],
            ],
            '4th (24)' => [
                [0, 'SWE-461 SC', '109', 'AAC'],
                [1, 'SWE-461 ES & IOT', '109', 'MAK'],
            ],
            '5th (42)' => [],
            '6th (35)' => [
                [1, 'SWE-115 AI', 'EXTN 1', 'AAC'],
                [2, 'SWE-211 BSP', 'EXTN 1', 'MMZ'],
            ],
            '7th (43)' => [
                [0, 'MAT-211 NA', 'EXTN 1', 'RP'],
                [1, 'SWE-213 SASP', '101', 'NSC'],
            ],
            '8th (41)' => [
                [1, 'SWE-226 DMS', 'EXTN-1', 'FA'],
                [2, 'SWE-222 ALGO Lab', '101', 'NSC'],
                [3, 'SWE-222 ALGO LAB', '101', 'NSC'],
            ],
            '9th (44)' => [
                [2, 'SWE-125 DS', '402', 'IAC'],
                [3, 'SWE-215 HLD', '105', 'AAC'],
                [4, 'SWE-235 MIS', '', 'MSE'],
            ],
            '10th (35)' => [],
            '11th (40)' => [
                [0, 'ACM', '101', 'IAC'],
                [1, 'ACM', '101', 'IAC'],
                [3, 'GED-101 CEL1', 'EXTN 2', ''],
            ],
        ],
    ],
];
