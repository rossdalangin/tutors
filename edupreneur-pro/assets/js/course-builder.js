(function($) {
    'use strict';

    const CourseBuilder = {
        init: function() {
            this.$container = $('#edu-course-builder-root');
            if (!this.$container.length) return;

            this.render();
            this.bindEvents();
            this.fetchData();
        },

        render: function() {
            this.$container.html(`
                <div class="edu-builder-container">
                    <div class="edu-builder-sidebar">
                        <div class="edu-card">
                            <h3>Curriculum Navigator</h3>
                            <p class="edu-caption">Organize your Course → Module → Lesson flow here.</p>
                            <button id="edu-add-course" class="edu-btn edu-btn-block">+ New Course</button>
                        </div>
                        <div class="edu-guide-section" style="margin-top:20px;">
                            <h4>Pro Tip</h4>
                            <p>Drag and drop modules or lessons to reorder your curriculum instantly.</p>
                        </div>
                    </div>
                    <div class="edu-builder-main">
                        <div id="edu-courses-list" class="edu-builder-list">
                            <div class="edu-loading">Initialising your workspace...</div>
                        </div>
                    </div>
                </div>

                <!-- Course/Module/Lesson Modal -->
                <div id="edu-builder-modal" class="edu-modal" style="display:none;">
                    <div class="edu-modal-content">
                        <div class="edu-modal-header">
                            <h2 id="modal-title">Create Entity</h2>
                            <span class="edu-modal-close">&times;</span>
                        </div>
                        <div class="edu-modal-body">
                            <input type="hidden" id="entity-type">
                            <input type="hidden" id="parent-id">
                            <div class="edu-form-group">
                                <label>Title</label>
                                <input type="text" id="entity-title" placeholder="Enter title...">
                            </div>
                            <div class="edu-form-group" id="desc-group">
                                <label>Description</label>
                                <textarea id="entity-desc" placeholder="Describe the learning objective..."></textarea>
                            </div>
                            <div class="edu-form-group" id="lesson-settings" style="display:none;">
                                <label>Video URL (Vimeo/YouTube)</label>
                                <input type="text" id="lesson-video" placeholder="https://...">
                                <label style="margin-top:10px;">Drip Release (Days after enrollment)</label>
                                <input type="number" id="lesson-drip" value="0" min="0">
                            </div>
                        </div>
                        <div class="edu-modal-footer">
                            <button id="close-modal-btn" class="button">Cancel</button>
                            <button id="save-entity" class="edu-btn">Save Changes</button>
                        </div>
                    </div>
                </div>
            `);
        },

        bindEvents: function() {
            const self = this;
            $(document).on('click', '#edu-add-course', () => self.openModal('course'));
            $(document).on('click', '.edu-modal-close, #close-modal-btn', () => $('#edu-builder-modal').hide());
            $(document).on('click', '#save-entity', () => self.saveEntity());
            $(document).on('click', '.edu-add-module', (e) => self.openModal('module', $(e.currentTarget).data('course-id')));
            $(document).on('click', '.edu-add-lesson', (e) => self.openModal('lesson', $(e.currentTarget).data('module-id')));
        },

        initSortable: function() {
            const self = this;

            // Reorder Modules
            $('.edu-modules-list').sortable({
                handle: '.edu-drag-handle-mini',
                update: function(event, ui) {
                    const sortedIDs = $(this).sortable('toArray', { attribute: 'data-id' });
                    self.updateOrder('modules', sortedIDs);
                }
            });

            // Reorder Lessons
            $('.edu-lessons-list').sortable({
                handle: '.edu-drag-handle-mini',
                connectWith: '.edu-lessons-list',
                update: function(event, ui) {
                    const sortedIDs = $(this).sortable('toArray', { attribute: 'data-id' });
                    const moduleId = $(this).closest('.edu-module-box').data('id');
                    self.updateOrder('lessons', sortedIDs, moduleId);
                }
            });
        },

        updateOrder: function(type, ids, parentId = null) {
            console.log(`Reordering ${type}:`, ids, 'Parent:', parentId);

            let url = eduApi.root + 'edupreneur/v1/' + type + '/reorder';
            let data = { ids: ids };
            if (type === 'lessons') data.module_id = parentId;

            $.ajax({
                url: url,
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                data: data,
                success: () => console.log(`${type} reordered successfully`),
                error: (err) => console.error(`Error reordering ${type}`, err)
            });
        },

        openModal: function(type, parentId = 0) {
            $('#entity-type').val(type);
            $('#parent-id').val(parentId);
            $('#modal-title').text('New ' + type.charAt(0).toUpperCase() + type.slice(1));
            $('#entity-title, #entity-desc, #lesson-video').val('');

            if (type === 'lesson') {
                $('#lesson-settings').show();
                $('#desc-group').show();
            } else if (type === 'module') {
                $('#lesson-settings').hide();
                $('#desc-group').hide();
            } else {
                $('#lesson-settings').hide();
                $('#desc-group').show();
            }

            $('#edu-builder-modal').show();
        },

        fetchData: function() {
            const self = this;
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/courses',
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: (courses) => self.renderWorkspace(courses)
            });
        },

        renderWorkspace: function(courses) {
            const $list = $('#edu-courses-list');
            if (!courses.length) {
                $list.html('<div class="edu-card empty-state"><h3>Ready to start?</h3><p>Create your first course using the sidebar button.</p></div>');
                return;
            }

            $list.empty();
            courses.forEach(course => {
                const $courseRow = $(`
                    <div class="edu-course-container edu-card" data-id="${course.id}">
                        <div class="edu-course-header">
                            <div class="edu-drag-handle">⠿</div>
                            <h3>${course.title}</h3>
                            <div class="edu-actions">
                                <button class="edu-btn edu-btn-small edu-add-module" data-course-id="${course.id}">+ Add Module</button>
                            </div>
                        </div>
                        <div class="edu-modules-list" id="modules-for-${course.id}">
                            <div class="edu-loading-mini">Loading modules...</div>
                        </div>
                    </div>
                `);
                $list.append($courseRow);
                this.fetchModules(course.id);
            });
        },

        fetchModules: function(courseId) {
            const self = this;
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/modules?course_id=' + courseId,
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: (modules) => self.renderModules(courseId, modules)
            });
        },

        renderModules: function(courseId, modules) {
            const $container = $(`#modules-for-${courseId}`);
            $container.empty();

            if (!modules.length) {
                $container.append('<p class="edu-empty-msg">No modules yet. Modules group your lessons together.</p>');
                return;
            }

            modules.forEach(module => {
                const $moduleBox = $(`
                    <div class="edu-module-box" data-id="${module.id}">
                        <div class="edu-module-header">
                            <div class="edu-drag-handle-mini">⠿</div>
                            <h4>${module.title}</h4>
                            <button class="edu-btn-link edu-add-lesson" data-module-id="${module.id}">+ Add Lesson</button>
                        </div>
                        <div class="edu-lessons-list" id="lessons-for-${module.id}">
                            <!-- Lessons go here -->
                        </div>
                    </div>
                `);
                $container.append($moduleBox);
                this.fetchLessons(module.id, courseId);
            });

            setTimeout(() => this.initSortable(), 500);
        },

        fetchLessons: function(moduleId, courseId) {
            const self = this;
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/lessons?course_id=' + courseId + '&module_id=' + moduleId,
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: (lessons) => self.renderLessons(moduleId, lessons)
            });
        },

        renderLessons: function(moduleId, lessons) {
            const $container = $(`#lessons-for-${moduleId}`);
            $container.empty();

            lessons.forEach(lesson => {
                $container.append(`
                    <div class="edu-lesson-item" data-id="${lesson.id}">
                        <div class="edu-drag-handle-mini">⠿</div>
                        <span class="edu-lesson-icon">📄</span>
                        <span class="edu-lesson-title">${lesson.title}</span>
                        <span class="edu-lesson-type tag">${lesson.lesson_type || 'video'}</span>
                    </div>
                `);
            });

            this.initSortable();
        },

        saveEntity: function() {
            const self = this;
            const type = $('#entity-type').val();
            const parentId = $('#parent-id').val();
            const title = $('#entity-title').val();
            const desc = $('#entity-desc').val();

            if (!title) return alert('Title is required');

            let url = eduApi.root + 'edupreneur/v1/' + (type === 'course' ? 'courses' : (type === 'module' ? 'modules' : 'lessons'));
            let data = { title: title, description: desc };

            if (type === 'module') data.course_id = parentId;
            if (type === 'lesson') {
                data.module_id = parentId;
                data.video_url = $('#lesson-video').val();
                data.drip_days = $('#lesson-drip').val();
                data.course_id = $(`.edu-module-box[data-id="${parentId}"]`).closest('.edu-course-container').data('id');
            }

            $.ajax({
                url: url,
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                data: data,
                success: () => {
                    $('#edu-builder-modal').hide();
                    self.fetchData();
                },
                error: (err) => alert('Error saving: ' + err.responseJSON.message)
            });
        }
    };

    $(document).ready(() => CourseBuilder.init());

})(jQuery);
