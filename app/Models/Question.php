<?php

namespace App\Models;

use function App\getDB;
use function App\clean;

/**
 * Question Model
 */
class Question
{
    public static function findApprovedById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT q.*, c.name AS course_name, c.code AS course_code, u.name AS submitted_by_name
            FROM questions q
            JOIN courses c ON q.course_id = c.id
            LEFT JOIN users u ON q.submitted_by = u.id
            WHERE q.id = ? AND q.is_approved = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function incrementViewCount(int $id): void
    {
        $db = getDB();
        $db->prepare("UPDATE questions SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function findRelatedApproved(int $courseId, int $excludeId, int $limit = 4): array
    {
        $db = getDB();
        $limit = max(1, $limit);
        $stmt = $db->prepare("
            SELECT id, question_text
            FROM questions
            WHERE course_id = ? AND id != ? AND is_approved = 1
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$courseId, $excludeId]);
        return $stmt->fetchAll();
    }

    public static function isBookmarkedByUser(int $userId, int $questionId): bool
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM question_bookmarks WHERE user_id = ? AND question_id = ?");
        $stmt->execute([$userId, $questionId]);
        return (bool) $stmt->fetch();
    }

    public static function toggleBookmarkForUser(int $userId, int $questionId): bool
    {
        $db = getDB();
        if (self::isBookmarkedByUser($userId, $questionId)) {
            $db->prepare("DELETE FROM question_bookmarks WHERE user_id = ? AND question_id = ?")
                ->execute([$userId, $questionId]);
            return false;
        }

        $db->prepare("INSERT INTO question_bookmarks (user_id, question_id) VALUES (?, ?)")
            ->execute([$userId, $questionId]);
        return true;
    }

