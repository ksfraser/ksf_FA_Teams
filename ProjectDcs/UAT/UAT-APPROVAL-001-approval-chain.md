# UAT-APPROVAL-001 - Approval Chain UAT Plan

## User Acceptance Testing

**Module**: Teams
**BR**: BR-APPROVAL-001
**Tester**: Manager / Employee / System Admin

---

## Test Scenarios

### UAT-APPROVAL-001-TC01: Multi-Level Approval Chain

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Admin defines chain: Step 1=PM, Step 2=Dept Head | Chain configured |
| 2 | Employee submits expense $500 | Step 1 pending (PM) |
| 3 | PM approves | Step 2 pending (Dept Head) |
| 4 | Dept Head approves | Status = approved |

**Pass Criteria**: Multi-level chain works correctly

---

### UAT-APPROVAL-001-TC02: Timeout Escalation

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Chain configured: PM timeout 1 hour, escalate to Dept Head | Chain configured |
| 2 | Employee submits expense | PM receives |
| 3 | Wait 1 hour (or simulate) | Escalated to Dept Head |
| 4 | Verify escalation notification | Email sent to Dept Head |

**Pass Criteria**: Timeout escalation works

---

### UAT-APPROVAL-001-TC03: Delegation

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Manager A delegates to Manager B | Delegation created |
| 2 | Expense submitted for Manager A approval | - |
| 3 | Manager B sees pending approval | Delegation honored |
| 4 | Manager B approves | Approved on behalf of A |

**Pass Criteria**: Delegation works both ways

---

### UAT-APPROVAL-001-TC04: Org Chart Integration

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit timesheet | - |
| 2 | System queries orgchart_get_manager | Returns direct manager |
| 3 | Manager approves | - |
| 4 | Verify correct manager notified | Correct person notified |

**Pass Criteria**: Org chart integration correct

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| System Admin | | | |
| Manager | | | |
| Employee | | | |
