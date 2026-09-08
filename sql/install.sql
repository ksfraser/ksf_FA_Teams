-- Teams module database schema for FrontAccounting (using TB_PREF standard)
-- Per architecture spec: modules register/unregister contact types; 
-- tables use standard FA table naming (TB_PREF handled by update_databases)

-- Note: This SQL is applied via update_databases() which substitutes {TB_PREF} or TB_PREF.
-- The module hooks (install_access) defines the security sections; the 
-- SQL creates supporting tables for team management.

-- Teams table (uses TB_PREF or 0_ prefix per module convention)
CREATE TABLE IF NOT EXISTS `0_ksf_teams` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `manager_id` INT(11) DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    `created_by` INT(11) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `manager_id` (`manager_id`),
    KEY `department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team members (links employees to teams)
CREATE TABLE IF NOT EXISTS `0_ksf_team_members` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team_id` INT(11) NOT NULL,
    `employee_id` INT(11) NOT NULL,
    `role` ENUM('Member','Lead','Assistant') NOT NULL DEFAULT 'Member',
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_employee` (`team_id`,`employee_id`),
    KEY `employee_id` (`employee_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team-project links (for project management integration)
CREATE TABLE IF NOT EXISTS `0_ksf_team_projects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team_id` INT(11) NOT NULL,
    `project_id` INT(11) NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_project` (`team_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
