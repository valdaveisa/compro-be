# API Endpoints

This document documents the API endpoints implemented in this project (from `routes/api.php` and `routes/auth.php`). For each endpoint you will find:
- Method & path
- Authentication required
- Request fields / validation
- Example request (JSON or form-data where relevant)
- Example success response and status code
- Possible error responses (when implemented)

---

**Auth — API**

- **POST**: `/api/register`
  - **Auth**: No
  - **Request validation**:
    - `name` (required, string)
    - `username` (required, string, unique)
    - `email` (required, email, unique)
    - `password` (required, string, min:8, confirmed)
  - **Example request (JSON)**:
```json
{
  "name": "Alice",
  "username": "alice01",
  "email": "alice@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```
  - **Success response (201)**
```json
{
  "user": {
    "id": 1,
    "name": "Alice",
    "username": "alice01",
    "email": "alice@example.com",
    // ...other user fields
  },
  "token": "plain-text-personal-access-token"
}
```

- **POST**: `/api/login`
  - **Auth**: No
  - **Request validation**:
    - `email` (required, email)
    - `password` (required)
  - **Example request (JSON)**:
```json
{
  "email": "alice@example.com",
  "password": "secret123"
}
```
  - **Success response (200)**
```json
{
  "user": { /* user object */ },
  "token": "plain-text-personal-access-token"
}
```
  - **Failure**: 401 with
```json
{ "message": "Email atau password salah." }
```

---

**Protected: User**

- **GET**: `/api/me`
  - **Auth**: Bearer token (Sanctum)
  - **Request**: none
  - **Response (200)**: current authenticated user object (JSON)

---

**Projects**

- **GET**: `/api/projects`
  - **Auth**: Yes
  - **Request**: query args optional
  - **Response (200)**: array of projects. Each project includes counts `total_tasks` and `done_tasks`.

- **POST**: `/api/projects`
  - **Auth**: Yes
  - **Validation**:
    - `name` (required, string)
    - `description` (nullable, string)
    - `start_date` (nullable, date)
    - `end_date` (nullable, date, after_or_equal:start_date)
  - **Example request**:
```json
{
  "name": "Website Redesign",
  "description": "Redesign homepage",
  "start_date": "2025-11-01",
  "end_date": "2025-12-01"
}
```
  - **Success (201)**: created project object. Creator is attached as project member with role `pm`.

- **GET**: `/api/projects/{project}`
  - **Auth**: Yes
  - **Response (200)**: project object with `members` and `tasks` relations loaded.

- **PUT**: `/api/projects/{project}`
  - **Auth**: Yes (only `created_by` allowed to update)
  - **Validation**:
    - `name` (sometimes|required|string)
    - `description` (nullable|string)
    - `start_date` (nullable|date)
    - `end_date` (nullable|date|after_or_equal:start_date)
    - `status` (nullable, in: planned, active, on_hold, done)
  - **Response (200)**: updated project object
  - **Failure**: 403 when non-creator attempts update

- **DELETE**: `/api/projects/{project}`
  - **Auth**: Yes (only `created_by` can delete)
  - **Response (200)**:
```json
{ "message": "Project deleted" }
```
  - **Failure**: 403 when non-creator attempts delete

- **POST**: `/api/projects/{project}/members`
  - **Auth**: Yes (only `created_by` can manage members)
  - **Validation**:
    - `user_id` (required, exists:users,id)
    - `role_in_project` (required, in: pm,member,qa,writer)
  - **Response (200)**:
```json
{ "message": "Member added/updated" }
```

- **DELETE**: `/api/projects/{project}/members/{user}`
  - **Auth**: Yes (only `created_by`)
  - **Response (200)**:
```json
{ "message": "Member removed" }
```

- **GET**: `/api/projects/{project}/kanban`
  - **Auth**: Yes
  - **Response (200)**: grouped tasks by status with keys `todo`, `in_progress`, `review`, `done`. Each key contains array of task objects.

- **GET**: `/api/projects/{project}/calendar?start=YYYY-MM-DD&end=YYYY-MM-DD`
  - **Auth**: Yes
  - **Query params**: `start` and `end` optional; defaults to current month.
  - **Response (200)**: array of event objects with fields: `id`, `title`, `date`, `status`, `priority`, `type` (== 'task').

- **GET**: `/api/projects/{project}/gantt`
  - **Auth**: Yes
  - **Response (200)**: array of items with `id`, `title`, `start`, `end`, `status`, and optional `assignee` object `{id,name}`.

