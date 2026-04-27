<?php
/**
 * scratch/auto_register_swe.php
 * Triggers the automatic registration/sync for all students in the SWE roster.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\User;
use App\Support\StudentRoster;

echo "Starting auto-registration for all SWE students...\n";

// This method iterates through the roster and creates/updates user accounts
User::ensureRosterSynced();

$db = App\getDB();
$count = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

echo "Success! Total students registered in the system: $count\n";
echo "Default login: Student ID (as both username and password)\n";
echo "Department set to: Software Engineering\n";
