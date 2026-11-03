# Appendix: System Testing

The following test cases were derived from the library system's routes, components, and existing feature tests. They are a proposed manual system-testing checklist, not a record of completed testing. **Actual result** is “Not executed” and **Status** is “Pending” until a tester records an observation. Replace those entries with the observed behavior and Pass or Fail after execution; record defects separately. Test codes are identifiers for these cases, not source-code filenames. For a combined case, test every scenario in its description and mark it Pass only when all scenarios pass; describe any failed scenario in the actual result.

Use an isolated test environment with an administrator, authorized and restricted librarians, verified and unverified student accounts, available and borrowed copies, reservations, and sample import files. Configure email, SMS, queues, and recommendation services as needed; simulated deliveries do not prove real-world delivery. Do not use production records for destructive cases.

## Authentication and account recovery

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| AUTH-01 | Student and employee login with valid credentials | Not executed | Pending |
| AUTH-02 | Login with invalid password | Not executed | Pending |
| AUTH-04 | Guest access to protected pages | Not executed | Pending |
| AUTH-05 | Logout | Not executed | Pending |
| AUTH-06 | Registration required information | Not executed | Pending |
| AUTH-07 | Email and SMS contact verification, including incorrect codes | Not executed | Pending |
| AUTH-09 | Email and SMS password recovery, including code verification and unverified phone restrictions | Not executed | Pending |
| AUTH-13 | Google email mismatch | Not executed | Pending |
| AUTH-14 | Password change | Not executed | Pending |

## Public catalog and search

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| OPAC-01 | Open catalog | Not executed | Pending |
| OPAC-02 | Catalog title search, advanced search, suggestions, and no matching results | Not executed | Pending |
| OPAC-05 | View book details and periodical issues | Not executed | Pending |
| OPAC-08 | Recommendations and handling recommendation service failure | Not executed | Pending |
| OPAC-10 | Online resource access | Not executed | Pending |

## Catalog and copy management

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| CAT-01 | Open catalog management | Not executed | Pending |
| CAT-02 | Add a book | Not executed | Pending |
| CAT-05 | Add a periodical issue | Not executed | Pending |
| CAT-08 | Add physical item | Not executed | Pending |
| CAT-03 | Validate required catalog fields and preserve entered data | Not executed | Pending |
| CAT-07 | Duplicate issue | Not executed | Pending |
| CAT-09 | Change catalog type | Not executed | Pending |
| CAT-10 | Edit book metadata and copy information | Not executed | Pending |
| CAT-12 | Confirm or cancel catalog deletion and verify other issues remain | Not executed | Pending |
| CAT-15 | Spreadsheet import and duplicate prevention | Not executed | Pending |
| CAT-17 | MARC import | Not executed | Pending |
| CAT-18 | Catalog filters | Not executed | Pending |

## Circulation and returns

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| CIR-01 | Open checkout | Not executed | Pending |
| CIR-02 | Identify student | Not executed | Pending |
| CIR-03 | Add and remove an available copy; reject unavailable copies | Not executed | Pending |
| CIR-06 | Complete checkout with a permitted due date | Not executed | Pending |
| CIR-08 | Reject an out-of-range due date | Not executed | Pending |
| CIR-09 | Enforce periodical loan limit | Not executed | Pending |
| CIR-10 | Active loan consistency | Not executed | Pending |
| CIR-11 | Reference item use | Not executed | Pending |
| CIR-12 | Confirm return and verify circulation history | Not executed | Pending |
| CIR-14 | Report and resolve a lost copy | Not executed | Pending |
| CIR-16 | Lost case blocks checkout | Not executed | Pending |
| CIR-17 | Borrowed books list | Not executed | Pending |

## Reservations and waitlist

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| RES-01 | Reserve an eligible item | Not executed | Pending |
| RES-02 | Reference-only reservation | Not executed | Pending |
| RES-03 | Staff view reservations | Not executed | Pending |
| RES-04 | Approve valid reservations and reject expired pending reservations | Not executed | Pending |
| RES-06 | Load approved pickups and reject unapproved pickup links | Not executed | Pending |
| RES-08 | Join waitlist and reject duplicate entries | Not executed | Pending |
| RES-10 | Blocked account cannot join waitlist | Not executed | Pending |
| RES-11 | Allocate released copies in order, skipping blocked students | Not executed | Pending |
| RES-13 | Expired reservation reallocation | Not executed | Pending |
| RES-14 | Lost reserved copy | Not executed | Pending |
| RES-15 | Reservation receipt privacy | Not executed | Pending |

## Fines and payments

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| FINE-01 | Calculate overdue fines using configured and item-specific rates | Not executed | Pending |
| FINE-03 | Record payment and prevent duplicate payment | Not executed | Pending |
| FINE-05 | Reject payment for the wrong student | Not executed | Pending |
| FINE-06 | Student QR lookup | Not executed | Pending |
| FINE-07 | Public fine lookup | Not executed | Pending |

## User management and approvals

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| USER-01 | Open user management | Not executed | Pending |
| USER-02 | Search and filter users | Not executed | Pending |
| USER-03 | Add and edit a student record | Not executed | Pending |
| USER-05 | Review applications, skip missing documents, and protect completed reviews | Not executed | Pending |
| USER-08 | Import students and verify cancellation | Not executed | Pending |
| USER-10 | Restricted student import | Not executed | Pending |
| USER-11 | Invited student setup | Not executed | Pending |
| USER-12 | Duplicate setup email | Not executed | Pending |
| USER-13 | Librarian permissions | Not executed | Pending |

## Dashboard, reports, settings, and audit

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| SYS-01 | Staff dashboard | Not executed | Pending |
| SYS-02 | View analytics | Not executed | Pending |
| SYS-03 | View AI analytics and handle missing metrics | Not executed | Pending |
| SYS-05 | Audit trail | Not executed | Pending |
| SYS-06 | Save, validate, and reset library settings | Not executed | Pending |
| SYS-09 | Access to settings | Not executed | Pending |

## Notifications and student mobile API

| Test code | Test description | Actual result | Status |
|---|---|---|---|
| NOTIF-01 | Send due reminders for eligible loans and exclude returned loans | Not executed | Pending |
| NOTIF-03 | Staff reservation notification | Not executed | Pending |
| NOTIF-04 | Notification privacy and history | Not executed | Pending |
| MOB-01 | Mobile login with valid and invalid credentials | Not executed | Pending |
| MOB-03 | Mobile dashboard access with valid and incomplete-setup credentials | Not executed | Pending |
| MOB-04 | Reject unauthenticated and revoked mobile tokens | Not executed | Pending |

## Source traceability

The cases were informed by `routes/web.php`, `routes/api.php`, the Livewire pages under `app/Livewire/Pages`, and feature tests under `tests/Feature`. Particularly relevant test files include `SystemModuleWorkflowTest.php`, `Circulation/CheckOutTest.php`, `Circulation/CheckInTest.php`, `ReservationWaitlistTest.php`, `FinePaymentTest.php`, `PeriodicalIssueTest.php`, `StudentImportTest.php`, `ContactRecoveryTest.php`, and `StudentMobileApiTest.php`. Existing automated test names and assertions inform expected behavior; they are not evidence that these manual cases passed.
