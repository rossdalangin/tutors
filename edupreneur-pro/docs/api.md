# EdupreneurPro REST API Documentation

## Base URL
`https://yourdomain.com/wp-json/edupreneur/v1`

## Authentication
Authentication is handled via WordPress standard cookie authentication or Application Passwords.

## Endpoints

### Courses
- **GET** `/courses` - List all courses.
- **POST** `/courses` - Create a new course.
  - Params: `title`, `description`, `price`
- **GET** `/courses/{id}` - Get single course details.
- **PUT** `/courses/{id}` - Update a course.
- **DELETE** `/courses/{id}` - Delete a course.

### Lessons
- **POST** `/lessons` - Create a new lesson.
  - Params: `course_id`, `title`, `content`, `lesson_type`
- **GET** `/courses/{course_id}/lessons` - List lessons for a course.

### Webhooks (Payments)
- **POST** `/payments/webhook` - Standard endpoint for Stripe/PayPal webhooks.
