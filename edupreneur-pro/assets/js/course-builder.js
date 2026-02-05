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
                            <input type="hidden" id="course-id">
                            <div class="edu-form-group">
                                <label>Title</label>
                                <input type="text" id="entity-title" placeholder="Enter title...">
                                <p class="edu-field-caption">The primary name of your course, module, or lesson as it will appear to students.</p>
                            </div>
                            <div class="edu-form-group" id="course-category-group" style="display:none;">
                                <label>Category</label>
                                <select id="course-category">
                                    <option value="0">General</option>
                                </select>
                                <p class="edu-field-caption">Helps organize your courses in the catalog and makes them easier to find.</p>
                            </div>
                            <div class="edu-form-group" id="course-price-group" style="display:none;">
                                <label>Price ($)</label>
                                <input type="number" id="course-price" step="0.01" min="0" value="0.00">
                                <p class="edu-field-caption">The one-time enrollment fee for this course. Set to 0.00 for free access.</p>
                            </div>
                            <div class="edu-form-group" id="desc-group">
                                <label>Description</label>
                                <textarea id="entity-desc" placeholder="Describe the learning objective..."></textarea>
                                <p class="edu-field-caption">Provide a detailed overview of what students will learn or achieve in this section.</p>
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
                                    <p class="edu-field-caption">For video lessons, paste your YouTube or Vimeo link here. For other types, this is the primary resource link.</p>
                                </div>
                                <div style="margin-top:10px;">
                                    <label>Drip Release (Days after enrollment)</label>
                                    <input type="number" id="lesson-drip" value="0" min="0">
                                    <p class="edu-field-caption">Delay access to this lesson until a certain number of days after the student enrolls. 0 means instant access.</p>
                                </div>
                                <div id="quiz-builder-section" style="display:none; margin-top:10px;">
                                    <label>Quiz Questions</label>
                                    <p class="edu-field-caption" style="margin-bottom:10px;">Build your multiple-choice quiz below. Add questions and mark the correct choice for each.</p>
                                    <div id="quiz-questions-list"></div>
                                    <button type="button" class="edu-btn edu-btn-small" id="add-quiz-question">+ Add Question</button>
                                    <input type="hidden" id="quiz-data">
                                </div>
                                <div id="assignment-builder-section" style="display:none; margin-top:10px;">
                                    <label>Assignment Instructions</label>
                                    <textarea id="assignment-data" placeholder="Detailed tasks for the student..."></textarea>
                                    <p class="edu-field-caption">Explain the requirements for the assignment. Students will need to complete this before proceeding if progress logic is enabled.</p>
                                </div>
                                <div style="margin-top:10px;">
                                    <label>Resources (PDF/Docs)</label>
                                    <div id="lesson-resources-list"></div>
                                    <button type="button" class="edu-btn edu-btn-small" id="add-lesson-resource">+ Add Resource</button>
                                    <p class="edu-field-caption">Upload or link to additional materials like worksheets, checklists, or reading lists.</p>
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
            $(document).on('click', '.edu-add-lesson', (e) => {
                const mid = $(e.currentTarget).data('module-id');
                const cid = mid === 0 ? $(e.currentTarget).data('course-id') : $(e.currentTarget).closest('.edu-course-container').data('id');
                self.openModal('lesson', 0, mid, cid);
            });

            // Quick Add Lesson
            $(document).on('keypress', '.edu-quick-add-input', (e) => {
                if (e.which === 13) {
                    const $input = $(e.currentTarget);
                    const title = $input.val();
                    const mid = $input.data('module-id');
                    const cid = $input.data('course-id');
                    if (title) self.quickAddLesson(title, mid, cid, $input);
                }
            });

            // Expand/Collapse Module
            $(document).on('click', '.edu-module-toggle', (e) => {
                const $box = $(e.currentTarget).closest('.edu-module-box');
                $box.toggleClass('is-collapsed');
                $(e.currentTarget).text($box.hasClass('is-collapsed') ? '➕' : '➖');
            });

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

            // Quiz builder events
            $(document).on('click', '#add-quiz-question', () => self.addQuizQuestion());
            $(document).on('click', '.remove-quiz-question', (e) => $(e.currentTarget).closest('.quiz-question-box').remove());
            $(document).on('click', '.add-quiz-answer', (e) => self.addQuizAnswer($(e.currentTarget).closest('.quiz-question-box').find('.quiz-answers-list')));
            $(document).on('click', '.remove-quiz-answer', (e) => $(e.currentTarget).closest('.quiz-answer-row').remove());
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
                    <span class="remove-resource" style="cursor:pointer; line-height:35px;">❌</span>
                </div>
            `);
        },

        addQuizQuestion: function(qText = '', answers = [], correctIdx = 0) {
            if (!this.radioCounter) this.radioCounter = 0;
            const qId = ++this.radioCounter;

            const $qBox = $(`
                <div class="quiz-question-box" data-q-id="${qId}" style="border:1px solid #eee; padding:15px; margin-bottom:15px; border-radius:8px; background:#fafafa;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <strong>Question</strong>
                        <span class="remove-quiz-question" style="cursor:pointer; color:red;">Remove Q</span>
                    </div>
                    <input type="text" class="q-text" placeholder="Enter your question here..." value="${qText}" style="margin-bottom:10px; font-weight:600;">
                    <div class="quiz-answers-list"></div>
                    <button type="button" class="edu-btn-link add-quiz-answer">+ Add Choice</button>
                </div>
            `);
            $('#quiz-questions-list').append($qBox);
            const $ansList = $qBox.find('.quiz-answers-list');
            if (answers.length > 0) {
                answers.forEach((ans, idx) => this.addQuizAnswer($ansList, ans, idx === correctIdx));
            } else {
                this.addQuizAnswer($ansList, 'Option A', true);
                this.addQuizAnswer($ansList, 'Option B', false);
            }
        },

        addQuizAnswer: function($list, text = '', isCorrect = false) {
            const qId = $list.closest('.quiz-question-box').data('q-id');
            $list.append(`
                <div class="quiz-answer-row" style="display:flex; gap:10px; align-items:center; margin-bottom:5px;">
                    <input type="radio" name="correct_ans_${qId}" ${isCorrect ? 'checked' : ''} class="is-correct">
                    <input type="text" class="ans-text" value="${text}" placeholder="Choice text..." style="flex-grow:1;">
                    <span class="remove-quiz-answer" style="cursor:pointer;">❌</span>
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
                    const moduleId = $(this).data('id') === 0 ? 0 : $(this).closest('.edu-module-box').data('id');
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

        fetchCategories: function() {
            return $.ajax({
                url: eduApi.root + 'edupreneur/v1/categories',
                method: 'GET',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce)
            });
        },

        openModal: function(type, id = 0, parentId = 0, courseId = 0) {
            $('#entity-type').val(type);
            $('#entity-id').val(id);
            $('#parent-id').val(parentId);
            $('#course-id').val(courseId);
            this.currentCourseCategory = 0;
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
                $('#course-price-group').hide();
                $('#quiz-questions-list').empty();
                this.toggleLessonExtraFields();
            } else if (type === 'module') {
                $('#lesson-settings').hide();
                $('#desc-group').hide();
                $('#course-category-group').hide();
                $('#course-price-group').hide();
            } else {
                $('#lesson-settings').hide();
                $('#desc-group').show();
                $('#course-category-group').show();
                $('#course-price-group').show();
                this.fetchCategories().then(cats => {
                    const $sel = $('#course-category').empty();
                    $sel.append('<option value="0">General</option>');
                    cats.forEach(cat => {
                        $sel.append(`<option value="${cat.id}">${cat.name}</option>`);
                    });
                    if (this.currentCourseCategory) {
                        $sel.val(this.currentCourseCategory);
                    }
                });
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
                        this.currentCourseCategory = data.category_id || 0;
                        $('#course-category').val(this.currentCourseCategory);
                        $('#course-price').val(data.price || 0.00);
                    }
                    if (type === 'lesson') {
                        $('#lesson-type').val(data.lesson_type || 'video');
                        $('#lesson-video').val(data.video_url || '');
                        $('#lesson-drip').val(data.drip_days || 0);
                        if (data.quiz && data.quiz.questions) {
                            try {
                                const qData = JSON.parse(data.quiz.questions);
                                if (Array.isArray(qData)) {
                                    qData.forEach(q => self.addQuizQuestion(q.q, q.a, q.c));
                                }
                            } catch(e) { console.error('Error parsing quiz data', e); }
                        }
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
                                <h3 style="margin:0;">${course.title} <span class="tag" style="font-size:0.6em; vertical-align:middle;">${course.category || 'General'}</span></h3>
                                <div class="edu-item-actions">
                                    <span style="font-size:0.8em; color:#666; margin-right:10px;">$${parseFloat(course.price).toFixed(2)}</span>
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
                        <div class="edu-orphan-lessons-container" style="margin-top:15px; border-top:1px dashed #ddd; padding-top:15px;">
                            <h4 style="font-size:12px; text-transform:uppercase; color:#888;">Module-less Lessons</h4>
                            <div class="edu-lessons-list" id="orphan-lessons-for-${course.id}" data-id="0">
                                <!-- Orphan lessons go here -->
                            </div>
                            <div class="edu-quick-add-bar">
                                <input type="text" class="edu-quick-add-input" placeholder="Quick add lesson title..." data-module-id="0" data-course-id="${course.id}">
                                <button class="edu-btn-link edu-add-lesson" data-module-id="0" data-course-id="${course.id}">+ Full Editor</button>
                            </div>
                        </div>
                    </div>
                `);
                $list.append($courseRow);
                this.fetchModules(course.id);
                this.fetchLessons(0, course.id);
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
                            <div class="edu-module-toggle" style="cursor:pointer; margin-right:10px;">➖</div>
                            <div class="edu-drag-handle-mini">⠿</div>
                            <div style="flex-grow:1;">
                                <h4 style="margin:0;">${module.title}</h4>
                                <div class="edu-item-actions-mini">
                                    <span class="edu-edit-module" title="Edit Module">✏️</span>
                                    <span class="edu-delete-module" title="Delete Module">🗑️</span>
                                </div>
                            </div>
                        </div>
                        <div class="edu-module-content">
                            <div class="edu-lessons-list" id="lessons-for-${module.id}">
                                <!-- Lessons go here -->
                            </div>
                            <div class="edu-quick-add-bar">
                                <input type="text" class="edu-quick-add-input" placeholder="Quick add lesson title..." data-module-id="${module.id}" data-course-id="${courseId}">
                                <button class="edu-btn-link edu-add-lesson" data-module-id="${module.id}">+ Full Editor</button>
                            </div>
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
                success: (lessons) => self.renderLessons(moduleId, lessons, courseId)
            });
        },

        renderLessons: function(moduleId, lessons, courseId) {
            const isOrphan = parseInt(moduleId) === 0;
            const $container = isOrphan ? $(`#orphan-lessons-for-${courseId}`) : $(`#lessons-for-${moduleId}`);
            if (!$container.length) return;

            if (isOrphan) {
                $container.closest('.edu-orphan-lessons-container').toggle(lessons.length > 0 || $(`#modules-for-${courseId}`).children().length > 0);
            }

            $container.empty();
            lessons.forEach(lesson => {
                let indicators = '';
                if (lesson.video_url) indicators += '<span title="Video" style="margin-right:5px;">🎥</span>';
                if (lesson.lesson_type === 'quiz') indicators += '<span title="Quiz" style="margin-right:5px;">❓</span>';
                if (lesson.lesson_type === 'assignment') indicators += '<span title="Assignment" style="margin-right:5px;">📝</span>';

                const previewUrl = window.location.origin + window.location.pathname.replace('wp-admin/admin.php', '') + '?edu_lesson=' + lesson.id;

                $container.append(`
                    <div class="edu-lesson-item" data-id="${lesson.id}">
                        <div class="edu-drag-handle-mini">⠿</div>
                        <span class="edu-lesson-icon">📄</span>
                        <div style="flex-grow:1;">
                            <span class="edu-lesson-title">${lesson.title}</span>
                            <div class="edu-item-actions-mini">
                                <a href="${previewUrl}" target="_blank" title="Preview Lesson" style="text-decoration:none; filter:none; opacity:0.6;">👁️</a>
                                <span class="edu-edit-lesson" title="Edit Lesson">✏️</span>
                                <span class="edu-delete-lesson" title="Delete Lesson">🗑️</span>
                            </div>
                        </div>
                        <div class="edu-lesson-indicators" style="font-size:12px; opacity:0.7;">${indicators}</div>
                        <span class="edu-lesson-type tag">${lesson.lesson_type || 'video'}</span>
                    </div>
                `);
            });
            this.initSortable();
        },

        quickAddLesson: function(title, moduleId, courseId, $input) {
            const self = this;
            const data = {
                title: title,
                module_id: moduleId,
                course_id: courseId,
                lesson_type: 'video',
                description: ''
            };

            $input.prop('disabled', true);
            $.ajax({
                url: eduApi.root + 'edupreneur/v1/lessons',
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', eduApi.nonce),
                data: data,
                success: () => {
                    $input.val('').prop('disabled', false).focus();
                    self.fetchLessons(moduleId, courseId);
                },
                error: (err) => {
                    alert('Error adding lesson: ' + err.responseJSON.message);
                    $input.prop('disabled', false);
                }
            });
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
            if (type === 'course') {
                data.category_id = $('#course-category').val();
                data.price = $('#course-price').val();
            }
            if (type === 'module' && (!id || id == 0)) data.course_id = parentId;
            if (type === 'lesson') {
                if (!id || id == 0) {
                    data.module_id = parentId;
                    data.course_id = $('#course-id').val() || $(`.edu-module-box[data-id="${parentId}"]`).closest('.edu-course-container').data('id');
                }
                data.lesson_type = $('#lesson-type').val();
                data.video_url = $('#lesson-video').val();
                data.drip_days = $('#lesson-drip').val();

                // Collect Quiz Data
                if (data.lesson_type === 'quiz') {
                    let quizArray = [];
                    $('.quiz-question-box').each(function() {
                        let qText = $(this).find('.q-text').val();
                        let answers = [];
                        let correctIdx = 0;
                        $(this).find('.quiz-answer-row').each(function(idx) {
                            answers.push($(this).find('.ans-text').val());
                            if ($(this).find('.is-correct').is(':checked')) {
                                correctIdx = idx;
                            }
                        });
                        quizArray.push({ q: qText, a: answers, c: correctIdx });
                    });
                    data.quiz_data = JSON.stringify(quizArray);
                } else {
                    data.quiz_data = '';
                }

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
