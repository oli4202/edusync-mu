<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Question;
use App\Models\User;
use App\Models\Group;
use App\Models\Job;
use App\Support\Search;
use App\Support\Paginator;

/**
 * Search Controller
 * Handles global search across questions, users, groups, jobs
 */
class SearchController extends Controller
{
    public function search(): void
    {
        $query = clean($_GET['q'] ?? '');
        $type = clean($_GET['type'] ?? 'all');
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 15;

        $results = [
            'questions' => [],
            'users' => [],
            'groups' => [],
            'jobs' => [],
        ];

        $pagination = [];

        if (strlen($query) >= 2) {
            $db = getDB();

            // Search questions
            if ($type === 'all' || $type === 'questions') {
                $searchConditions = Search::buildLikeConditions($query, ['title', 'description', 'body']);
                $countStmt = $db->prepare("SELECT COUNT(*) FROM questions WHERE approved = 1 AND " . $searchConditions['clause']);
                $countStmt->execute($searchConditions['params']);
                $total = (int)$countStmt->fetchColumn();

                $pag = new Paginator($total, $perPage, $page);
                $pagination['questions'] = $pag;

                $stmt = $db->prepare("
                    SELECT q.id, q.title, q.description, q.created_at, u.name AS author_name, u.id AS author_id
                    FROM questions q
                    LEFT JOIN users u ON q.user_id = u.id
                    WHERE q.approved = 1 AND (" . $searchConditions['clause'] . ")
                    ORDER BY q.created_at DESC
                    " . $pag->getLimitClause()
                );
                $stmt->execute($searchConditions['params']);
                $results['questions'] = $stmt->fetchAll();
            }

            // Search users
            if ($type === 'all' || $type === 'users') {
                $searchConditions = Search::buildLikeConditions($query, ['name', 'email', 'student_id']);
                $countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'student' AND " . $searchConditions['clause']);
                $countStmt->execute($searchConditions['params']);
                $total = (int)$countStmt->fetchColumn();

                $pag = new Paginator($total, $perPage, $page);
                $pagination['users'] = $pag;

                $stmt = $db->prepare("
                    SELECT id, name, student_id, avatar, bio FROM users
                    WHERE role = 'student' AND (" . $searchConditions['clause'] . ")
                    ORDER BY name ASC
                    " . $pag->getLimitClause()
                );
                $stmt->execute($searchConditions['params']);
                $results['users'] = $stmt->fetchAll();
            }

            // Search study groups
            if ($type === 'all' || $type === 'groups') {
                $searchConditions = Search::buildLikeConditions($query, ['name', 'description']);
                $countStmt = $db->prepare("SELECT COUNT(*) FROM study_groups WHERE is_public = 1 AND " . $searchConditions['clause']);
                $countStmt->execute($searchConditions['params']);
                $total = (int)$countStmt->fetchColumn();

                $pag = new Paginator($total, $perPage, $page);
                $pagination['groups'] = $pag;

                $stmt = $db->prepare("
                    SELECT sg.id, sg.name, sg.description, sg.max_members, COUNT(sgm.id) AS member_count
                    FROM study_groups sg
                    LEFT JOIN study_group_members sgm ON sg.id = sgm.group_id
                    WHERE sg.is_public = 1 AND (" . $searchConditions['clause'] . ")
                    GROUP BY sg.id
                    ORDER BY member_count DESC, sg.name ASC
                    " . $pag->getLimitClause()
                );
                $stmt->execute($searchConditions['params']);
                $results['groups'] = $stmt->fetchAll();
            }

            // Search jobs/internships
            if ($type === 'all' || $type === 'jobs') {
                $searchConditions = Search::buildLikeConditions($query, ['title', 'company', 'description']);
                $countStmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE " . $searchConditions['clause']);
                $countStmt->execute($searchConditions['params']);
                $total = (int)$countStmt->fetchColumn();

                $pag = new Paginator($total, $perPage, $page);
                $pagination['jobs'] = $pag;

                $stmt = $db->prepare("
                    SELECT id, title, company, job_type, posted_date FROM jobs
                    WHERE " . $searchConditions['clause'] . "
                    ORDER BY posted_date DESC
                    " . $pag->getLimitClause()
                );
                $stmt->execute($searchConditions['params']);
                $results['jobs'] = $stmt->fetchAll();
            }
        }

        $this->render('pages/search', compact('query', 'type', 'results', 'pagination'));
    }

    /**
     * AJAX search suggestions
     */
    public function suggestions(): void
    {
        $query = clean($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            $this->json(['suggestions' => []]);
        }

        $db = getDB();
        $suggestions = [];

        // Get question suggestions
        $searchConditions = Search::buildLikeConditions($query, ['title']);
        $stmt = $db->prepare("
            SELECT 'question' AS type, id, title AS label FROM questions
            WHERE approved = 1 AND " . $searchConditions['clause'] . "
            ORDER BY title ASC
            LIMIT 5
        ");
        $stmt->execute($searchConditions['params']);
        $suggestions = array_merge($suggestions, $stmt->fetchAll());

        // Get user suggestions
        $searchConditions = Search::buildLikeConditions($query, ['name']);
        $stmt = $db->prepare("
            SELECT 'user' AS type, id, name AS label FROM users
            WHERE role = 'student' AND " . $searchConditions['clause'] . "
            ORDER BY name ASC
            LIMIT 5
        ");
        $stmt->execute($searchConditions['params']);
        $suggestions = array_merge($suggestions, $stmt->fetchAll());

        $this->json(['suggestions' => $suggestions]);
    }
}
