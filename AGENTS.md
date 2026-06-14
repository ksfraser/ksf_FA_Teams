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

## Development Workflow

All development is done in the **devel tree** (`~/Documents/ksf_FA_Teams`). Do **not** edit files in the UAT bind point directly.

### Workflow Steps
1. **Develop** in this repo (feature branches preferred)
2. **Test**: run repo-appropriate tests
3. **Lint**: `php -l` on modified PHP files (no syntax errors)
4. **Commit** and **Push** branch to GitHub
5. **Merge** to `master` when ready
6. **Push** `master` to GitHub
7. **Deploy** to UAT by pulling in the Infrastructure bind point:

   ```
   cd ~/ksf_Infrastructure/fa_modules/ksf_FA_Teams
   git stash -u
   git pull origin master
   git stash pop
   ```

### UAT Bind Point
| Path | Purpose |
|------|---------|
| `~/Documents/ksf_FA_Teams` | Devel tree — all development, testing, commits |
| `~/ksf_Infrastructure/fa_modules/ksf_FA_Teams` | UAT bind point — deployment target, integration testing (if mirrored) |

