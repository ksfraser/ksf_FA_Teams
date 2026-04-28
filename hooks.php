<?php
/**
 * Teams Module for FrontAccounting
 */

$module_id = 'Teams';
$module_version = '1.0.0';
$module_name = 'Teams Management';
$module_description = 'Team creation and member management';

$module_tables = ['fa_teams', 'fa_team_members', 'fa_team_projects'];

$module_capabilities = [
    'SA_TEAMSVIEW' => 'View Teams',
    'SA_TEAMSCREATE' => 'Create Teams',
    'SA_TEAMSDELETE' => 'Delete Teams',
    'SA_TEAMSMANAGE' => 'Manage Team Members',
];

function teams_install(): bool { return install_module_sql('Teams'); }
function teams_enable(): bool { return enable_module('Teams'); }
function teams_disable(): bool { return disable_module('Teams'); }
function teams_remove(): bool { return remove_module_sql('Teams'); }

add_module($module_name, $module_version, $module_description);