- **GET**: `/api/projects/{project}/stats`
  - **Auth**: Yes
  - **Response (200)**:
```json
{
  "total_tasks": 10,
  "done_tasks": 3,
  "completion_rate": 30.0,
  "overdue_tasks": 2,
  "tasks_by_status": { "todo": 4, "in_progress": 1, "review": 2, "done": 3 }
}
```

- **GET**: `/api/projects/{project}/member-performance`
  - **Auth**: Yes
  - **Response (200)**: array of member performance objects, each containing `user` (id,name,email), `tasks_assigned`, `tasks_done`, `done_ratio`, `avg_completion_days`.

---

**Tasks**

- **GET**: `/api/projects/{project}/tasks`
  - **Auth**: Yes
  - **Response (200)**: array of tasks with relations `assignee`, `labels`, `subtasks`.

- **POST**: `/api/projects/{project}/tasks`
  - **Auth**: Yes
  - **Validation**:
    - `title` (required, string)
    - `description` (nullable, string)
    - `status` (nullable, in: todo,in_progress,review,done)
    - `priority` (nullable, in: low,medium,high)
    - `start_date` (nullable, date)
    - `due_date` (nullable, date, after_or_equal:start_date)
    - `assignee_id` (nullable, exists:users,id)
    - `parent_task_id` (nullable, exists:tasks,id)
  - **Example request**:
```json
{
  "title": "Implement login",
  "description": "Add API login endpoint integration",
  "due_date": "2025-11-30",
  "assignee_id": 2
}
```
  - **Success (201)**: created task object

- **GET**: `/api/tasks/{task}`
  - **Auth**: Yes
  - **Response (200)**: task object with `project`, `assignee`, `labels`, `subtasks` loaded.

- **PUT**: `/api/tasks/{task}`
  - **Auth**: Yes
  - **Validation**: same as POST (title required)
  - **Response (200)**: updated task object

- **DELETE**: `/api/tasks/{task}`
  - **Auth**: Yes
  - **Response (200)**: `{ "message": "Task deleted" }`

- **PATCH**: `/api/tasks/{task}/status`
  - **Auth**: Yes
  - **Validation**:
    - `status` (required, in: todo,in_progress,review,done)
  - **Behavior**: if status becomes `done` and `completed_at` was null, sets `completed_at` to `now()`; if status not `done` clears `completed_at`.
  - **Response (200)**: updated task object

- **PATCH**: `/api/tasks/{task}/assign`
  - **Auth**: Yes
  - **Validation**:
    - `assignee_id` (required, exists:users,id)
  - **Response (200)**: updated task object with new `assignee_id`.

- **POST**: `/api/tasks/{task}/labels`
  - **Auth**: Yes
  - **Validation**:
    - `label_id` (required, exists:labels,id)
  - **Response (200)**: `{ "message": "Label attached" }`

- **DELETE**: `/api/tasks/{task}/labels/{label}`
  - **Auth**: Yes
  - **Response (200)**: `{ "message": "Label detached" }`

---

**Labels**

- **GET**: `/api/labels`
  - **Auth**: Yes
  - **Response (200)**: array of label objects

- **POST**: `/api/labels`
  - **Auth**: Yes
  - **Validation**:
    - `name` (required, string)
    - `color` (nullable, string)
  - **Success (201)**: created label object

- **PUT**: `/api/labels/{label}`
  - **Auth**: Yes
  - **Validation**:
    - `name` (sometimes|required|string)
    - `color` (nullable|string)
  - **Response (200)**: updated label object

- **DELETE**: `/api/labels/{label}`
  - **Auth**: Yes
  - **Response (200)**: `{ "message": "Label deleted" }`

---

**Comments**

- **GET**: `/api/tasks/{task}/comments`
  - **Auth**: Yes
  - **Response (200)**: list of comments with `user` relation (id, name, email)

- **POST**: `/api/tasks/{task}/comments`
  - **Auth**: Yes
  - **Validation**:
    - `content` (required, string)
  - **Behavior**: creates `Comment`, loads `user` and `task.project`, calls mention handler to create `UserNotification` for mentioned `@username` values.
  - **Response (201)**: created comment with `user` and `username` fields

- **PUT**: `/api/comments/{comment}`
  - **Auth**: Yes (only owner can edit)
  - **Validation**:
    - `content` (required, string)
  - **Response (200)**: updated comment with `user` relation
  - **Failure**: 403 if not owner

