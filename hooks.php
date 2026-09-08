<?php
/**
 * KSF FrontAccounting Module Hooks
 * 
 * STANDARD PATTERNS:
 * 
 * 1. ADDING MODULE TABS
 *    Define a class extending 'application' in hooks.php.
 *    Return new instance from install_tabs().
 *    Include add_extensions() to load other modules' install_options.
 * 
 * 2. ADDING MENU ITEMS TO EXISTING APPS
 *    Use install_options() with switch($app->id).
 *    Use add_module() + add_lapp_function() for new menu section.
 * 
 * 3. DATABASE SCHEMA
 *    DO NOT create tables in PHP code.
 *    Use sql/install.sql with @TB_PREF@ placeholders.
 *    Call $this->update_databases() in activate_extension().
 * 
 * 4. SECURITY
 *    Define SS_<MODULE> constant (section << 8).
 *    Define SA_<MODULE>VIEW and SA_<MODULE>MANAGE in install_access().
 * 
 * @package KsfFA_ksf_FA_Teams
 * @version 2.4.3
 */

define('SS_ksf_FA_Teams', 138 << 8);

// Shared utility: ensure Composer dependencies are installed (runs once).
// Uses ksf_FA_Common's ComposerDependencies utility (standard pattern).
$composerDepsPath = dirname(__DIR__) . '/ksf_FA_Common/src/Utils/ComposerDependencies.php';
if (file_exists($composerDepsPath)) {
    require_once $composerDepsPath;
    \ksfraser\FrontAccounting\Common\Utils\ComposerDependencies::ensure(__DIR__);
}

class hooks_ksf_FA_Teams extends hooks {
    var $module_name = 'ksf_FA_Teams';
    var $version     = '2.4.3-0';

    /**
     * Add module tab
     * 
     * Return new application class instance to add a tab.
     * Omit or return nothing to skip tab addition.
     * 
     * @param application|null $app Ignored
     * @return application|null New tab application instance or nothing
     */
    function install_tabs($app) {
        set_ext_domain('modules/ksf_FA_Teams');
        if (class_exists('application')) {
            $tab = new application('teams_app', 'Teams');
            $tab->set_title('Teams');
            $tab->set_icon('group');
            return $tab;
        }
        return null;
    }

    /**
     * Add menu items to existing FA applications
     * 
     * @param application $app FA application instance
     */
    function install_options($app) {
        global $path_to_root;
        // Basic menu items added per standard pattern
        switch($app->id) {
            case 'manuf':
            case 'setup':
                break;
        }
    }

    /**
     * Define security areas
     * 
     * @return array [0] => $security_areas, [1] => $security_sections
     */
    function install_access() {
        $security_sections[SS_ksf_FA_Teams] = _("");
        $security_areas['SA_ksf_FA_TeamsVIEW'] = array(
            SS_ksf_FA_Teams | 1, 
            _("View ")
        );
        $security_areas['SA_ksf_FA_TeamsMANAGE'] = array(
            SS_ksf_FA_Teams | 2, 
            _("Manage ")
        );
        return array($security_areas, $security_sections);
    }

    /**
     * Activate extension
     * 
     * @param int $company Company number
     * @param bool $check_only Only check if activation possible
     * @return bool Success
     */
    function activate_extension($company, $check_only=true) {
        $this->ensure_composer_dependencies();
        
        // Apply sql/install.sql using update_databases()
        // This handles @TB_PREF@ replacement automatically
        if (file_exists(dirname(__FILE__) . '/sql/install.sql')) {
            $updates = array('install.sql' => array($this->module_name));
            return $this->update_databases($company, $updates, $check_only);
        }
        
        return true;
    }
    /**
     * Install composer dependencies if needed
     */
    private function ensure_composer_dependencies(): void {
        $module_dir = dirname(__FILE__);
        $autoload_path = $module_dir . '/vendor/autoload.php';
        
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
            return;
        }
        
        $composer_path = $module_dir . '/composer.json';
        if (!file_exists($composer_path)) {
            return;
        }
        
        try {
            chdir($module_dir);
            $output = [];
            $return_code = 0;
            exec('composer install --no-interaction --prefer-dist 2>&1', $output, $return_code);
            if ($return_code !== 0) {
                error_log('KSF Teams: composer install failed: ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            error_log('KSF Teams: composer install exception: ' . $e->getMessage());
        }
    }

    /**
     * Emit team_created hook for GPG key management.
     *
     * @param int $teamId
     * @param string $teamName
     * @param string $teamEmail
     * @return void
     *
     * @since 1.5.0
     */

    // Minimal event dispatcher — only responds to team lifecycle events
    // No capability negotiation, no user/project event responses (reduces framework interaction)
    function hook_invoke_all($hook, &$data) {
        switch ($hook) {
            case 'team_created':
            case 'team_updated':
            case 'team_deleted':
            case 'user_team_assigned':
            case 'user_team_unassigned':
                return null;  // Events emitted by this module; no external consumption
            default:
                return null;
        }
    }

    public function emitTeamCreated(int $teamId, string $teamName, string $teamEmail = ''): void
    {
        $data = [
            'entity_type' => 'team',
            'entity_id' => 'team_' . $teamId,
            'team_id' => $teamId,
            'team_name' => $teamName,
            'team_email' => $teamEmail ?: 'team-' . $teamId . '@company.com',
        ];

        hook_invoke_all('team_created', $data);
    }

    /**
     * Emit team_updated hook.
     *
     * @param int $teamId
     * @param array $changes
     * @return void
     *
     * @since 1.5.0
     */
    public function emitTeamUpdated(int $teamId, array $changes = []): void
    {
        $data = [
            'entity_type' => 'team',
            'entity_id' => 'team_' . $teamId,
            'team_id' => $teamId,
            'changes' => $changes,
        ];

        hook_invoke_all('team_updated', $data);
    }

    /**
     * Emit team_deleted hook.
     *
     * @param int $teamId
     * @return void
     *
     * @since 1.5.0
     */
    public function emitTeamDeleted(int $teamId): void
    {
        $data = [
            'entity_type' => 'team',
            'entity_id' => 'team_' . $teamId,
            'team_id' => $teamId,
        ];

        hook_invoke_all('team_deleted', $data);
    }

    /**
     * Emit user_team_assigned hook.
     *
     * @param int $userId
     * @param int $teamId
     * @param string $role
     * @return void
     *
     * @since 1.5.0
     */
    public function emitUserTeamAssigned(int $userId, int $teamId, string $role = ''): void
    {
        $data = [
            'user_id' => $userId,
            'team_id' => $teamId,
            'entity_type' => 'team',
            'entity_id' => 'team_' . $teamId,
            'role' => $role,
        ];

        hook_invoke_all('user_team_assigned', $data);
    }

    /**
     * Emit user_team_unassigned hook.
     *
     * @param int $userId
     * @param int $teamId
     * @return void
     *
     * @since 1.5.0
     */
    public function emitUserTeamUnassigned(int $userId, int $teamId): void
    {
        $data = [
            'user_id' => $userId,
            'team_id' => $teamId,
            'entity_type' => 'team',
            'entity_id' => 'team_' . $teamId,
        ];

        hook_invoke_all('user_team_unassigned', $data);
    }
}
