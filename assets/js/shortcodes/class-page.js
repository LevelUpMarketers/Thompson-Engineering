(function(){
    'use strict';

    var root = document.getElementById('teqcidb-class-quiz-app');

    if (!root) {
        return;
    }

    var runtimeRaw = root.getAttribute('data-quiz-runtime') || '{}';
    var runtime = {};

    try {
        runtime = JSON.parse(runtimeRaw);
    } catch (e) {
        runtime = {};
    }

    if (!runtime || !runtime.quiz || !Array.isArray(runtime.questions) || runtime.questions.length === 0) {
        return;
    }

    var i18n = runtime.i18n || {};
    var questions = runtime.questions;
    var totalQuestions = questions.length;
    var answers = Object.assign({}, (runtime.attempt && runtime.attempt.answers) || {});
    var currentIndex = Math.max(0, parseInt((runtime.attempt && runtime.attempt.currentIndex) || 0, 10) || 0);
    var isSubmitted = runtime.attempt && (runtime.attempt.status === 0 || runtime.attempt.status === 1);
    var saveTimer = null;
    var saveState = {
        isSaving: false,
        hasPending: false
    };

    function esc(text){
        return String(text || '').replace(/[&<>"]+/g, function(char){
            if (char === '&') return '&amp;';
            if (char === '<') return '&lt;';
            if (char === '>') return '&gt;';
            return '&quot;';
        });
    }

    function t(key, fallback){
        var value = i18n && i18n[key];
        return (typeof value === 'string' && value.length) ? value : fallback;
    }

    function format(template){
        var args = Array.prototype.slice.call(arguments, 1);
        return String(template || '').replace(/%([0-9]+)\$s/g, function(_m, n){
            var idx = parseInt(n, 10) - 1;
            return typeof args[idx] !== 'undefined' ? args[idx] : '';
        });
    }

    function completedCount(){
        var count = 0;
        questions.forEach(function(question){
            var answer = answers[String(question.id)] || [];
            if (Array.isArray(answer) && answer.length > 0) {
                count += 1;
            }
        });
        return count;
    }

    function getQuestionByIndex(index){
        return questions[Math.max(0, Math.min(index, totalQuestions - 1))];
    }

    function getCurrentSelection(questionId){
        var selected = answers[String(questionId)];
        return Array.isArray(selected) ? selected : [];
    }

    function setCurrentSelection(questionId, selected){
        answers[String(questionId)] = selected;
    }

    function progressPercent(){
        return Math.round((completedCount() / totalQuestions) * 100);
    }

    function questionPositionLabel(){
        return format(t('questionOf', 'Question %1$s of %2$s'), String(currentIndex + 1), String(totalQuestions));
    }

    function statusLine(){
        return format(t('completedRemaining', '%1$s completed / %2$s remaining'), String(completedCount()), String(totalQuestions - completedCount()));
    }

    function normalizeSelected(question, selectedValues){
        if (!Array.isArray(selectedValues)) {
            return [];
        }

        var dedupe = {};
        var list = [];

        selectedValues.forEach(function(value){
            var normalized = String(value || '').toLowerCase();
            if (!dedupe[normalized]) {
                dedupe[normalized] = true;
                list.push(normalized);
            }
        });

        if (question.type === 'multiple_choice' || question.type === 'true_false') {
            return list.length > 0 ? [list[0]] : [];
        }

        return list;
    }

    function buildChoicesHtml(question){
        var selected = getCurrentSelection(question.id);
        var type = question.type === 'multi_select' ? 'checkbox' : 'radio';

        return (question.choices || []).map(function(choice, idx){
            var value = String(choice.value || '').toLowerCase();
            var isChecked = selected.indexOf(value) !== -1;
            var inputName = 'teqcidb-question-' + question.id + (type === 'checkbox' ? '[]' : '');
            return '<label class="teqcidb-class-quiz__choice">' +
                '<input type="' + type + '" name="' + esc(inputName) + '" value="' + esc(value) + '" ' + (isChecked ? 'checked' : '') + ' />' +
                '<span>' + esc(choice.label || (t('optionLabel', 'Option %s').replace('%s', String(idx + 1)))) + '</span>' +
            '</label>';
        }).join('');
    }

    function render(resultData){
        if (isSubmitted && !resultData) {
            resultData = {
                passed: runtime.attempt && runtime.attempt.status === 0,
                score: runtime.attempt && typeof runtime.attempt.score === 'number' ? runtime.attempt.score : 0,
                passThreshold: runtime.quiz.passThreshold || 75,
                incorrectDetails: []
            };
        }

        var question = getQuestionByIndex(currentIndex);
        var completed = completedCount();
        var percent = progressPercent();
        var notice = (runtime.attempt && runtime.attempt.status === 2 && completed > 0) ? ('<div class="teqcidb-class-quiz__notice">' + esc(i18n.resumeNotice || '') + '</div>') : '';

        if (isSubmitted && resultData) {
            root.innerHTML = '<div class="teqcidb-class-quiz__result">' +
                '<h3>' + esc(resultData.passed ? (i18n.passed || 'Passed') : (i18n.failed || 'Failed')) + '</h3>' +
                '<p>' + esc(format(t('scoreSummary', 'Score: %1$s%% (Required: %2$s%%)'), String(resultData.score), String(resultData.passThreshold))) + '</p>' +
                buildIncorrectHtml(resultData.incorrectDetails || []) +
            '</div>';
            return;
        }

        var atEnd = currentIndex >= (totalQuestions - 1);

        root.innerHTML = '<div class="teqcidb-class-quiz">' +
            notice +
            '<div class="teqcidb-class-quiz__meta">' +
                '<strong>' + esc(questionPositionLabel()) + '</strong>' +
                '<span>' + esc(statusLine()) + '</span>' +
            '</div>' +
            '<div class="teqcidb-class-quiz__progress"><span style="width:' + percent + '%"></span></div>' +
            '<div class="teqcidb-class-quiz__prompt">' + (question.prompt || '') + '</div>' +
            '<div class="teqcidb-class-quiz__choices">' + buildChoicesHtml(question) + '</div>' +
            '<div class="teqcidb-class-quiz__actions">' +
                '<button type="button" class="teqcidb-button teqcidb-button-primary" id="teqcidb-quiz-next">' + esc(atEnd ? t('submitQuiz', 'Submit Quiz') : t('nextQuestion', 'Next Question')) + '</button>' +
                '<span class="teqcidb-class-quiz__save-state" id="teqcidb-quiz-save-state"></span>' +
            '</div>' +
            '<div class="teqcidb-class-quiz__error" id="teqcidb-quiz-error" aria-live="polite"></div>' +
        '</div>';

        bindChoiceEvents(question);
        bindNextButton(question, atEnd);
    }

    function buildIncorrectHtml(incorrect){
        if (!incorrect.length) {
            return '';
        }

        var rows = incorrect.map(function(item){
            var choices = (item.choices || []).map(function(choice){
                return '<li>' + esc(choice.label) + '</li>';
            }).join('');
            return '<article class="teqcidb-class-quiz__incorrect-item">' +
                '<h4>' + esc(item.prompt || '') + '</h4>' +
                '<p><strong>' + esc(t('yourAnswer', 'Your answer:')) + '</strong> ' + esc((item.selected || []).join(', ') || t('noAnswer', 'No answer')) + '</p>' +
                '<p><strong>' + esc(t('correctAnswer', 'Correct answer:')) + '</strong> ' + esc((item.correctSelections || []).join(', ')) + '</p>' +
                '<ul>' + choices + '</ul>' +
            '</article>';
        }).join('');

        return '<div class="teqcidb-class-quiz__incorrect"><h4>' + esc(t('questionsToReview', 'Questions to Review')) + '</h4>' + rows + '</div>';
    }

    function bindChoiceEvents(question){
        var choiceInputs = root.querySelectorAll('.teqcidb-class-quiz__choice input');

        choiceInputs.forEach(function(input){
            input.addEventListener('change', function(){
                var selected = [];
                var allInputs = root.querySelectorAll('.teqcidb-class-quiz__choice input');
                allInputs.forEach(function(el){
                    if (el.checked) {
                        selected.push(el.value);
                    }
                });
                setCurrentSelection(question.id, normalizeSelected(question, selected));
                queueAutosave();
                updateSaveStatus(i18n.saving || 'Saving…');
            });
        });
    }

    function bindNextButton(question, atEnd){
        var btn = root.querySelector('#teqcidb-quiz-next');
        var err = root.querySelector('#teqcidb-quiz-error');

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function(){
            var selected = getCurrentSelection(question.id);

            if (!Array.isArray(selected) || selected.length === 0) {
                err.textContent = i18n.validationAnswerRequired || 'Please select an answer before continuing.';
                return;
            }

            err.textContent = '';

            if (atEnd) {
                submitQuiz();
                return;
            }

            currentIndex += 1;
            queueAutosave(true);
            render();
        });
    }

    function updateSaveStatus(message){
        var stateEl = root.querySelector('#teqcidb-quiz-save-state');
        if (stateEl) {
            stateEl.textContent = message || '';
        }
    }

    function queueAutosave(immediate){
        if (saveTimer) {
            clearTimeout(saveTimer);
            saveTimer = null;
        }

        if (immediate) {
            saveProgress();
            return;
        }

        saveTimer = setTimeout(saveProgress, 1000);
    }

    function saveProgress(){
        if (isSubmitted) {
            return;
        }

        if (saveState.isSaving) {
            saveState.hasPending = true;
            return;
        }

        saveState.isSaving = true;
        updateSaveStatus(i18n.saving || 'Saving…');

        var formData = new FormData();
        formData.append('action', 'teqcidb_save_quiz_progress');
        formData.append('_ajax_nonce', runtime.nonce || '');
        formData.append('quiz_id', runtime.quiz.id);
        formData.append('class_id', runtime.quiz.classId);
        formData.append('current_index', String(currentIndex));
        formData.append('answers_json', JSON.stringify(answers));

        fetch(runtime.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function(resp){
            return resp.json();
        }).then(function(payload){
            if (!payload || !payload.success) {
                throw new Error((payload && payload.data && payload.data.message) || (i18n.saveError || 'Save failed.'));
            }
            updateSaveStatus(i18n.saved || 'Progress saved.');
        }).catch(function(err){
            updateSaveStatus(err.message || (i18n.saveError || 'Save failed.'));
        }).finally(function(){
            saveState.isSaving = false;
            if (saveState.hasPending) {
                saveState.hasPending = false;
                saveProgress();
            }
        });
    }

    function submitQuiz(){
        updateSaveStatus(i18n.submitting || 'Submitting quiz…');

        var formData = new FormData();
        formData.append('action', 'teqcidb_submit_quiz_attempt');
        formData.append('_ajax_nonce', runtime.nonce || '');
        formData.append('quiz_id', runtime.quiz.id);
        formData.append('class_id', runtime.quiz.classId);
        formData.append('answers_json', JSON.stringify(answers));

        fetch(runtime.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function(resp){
            return resp.json();
        }).then(function(payload){
            if (!payload || !payload.success) {
                throw new Error((payload && payload.data && payload.data.message) || (i18n.submitError || 'Submit failed.'));
            }
            isSubmitted = true;
            render(payload.data || {});
        }).catch(function(err){
            var errorEl = root.querySelector('#teqcidb-quiz-error');
            if (errorEl) {
                errorEl.textContent = err.message || (i18n.submitError || 'Submit failed.');
            }
        });
    }

    window.addEventListener('beforeunload', function(){
        if (!runtime.ajaxUrl || isSubmitted) {
            return;
        }

        var body = new URLSearchParams();
        body.append('action', 'teqcidb_save_quiz_progress');
        body.append('_ajax_nonce', runtime.nonce || '');
        body.append('quiz_id', runtime.quiz.id);
        body.append('class_id', runtime.quiz.classId);
        body.append('current_index', String(currentIndex));
        body.append('answers_json', JSON.stringify(answers));

        if (navigator.sendBeacon) {
            navigator.sendBeacon(runtime.ajaxUrl, body);
        }
    });

    if (completedCount() >= totalQuestions) {
        currentIndex = totalQuestions - 1;
    }

    render();
})();
