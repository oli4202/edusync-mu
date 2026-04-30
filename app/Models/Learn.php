<?php

namespace App\Models;

use function getDB;

/**
 * Learn Model
 */
class Learn
{
    public static function getYoutubeResources(): array
    {
        return [
            'Batch 11' => [
                'Structured Programming' => [
                    ['Neso Academy — C Programming','https://www.youtube.com/watch?v=rLf3jnHxSmU','Complete C programming for beginners','Neso Academy','Full'],
                    ['CS50 — Harvard','https://www.youtube.com/watch?v=8mAITcNt710','World-famous intro to CS using C','Harvard','Full Course'],
                ],
                'Discrete Mathematics' => [
                    ['Trefor Bazett — Discrete Math','https://www.youtube.com/watch?v=rdXw7Ps9vxc','Full discrete math course','Trefor Bazett','Full'],
                    ['TrevTutor — Discrete Math','https://www.youtube.com/watch?v=tyDKR4FG3Yw','Proofs, logic, sets and graphs','TrevTutor','Series'],
                ],
                'Calculus' => [
                    ['Professor Leonard — Calculus 1','https://www.youtube.com/watch?v=fYyARMqiaag','The legendary calculus teacher','Professor Leonard','60h'],
                    ['The Organic Chemistry Tutor — Calculus','https://www.youtube.com/watch?v=W3AdLIn69_g','Great for problem-solving techniques','The Organic Chemistry Tutor','Series'],
                ],
                'Basic Physics' => [
                    ['Walter Lewin — Lectures','https://www.youtube.com/watch?v=7Zc999eS2zE','Classical Mechanics from MIT','Walter Lewin','Series'],
                    ['Flipping Physics — Mechanics','https://www.youtube.com/watch?v=j9y8W2nC4_M','Very visual physics explanations','Flipping Physics','Series'],
                ]
            ],
            'Batch 10' => [
                'Structured Programming' => [
                    ['Neso Academy — C Programming','https://www.youtube.com/watch?v=rLf3jnHxSmU','Complete C programming for beginners','Neso Academy','Full'],
                    ['CS50 — Harvard','https://www.youtube.com/watch?v=8mAITcNt710','World-famous intro to CS using C','Harvard','Full Course'],
                ],
                'Calculus' => [
                    ['Professor Leonard — Calculus 1','https://www.youtube.com/watch?v=fYyARMqiaag','The legendary calculus teacher','Professor Leonard','60h'],
                ],
            ],
            'Batch 9' => [
                'Data Structures' => [
                    ['Abdul Bari — DSA Full Course','https://www.youtube.com/watch?v=0IAPZzGSbME','8h full DSA course, highly recommended for exams','Abdul Bari','12h'],
                    ['mycodeschool — Data Structures','https://www.youtube.com/watch?v=92S4zgXN17o','Pointers, linked lists, trees and more','mycodeschool','4h'],
                    ['William Fiset — Graph Theory','https://www.youtube.com/watch?v=09_LlHjoEiY','Graph algorithms explained with animations','William Fiset','7h'],
                ],
                'Digital Logic Design' => [
                    ['Neso Academy - Digital Electronics','https://www.youtube.com/watch?v=M0mx8S05v60','Covers logic gates, combinational circuits, and exam topics','Neso Academy','Series'],
                    ['Gate Smashers - Digital Logic','https://www.youtube.com/watch?v=VG3N3eA5l4w','Helpful for quick revision before exams','Gate Smashers','Series'],
                ],
                'Management Information Systems' => [
                    ['MIS Lecture 1','https://www.youtube.com/watch?v=uD_6-S_12_k','Introduction to MIS concepts','MIS Guru','45m'],
                ]
            ],
            'Batch 8' => [
                'Algorithms' => [
                    ['Abdul Bari — Algorithms','https://www.youtube.com/watch?v=0IAPZzGSbME','Expertly explained algorithms and complexity analysis','Abdul Bari','Series'],
                    ['MIT 6.006 — Algorithms','https://www.youtube.com/watch?v=HtSuA80QTyo','Full course from MIT','MIT OpenCourseWare','Series'],
                ],
                'Database Management Systems' => [
                    ['Decomplexify — Learn Database Normalization','https://www.youtube.com/watch?v=GFQaEYEc8_8','Best normalization tutorial on YouTube','Decomplexify','1h'],
                    ['freeCodeCamp — SQL Full Course','https://www.youtube.com/watch?v=HXV3zeQKqGY','Complete SQL for beginners','freeCodeCamp','4h'],
                    ['Traversy Media — MySQL Crash Course','https://www.youtube.com/watch?v=9ylj9NR0Lcg','MySQL basics and CRUD','Traversy Media','1.5h'],
                ],
                'Software Requirement Engineering' => [
                    ['Introduction to Requirements Engineering','https://www.youtube.com/watch?v=vVp4p7_h6hM','Concepts of SRS and user stories','Udacity','30m'],
                ]
            ],
            'Batch 7' => [
                'Software Architecture and Design Patterns' => [
                    ['Hussein Nasser — Software Engineering','https://www.youtube.com/watch?v=gNFGAaHKZ6A','Software architecture principles','Hussein Nasser','2h'],
                    ['ArjanCodes — Software Design','https://www.youtube.com/watch?v=pTB30aXS77U','Clean code and SOLID principles','ArjanCodes','Series'],
                    ['Derek Banas — Design Patterns','https://www.youtube.com/watch?v=vNHpsC5ng_E','All GOF design patterns in one series','Derek Banas','Series'],
                ],
                'Numerical Analysis' => [
                    ['Numerical Analysis Lecture','https://www.youtube.com/watch?v=as_S8X6Wp3A','Root finding and interpolation','Math Is Power','Series'],
                ],
            ],
            'Batch 6' => [
                'Artificial Intelligence' => [
                    ['3Blue1Brown — Neural Networks','https://www.youtube.com/watch?v=aircAruvnKk','Best visual explanation of neural nets','3Blue1Brown','4 episodes'],
                    ['Sentdex — Python AI','https://www.youtube.com/watch?v=OGxgnH8y2NM','Practical ML with Python','Sentdex','Series'],
                    ['Two Minute Papers — AI News','https://www.youtube.com/watch?v=pTB30aXS77U','Stay updated with latest AI research','Two Minute Papers','Series'],
                ],
                'Web Programming' => [
                    ['Traversy Media — Full Stack Web Dev','https://www.youtube.com/watch?v=ysEN5RaKOlA','HTML CSS JS PHP MySQL full stack','Traversy Media','Full'],
                    ['Web Dev Simplified — JS Tips','https://www.youtube.com/watch?v=W6NZfCO5SIk','Advanced JavaScript techniques','Web Dev Simplified','Series'],
                ],
                'Software UI & UX Design' => [
                    ['DesignCourse - UI UX Design Full Course','https://www.youtube.com/watch?v=c9Wg6Cb_YlU','Strong UI/UX foundation with practical design examples','DesignCourse','Full'],
                    ['Figma Tutorial for Beginners','https://www.youtube.com/watch?v=jwCmIBJ8Jtc','Useful for course projects, wireframes, and mockups','Figma','Tutorial'],
                ]
            ],
            'Batch 5' => [
                'Machine Learning' => [
                    ['StatQuest — Machine Learning','https://www.youtube.com/watch?v=Gv9_4yMHFhI','Stats and ML explained simply','StatQuest','Series'],
                    ['Andrew Ng — ML Specialization','https://www.youtube.com/watch?v=PPLop4L2eGk','The definitive introduction to ML','Stanford','Series'],
                ],
                'Computer Networking' => [
                    ['Sunny Classroom — Computer Networks','https://www.youtube.com/watch?v=3QhU9jd03a0','Easy to understand networking basics','Sunny Classroom','Full'],
                    ['Neso Academy — CN Course','https://www.youtube.com/watch?v=VwN91x5i25g','TCP/IP, OSI model, routing protocols','Neso Academy','Full'],
                    ['NetworkChuck — Networking','https://www.youtube.com/watch?v=H8W9oMNSuwo','Fun practical networking tutorials','NetworkChuck','Series'],
                ],
                'Software Project Management' => [
                    ['SPM Concepts','https://www.youtube.com/watch?v=as_S8X6Wp3A','Managing software projects with Agile','Fireship','15m'],
                ]
            ],
            'Batch 4' => [
                'Embedded System & IoT' => [
                    ['GreatScott! — IoT Basics','https://www.youtube.com/watch?v=uD_6-S_12_k','Introduction to IoT and embedded systems','GreatScott!','Series'],
                    ['Andreas Spiess — The Guy with the Swiss Accent','https://www.youtube.com/watch?v=uD_6-S_12_k','Excellent ESP32 and IoT tutorials','Andreas Spiess','Series'],
                ],
                'Cryptography' => [
                    ['Computerphile — Cryptography','https://www.youtube.com/watch?v=N6C6D6Vp9hE','Encryption, hashing, and public keys','Computerphile','Series'],
                ],
            ],
            'Batch 3' => [
                'Final Year Project' => [
                    ['How to build a SaaS','https://www.youtube.com/watch?v=as_S8X6Wp3A','End-to-end development guide','Fireship','15m'],
                    ['System Design Interview','https://www.youtube.com/watch?v=m8Icp_Cid5o','Designing large-scale systems','Gaurav Sen','Series'],
                ],
            ]
        ];
    }

