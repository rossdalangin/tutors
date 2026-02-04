# EdupreneurPro - Comprehensive User Manual

Welcome to EdupreneurPro, the all-in-one operating system for your education business. This manual provides a detailed guide on how to use the plugin, its shortcodes, and the business processes it supports.

---

## 🚀 Business Process Lifecycle

EdupreneurPro is designed to follow the natural flow of an education business:

### 1. Curriculum Architecture (Creation)
- **Categories:** Start by defining your niches (e.g., "Business Mastery", "Tech Skills").
- **Courses:** Create a course and assign it to a category. Set your price (0.00 for free).
- **Hierarchy:** Build your curriculum using the `Course -> Module -> Lesson` structure. Modules group related lessons, and Lessons contain the actual educational content (Video, PDF, Quizzes, etc.).

### 2. Marketing & Promotions
- **Assets:** Create promotional banners and email swipes for your affiliate partners.
- **Affiliates:** Recruit partners who will promote your courses for a commission. They get a unique referral code.
- **Shortcodes:** Use the built-in shortcodes to build high-converting landing pages.

### 3. Monetization & Enrollment (The Sale)
- **Checkout:** When a student clicks "Enroll," they are guided through a multi-step checkout:
    1. **Gateway Selection:** Student chooses Stripe, PayPal, or GCash.
    2. **Authorization:** Student is redirected to a (simulated) secure payment page.
    3. **Confirmation:** Upon success, the student is automatically enrolled and redirected to their course.

### 4. Learning & Engagement
- **Student Dashboard:** Students manage their progress, see completed lessons, and resume learning.
- **Lesson Player:** Delivers video content and resources.
- **Quizzes:** Interactive multiple-choice questions to test knowledge.
- **Community:** Each course can have a discussion board where students and tutors interact.

### 5. Business Analytics (Reporting)
- **Insights:** Use the Business Dashboard to track Gross Sales, Net Profit, and student completion rates.
- **Tax-Ready Reports:** View and print financial summaries for accounting and tax obligations.

---

## 🧩 Shortcode Reference

EdupreneurPro provides several shortcodes to display your content on any WordPress page or post.

### `[edu_homepage]`
The ultimate "All-in-One" landing page.
- **Details:** Renders a beautiful hero section, featured courses, "Why Join" section, and category browser.
- **Usage:** Create a new page titled "Home" and paste `[edu_homepage]`.

### `[edu_recent_courses]`
Displays a grid of your latest courses.
- **Parameters:**
    - `limit` (int): Number of courses to show. Default: `10`.
    - `category` (string): Filter by category slug. Default: empty (all categories).
- **Example:** `[edu_recent_courses limit="3" category="business"]`

### `[edu_course]`
Displays a single course card.
- **Parameters:**
    - `id` (int): The unique ID of the course. Required.
- **Example:** `[edu_course id="1"]`

### `[edu_categories]`
Displays a grid of all course categories.
- **Details:** Each card shows the category name, description, and a link to view courses in that niche.
- **Usage:** `[edu_categories]`

### `[edu_student_dashboard]`
The learner's central hub.
- **Details:** Shows enrolled courses, progress bars, and "Continue Learning" buttons.
- **Usage:** Recommended for a page named "My Account" or "Dashboard".

### `[edu_checkout]`
The multi-step payment and enrollment system.
- **Details:** Automatically handles the course selection from the URL and guides the user through payment.
- **Usage:** Create a page named "Checkout" and paste `[edu_checkout]`.

---

## 💡 Pro Tips for Success

- **Use Drip Content:** In the Lesson settings, set `Drip Release` to keep students engaged over time rather than overwhelming them on day one.
- **Leverage Affiliates:** Set competitive commission rates in the Affiliate settings to motivate partners to drive more traffic.
- **Interactive Quizzes:** Use the new Quiz Builder to add multiple-choice questions at the end of each module to ensure learning objectives are met.
- **Field Captions:** Notice the small italicized text below fields in the admin area? Those are "Field Captions" designed to help you understand exactly what information is needed for each feature.
