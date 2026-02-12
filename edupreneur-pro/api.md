# EdupreneurPro API Reference

The EdupreneurPro REST API allows you to interact with the platform programmatically. All endpoints are under the `edupreneur/v1` namespace.

## Authentication
Authentication is handled via standard WordPress nonces for AJAX requests or via Application Passwords for external integrations.

## Courses
`GET /courses` - List all courses.
`POST /courses` - Create a new course.
`PUT /courses/{id}` - Update a course.
`DELETE /courses/{id}` - Delete a course.

## Modules
`GET /modules?course_id={id}` - List modules for a course.
`POST /modules` - Create a new module.
`PUT /modules/{id}` - Update a module.
`DELETE /modules/{id}` - Delete a module.
`POST /modules/reorder` - Reorder modules.

## Lessons
`GET /lessons?module_id={id}` - List lessons for a module.
`POST /lessons` - Create a new lesson.
`PUT /lessons/{id}` - Update a lesson.
`DELETE /lessons/{id}` - Delete a lesson.
`POST /lessons/reorder` - Reorder lessons.

## Affiliates
`GET /affiliates/stats` - Get stats for the current affiliate.
`POST /affiliates/register` - Register current user as an affiliate.
`GET /affiliates` - List all affiliates (Admin).
`PUT /affiliates/{id}` - Update affiliate status/rate (Admin).
`DELETE /affiliates/{id}` - Delete an affiliate (Admin).

## Orders
`GET /orders` - List all orders (Admin).
`PUT /orders/{id}` - Update order status (Admin).
`DELETE /orders/{id}` - Delete an order (Admin).

## Community
`GET /community/posts?course_id={id}` - Get discussion posts for a course.
`POST /community/posts` - Create a new post.
`PUT /community/posts/{id}` - Update a post.
`DELETE /community/posts/{id}` - Delete a post.

### Private Messaging
`GET /community/messages?to={id}` - Get DM conversation with a user.
`POST /community/messages` - Send a direct message.

## System Tools
`POST /system/sample-data` - Seed the database with sample data.
`POST /system/reset` - Clear all custom plugin tables.
