<?php
/**
 * Teams Database Functions
 */

function create_team(string $name, int $manager_id = null, string $department = '', string $description = ''): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "teams (name, manager_id, department, description, created_by)
        VALUES (" . $db->escape($name) . ", " . ($manager_id ? $manager_id : 'NULL') . ", " . $db->escape($department) . ", " . $db->escape($description) . ", " . user_id() . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_team(int $id): ?array
{
    global $db;
    $sql = "SELECT t.*, m.name as manager_name FROM " . TB_PREF . "teams t
        LEFT JOIN " . TB_PREF . "hr_employees m ON t.manager_id = m.id WHERE t.id = " . $id;
    $result = $db->query($sql);
    if ($result && $db->num_rows($result) > 0) {
        return $db->fetch($result);
    }
    return null;
}

function get_teams(array $filters = []): ?object
{
    global $db;
    $sql = "SELECT t.*, (SELECT COUNT(*) FROM " . TB_PREF . "team_members WHERE team_id = t.id) as member_count FROM " . TB_PREF . "teams t WHERE status = 'Active'";
    
    if (!empty($filters['department'])) {
        $sql .= " AND t.department = " . $db->escape($filters['department']);
    }
    if (!empty($filters['manager_id'])) {
        $sql .= " AND t.manager_id = " . $filters['manager_id'];
    }
    
    $sql .= " ORDER BY t.name";
    return $db->query($sql);
}

function add_team_member(int $team_id, int $employee_id, string $role = 'Member'): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "team_members (team_id, employee_id, role)
        VALUES (" . $team_id . ", " . $employee_id . ", " . $db->escape($role) . ")
        ON DUPLICATE KEY UPDATE role = " . $db->escape($role);
    $db->query($sql);
    return $db->insert_id;
}

function remove_team_member(int $team_id, int $employee_id): bool
{
    global $db;
    $sql = "DELETE FROM " . TB_PREF . "team_members WHERE team_id = " . $team_id . " AND employee_id = " . $employee_id;
    return $db->query($sql);
}

function get_team_members(int $team_id): ?object
{
    global $db;
    $sql = "SELECT tm.*, e.name as employee_name, e.department FROM " . TB_PREF . "team_members tm
        LEFT JOIN " . TB_PREF . "hr_employees e ON tm.employee_id = e.id
        WHERE tm.team_id = " . $team_id . " ORDER BY tm.role, e.name";
    return $db->query($sql);
}

function get_employee_teams(int $employee_id): ?object
{
    global $db;
    $sql = "SELECT t.*, tm.role FROM " . TB_PREF . "teams t
        INNER JOIN " . TB_PREF . "team_members tm ON t.id = tm.team_id
        WHERE tm.employee_id = " . $employee_id . " AND t.status = 'Active' ORDER BY t.name";
    return $db->query($sql);
}

function assign_team_project(int $team_id, int $project_id): bool
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "team_projects (team_id, project_id)
        VALUES (" . $team_id . ", " . $project_id . ")
        ON DUPLICATE KEY UPDATE project_id = " . $project_id;
    return $db->query($sql);
}

function get_team_projects(int $team_id): ?object
{
    global $db;
    $sql = "SELECT p.* FROM " . TB_PREF . "projects p
        INNER JOIN " . TB_PREF . "team_projects tp ON p.id = tp.project_id
        WHERE tp.team_id = " . $team_id . " ORDER BY p.name";
    return $db->query($sql);
}