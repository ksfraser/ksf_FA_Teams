# AGENTS.md - ksf_FA_Teams#

## Architecture Overview#

**FA Module** for Team Management - team creation, member assignments, and org chart integration.

### Core Principles#
- **SOLID**, **DRY**, **TDD**, **DI**, **SRP**#

## Repository Structure#

```
ksf_FA_Teams/
├── sql/#
│   ├── fa_teams.sql#
│   ├── fa_team_members.sql#
│   └── fa_team_projects.sql#
├── includes/#
│   ├── teams_db.inc#
│   ├── members_db.inc#
│   └── projects_db.inc#
├── pages/#
├── hooks.php#
├── composer.json#
└── ProjectDocs/#
```

## Dependencies#

- **ksf_FA_Teams_Core** (business logic)#
- **ksf_FA_HRM** (link to employees)#
- **ksf_FA_ProjectManagement** (link to projects)#
- **FrontAccounting 2.4+**#
