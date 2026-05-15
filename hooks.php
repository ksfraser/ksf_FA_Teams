<?php
/**
 * FA_Teams Module Hooks for FrontAccounting
 */

define('SS_TEAMS', 137 << 8);

class hooks_fa_teams extends hooks {
    var $module_name = 'fa_teams';
    var $version = '1.0.0';

    function install_options($app) {
        global $path_to_root;

        switch($app->id) {
            case 'HR':
                $app->add_lapp_function(0, _("Teams"),
                    $path_to_root."/modules/".$this->module_name."/teams.php", 'SA_TEAMSVIEW', MENU_ENTRY);
                $app->add_lapp_function(1, _("Create Team"),
                    $path_to_root."/modules/".$this->module_name."/create.php", 'SA_TEAMSCREATE', MENU_ENTRY);
                $app->add_lapp_function(2, _("Team Members"),
                    $path_to_root."/modules/".$this->module_name."/members.php", 'SA_TEAMSMANAGE', MENU_ENTRY);
                break;
        }
    }

    function install_access() {
        $security_sections[SS_TEAMS] = _("Teams Management");
        $security_areas['SA_TEAMSVIEW'] = array(SS_TEAMS | 1, _("View Teams"));
        $security_areas['SA_TEAMSCREATE'] = array(SS_TEAMS | 2, _("Create Teams"));
        $security_areas['SA_TEAMSDELETE'] = array(SS_TEAMS | 3, _("Delete Teams"));
        $security_areas['SA_TEAMSMANAGE'] = array(SS_TEAMS | 4, _("Manage Team Members"));
        return array($security_areas, $security_sections);
    }

    function activate_extension($company, $check_only=true) {
        $updates = array('sql/update.sql' => array($this->module_name));
        $ok = $this->update_databases($company, $updates, $check_only);
        if ($check_only || !$ok) {
            return $ok;
        }
        $this->ensure_teams_schema();
        return $ok;
    }

    private function table_exists($table) {
        $sql = "SHOW TABLES LIKE " . db_escape($table);
        $res = db_query($sql, 'Failed checking table existence');
        return db_num_rows($res) > 0;
    }

    private function ensure_teams_schema() {
        $tables = array(
            TB_PREF . "fa_teams" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_teams` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL,
                    `description` TEXT,
                    `leader_id` VARCHAR(100) DEFAULT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_team_members" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_team_members` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `team_id` INT(11) NOT NULL,
                    `employee_id` VARCHAR(100) NOT NULL,
                    `role` VARCHAR(50) DEFAULT 'Member',
                    `joined_at` DATE DEFAULT NULL,
                    `left_at` DATE DEFAULT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_team_employee` (`team_id`, `employee_id`),
                    KEY `idx_employee` (`employee_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_team_projects" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_team_projects` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `team_id` INT(11) NOT NULL,
                    `project_id` VARCHAR(20) NOT NULL,
                    `assigned_at` DATE DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_team_project` (`team_id`, `project_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        foreach ($tables as $table_name => $sql) {
            db_query($sql, "Could not create Teams table: $table_name");
        }
    }

    function db_prevoid($trans_type, $trans_no) {
        // Handle voiding if needed
    }
}
?>
