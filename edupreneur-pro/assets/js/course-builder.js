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
                            <input type="hidden" id="entity-id">
                            <input type="hidden" id="parent-id">
                            <div class="edu-form-group">
                                <label>Title</label>
                                <input type="text" id="entity-title" placeholder="Enter title...">
                            </div>
                            <div class="edu-form-group" id="course-category-group" style="display:none;">
                                <label>Category</label>
                                <input type="text" id="course-category" placeholder="e.g. Marketing, Business...">
                            </div>
                            <div class="edu-form-group" id="desc-group">
                                <label>Description</label>
                                <textarea id="entity-desc" placeholder="Describe the learning objective..."></textarea>
                            </div>
                            <div class="edu-form-group" id="lesson-settings" style="display:none;">
                                <label>Lesson Type</label>
                                <select id="lesson-type">
                                    <option value="video">Video (Vimeo/YouTube)</option>
                                    <option value="pdf">PDF Resource</option>
                                    <option value="audio">Audio Lesson</option>
                                    <option value="quiz">Quiz (MCQ/True-False)</option>
                                    <option value="assignment">Assignment Task</option>
                                    <option value="live">Live Session (Zoom/Meet)</option>
                                </select>
                                <div style="margin-top:10px;">
                                    <label>Video/Resource URL</label>
                                    <input type="text" id="lesson-video" placeholder="https://...">
                                </div>
                                <div style="margin-top:10px;">
                                    <label>Drip Release (Days after enrollment)</label>
                                    <input type="number" id="lesson-drip" value="0" min="0">
                                </div>
                                <div id="quiz-builder-section" style="display:none; margin-top:10px;">
                                    <label>Quiz Questions (MCQ/JSON)</label>
                                    <textarea id="quiz-data" placeholder='[{"q": "Is WP an LMS?", "a": ["Yes", "No"], "c": 0}]'></textarea>
                                </div>
                                <div id="assignment-builder-section" style="display:none; margin-top:10px;">
                                    <label>Assignment Instructions</label>
                                    <textarea id="assignment-data" placeholder="Detailed tasks for the student..."></textarea>
                                </div>
                                <div style="margin-top:10px;">
                                    <label>Resources (PDF/Docs)</label>
                                    <div id="lesson-resources-list"></div>
                                    <button type="button" class="edu-btn edu-btn-small" id="add-lesson-resource">+ Add Resource</button>
                                </div>
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
            $(document).on('click', '.edu-add-module', (e) => self.openModal('module', 0, $(e.currentTarget).data('course-id')));
            $(document).on('click', '.edu-add-lesson', (e) => self.openModal('lesson', 0, $(e.currentTarget).data('module-id')));

            // Edit actions
            $(document).on('click', '.edu-edit-course', (e) => self.loadAndOpenModal('course', $(e.currentTarget).closest('.edu-course-container').data('id')));
            $(document).on('click', '.edu-edit-module', (e) => self.loadAndOpenModal('module', $(e.currentTarget).closest('.edu-module-box').data('id')));
            $(document).on('click', '.edu-edit-lesson', (e) => self.loadAndOpenModal('lesson', $(e.currentTarget).closest('.edu-lesson-item').data('id')));

            // Delete actions
            $(document).on('click', '.edu-delete-course', (e) => self.deleteEntity('course', $(e.currentTarget).closest('.edu-course-container').data('id')));
            $(document).on('click', '.edu-delete-module', (e) => self.deleteEntity('module', $(e.currentTarget).closest('.edu-module-box').data('id')));
            $(document).on('click', '.edu-delete-lesson', (e) => self.deleteEntity('lesson', $(e.currentTarget).closest('.edu-lesson-item').data('id')));

            $(document).on('change', '#lesson-type', () => self.toggleLessonExtraFields());

            $(document).on('click', '#add-lesson-resource', () => self.addResourceField());
            $(document).on('click', '.remove-resource', (e) => $(e.currentTarget).closest('.resource-row').remove());
        },

        toggleLessonExtraFields: function() {
            const type = $('#lesson-type').val();
            $('#quiz-builder-section').toggle(type === 'quiz');
            $('#assignment-builder-section').toggle(type === 'assignment');
        },

        addResourceField: function(title = '', url = '') {
            $('#lesson-resources-list').append(`
                <div class="resource-row" style="display:flex; gap:5px; margin-bottom:5px;">
                    <input type="text" class="res-title" placeholder="Title" value="${title}" style="width:40%;">
                    <input type="text" class="res-url" placeholder="URL" value="${url}" style="width:50%;">
                    <span class="remove-resource" style="cursor:pointer;">❌</span>
                </div>
            `);
        },

        initSortable: function() {
            const self = this;
            $('.edu-modules-list').sortable({
                handle: '.edu-drag-handle-mini',
                update: function(event, ui) {
                    const sortedIDs = $(this).sortable('toArray', { attribute: 'data-id' });
                    self.updateOrder('modules', sortedIDs);
                }
            });
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

        openModal: function(type, id = 0, parentId = 0) {
            $('#entity-type').val(type);
            $('#entity-id').val(id);
            $('#parent-id').val(parentId);
            $('#modal-title').text((id ? 'Edit ' : 'New ') + type.charAt(0).toUpperCase() + type.slice(1));
            $('#entity-title, #entity-desc, #lesson-video').val('');
            $('#lesson-type').val('video');
            $('#lesson-drip').val(0);
            $('#quiz-data, #assignment-data').val('');
            $('#lesson-resources-list').empty();

            if (type === 'lesson') {
                $('#lesson-settings').show();
                $('#desc-group').show();
                $('#course-category-group').hide();
                this.toggleLessonExtraFields();
            } else if (type === 'module') {
                $('#lesson-settings').hide();
                $('#desc-group').hide();
                $('#course-category-group').hide();
            } else {
                $('#lesson-settings').hide();
                $('#desc-group').show();
                $('#course-category-group').show();
            }
            $('#edu-builder-modal').show();
        },

        loadAndOpenModal: function(type, id) {
            const self = this;
            let url = eduApi.root + 'edupreneur/v1/' + (type === 'course' ? 'courses' : (type === 'module' ? 'modules' : 'lessons')) + '/' + id;
            $.ajax({
                url: url,
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: (data) => {
                    self.openModal(type, id);
                    $('#entity-title').val(data.title || data.name);
                    $('#entity-desc').val(data.description || data.content);
                    if (type === 'course') {
                        $('#course-category').val(data.category || 'General');
                    }
                    if (type === 'lesson') {
                        $('#lesson-type').val(data.lesson_type || 'video');
                        $('#lesson-video').val(data.video_url || '');
                        $('#lesson-drip').val(data.drip_days || 0);
                        if (data.quiz) $('#quiz-data').val(data.quiz.questions);
                        if (data.assignment) $('#assignment-data').val(data.assignment.instructions);
                        this.toggleLessonExtraFields();
                        if (data.resources) {
                            data.resources.forEach(r => self.addResourceField(r.title, r.url));
                        }
                    }
                }
            });
        },

        deleteEntity: function(type, id) {
            if (!confirm(`Are you sure you want to delete this ${type}?`)) return;
            const self = this;
            let url = eduApi.root + 'edupreneur/v1/' + (type === 'course' ? 'courses' : (type === 'module' ? 'modules' : 'lessons')) + '/' + id;
            $.ajax({
                url: url,
                method: 'DELETE',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                success: () => self.fetchData(),
                error: (err) => alert('Error deleting: ' + err.responseJSON.message)
            });
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
                            <div style="flex-grow:1;">
                                <h3 style="margin:0;">${course.title}</h3>
                                <div class="edu-item-actions">
                                    <span class="edu-edit-course" title="Edit Course">✏️</span>
                                    <span class="edu-delete-course" title="Delete Course">🗑️</span>
                                </div>
                            </div>
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
                            <div style="flex-grow:1;">
                                <h4 style="margin:0;">${module.title}</h4>
                                <div class="edu-item-actions-mini">
                                    <span class="edu-edit-module" title="Edit Module">✏️</span>
                                    <span class="edu-delete-module" title="Delete Module">🗑️</span>
                                </div>
                            </div>
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
                        <div style="flex-grow:1;">
                            <span class="edu-lesson-title">${lesson.title}</span>
                            <div class="edu-item-actions-mini">
                                <span class="edu-edit-lesson" title="Edit Lesson">✏️</span>
                                <span class="edu-delete-lesson" title="Delete Lesson">🗑️</span>
                            </div>
                        </div>
                        <span class="edu-lesson-type tag">${lesson.lesson_type || 'video'}</span>
                    </div>
                `);
            });
            this.initSortable();
        },

        saveEntity: function() {
            const self = this;
            const type = $('#entity-type').val();
            const id = $('#entity-id').val();
            const parentId = $('#parent-id').val();
            const title = $('#entity-title').val();
            const desc = $('#entity-desc').val();
            if (!title) return alert('Title is required');

            let url = eduApi.root + 'edupreneur/v1/' + (type === 'course' ? 'courses' : (type === 'module' ? 'modules' : 'lessons'));
            if (id && id != 0) url += '/' + id;

            let data = { title: title, description: desc };
            if (type === 'course') data.category = $('#course-category').val();
            if (type === 'module' && (!id || id == 0)) data.course_id = parentId;
            if (type === 'lesson') {
                if (!id || id == 0) {
                    data.module_id = parentId;
                    data.course_id = $(`.edu-module-box[data-id="${parentId}"]`).closest('.edu-course-container').data('id');
                }
                data.lesson_type = $('#lesson-type').val();
                data.video_url = $('#lesson-video').val();
                data.drip_days = $('#lesson-drip').val();
                data.quiz_data = $('#quiz-data').val();
                data.assignment_data = $('#assignment-data').val();
                data.resources = [];
                $('.resource-row').each(function() {
                    data.resources.push({
                        title: $(this).find('.res-title').val(),
                        url: $(this).find('.res-url').val()
                    });
                });
            }

            $.ajax({
                url: url,
                method: id && id != 0 ? 'PUT' : 'POST',
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
