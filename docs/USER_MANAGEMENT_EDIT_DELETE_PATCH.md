# User Management Edit/Delete Patch

This patch upgrades BlueHR User Management from list/create only to an operational account management page.

Added:
- `/user-management/create`
- `/user-management/edit?id={id}`
- `/user-management/update`
- `/user-management/deactivate`
- `/user-management/delete`
- Role checkbox assignment
- Email duplicate validation
- Audit logs for create/update/deactivate/delete
- Protection against deleting/deactivating the current logged-in user

Notes:
- Deactivate is safer for operational use.
- Delete permanently removes the user row and related `user_roles` rows.
- Existing databases do not need a schema migration because this uses existing `users`, `roles`, and `user_roles` tables.
