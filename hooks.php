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
// Per AGENTS.md standard; safe non-fatal load (try/catch in ensure_composer_dependencies).
$composerDepsPath = dirname(__DIR__) . '/ksf_FA_Common/src/Utils/ComposerDependencies.php';
if (file_exists($composerDepsPath)) {
    require_once $composerDepsPath;
    \\ksfraser\\FrontAccounting\\Common\\Utils\\ComposerDependencies::ensure(__DIR__);
}

class hooks_ksf_FA_Teams extends hooks {
    var $module_name = 'ksf_FA_Teams';
    var $version = '1.0.0';

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
        // Override in modules that add apps
        // return new ksf_FA_Teams_app();
    }

    /**
     * Add menu items to existing FA applications
     * 
     * @param application $app FA application instance
     */
    function install_options($app) {
        // Override in modules that add menu items
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
    function deactivate_extension($company, $check_only=true) {
        return true;
    }


    /**
     * Install composer dependencies if needed
     */
    private function ensure_composer_dependencies() {
        $module_dir = dirname(__FILE__);
        $autoload_path = $module_dir . '/vendor/autoload.php';
        
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
            return;
        }
        
        $composer_path = $module_dir . '/composer.json';
        if (!file_exists($composer_path)) {
            return true; // Safe: no dependencies required
        }
        
        try {
            chdir($module_dir);
            $output = array();
            $return_code = 0;
            exec('composer install --no-interaction --prefer-dist 2>&1', $output, $return_code);
            if ($return_code !== 0) {
                error_log('KSF Teams: composer install failed (non-fatal): ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            error_log('KSF Teams: composer install exception (non-fatal): ' . $e->getMessage());
        }
        return true; // Never fail activation
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

    // -------------------------------------------------------------------------
    // Inter-Module Hook Events — called by hook_invoke_first / hook_invoke_all
    // -------------------------------------------------------------------------

    function respondToCapabilityRequest(&$data, $opts = null) {
        $request = isset($opts['request']) ? $opts['request'] : (isset($data['request']) ? $data['request'] : 'capabilities');
        $data['request'] = $request;
        $data['module'] = $this->module_name;
        switch ($request) {
            case 'capabilities':
                return $this->getModuleCapabilities($data, $opts);
            case 'constants':
                return $this->getModuleConstants($data, $opts);
            case 'has:team_management':
            case 'has:team_crud':
                return $this->hasCapability($data, array('capability' => 'team_management'));
            default:
                $data['error'] = 'Unknown request: ' . $request;
                return null;
        }
    }

    /**
     * Team event dispatcher — listens to team lifecycle events from Project module.
     */
    function hook_invoke_all($hook, &$data) {
        switch ($hook) {
            case 'team_created':
            case 'team_updated':
            case 'team_deleted':
            case 'user_team_assigned':
            case 'user_team_unassigned':
                // These events are emitted by this module (not consumed here)
                return null;
            case 'user_provisioned':
            case 'user_updated':
            case 'user_deactivated':
                // Consume RBAC user events for team synchronization
                $this->handleUserEvent($hook, $data);
                return null;
            case 'project_template_applied':
                // Consume Project module event; create team reference if needed
                $this->handleProjectTemplateApplied($data);
                return null;
            default:
                return null;
        }
    }

    private function handleUserEvent(string $hook, array &$data): void {
        $entityType = $data['entity_type'] ?? 'user';
        $entityId = $data['entity_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        // Team module can respond to user events (e.g., sync team membership)
        // No-op by default; override for team synchronization logic
    }

    private function handleProjectTemplateApplied(array &$data): void {
        $templateId = $data['template_id'] ?? null;
        $projectId = $data['project_id'] ?? null;
        $teamName = $data['project_name'] ?? ($data['team_name'] ?? 'Project Team');
        // Team module can create default team for new project
        // No-op by default; override if team auto-creation needed
    }
\n    public function emitTeamCreated(int $teamId, string $teamName, string $teamEmail = ''): void
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