- **DELETE**: `/api/comments/{comment}`
  - **Auth**: Yes (only owner)
  - **Response (200)**: `{ "message": "Comment deleted" }`
  - **Failure**: 403 if not owner

---

**Attachments**

- **GET**: `/api/tasks/{task}/attachments`
  - **Auth**: Yes
  - **Response (200)**: array of attachments with `user` relation

- **POST**: `/api/tasks/{task}/attachments`
  - **Auth**: Yes
  - **Request**: multipart/form-data
    - `file` (required, file, max: 5120 KB)
  - **Success (201)**: created attachment object with fields like `id`, `task_id`, `user_id`, `filename`, `path`, `mime_type`, `size`

- **DELETE**: `/api/attachments/{attachment}`
  - **Auth**: Yes
  - **Behavior**: deletes file from storage and removes DB record
  - **Response (200)**: `{ "message": "Attachment deleted" }`

---

**Time Entries / Time Tracking**

- **GET**: `/api/tasks/{task}/time-entries`
  - **Auth**: Yes
  - **Response (200)**: array of time entries with `user` relation, ordered newest first

- **POST**: `/api/tasks/{task}/time-entries` (manual entry)
  - **Auth**: Yes
  - **Validation**:
    - `started_at` (required, date)
    - `ended_at` (required, date, after:started_at)
    - `note` (nullable, string)
  - **Behavior**: calculates `duration_minutes` from start/end
  - **Success (201)**: created entry with loaded `user`

- **POST**: `/api/tasks/{task}/time-entries/start`
  - **Auth**: Yes
  - **Request body**: optional `note`
  - **Behavior**: fails (422) if user has another running timer. Otherwise creates entry with `started_at = now()` and `ended_at = null`.
  - **Success (201)**: created entry object
  - **Failure (422)**:
```json
{ "message": "Masih ada timer yang berjalan. Stop dulu sebelum start baru." }
```

- **POST**: `/api/tasks/{task}/time-entries/stop`
  - **Auth**: Yes
  - **Behavior**: finds the latest running time entry for the authenticated user on the task, sets `ended_at = now()` and calculates `duration_minutes`.
  - **Failure (404)** if no running timer for this task/user:
```json
{ "message": "Tidak ada timer berjalan untuk task ini." }
```
  - **Success (200)**: updated entry with `user` relation

- **GET**: `/api/projects/{project}/time-report`
  - **Auth**: Yes
  - **Response (200)**: summary per user for the project, e.g.
```json
[{
  "user_id": 2,
  "total_minutes": 420,
  "user": { "id": 2, "name": "Bob", "email": "bob@example.com" }
}]
```

---

**Notifications**

- **GET**: `/api/notifications?only_unread=1`
  - **Auth**: Yes
  - **Query**: `only_unread` optional boolean; when true only unread are returned.
  - **Response (200)**: array of `UserNotification` objects

- **POST**: `/api/notifications/{notification}/read`
  - **Auth**: Yes (notification must belong to current user)
  - **Behavior**: sets `is_read = true` for the notification
  - **Response (200)**: `{ "message": "Marked as read" }`
  - **Failure**: 403 if notification doesn't belong to user

- **POST**: `/api/notifications/read-all`
  - **Auth**: Yes
  - **Behavior**: marks all unread notifications for user as read
  - **Response (200)**: `{ "message": "All notifications marked as read" }`

---

**Web Auth Routes (session-based)**

The project also contains the default web auth routes under `routes/auth.php` used for HTML/web flows. These endpoints accept/return HTTP status-only responses (no JSON body):

- **POST**: `/register` — registers and logs in the user (responds with 204 No Content on success)
- **POST**: `/login` — logs in (204 No Content)
- **POST**: `/logout` — logs out (204 No Content)
- **POST**: `/forgot-password`, `/reset-password`, `/email/verification-notification`, **GET** `/verify-email/{id}/{hash}` — standard Laravel auth flows

---

Notes & tips
- Authentication for the API uses Laravel Sanctum personal access tokens. Include `Authorization: Bearer <token>` header for protected endpoints.
- Validation errors return default Laravel validation responses (422 with `errors` object).
- Many responses return Eloquent models directly — fields may include timestamps and relation attributes. Example responses above are illustrative; check model fields (`app/Models`) for exact attributes.

If you want, I can:
- split this single file into per-resource markdown files (`docs/projects.md`, `docs/tasks.md`, etc.),
- add example curl commands for each endpoint,
- or generate an OpenAPI (Swagger) specification from these routes.

---

Generated from `routes/api.php` and controller source in `app/Http/Controllers`.