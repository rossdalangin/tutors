# EdupreneurPro - The Complete Educator's Business OS

Welcome to **EdupreneurPro**. This guide is your step-by-step roadmap to transforming your knowledge into a full-scale, profitable education business on WordPress.

---

## 🏁 Step 1: Getting Started (The 15-Minute Launch)

Upon activating the plugin, your first destination should be the **Setup Wizard**.

1.  **Navigate to:** `EdupreneurPro > Setup Wizard` in your WordPress sidebar.
2.  **Basic Info:** Set your Academy Name. This is what students will see in emails and on their dashboards.
3.  **Payment Setup:** Enter your Stripe Secret Key or PayPal Business Email. You can skip this during testing and use the "Authorize & Pay" simulation.
4.  **Finish:** Click "Load Sample Data & Finish" to instantly populate your site with a professional-looking demo course, lessons, and community posts. This is the best way to learn how the system works.

---

## 🏗️ Step 2: Designing Your Curriculum (The Course Builder)

EdupreneurPro uses a hierarchical structure: **Course → Module → Lesson → Resource**.

1.  **Manage Courses:** Go to `EdupreneurPro > Manage Courses`.
2.  **Add a Course:** Click "+ New Course". Provide a title and a description.
3.  **Pricing:** Choose between a "One-Time" purchase or a "Subscription". If subscription, select the billing period (Monthly/Yearly).
4.  **Modules:** Inside your course, click "+ Add Module" to group your content (e.g., "Phase 1: Foundations").
5.  **Lessons:** Inside a module, click "+ Add a Lesson".
    -   **Video Lessons:** Paste a YouTube or Vimeo link.
    -   **Interactive Quizzes:** Use the Quiz builder to add multiple-choice questions.
    -   **Assignments:** Provide instructions for students to complete.
    -   **Live Sessions:** Set the lesson type to "Live" and provide a Zoom/Meet link. Students will get a "Join" button and an "Add to Google Calendar" link.
6.  **Reordering:** Simply drag and drop modules or lessons to change the flow.

---

## 💳 Step 3: Monetization & The Checkout Flow

Once your courses are ready, you need a way to sell them.

1.  **Landing Page:** Create a new page and add the `[edu_homepage]` shortcode. This is your "Storefront".
2.  **Checkout Page:** Create a page titled "Checkout" and add the `[edu_checkout]` shortcode. Ensure your courses link to this page.
3.  **Coupons:** Go to `EdupreneurPro > Coupons` to create discount codes (e.g., "SAVE50" for 50% off). Students can apply these on the checkout page.
4.  **Subscription Management:** View active recurring plans under `EdupreneurPro > Business Dashboard` insights.

---

## 🤝 Step 4: Scaling with Affiliates

Let other people sell your courses for you.

1.  **Affiliate Registration:** Students can click "Become an Affiliate" on their dashboard.
2.  **Promo Assets:** Go to `EdupreneurPro > Manage Assets` to upload banners and write email swipes. Your affiliates will see these in their dashboard to help them promote.
3.  **Commission Management:** Set a default commission rate in Settings.
4.  **Payouts:** When an affiliate reaches your payout threshold, they can request a withdrawal. You approve these under `EdupreneurPro > Payouts`.

---

## 🗣️ Step 5: Community & Student Engagement

Education is better together.

1.  **Community Board:** Add the `[edu_student_community]` logic to a page. Students can post updates, ask questions, and interact.
2.  **Private Messaging:** Use the `[edu_messages]` shortcode to provide a secure inbox. Students can message you (the tutor) or each other.
3.  **Member Directory:** Add `[edu_directory]` to let students find their peers and start study groups.
4.  **Moderation:** You can pin important announcements or "Lock" threads that have been resolved under `EdupreneurPro > Community`.

---

## 📊 Step 6: Business Operations & Analytics

Stay on top of your growth with the **Tutor Business Dashboard**.

1.  **Insights:** View Gross vs. Net revenue, Average Order Value, and Student Completion Rates.
2.  **Audit Log:** Every major action (course deletion, price change) is tracked under `EdupreneurPro > Audit Log` for your security.
3.  **Data Portability:** Students can download their entire learning history as a JSON file via the "Privacy & Data" section on their dashboard (GDPR/CCPA compliant).
4.  **Tax Reporting:** Generate and print financial reports ready for your accountant with one click.

---

## 📱 Step 7: The Student Experience

How your students see your business:

1.  **Learning Dashboard (`[edu_student_dashboard]`):** Their central hub. They see their progress bars, enrolled courses, and "Continue Learning" shortcuts.
2.  **Lesson Player:** A distraction-free environment for watching videos, taking notes (auto-saved), and completing quizzes.
3.  **Certificates:** Once a student completes 100% of a course, they can download a high-quality, printable certificate with a unique verification token.
4.  **Mobile App Experience:** Since EdupreneurPro is **PWA-Ready**, students can "Add to Home Screen" on their phones to access your academy like a native app.

---

## 🔧 Shortcode Quick Reference

| Shortcode | Description |
| :--- | :--- |
| `[edu_homepage]` | Full landing page with Hero, Featured Courses, and Categories. |
| `[edu_student_dashboard]` | Central hub for enrolled students to manage their learning. |
| `[edu_checkout]` | The multi-step payment and enrollment system. |
| `[edu_recent_courses]` | Grid of latest courses with optional `category` filter. |
| `[edu_messages]` | Private direct messaging center and inbox. |
| `[edu_directory]` | Searchable list of all academy members. |
| `[edu_certificate]` | Renders a printable certificate (only for completed courses). |

---

## 💡 Best Practices for Tutors

-   **Sequential Learning:** We've built in "Drip Locking". Students must complete Lesson 1 before Lesson 2 unlocks. This ensures a logical learning path.
-   **Automated Emails:** Ensure your WordPress mail settings are working. The plugin automatically sends Welcome emails on enrollment and Congratulations emails on completion.
-   **Engage Often:** Pinned posts in the community are the best way to share weekly updates or "Office Hours" schedules.

**Congratulations!** You are now ready to operate a professional, scalable education business with EdupreneurPro.