    /**
     * Get resources for a specific batch
     */
    public static function getResourcesByBatch(string $batch): array
    {
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $resources = self::getYoutubeResources();
        return $resources['Batch ' . $batchNum] ?? [];
    }

    public static function findCourseVideos(string $courseName): ?array
    {
        $allBatches = self::getYoutubeResources();
        $flattened = [];
        foreach ($allBatches as $batch => $subjects) {
            foreach ($subjects as $name => $videos) {
                if (!isset($flattened[$name])) {
                    $flattened[$name] = $videos;
                } else {
                    $flattened[$name] = array_merge($flattened[$name], $videos);
                }
            }
        }

        if (isset($flattened[$courseName])) return $flattened[$courseName];

        $rules = [
            'Digital Logic' => 'Digital Logic Design',
            'UX' => 'Software UX and UI Design Practice Lab',
            'UI' => 'Software UX and UI Design Practice Lab',
            'Object Oriented' => 'Object Oriented Programming (Java)',
            'Database' => 'Database Management Systems',
            'Operating System' => 'Operating Systems',
            'Networking' => 'Computer Networks',
            'Web Programming' => 'Web Technologies',
            'Structured Programming' => 'Introduction to Programming (C)',
            'Computer Architecture' => 'Computer Architecture',
            'Discrete Mathematics' => 'Discrete Mathematics',
            'Artificial Intelligence' => 'Artificial Intelligence',
        ];

        foreach ($rules as $needle => $resourceKey) {
            if (stripos($courseName, $needle) !== false && isset($flattened[$resourceKey])) {
                return $flattened[$resourceKey];
            }
        }

        return null;
    }

    public static function getAllCourses(): array
    {
        $db = getDB();
        return $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();
    }

    public static function getCourseById(int $id): ?array
    {
        $db = getDB();
        $s = $db->prepare("SELECT * FROM courses WHERE id=?");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
