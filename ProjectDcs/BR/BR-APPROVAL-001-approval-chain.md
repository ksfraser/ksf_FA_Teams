# BR-APPROVAL-001 - Approval Chain Engine

## Business Requirement

**Module**: ksf_FA_Teams
**Status**: Proposed
**Integration**: Hook-based (hook_invoke_all)

### Problem Statement

Expense reports and timesheets need configurable approval chains:
- Project manager approval
- Department manager escalation
- Org chart-based routing
- Delegation when approver unavailable
- Timeout escalation

### Scope

#### In Scope
1. Approval chain definition (multi-level)
2. Org chart integration (Teams module)
3. Delegation rules (when manager away)
4. Timeout escalation
5. Notification hooks
6. Audit trail

#### Out of Scope
1. Financial approval limits (separate BR)
2. Mobile approvals (future)

### Hook Integration Points

```php
// Request approval from chain
$result = hook_invoke_all('approval_request', [
    'document_type' => 'expense_report|time_sheet|purchase_order',
    'document_id' => $documentId,
    'submitter_id' => $submitterId,
    'amount' => $amount,
    'project_id' => $projectId,
    'priority' => 'normal|high|urgent',
]);
// Returns: ['chain_id' => 123, 'current_approver' => $approverId, 'step' => 1]

// Approve document
hook_invoke_all('approval_approve', [
    'chain_id' => $chainId,
    'approver_id' => $approverId,
    'comments' => $comments,
    'step' => $step,
]);

// Reject document
hook_invoke_all('approval_reject', [
    'chain_id' => $chainId,
    'rejector_id' => $rejectorId,
    'reason' => $reason,
    'step' => $step,
]);

// Escalate (timeout)
hook_invoke_all('approval_escalate', [
    'chain_id' => $chainId,
    'from_approver_id' => $fromApproverId,
    'to_approver_id' => $toApproverId,
    'reason' => 'timeout',
    'timeout_hours' => $hours,
]);

// Delegate approval
hook_invoke_all('approval_delegate', [
    'delegator_id' => $delegatorId,
    'delegate_id' => $delegateId,
    'valid_from' => $startDate,
    'valid_until' => $endDate,
    'document_types' => ['expense_report', 'time_sheet'],
]);

// Get next approver for document
$result = hook_invoke_first('approval_get_next_approver', [
    'chain_id' => $chainId,
]);
// Returns: ['approver_id' => $id, 'name' => $name, 'email' => $email]

// Check if user can approve
$result = hook_invoke_first('approval_can_approve', [
    'chain_id' => $chainId,
    'user_id' => $userId,
]);
// Returns: ['can_approve' => true/false, 'reason' => '...']

// Notify approver
hook_invoke_all('approval_notify', [
    'notification_type' => 'pending|approved|rejected|escalated',
    'chain_id' => $chainId,
    'approver_id' => $approverId,
    'submitter_id' => $submitterId,
    'document_ref' => $ref,
]);
```

### Approval Chain Definition

```php
// Chain structure
$chain = [
    'id' => 1,
    'name' => 'Expense Approval',
    'document_type' => 'expense_report',
    'steps' => [
        [
            'step' => 1,
            'approver_type' => 'project_manager',
            'timeout_hours' => 48,
            'escalate_to' => 2, // step number
        ],
        [
            'step' => 2,
            'approver_type' => 'department_manager',
            'timeout_hours' => 72,
            'escalate_to' => null,
        ],
    ],
];
```

### Approver Types

| Type | Resolution |
|------|-----------|
| `submitter_manager` | Direct manager from org chart |
| `project_manager` | Project owner or PM assigned |
| `department_manager` | Department head |
| `team_lead` | Team lead from Teams module |
| `specific_user` | Fixed user ID |
| `role` | Any user with specific RBAC role |
| `delegation` | Delegated authority |

### Delegation Rules

```php
// Delegation active period
$delegation = [
    'delegator_id' => $managerId,
    'delegate_id' => $tempApproverId,
    'valid_from' => '2026-09-01',
    'valid_until' => '2026-09-15',
    'document_types' => ['expense_report', 'time_sheet'],
    'active' => true,
];

// Delegation check in approval chain
hook_invoke_first('approval_check_delegation', [
    'delegator_id' => $originalApproverId,
    'document_type' => $docType,
    'date' => $today,
]);
// Returns: ['delegated' => true, 'delegate_id' => $id] or ['delegated' => false]
```

### Org Chart Integration

```php
// Get manager for user
$result = hook_invoke_first('orgchart_get_manager', [
    'user_id' => $userId,
]);
// Returns: ['manager_id' => $id, 'manager_name' => $name]

// Get team members
$result = hook_invoke_all('orgchart_get_team', [
    'manager_id' => $managerId,
]);
// Returns: ['members' => [[id, name, role], ...]]

// Get department head
$result = hook_invoke_first('orgchart_get_department_head', [
    'user_id' => $userId,
]);
```

### Dependencies

- ksf_FA_Teams (org chart structure)
- ksf_FA_RBAC (role-based approvers)
- ksf_FA_ProjectManagement (project-based approval)
- ksf_FA_TravelExpense (expense approval)
- ksf_FA_Timesheets (timesheet approval)
- ksf_FA_EmailManager (notifications)
