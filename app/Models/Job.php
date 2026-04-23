<?php

namespace App\Models;

use function App\getDB;
use function App\clean;

/**
 * Job Model
 */
class Job
{
    public static function findAll(int $userId, array $filters = []): array
    {
        $db = getDB();
        $typeFilter = $filters['type'] ?? '';
        $search = $filters['q'] ?? '';
        
        $where = ['j.is_approved=1'];
        $params = [$userId];
        
        if ($typeFilter) {
            $where[] = 'j.type=?';
            $params[] = $typeFilter;
        }
        if ($search) {
            $where[] = '(j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)';
            $params = array_merge($params, array_fill(0, 3, "%$search%"));
        }

        $stmt = $db->prepare("SELECT j.*, u.name AS poster_name,
            (SELECT COUNT(*) FROM job_saves s WHERE s.job_id=j.id AND s.user_id=?) AS saved
            FROM job_posts j JOIN users u ON j.user_id=u.id
            WHERE " . implode(' AND ', $where) . " ORDER BY j.posted_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data, bool $isAdmin = false): bool
    {
        $db = getDB();
        $company = clean($data['company']);
        $title   = clean($data['title']);
        $type    = clean($data['type']);
        $loc     = clean($data['location'] ?? '');
        $desc    = clean($data['description']);
        $req     = clean($data['requirements'] ?? '');
        $salary  = clean($data['salary'] ?? '');
        $deadline= clean($data['deadline'] ?? '');
        $link    = clean($data['apply_link'] ?? '');
        $email   = clean($data['apply_email'] ?? '');
        $approved = $isAdmin ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO job_posts (user_id,company,title,type,location,description,requirements,salary,deadline,apply_link,apply_email,is_approved) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        return $stmt->execute([$userId, $company, $title, $type, $loc, $desc, $req, $salary, $deadline ?: null, $link, $email, $approved]);
    }

    public static function approve(int $jobId, int $adminId): bool
    {
        $db = getDB();
        return $db->prepare("UPDATE job_posts SET is_approved=1, approved_by=? WHERE id=?")->execute([$adminId, $jobId]);
    }

    public static function delete(int $jobId): bool
    {
        $db = getDB();
        return $db->prepare("DELETE FROM job_posts WHERE id=?")->execute([$jobId]);
    }

    public static function toggleSave(int $jobId, int $userId): bool
    {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM job_saves WHERE user_id=? AND job_id=?");
        $check->execute([$userId, $jobId]);
        if ($check->fetch()) {
            return $db->prepare("DELETE FROM job_saves WHERE user_id=? AND job_id=?")->execute([$userId, $jobId]);
        } else {
            return $db->prepare("INSERT INTO job_saves (user_id,job_id) VALUES (?,?)")->execute([$userId, $jobId]);
        }
    }
}