    public static function findPending(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT q.*, c.name AS course_name, c.code, u.name AS submitter 
            FROM questions q 
            JOIN courses c ON q.course_id = c.id 
            LEFT JOIN users u ON q.submitted_by = u.id 
            WHERE q.is_approved = 0 
            ORDER BY q.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function approve(int $id, int $approvedBy): bool
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE questions SET is_approved = 1, approved_by = ?, approved_at = NOW() WHERE id = ?");
        return $stmt->execute([$approvedBy, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM questions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getCount(bool $approvedOnly = true): int
    {
        $db = getDB();
        $sql = "SELECT COUNT(*) FROM questions";
        if ($approvedOnly) {
            $sql .= " WHERE is_approved = 1";
        }
        return (int)$db->query($sql)->fetchColumn();
    }

    public static function getRecentApproved(int $limit = 4): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM questions WHERE is_approved=1 ORDER BY created_at DESC LIMIT $limit");
        return $stmt->fetchAll();
    }

    public static function getAll(): array
    {
        return [
            'MAT-113' => [
                'name' => 'Discrete Mathematics',
                'exams' => [
                    ['type' => 'Mid-Term', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 30, 'time' => '1.5h'],
                    ['type' => 'Final', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Let p, q, r be propositions: p=Grizzly bears seen, q=Hiking safe, r=Berries ripe. Write 5 propositions using logical connectives and negations.', 'marks' => 5, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Propositional Logic'],
                    ['q' => 'Show that (p∧q)→(p∨q) is a tautology by applying a chain of logical identities.', 'marks' => 3, 'type' => 'Proof', 'freq' => 4, 'topic' => 'Logic'],
                    ['q' => 'Use truth tables to verify the absorption law: p∧(p∨q)≡p', 'marks' => 2, 'type' => 'Computation', 'freq' => 3, 'topic' => 'Truth Tables'],
                    ['q' => 'Find the first five terms of the sequence: aₙ=naₙ₋₁+n²aₙ₋₂, a₀=1, a₁=1', 'marks' => 3, 'type' => 'Computation', 'freq' => 2, 'topic' => 'Sequences & Recurrence'],
                    ['q' => 'Use the Euclidean algorithm to find gcd(9888, 6060).', 'marks' => 2, 'type' => 'Computation', 'freq' => 3, 'topic' => 'Number Theory'],
                    ['q' => 'Decrypt ciphertext "LKBJHAPVU ULCLY LUKZ" encrypted with shift cipher k=7.', 'marks' => 3, 'type' => 'Computation', 'freq' => 2, 'topic' => 'Cryptography'],
                    ['q' => 'Use Dijkstra\'s algorithm to find the shortest path between vertices a and z in the given weighted graph.', 'marks' => 5, 'type' => 'Graph Algorithm', 'freq' => 5, 'topic' => 'Graph Theory'],
                    ['q' => 'Find the inverse function of f(x)=x³+1 and determine if it is one-to-one.', 'marks' => 2, 'type' => 'Function', 'freq' => 2, 'topic' => 'Functions'],
                ],
            ],
            'PHY-111' => [
                'name' => 'Basic Physics',
                'exams' => [
                    ['type' => 'Mid-Term', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 30, 'time' => '1.5h'],
                    ['type' => 'Final', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'State Hooke\'s law with mathematical expression. (2 marks)', 'marks' => 2, 'type' => 'Definition', 'freq' => 3, 'topic' => 'SHM'],
                    ['q' => 'Find the differential equation of SHM for a spring-mass system. Show total energy is proportional to square of amplitude.', 'marks' => 5, 'type' => 'Derivation', 'freq' => 4, 'topic' => 'SHM'],
                    ['q' => 'A particle of mass 650g oscillates in SHM with amplitude 15cm and period π sec. Find angular frequency, KE, PE, and total energy when at 8cm from equilibrium.', 'marks' => 3, 'type' => 'Numerical', 'freq' => 3, 'topic' => 'SHM Numericals'],
                    ['q' => 'Find the differential equation for a damped harmonic oscillator. Find its angular frequency, amplitude and energy.', 'marks' => 5, 'type' => 'Derivation', 'freq' => 4, 'topic' => 'Damped Oscillation'],
                    ['q' => 'Damped oscillator: mass 250g, spring constant 85 N/m, damping constant 0.07 kg/s. Find: (i) time for amplitude to drop to half (ii) time for energy to drop to 1/4.', 'marks' => 5, 'type' => 'Numerical', 'freq' => 3, 'topic' => 'Damped Oscillation'],
                    ['q' => 'State the Kelvin-Plank and Clausius statements of the second law of thermodynamics with diagrams.', 'marks' => 4, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Thermodynamics'],
                    ['q' => 'A Carnot engine between 450K and 350K receives 1000 calorie per cycle. Calculate heat rejected, efficiency, and work done.', 'marks' => 4, 'type' => 'Numerical', 'freq' => 4, 'topic' => 'Carnot Engine'],
                ],
            ],
            'MAT-111' => [
                'name' => 'Differential & Integral Calculus',
                'exams' => [
                    ['type' => 'Mid-Term', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 30, 'time' => '1.5h'],
                    ['type' => 'Final', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Determine the domain and range of: (i) √(x²-36)  (ii) 1/|x-3|', 'marks' => 4, 'type' => 'Calculus', 'freq' => 2, 'topic' => 'Domain & Range'],
                    ['q' => 'Evaluate: lim(t→0) [t/(√(1+t)-√(1-t))], lim(x→0) (eˣ-1-x)/x², and show lim(x→0) x³sin(2/x²)=0', 'marks' => 7, 'type' => 'Limits', 'freq' => 4, 'topic' => 'Limits'],
                    ['q' => 'Given f(x)={x² if x<0; ax+b if 0≤x<1; 2 if x≥1}. Determine a and b for f(x) to be continuous.', 'marks' => 5, 'type' => 'Continuity', 'freq' => 4, 'topic' => 'Continuity'],
                    ['q' => 'Find nth derivative formula for u=sin(ax+b) and v=cos(ax+b). Use it to evaluate nth derivative of y=sin6x·cos4x.', 'marks' => 6, 'type' => 'Differentiation', 'freq' => 3, 'topic' => 'Derivatives'],
                    ['q' => 'Let f(x)=2x²-4x+5 on [-1,3]. Verify the three hypotheses of Rolle\'s Theorem. Find all numbers satisfying its conclusion.', 'marks' => 5, 'type' => 'Theorem', 'freq' => 4, 'topic' => 'Rolle\'s Theorem'],
                    ['q' => 'Evaluate any two integrals: (a) ∫dx/(5x²+2x+3) (b) ∫(x+1)/√(4+8x-5x²)dx (c) ∫x²dx/((x+1)²(x+2))', 'marks' => 10, 'type' => 'Integration', 'freq' => 3, 'topic' => 'Integration Techniques'],
                    ['q' => 'Show that ∫₀¹ dx/(√(1-2x²)√(1-x²)) = ½log(2+√3). Define Gamma function and prove Γ(n+1)=nΓ(n)=n!', 'marks' => 6, 'type' => 'Proof', 'freq' => 3, 'topic' => 'Gamma Function'],
                ],
            ],
            'SWE-121' => [
                'name' => 'Structured Programming',
                'exams' => [
                    ['type' => 'Mid-Term', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 30, 'time' => '1.5h'],
                    ['type' => 'Final', 'term' => 'Summer 2023', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'True/False: (a) /* */ are for multi-line comments (b) C allows variable names with spaces in double quotes (c) sizeof returns size in bits (d) do-while executes at least once (e) C allows variable use before declaration (f) break exits for loop (g) else part of if-else is mandatory', 'marks' => 5, 'type' => 'True/False', 'freq' => 3, 'topic' => 'C Basics'],
                    ['q' => 'Write a C program to check whether an integer is odd or even.', 'marks' => 5, 'type' => 'Code', 'freq' => 4, 'topic' => 'Control Flow'],
                    ['q' => 'Write a C program to calculate sum of all positive integers less than 1000 divisible by both 3 and 5.', 'marks' => 5, 'type' => 'Code', 'freq' => 3, 'topic' => 'Loops'],
                    ['q' => 'Write a recursive function that generates the first 10 Fibonacci numbers.', 'marks' => 4, 'type' => 'Code', 'freq' => 5, 'topic' => 'Recursion'],
                    ['q' => 'Write a program that checks whether a number is a palindrome (e.g., 121 is palindrome).', 'marks' => 4, 'type' => 'Code', 'freq' => 3, 'topic' => 'String/Number'],
                    ['q' => 'Define a structure account with members name, account_no, and balance. Declare an array of type account. Display the details.', 'marks' => 4, 'type' => 'Code', 'freq' => 3, 'topic' => 'Structures'],
                    ['q' => 'Write the output of the following code:\n#include<stdio.h>\nint main(){\n  int a[]={21,34,5,6,12,100};\n  int b,c=0;\n  for(b=0;b<6;++b)\n    if((a[b]%2)==1) c+= a[b];\n  printf("%d",c);\n}', 'marks' => 5, 'type' => 'Output Tracing', 'freq' => 5, 'topic' => 'Arrays & Loops'],
                    ['q' => 'Write a function convertCase() to receive a character argument and change its case.', 'marks' => 5, 'type' => 'Code', 'freq' => 3, 'topic' => 'Functions'],
                ],
            ],
            'SWE-123' => [
                'name' => 'Data Structures',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Spring 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Class Test 1', 'term' => 'Spring 2026', 'batch' => '6th', 'marks' => 20, 'time' => '40m'],
                ],
                'questions' => [
                    ['q' => 'Define Abstract Data Type with example. Explain which data structures are suitable for: (I) Evaluating arithmetic expressions (II) Process scheduling by OS (III) Developing social networks.', 'marks' => 4, 'type' => 'Theory', 'freq' => 3, 'topic' => 'ADT'],
                    ['q' => 'Given input {1,16,49,36,25,64,0,81,4,9} and hash function h(x)=(x+3) mod 9. (I) Draw Hash table using quadratic probing (II) Draw Hash table using linear probing.', 'marks' => 6, 'type' => 'Computation', 'freq' => 4, 'topic' => 'Hash Tables'],
                    ['q' => 'Construct binary tree from: Preorder: F A E K C D H G B | Inorder: E A C K F H D B G', 'marks' => 5, 'type' => 'Tree Construction', 'freq' => 5, 'topic' => 'Binary Trees'],
                    ['q' => 'Draw a Huffman Tree for: A=22, B=17, C=7, D=19, E=2, F=11, G=25, H=5. Calculate total bits required.', 'marks' => 5, 'type' => 'Computation', 'freq' => 4, 'topic' => 'Huffman Encoding'],
                    ['q' => 'Find shortest path from vertex A to I using Dijkstra\'s algorithm. Show the distance table and final path.', 'marks' => 5, 'type' => 'Graph Algorithm', 'freq' => 5, 'topic' => 'Dijkstra'],
                    ['q' => 'Explain four different rotations to rebalance AVL tree with examples. Construct AVL Tree for: 5,7,13,9,6,3,14,10,4.', 'marks' => 7, 'type' => 'Tree', 'freq' => 4, 'topic' => 'AVL Trees'],
                    ['q' => 'Implement DFS algorithm to traverse an undirected graph. Write code in a programming language of your choice.', 'marks' => 5, 'type' => 'Code', 'freq' => 4, 'topic' => 'Graph BFS/DFS'],
                    ['q' => 'Sort 66,33,40,22,55,88,60,11,80,20,50,44,77 using merge-sort algorithm.', 'marks' => 4, 'type' => 'Sorting', 'freq' => 4, 'topic' => 'Merge Sort'],
                    ['q' => 'Implement create_node and append_node methods for Linked_List class. Implement constructor and push for Stack class.', 'marks' => 20, 'type' => 'Code', 'freq' => 4, 'topic' => 'Linked List & Stack'],
                ],
            ],
            'SWE-131' => [
                'name' => 'Introduction to Software Engineering',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Spring 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Indicate correct/incorrect: (a) Black Box testing is for verification (b) Porting and migration improves design/function/reliability (c) Sequence diagrams show activities in a process (d) Unit testing includes functional and non-functional tests (e) Adaptive maintenance involves fixing bugs.', 'marks' => 10, 'type' => 'True/False', 'freq' => 4, 'topic' => 'Software Testing'],
                    ['q' => 'Write short notes: Behavioral model, Black-Box testing, Work Breakdown Structure, Risk Management.', 'marks' => 10, 'type' => 'Short Notes', 'freq' => 4, 'topic' => 'SE Concepts'],
                    ['q' => 'State the use of graphical models in SE. Describe different types of UML useful for system modeling. Define Generalization with example.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'UML'],
                    ['q' => 'Differentiate between Unit testing and Integration testing. State the check lists of GUI Testing.', 'marks' => 8, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Testing'],
                    ['q' => 'Explain key aspects of Software maintenance. List and describe the use of reverse engineering.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Maintenance'],
                ],
            ],
            'MAT-112' => [
                'name' => 'Linear Algebra & Differential Equations',
                'exams' => [
                    ['type' => 'Class Test 2', 'term' => 'Spring 2025', 'batch' => '6th+', 'marks' => 15, 'time' => '45m'],
                    ['type' => 'Final', 'term' => 'Spring 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Classify the following DEs as ordinary/partial; state order, degree, and linear/nonlinear:\n(a) dy/dx + x²y = xeˣ\n(b) d³y/dx³ + 4d²y/dx² - 5dy/dx + 3y = sinx\n(c) ∂²u/∂x² + ∂²u/∂y² = 0\n(d) d²y/dx² + y·sinx = 0', 'marks' => 5, 'type' => 'Classification', 'freq' => 5, 'topic' => 'Differential Equations Classification'],
                    ['q' => 'Determine whether the equations are Homogeneous, Linear, or Bernoulli:\n(a) y′ = x²/y²\n(b) y′ = xy + 1', 'marks' => 5, 'type' => 'Classification', 'freq' => 4, 'topic' => 'DE Types'],
                    ['q' => 'Solve any one: (a) xdx - y²dy = 0  (b) y′ = y²x³', 'marks' => 5, 'type' => 'Solve DE', 'freq' => 4, 'topic' => 'Separable DEs'],
                    ['q' => 'Find symmetric and skew-symmetric parts of A = [[1,2,4],[6,8,1],[3,5,7]]', 'marks' => 3, 'type' => 'Matrix', 'freq' => 3, 'topic' => 'Matrix Operations'],
                    ['q' => 'If A and B are given matrices, prove (AB)ᵀ = BᵀAᵀ', 'marks' => 4, 'type' => 'Proof', 'freq' => 4, 'topic' => 'Matrix Properties'],
                    ['q' => 'Solve using Gaussian elimination: 5x-6y+4z=15; 7x+4y-3z=19; 2x+y+6z=46', 'marks' => 6, 'type' => 'Linear Systems', 'freq' => 3, 'topic' => 'Gaussian Elimination'],
                    ['q' => 'Find eigenvalues and eigenvectors for A = [[2,3],[1,4]]', 'marks' => 5, 'type' => 'Eigenvalues', 'freq' => 5, 'topic' => 'Eigenvalues & Eigenvectors'],
                ],
            ],
            'SWE-221' => [
                'name' => 'Algorithm (SWE-221)',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Summer 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Final', 'term' => 'Spring 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Class Test 1', 'term' => 'Spring 2026', 'batch' => '6th', 'marks' => 20, 'time' => '40m'],
                ],
                'questions' => [
                    ['q' => 'List three real-world applications of backtracking. Calculate all possible solutions of 5-Queens problem (5×5) using Backtracking method.', 'marks' => 7, 'type' => 'Algorithm', 'freq' => 4, 'topic' => 'Backtracking & N-Queens'],
                    ['q' => 'Find two different (if exists) MSTs from the given graph using Prim\'s and Kruskal\'s algorithms (graph: A-B:3, A-D:13, B-D:3, B-E:14, B-G:12, B-C:6, C-F:2, etc.)', 'marks' => 8, 'type' => 'MST', 'freq' => 5, 'topic' => 'Prim & Kruskal MST'],
                    ['q' => 'Find all LCS of X=[PRESIDENT] and Y=[PROVIDENCE] using dynamic programming method.', 'marks' => 6, 'type' => 'DP', 'freq' => 5, 'topic' => 'Longest Common Subsequence'],
                    ['q' => 'Provide step-by-step diagram to solve Tower of Hanoi with 3 disks. Show minimum moves and label pegs.', 'marks' => 4, 'type' => 'Recursion', 'freq' => 3, 'topic' => 'Tower of Hanoi'],
                    ['q' => 'Find All Pairs Shortest Path from given directed graph using Floyd-Warshall.', 'marks' => 5, 'type' => 'Graph', 'freq' => 4, 'topic' => 'Floyd-Warshall'],
                    ['q' => 'Apply Dijkstra\'s algorithm. S→A:3, S→B:5, A→C:2, B→C:1, C→T:4. Show distance table and final path.', 'marks' => 4, 'type' => 'Graph', 'freq' => 5, 'topic' => 'Dijkstra\'s Algorithm'],
                    ['q' => 'Add negative-weight edge C→A:-2. Explain why Dijkstra fails. Solve using Bellman-Ford. Highlight negative cycles.', 'marks' => 6, 'type' => 'Graph', 'freq' => 3, 'topic' => 'Bellman-Ford'],
                    ['q' => 'Traverse the graph using BFS and DFS from node A. Use adjacency list. Show order of visited nodes.', 'marks' => 10, 'type' => 'Graph', 'freq' => 5, 'topic' => 'BFS & DFS'],
                    ['q' => 'Design a DP solution for Longest Increasing Subsequence of [3,1,4,2,7,5]. Show DP table and trace solution. What is time complexity?', 'marks' => 8, 'type' => 'DP', 'freq' => 4, 'topic' => 'LIS - Dynamic Programming'],
                    ['q' => 'Compare divide-and-conquer and dynamic programming paradigms. Solve fractional knapsack for items with weights [2,3,5] and values [4,5,8], capacity=10.', 'marks' => 6, 'type' => 'Theory+Algo', 'freq' => 4, 'topic' => 'Greedy vs DP'],
                    ['q' => 'List three real-world applications of Dijkstra algorithm. Write two applications of Backtracking. Describe P, NP, NP-hard relationships.', 'marks' => 5, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Algorithm Theory'],
                    ['q' => 'How is a stack utilized in DFS traversal? Explain with example.', 'marks' => 2, 'type' => 'Theory', 'freq' => 4, 'topic' => 'Stack in DFS'],
                ],
            ],
            'SWE-225' => [
                'name' => 'Database Management System',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Summer 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Define DBMS. Explain functionality of any application where DBMS is used. Write drawbacks of file management system which count as strength of DBMS. Explain three levels of data abstraction.', 'marks' => 10, 'type' => 'Theory', 'freq' => 4, 'topic' => 'DBMS Fundamentals'],
                    ['q' => 'Think you are DBA of Grameen Phone. Explain your job responsibilities. Illustrate instance and schema in terms of DBMS. Explain how DBMS solves data isolation problem.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'DBA Role & Schema'],
                    ['q' => 'Demonstrate a suitable ERD for Appendix A database schema (Account, Customer, Branch, Depositor, Employee).', 'marks' => 3, 'type' => 'ERD', 'freq' => 5, 'topic' => 'ER Diagram'],
                    ['q' => 'Demonstrate query optimization for: "Find details of employees who opened account at Zindabazar branch". Produce stored procedure to delete tuple from Customer by customer_id.', 'marks' => 7, 'type' => 'SQL/Optimization', 'freq' => 4, 'topic' => 'Query Optimization & Stored Procedures'],
                    ['q' => 'Using Relational Algebra on Appendix A: (a) Find those involved with the bank (b) Find employees on same street/city as Razib (c) Generate two separate query trees.', 'marks' => 10, 'type' => 'Relational Algebra', 'freq' => 4, 'topic' => 'Relational Algebra'],
                    ['q' => 'Find customers who are not employees. Find all customers with account at all Sylhet branches. Generate query trees.', 'marks' => 10, 'type' => 'Relational Algebra', 'freq' => 3, 'topic' => 'Complex RA Queries'],
                ],
            ],
            'SWE-231' => [
                'name' => 'Software Requirement Engineering',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Spring 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Final', 'term' => 'Summer 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => 'Indicate correct/incorrect: (i) Functional requirements expressed as data redundancy (ii) Requirement engineering done in last stages (iii) Business requirement identifies why software needed (iv) QA team oversees design at verification (v) Performance addresses portability.', 'marks' => 5, 'type' => 'True/False', 'freq' => 4, 'topic' => 'Requirements Theory'],
                    ['q' => 'Write short notes: Requirement Elicitation, Unified Modeling Language (UML).', 'marks' => 5, 'type' => 'Short Notes', 'freq' => 4, 'topic' => 'Elicitation & UML'],
                    ['q' => '(a) Differentiate between activity diagram and use case diagram with example (b) Show sequence diagram of ATM banking system (c) Illustrate Class diagram of restaurant management system.', 'marks' => 10, 'type' => 'UML Diagrams', 'freq' => 5, 'topic' => 'UML Diagrams'],
                    ['q' => '(a) Describe role of QA in each step of Agile model (b) Explain characteristics of good SRS and explain briefly (c) State differences between Validation and Verification.', 'marks' => 10, 'type' => 'Theory', 'freq' => 4, 'topic' => 'Agile & SRS'],
                    ['q' => '(a) Define Requirement Prioritization (b) List importance of prioritizing requirements in a product (c) Discuss different kinds of safety-critical systems.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Requirements Management'],
                    ['q' => '(a) List four methods for Software estimation (b) Characterize Cost-Value Approach and Win-Win Approach (c) Discuss best method for software estimation per your perception.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Software Estimation'],
                ],
            ],
            'SWE-311' => [
                'name' => 'Theory of Computation',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Spring 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Class Test 1', 'term' => 'Spring 2025', 'batch' => '6th', 'marks' => 20, 'time' => '—'],
                    ['type' => 'Class Test 2', 'term' => 'Spring 2025', 'batch' => '6th', 'marks' => 20, 'time' => '—'],
                    ['type' => 'Final', 'term' => 'Summer 2024', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => '(a) Define DFA formally with example diagram (b) Convert NFA to equivalent DFA (state diagram provided) (c) Convert ε-NFA {Σ={x,y,z}} to NFA by eliminating ε-transitions.', 'marks' => 15, 'type' => 'Automata', 'freq' => 5, 'topic' => 'DFA & NFA Conversion'],
                    ['q' => '(a) State NFA. Draw NFA for regular expression (a|b)*abb (b) Convert (xy*z|z|y)(z|x)* to equivalent finite automata (c) Identify whether P and Q finite automata are equivalent.', 'marks' => 10, 'type' => 'Automata', 'freq' => 4, 'topic' => 'NFA & Regular Expressions'],
                    ['q' => '(a) Explain regular expressions with 5 rules and examples. Write RE for binary strings with exactly two 1s (b) Design DFA for binary strings divisible by 2 (c) Minimize DFA using equivalence theorem (diagram given).', 'marks' => 10, 'type' => 'DFA/RE', 'freq' => 5, 'topic' => 'Regular Expressions & DFA Minimization'],
                    ['q' => '(a) Differentiate Mealy and Moore machines with examples (b) Find regular expression for given NFA with states q0,q1,q2.', 'marks' => 10, 'type' => 'Machines', 'freq' => 4, 'topic' => 'Mealy & Moore Machines'],
                    ['q' => 'Given Mealy machine that prints x for "11" or "00", y otherwise. Design equivalent Moore machine.', 'marks' => 5, 'type' => 'Machines', 'freq' => 4, 'topic' => 'Mealy to Moore Conversion'],
                    ['q' => '(a) What is Equivalence Theorem? For what condition are states A and B equivalent? (b) Construct Moore Machine that prints 0 for "ba", 2 otherwise, Σ={a,b}. (c) Convert ε-NFA to NFA (Q0,Q1,Q2,Q3 diagram).', 'marks' => 20, 'type' => 'Class Test', 'freq' => 4, 'topic' => 'State Equivalence & Moore Machine'],
                    ['q' => '(a) Explain significance of ε-transition in NFA (b) Remove unit production from S→XY, X→a, Y→Z|b, Z→M, M→N, N→a (c) Prove {aⁿbⁿ|n≥0} is not regular using Pumping Lemma.', 'marks' => 10, 'type' => 'Grammar', 'freq' => 4, 'topic' => 'Context-Free Grammar & Pumping Lemma'],
                    ['q' => '(a) Construct DFA for L={w|w has even 0s AND even 1s} over {0,1} (b) Design NFA where all strings contain substring "1110" (c) Draw PDA for L={aᵐbⁿ|m<n}.', 'marks' => 11, 'type' => 'Automata', 'freq' => 3, 'topic' => 'PDA & DFA Construction'],
                    ['q' => 'Convert regular expression (a|b)* to equivalent ε-NFA. Draw Turing Machine for L={aⁿbⁿ|n≥1}.', 'marks' => 7, 'type' => 'TM', 'freq' => 3, 'topic' => 'Turing Machine'],
                ],
            ],
            'MAT-211' => [
                'name' => 'Numerical Analysis',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Autumn 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Class Test 1', 'term' => '2025', 'batch' => '6th', 'marks' => 20, 'time' => '1.25h'],
                ],
                'questions' => [
                    ['q' => 'Riya recorded bacterial population as 1250.6 but true value is 1250.5783. She used eᵏᵗ≈1+kt+(kt)²/2!. (i) Identify types of errors (ii) Explain methods to minimize errors.', 'marks' => 4, 'type' => 'Error Analysis', 'freq' => 4, 'topic' => 'Types of Errors'],
                    ['q' => 'Given x̃=1.06 with error Δx̃=0.004, estimate resulting error in f(x)=x²-2x+3.', 'marks' => 2, 'type' => 'Numerical', 'freq' => 3, 'topic' => 'Error Propagation'],
                    ['q' => 'Round off 325.678, 30.7685 to four significant figures and compute eₚ.', 'marks' => 4, 'type' => 'Numerical', 'freq' => 4, 'topic' => 'Significant Figures & Rounding'],
                    ['q' => 'Find real root of x³=2x+5 by Iteration method, correct to three decimal places.', 'marks' => 4, 'type' => 'Root Finding', 'freq' => 4, 'topic' => 'Iteration Method'],
                    ['q' => '2x³-3x=5. John uses Bisection, Smith uses Regula-Falsi. Which do you prefer and why? Solve using your chosen method to 3 decimal places.', 'marks' => 6, 'type' => 'Root Finding', 'freq' => 5, 'topic' => 'Bisection vs Regula-Falsi'],
                    ['q' => 'Ship data: V=[8,10,12,14,16], I=[1000,1900,3250,5400,8950]. Find I when V=13 using Gauss\'s forward interpolation formula.', 'marks' => 5, 'type' => 'Interpolation', 'freq' => 4, 'topic' => 'Newton-Gauss Forward Interpolation'],
                    ['q' => 'Evaluate f(9) using Lagrange\'s interpolation: x=[5,7,11,13,17], f(x)=[150,392,1452,2366,5202].', 'marks' => 5, 'type' => 'Interpolation', 'freq' => 4, 'topic' => 'Lagrange Interpolation'],
                    ['q' => 'Velocity v of particle at distance s: s=[0,10,20,30,40,50,60], v=[47,58,64,65,61,52,38]. Estimate time to travel 60 ft using Simpson\'s 1/3 rule. Compare with 3/8 rule.', 'marks' => 5, 'type' => 'Numerical Integration', 'freq' => 4, 'topic' => 'Simpson\'s Rules'],
                    ['q' => 'Solve dy/dx = y - 2x/y, y(0)=1, h=0.1, range 0≤x≤0.2 using (i) improved Euler\'s method (ii) modified Euler\'s Method.', 'marks' => 5, 'type' => 'ODE', 'freq' => 4, 'topic' => 'Euler\'s Method'],
                    ['q' => 'Solve dy/dx = x+y where y(0)=1. Find y(0.2) using 4th order Runge-Kutta Method.', 'marks' => 5, 'type' => 'ODE', 'freq' => 5, 'topic' => 'Runge-Kutta Method'],
                    ['q' => 'Use Taylor series with n=0 to 6 to approximate f(x)=ln(x) at x=3, given f(x) and derivatives at x=2. Calculate percentage error.', 'marks' => 6, 'type' => 'Series', 'freq' => 3, 'topic' => 'Taylor Series'],
                ],
            ],
            'SWE-315' => [
                'name' => 'Artificial Intelligence',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Autumn 2025', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Final', 'term' => 'Summer 2024', 'batch' => '3rd', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Mid-Term', 'term' => 'Summer 2024', 'batch' => '3rd', 'marks' => 15, 'time' => '1h'],
                ],
                'questions' => [
                    ['q' => 'Differentiate supervised and unsupervised learning. Define heuristic search and distinguish uninformed vs informed search.', 'marks' => 5, 'type' => 'Theory', 'freq' => 4, 'topic' => 'Search Strategies'],
                    ['q' => 'Solve A* algorithm: S is start, G is goal. h(n): S→17, A→10, B→13, C→4, D→2, E→4, F→1, G→0. Graph edges given.', 'marks' => 5, 'type' => 'Algorithm', 'freq' => 5, 'topic' => 'A* Algorithm'],
                    ['q' => 'For matrix A=[[4,0],[3,-5]]: (A) Compute eigenvalues of AᵀA. (B) Compute full Singular Value Decomposition (SVD).', 'marks' => 10, 'type' => 'Math', 'freq' => 3, 'topic' => 'SVD & Eigenvalues'],
                    ['q' => 'Explain atomic proposition variables for Wumpus world. Given propositional rules R1-R4, prove Wumpus is in room (1,3).', 'marks' => 10, 'type' => 'Knowledge Rep.', 'freq' => 4, 'topic' => 'Wumpus World & Knowledge Representation'],
                    ['q' => 'Apply Minimax procedure to find which move the maximizing player should choose. Apply Alpha-Beta Cutoffs and show cutoffs.', 'marks' => 10, 'type' => 'Game Theory', 'freq' => 5, 'topic' => 'Minimax & Alpha-Beta Pruning'],
                    ['q' => 'Differentiate local optimum and global optimum. Solve 8-puzzle problem using Heuristic Method (initial and goal state given).', 'marks' => 5, 'type' => 'Search', 'freq' => 4, 'topic' => 'Heuristic Search & 8-Puzzle'],
                    ['q' => 'Calculate accuracy, precision, recall, and F1 score. Confusion matrix: TP=540, FP=150, FN=110, TN=200.', 'marks' => 5, 'type' => 'ML Metrics', 'freq' => 5, 'topic' => 'Model Evaluation Metrics'],
                    ['q' => 'Given 2D dataset, calculate Euclidean distance between P(4,4) and all other points. Show how changing K from 3 to 5 affects KNN classification.', 'marks' => 6, 'type' => 'ML', 'freq' => 4, 'topic' => 'K-Nearest Neighbors (KNN)'],
                ],
            ],
            'SWE-233' => [
                'name' => 'Software Architecture & Design Patterns',
                'exams' => [
                    ['type' => 'Class Test 1', 'term' => 'Spring 2026', 'batch' => '6th', 'marks' => 20, 'time' => '40m'],
                    ['type' => 'Lab Final', 'term' => 'Spring 2026', 'batch' => '6th', 'marks' => 20, 'time' => '1h'],
                ],
                'questions' => [
                    ['q' => 'Given UserService class with registerUser(), sendEmail(), writeLog(). Identify the SOLID principle violated. Explain why it is a violation.', 'marks' => 3, 'type' => 'SOLID', 'freq' => 5, 'topic' => 'Single Responsibility Principle'],
                    ['q' => 'Refactor UserService to adhere to the identified SOLID principle. Provide improved code structure with clear class names and methods.', 'marks' => 4, 'type' => 'Code Refactoring', 'freq' => 5, 'topic' => 'SOLID Refactoring'],
                    ['q' => 'How are Design Principles Different from Design Patterns?', 'marks' => 3, 'type' => 'Theory', 'freq' => 4, 'topic' => 'Principles vs Patterns'],
                    ['q' => 'What are the SOLID Principles? List and briefly explain each.', 'marks' => 3, 'type' => 'Theory', 'freq' => 5, 'topic' => 'SOLID Principles'],
                    ['q' => 'Given Account class with withdraw() and FixedDeposit extending Account throwing RuntimeException. Identify violated SOLID principle and refactor.', 'marks' => 7, 'type' => 'SOLID+Code', 'freq' => 4, 'topic' => 'Liskov Substitution Principle'],
                    ['q' => 'Lab: Build chat application with Factory (message type) and Observer (notify users) patterns.', 'marks' => 10, 'type' => 'Implementation', 'freq' => 3, 'topic' => 'Factory + Observer Patterns'],
                    ['q' => 'Lab: Stock trading platform — (a) Observer pattern for subscribe/unsubscribe to stock events (b) Strategy pattern for flexible alert actions.', 'marks' => 10, 'type' => 'Implementation', 'freq' => 3, 'topic' => 'Observer + Strategy Patterns'],
                    ['q' => 'Lab: Reporting tool aggregating multiple analytics services — (a) Facade for unified interface (b) Iterator for combined dataset traversal.', 'marks' => 10, 'type' => 'Implementation', 'freq' => 3, 'topic' => 'Facade + Iterator Patterns'],
                ],
            ],
            'GED-301' => [
                'name' => 'Basic Statistics & Probability',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Summer 2025', 'batch' => '5th', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Final', 'term' => 'Summer 2024', 'batch' => '3rd', 'marks' => 40, 'time' => '2h'],
                    ['type' => 'Mid-Term', 'term' => 'Summer 2024', 'batch' => '3rd', 'marks' => 15, 'time' => '1h'],
                    ['type' => 'Mid-Term', 'term' => 'Spring 2024', 'batch' => '1st & 2nd', 'marks' => 30, 'time' => '1.5h'],
                ],
                'questions' => [
                    ['q' => 'Define Statistics per R.A. Fisher. Discuss data types by way of collection. Construct more-than cumulative frequency table and Ogive for given marks data.', 'marks' => 7, 'type' => 'Theory+Computation', 'freq' => 3, 'topic' => 'Frequency Distribution & Ogive'],
                    ['q' => 'Prove Geometric Mean (G.M.) ≥ Harmonic Mean (H.M.)', 'marks' => 3, 'type' => 'Proof', 'freq' => 4, 'topic' => 'AM-GM-HM Inequality'],
                    ['q' => 'Female workers salary distribution: 500-600→10, 600-700→40, 700-800→65, 800-900→250, etc. Find maximum salary on average of the major group.', 'marks' => 5, 'type' => 'Numerical', 'freq' => 3, 'topic' => 'Mode / Central Tendency'],
                    ['q' => 'Emails received: 12,18,10,15,8,11,19,17,20,15,18. Calculate Q₁, Q₂, Q₃ and Quartile Deviation.', 'marks' => 5, 'type' => 'Numerical', 'freq' => 5, 'topic' => 'Quartiles & Quartile Deviation'],
                    ['q' => 'Demonstrate Variance and Standard Deviation. Calculate SD of working hours: 8-9→53, 9-10→169, 10-11→51, 11-12→27.', 'marks' => 5, 'type' => 'Numerical', 'freq' => 4, 'topic' => 'Standard Deviation'],
                    ['q' => 'Export data (x) and total export (y) data given. Find Pearson\'s correlation coefficient (rₓᵧ) and interpret r.', 'marks' => 5, 'type' => 'Correlation', 'freq' => 4, 'topic' => 'Pearson Correlation'],
                    ['q' => 'Bayes Theorem: Machine A produces 45%, B produces rest. A has 9/1000 defective, B has 2/500 defective. A random item is defective. Find P(from A) and P(from B).', 'marks' => 8, 'type' => 'Probability', 'freq' => 5, 'topic' => 'Bayes Theorem'],
                    ['q' => 'Fair coin tossed until head appears OR 4 times. Given no head in first two tosses, find P(tossed 4 times) and P(tossed 3 times).', 'marks' => 6, 'type' => 'Probability', 'freq' => 4, 'topic' => 'Conditional Probability'],
                    ['q' => 'P[A]=0.25, P[B]=0.40, P[AB]=0.15. Find P[A\'∩B\'], P[A\'∪B\'], P[A\'∩B], P[A∩B\'].', 'marks' => 4, 'type' => 'Probability', 'freq' => 5, 'topic' => 'Set Probability'],
                ],
            ],
            'SWE-449' => [
                'name' => 'Digital Marketing',
                'exams' => [
                    ['type' => 'Final', 'term' => 'Autumn 2025', 'batch' => '6th', 'marks' => 40, 'time' => '2h'],
                ],
                'questions' => [
                    ['q' => '(a) Define Email Marketing (b) Explain Key Steps in an Email Marketing Campaign (c) Justify the importance of CTR.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Email Marketing & CTR'],
                    ['q' => '(a) Describe E-Commerce Marketing (b) Compare SEO and Social Media Marketing (c) Describe On-Page, Off-Page, and Technical SEO.', 'marks' => 10, 'type' => 'Theory', 'freq' => 4, 'topic' => 'SEO & E-Commerce'],
                    ['q' => '(a) Explain Online Reputation Management (ORM) and how it helps a Brand (b) Discuss key strategies of Mobile Marketing & Video Marketing (c) Describe Digital Marketing Tools & Platforms.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'ORM & Mobile Marketing'],
                    ['q' => '(a) Describe key parties in a Successful Affiliate Marketing Campaign (b) Illustrate a Marketing Funnel (c) Describe the Stages of Marketing Funnels.', 'marks' => 10, 'type' => 'Theory', 'freq' => 4, 'topic' => 'Affiliate Marketing & Funnel'],
                    ['q' => '(a) Define Content Marketing (b) Describe Key Elements of Content Marketing (c) Explain Influencer Marketing and four Types of Influencers.', 'marks' => 10, 'type' => 'Theory', 'freq' => 3, 'topic' => 'Content & Influencer Marketing'],
                    ['q' => '(a) Analyze how a food blog can rank higher for "quick healthy breakfast ideas" using On-Page SEO (b) Analyze why a high-traffic website has high bounce rate and suggest improvements.', 'marks' => 10, 'type' => 'Analysis', 'freq' => 3, 'topic' => 'Applied SEO Analysis'],
                ],
            ],
        ];
    }

    public static function hasApprovedQuestions(): bool
    {
        $db = getDB();
        return (int) $db->query("SELECT COUNT(*) FROM questions WHERE is_approved = 1")->fetchColumn() > 0;
    }

    public static function getCourseSummariesFromDb(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT c.id, c.code, c.name, c.year, c.semester, COUNT(q.id) AS question_count
            FROM courses c
            JOIN questions q ON q.course_id = c.id AND q.is_approved = 1
            GROUP BY c.id, c.code, c.name, c.year, c.semester
            ORDER BY c.year ASC, c.semester ASC, c.code ASC
        ");

        $courses = [];
        foreach ($stmt->fetchAll() as $row) {
            $code = $row['code'] ?: ('COURSE-' . $row['id']);
            $courses[$code] = [
                'id' => (int) $row['id'],
                'code' => $code,
                'name' => $row['name'],
                'year' => (int) $row['year'],
                'semester' => (int) $row['semester'],
                'question_count' => (int) $row['question_count'],
            ];
        }

        return $courses;
    }

    public static function findFilteredFromDb(array $filters): array
    {
        $db = getDB();
        $conditions = ['q.is_approved = 1'];
        $params = [];

        if (!empty($filters['course'])) {
            $conditions[] = 'c.code = ?';
            $params[] = $filters['course'];
        }

        if (!empty($filters['topic'])) {
            $conditions[] = 'q.topic = ?';
            $params[] = $filters['topic'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = 'q.question_type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(q.question_text LIKE ? OR q.topic LIKE ? OR c.name LIKE ? OR c.code LIKE ?)';
            $search = '%' . trim($filters['q']) . '%';
            array_push($params, $search, $search, $search, $search);
        }

        $whereClause = implode(' AND ', $conditions);
        $stmt = $db->prepare("
            SELECT
                q.id,
                q.question_text AS q,
                q.marks,
                q.topic,
                q.question_type AS type,
                q.exam_year,
                q.exam_semester,
                q.view_count,
                c.id AS course_id,
                c.code AS course_code,
                c.name AS course_name,
                (
                    SELECT COUNT(*)
                    FROM questions q2
                    WHERE q2.is_approved = 1
                      AND q2.course_id = q.course_id
                      AND COALESCE(q2.topic, '') = COALESCE(q.topic, '')
                ) AS freq
            FROM questions q
            JOIN courses c ON c.id = q.course_id
            WHERE $whereClause
            ORDER BY q.created_at DESC, q.id DESC
        ");
        $stmt->execute($params);

        $questions = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['marks'] = (int) $row['marks'];
            $row['freq'] = max(1, min(5, (int) $row['freq']));
            $questions[] = $row;
        }

        return $questions;
    }

    public static function getHotTopicsFromDb(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT topic, COUNT(*) AS count
            FROM questions
            WHERE is_approved = 1 AND topic IS NOT NULL AND topic != ''
            GROUP BY topic
            ORDER BY count DESC, topic ASC
            LIMIT 12
        ");

        $topics = [];
        foreach ($stmt->fetchAll() as $row) {
            $topics[$row['topic']] = [
                'count' => (int) $row['count'],
                'freq_sum' => (int) $row['count'],
            ];
        }

        return $topics;
    }

    public static function getTypesFromDb(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT question_type, COUNT(*) AS count
            FROM questions
            WHERE is_approved = 1
            GROUP BY question_type
            ORDER BY count DESC, question_type ASC
        ");

        $types = [];
        foreach ($stmt->fetchAll() as $row) {
            $types[$row['question_type']] = (int) $row['count'];
        }

        return $types;
    }

    public static function getExamHistoryByCourseCode(string $courseCode): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT
                q.exam_year,
                q.exam_semester,
                COUNT(*) AS question_count,
                MAX(q.marks) AS top_marks
            FROM questions q
            JOIN courses c ON c.id = q.course_id
            WHERE q.is_approved = 1 AND c.code = ?
            GROUP BY q.exam_year, q.exam_semester
            ORDER BY q.exam_year DESC, q.exam_semester DESC
        ");
        $stmt->execute([$courseCode]);
        return $stmt->fetchAll();
    }

    public static function findFiltered(array $filters): array
    {
        $all = self::getAll();
        $filteredCourse = $filters['course'] ?? '';
        $filterTopic = $filters['topic'] ?? '';
        $filterType = $filters['type'] ?? '';
        $search = strtolower(trim($filters['q'] ?? ''));

        $filteredQs = [];
        foreach ($all as $code => $cd) {
            if ($filteredCourse && $code !== $filteredCourse) continue;
            foreach ($cd['questions'] as $q) {
                if ($filterTopic && $q['topic'] !== $filterTopic) continue;
                if ($filterType && $q['type'] !== $filterType) continue;
                if ($search && strpos(strtolower($q['q'] . ' ' . $q['topic']), $search) === false) continue;
                $filteredQs[] = array_merge($q, ['course_code' => $code, 'course_name' => $cd['name']]);
            }
        }
        return $filteredQs;
    }
    
    public static function getHotTopics(): array
    {
        $all = self::getAll();
        $hotTopics = [];
        foreach ($all as $code => $cd) {
            foreach ($cd['questions'] as $q) {
                $t = $q['topic'];
                if (!isset($hotTopics[$t])) $hotTopics[$t] = ['count' => 0, 'freq_sum' => 0];
                $hotTopics[$t]['count']++;
                $hotTopics[$t]['freq_sum'] += $q['freq'];
            }
        }
        uasort($hotTopics, fn($a, $b) => $b['freq_sum'] <=> $a['freq_sum']);
        return array_slice($hotTopics, 0, 12, true);
    }

    public static function getTypes(): array
    {
        $all = self::getAll();
        $types = [];
        foreach ($all as $code => $cd) {
            foreach ($cd['questions'] as $q) {
                $types[$q['type']] = ($types[$q['type']] ?? 0) + 1;
            }
        }
        arsort($types);
        return $types;
    }
}
