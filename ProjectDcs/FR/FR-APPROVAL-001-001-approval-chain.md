# FR-APPROVAL-001-001 - Approval Chain Engine

## Functional Requirement

**Module**: Teams
**Priority**: P0 - Critical
**Status**: Proposed
**Integration**: Hook-based

### Description

Configurable multi-level approval chains with delegation, timeout escalation, and org chart integration.

### Acceptance Criteria

| ID | Criteria | Hook |
|----|----------|------|
| AC-001 | Define approval chain for document type | - |
| AC-002 | Request approval (initiate chain) | Emit: approval_request |
| AC-003 | Get next approver (org chart resolution) | Query: approval_get_next_approver |
| AC-004 | Check delegation | Query: approval_check_delegation |
| AC-005 | Approve document | Emit: approval_approve |
| AC-006 | Reject document | Emit: approval_reject |
| AC-007 | Timeout escalation | Emit: approval_escalate |
| AC-008 | Delegate approval authority | Emit: approval_delegate |

### Hooks

```php
// Emit: Request approval
$result = hook_invoke_all('approval_request', [
    'document_type' => 'expense_report',
    'document_id' => $reportId,
    'submitter_id' => $userId,
    'amount' => 1500.00,
    'project_id' => $projectId,
]);
// Returns: ['chain_id' => 123, 'current_step' => 1, 'current_approver' => $approverId]

// Query: Get next approver
$result = hook_invoke_first('approval_get_next_approver', [
    'chain_id' => $chainId,
]);
// Returns: ['approver_id' => $id, 'name' => $name, 'email' => $email, 'delegated' => false]

// Query: Check delegation
$result = hook_invoke_first('approval_check_delegation', [
    'delegator_id' => $originalApproverId,
    'document_type' => 'expense_report',
    'date' => '2026-09-07',
]);
// Returns: ['delegated' => true, 'delegate_id' => $delegateId, 'delegator_name' => $name]

// Query: Can user approve?
$result = hook_invoke_first('approval_can_approve', [
    'chain_id' => $chainId,
    'user_id' => $userId,
]);
// Returns: ['can_approve' => true] or ['can_approve' => false, 'reason' => 'Not your turn']

// Emit: Approve
hook_invoke_all('approval_approve', [
    'chain_id' => $chainId,
    'step' => 1,
    'approver_id' => $approverId,
    'comments' => 'Approved',
]);

// Emit: Reject
hook_invoke_all('approval_reject', [
    'chain_id' => $chainId,
    'step' => 1,
    'rejector_id' => $approverId,
    'reason' => 'Missing receipts',
]);

// Emit: Escalate (timeout)
hook_invoke_all('approval_escalate', [
    'chain_id' => $chainId,
    'step' => 1,
    'from_approver_id' => $originalApproverId,
    'to_approver_id' => $escalatedApproverId,
    'reason' => 'timeout',
    'timeout_hours' => 48,
]);
```

### Org Chart Integration

```php
// Query: Get manager
$result = hook_invoke_first('orgchart_get_manager', [
    'user_id' => $userId,
]);
// Returns: ['manager_id' => $id, 'manager_name' => $name]

// Query: Get department head
$result = hook_invoke_first('orgchart_get_department_head', [
    'user_id' => $userId,
]);
// Returns: ['head_id' => $id, 'head_name' => $name]
```

### Approval Chain Resolution Order

1. Check delegation (if active, use delegate)
2. If `specific_user`, use that user
3. If `role`, find any user with that RBAC role
4. If `project_manager`, get from project owner
5. If `submitter_manager`, get from org chart
6. If `department_manager`, get from org chart
7. If `team_lead`, get from Teams module

### Dependencies

- FR-APPROVAL-001-002: Delegation rules
- FR-APPROVAL-001-003: Notification hooks
