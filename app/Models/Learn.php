<?php

namespace App\Models;

use function App\getDB;

/**
 * Learn Model
 */
class Learn
{
    public static function getYoutubeResources(): array
    {
        return [
            'Data Structures & Algorithms' => [
                ['Abdul Bari — DSA Full Course','https://www.youtube.com/watch?v=0IAPZzGSbME','8h full DSA course, highly recommended for exams','Abdul Bari','12h'],
                ['William Fiset — Graph Theory','https://www.youtube.com/watch?v=09_LlHjoEiY','Graph algorithms explained with animations','William Fiset','7h'],
                ['mycodeschool — Data Structures','https://www.youtube.com/watch?v=92S4zgXN17o','Pointers, linked lists, trees and more','mycodeschool','4h'],
                ['CS Dojo — Intro to DSA','https://www.youtube.com/watch?v=bum_19loj9A','Beginner-friendly walkthrough','CS Dojo','1h'],
            ],
            'Object Oriented Programming (Java)' => [
                ['Programming with Mosh — Java','https://www.youtube.com/watch?v=eIrMbAQSU34','Complete Java for beginners, covers OOP concepts','Mosh Hamedani','2.5h'],
                ['Telusko — Java Full Course','https://www.youtube.com/watch?v=BGTx91t8q50','Java OOP concepts with examples','Telusko','12h'],
                ['Derek Banas — OOP Tutorial','https://www.youtube.com/watch?v=NU_1StN5Tkk','Quick OOP concepts in Java','Derek Banas','45m'],
            ],
            'Database Management Systems' => [
                ['Decomplexify — Learn Database Normalization','https://www.youtube.com/watch?v=GFQaEYEc8_8','Best normalization tutorial on YouTube','Decomplexify','1h'],
                ['freeCodeCamp — SQL Full Course','https://www.youtube.com/watch?v=HXV3zeQKqGY','Complete SQL for beginners','freeCodeCamp','4h'],
                ['Traversy Media — MySQL Crash Course','https://www.youtube.com/watch?v=9ylj9NR0Lcg','MySQL basics and CRUD','Traversy Media','1.5h'],
                ['CMU Intro to DB Systems','https://www.youtube.com/watch?v=oeYBdghaIjc','University-level DBMS concepts','CMU Database Group','Full Course'],
            ],
            'Operating Systems' => [
                ['Neso Academy — OS Full Course','https://www.youtube.com/watch?v=mXw9ruZaxzQ','Complete OS for exams — processes, scheduling, memory','Neso Academy','Full'],
                ['Gate Smashers — OS','https://www.youtube.com/watch?v=bkSWJJZNgf8','Exam-focused OS concepts','Gate Smashers','Full'],
            ],
            'Computer Networks' => [
                ['Sunny Classroom — Computer Networks','https://www.youtube.com/watch?v=3QhU9jd03a0','Easy to understand networking basics','Sunny Classroom','Full'],
                ['Neso Academy — CN Course','https://www.youtube.com/watch?v=VwN91x5i25g','TCP/IP, OSI model, routing protocols','Neso Academy','Full'],
                ['NetworkChuck — Networking','https://www.youtube.com/watch?v=H8W9oMNSuwo','Fun practical networking tutorials','NetworkChuck','Series'],
            ],
            'Software Engineering' => [
                ['Hussein Nasser — Software Engineering','https://www.youtube.com/watch?v=gNFGAaHKZ6A','Software architecture principles','Hussein Nasser','2h'],
                ['ArjanCodes — Software Design','https://www.youtube.com/watch?v=pTB30aXS77U','Clean code and SOLID principles','ArjanCodes','Series'],
            ],
            'Artificial Intelligence' => [
                ['3Blue1Brown — Neural Networks','https://www.youtube.com/watch?v=aircAruvnKk','Best visual explanation of neural nets','3Blue1Brown','4 episodes'],
                ['Sentdex — Python AI','https://www.youtube.com/watch?v=OGxgnH8y2NM','Practical ML with Python','Sentdex','Series'],
                ['StatQuest — Machine Learning','https://www.youtube.com/watch?v=Gv9_4yMHFhI','Stats and ML explained simply','StatQuest','Series'],
            ],
            'Web Technologies' => [
                ['Traversy Media — Full Stack Web Dev','https://www.youtube.com/watch?v=ysEN5RaKOlA','HTML CSS JS PHP MySQL full stack','Traversy Media','Full'],
                ['Kevin Powell — CSS','https://www.youtube.com/watch?v=1Rs2ND1ryYc','CSS made easy','Kevin Powell','Series'],
                ['Web Dev Simplified — JavaScript','https://www.youtube.com/watch?v=W6NZfCO5SIk','JS for beginners','Web Dev Simplified','1h'],
            ],
            'Introduction to Programming (C)' => [
                ['Neso Academy — C Programming','https://www.youtube.com/watch?v=rLf3jnHxSmU','Complete C programming for beginners','Neso Academy','Full'],
                ['CS50 — Harvard','https://www.youtube.com/watch?v=8mAITcNt710','World-famous intro to CS using C','Harvard','Full Course'],
            ],
            'Discrete Mathematics' => [
                ['Trefor Bazett — Discrete Math','https://www.youtube.com/watch?v=rdXw7Ps9vxc','Full discrete math course','Trefor Bazett','Full'],
                ['TrevTutor — Discrete Math','https://www.youtube.com/watch?v=tyDKR4FG3Yw','Proofs, logic, sets and graphs','TrevTutor','Series'],
            ],
            'Digital Logic Design' => [
                ['Neso Academy - Digital Electronics','https://www.youtube.com/watch?v=M0mx8S05v60','Covers logic gates, combinational circuits, and exam topics','Neso Academy','Series'],
                ['All About Electronics - Digital Electronics','https://www.youtube.com/watch?v=lKTsv6iVxV4','Great for flip flops, counters, and logic circuit ideas','All About Electronics','Series'],
                ['Gate Smashers - Digital Logic','https://www.youtube.com/watch?v=VG3N3eA5l4w','Helpful for quick revision before exams','Gate Smashers','Series'],
            ],
            'Software UX and UI Design Practice Lab' => [
                ['DesignCourse - UI UX Design Full Course','https://www.youtube.com/watch?v=c9Wg6Cb_YlU','Strong UI/UX foundation with practical design examples','DesignCourse','Full'],
                ['Figma Tutorial for Beginners','https://www.youtube.com/watch?v=jwCmIBJ8Jtc','Useful for course projects, wireframes, and mockups','Figma','Tutorial'],
                ['AJ&Smart - UX Fundamentals','https://www.youtube.com/watch?v=Ovj4hFxko7c','Good overview of UX process and product thinking','AJ&Smart','Series'],
            ],
            'Computer Architecture' => [
                ['Neso Academy - Computer Organization and Architecture','https://www.youtube.com/watch?v=Ol8D69VKX2k','CPU, memory, instruction cycle, and architecture basics','Neso Academy','Series'],
                ['Gate Smashers - Computer Organization','https://www.youtube.com/watch?v=6gX3cM4k3r0','Exam-oriented architecture explanations','Gate Smashers','Series'],
            ],
        ];
    }

    public static function findCourseVideos(string $courseName): ?array
    {
        $resources = self::getYoutubeResources();
        if (isset($resources[$courseName])) return $resources[$courseName];

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
            if (stripos($courseName, $needle) !== false && isset($resources[$resourceKey])) {
                return $resources[$resourceKey];
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
