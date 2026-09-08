-- Teams module database schema for FrontAccounting

-- Teams table
CREATE TABLE IF NOT EXISTS `fa_teams` (
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

-- Team members
CREATE TABLE IF NOT EXISTS `fa_team_members` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team_id` INT(11) NOT NULL,
    `employee_id` INT(11) NOT NULL,
    `role` ENUM('Member','Lead','Assistant') NOT NULL DEFAULT 'Member',
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_employee` (`team_id`,`employee_id`),
    KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team projects
CREATE TABLE IF NOT EXISTS `fa_team_projects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team_id` INT(11) NOT NULL,
    `project_id` INT(11) NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_project` (`team_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Module version
INSERT INTO `fa_modules` (`name`, `version`, `enabled`, `installed`) VALUES
('Teams', '1.0.0', 1, NOW())
ON DUPLICATE KEY UPDATE `version` = '1.0.0', `installed` = NOW();