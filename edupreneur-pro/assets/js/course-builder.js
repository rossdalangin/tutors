(function($) {
    'use strict';

    const CourseBuilder = {
        init: function() {
            this.$container = $('#edu-course-builder-root');
            if (!this.$container.length) return;

            this.render();
            this.bindEvents();
            this.fetchCourses();
        },

        render: function() {
            this.$container.html(`
                <div class="edu-builder-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Your Curriculum Library</h2>
                    <button id="edu-add-course" class="edu-btn">+ Create New Course</button>
                </div>
                <p style="color: #646970;">Manage your educational content from here. You can create courses, and then add logical lessons to each.</p>
                <div id="edu-courses-list" class="edu-grid" style="margin-top: 20px;">
                    <p>Loading your courses...</p>
                </div>
                <div id="edu-builder-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
                    <div style="background:#fff; margin:5% auto; padding:30px; width:50%; border-radius:12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                        <h2 id="modal-title" style="margin-top:0;">Launch a New Course</h2>
                        <p style="color: #646970; margin-bottom: 20px;">Fill in the details below to start building your new educational offering.</p>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:600; margin-bottom:5px;">Course Title</label>
                            <input type="text" id="course-title" placeholder="e.g. Masterclass in Modern Physics" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display:block; font-weight:600; margin-bottom:5px;">Course Description</label>
                            <textarea id="course-desc" placeholder="Describe what students will achieve after completing this course..." style="width:100%; height:120px; padding:10px; border:1px solid #ddd; border-radius:6px;"></textarea>
                        </div>
                        <div style="display:flex; gap:10px; justify-content: flex-end;">
                            <button id="close-modal" class="button" style="padding: 10px 20px;">Cancel</button>
                            <button id="save-course" class="edu-btn" style="padding: 10px 20px;">Create Course</button>
                        </div>
                    </div>
                </div>
            `);
        },

        bindEvents: function() {
            $('#edu-add-course').on('click', () => $('#edu-builder-modal').show());
            $('#close-modal').on('click', () => $('#edu-builder-modal').hide());
            $('#save-course').on('click', () => this.saveCourse());
        },

        fetchCourses: function() {
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/courses',
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: (data) => {
                    this.renderCourses(data);
                }
            });
        },

        renderCourses: function(courses) {
            const $list = $('#edu-courses-list');
            if (!courses.length) {
                $list.html('<div class="edu-card" style="grid-column: 1/-1; text-align:center;"><p>No courses found yet. Click "Create New Course" above to begin your journey!</p></div>');
                return;
            }

            $list.empty();
            courses.forEach(course => {
                $list.append(`
                    <div class="edu-card">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <h3 style="margin:0;">${course.title}</h3>
                            <span class="status-tag" style="background:#e5f5fa; color:#007396; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600;">ACTIVE</span>
                        </div>
                        <p style="color:#646970; font-size:14px; margin:15px 0;">${course.description || 'No description provided.'}</p>
                        <div style="margin-top:20px; padding-top:15px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:12px; color:#a7aaad;">Course ID: #${course.id}</span>
                            <button class="edu-btn edu-add-lesson" data-id="${course.id}" style="font-size:13px; padding:6px 12px;">+ Add Lesson</button>
                        </div>
                    </div>
                `);
            });

            $('.edu-add-lesson').on('click', (e) => {
                const id = $(e.currentTarget).data('id');
                const title = prompt('Enter the title for your new lesson:');
                if (title) this.addLesson(id, title);
            });
        },

        saveCourse: function() {
            const title = $('#course-title').val();
            const desc = $('#course-desc').val();

            if (!title) return alert('Please enter a course title.');

            $.ajax({
                url: eduApi.root + 'edupreneur/v1/courses',
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                data: { title: title, description: desc },
                success: () => {
                    $('#edu-builder-modal').hide();
                    $('#course-title').val('');
                    $('#course-desc').val('');
                    this.fetchCourses();
                },
                error: (err) => alert('Failed to create course. Please try again.')
            });
        },

        addLesson: function(courseId, title) {
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/lessons',
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                data: { course_id: courseId, title: title },
                success: () => alert('Great! Your lesson has been added to the course.'),
                error: (err) => alert('Failed to add lesson. Ensure you have the correct permissions.')
            });
        }
    };

    $(document).ready(() => CourseBuilder.init());

})(jQuery);
