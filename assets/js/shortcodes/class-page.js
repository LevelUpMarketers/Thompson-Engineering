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
    var saveMessageTimer = null;
    var saveState = {
        isSaving: false,
        hasPending: false
    };
    var useRestQuizApi = runtime.useRestQuizApi !== false;
    var attemptId = parseInt((runtime.attempt && runtime.attempt.id) || 0, 10) || 0;
    var hasShownResumeNotice = false;
    var autosaveIntervalMs = 8000;
    var isDirty = false;
    var lastSavedHash = '';
    var metrics = {
        saveAttempts: 0,
        saveSuccess: 0,
        saveFailures: 0,
        skippedNoop: 0
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

    function buildProgressPayload(){
        return {
            quiz_id: runtime.quiz.id,
            class_id: runtime.quiz.classId,
            attempt_id: attemptId,
            current_question_index: currentIndex,
            answers: answers
        };
    }

    function getProgressPayloadHash(){
        return JSON.stringify(buildProgressPayload());
    }

    function markDirty(){
        isDirty = true;
    }

    function recordMetric(eventName, extra){
        if (window && typeof window.teqcidbQuizMetricHook === 'function') {
            window.teqcidbQuizMetricHook(eventName, extra || {});
        }
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
        var shouldShowResumeNotice = !hasShownResumeNotice && runtime.attempt && runtime.attempt.status === 2 && completed > 0;
        var notice = shouldShowResumeNotice ? ('<div class="teqcidb-class-quiz__notice">' + esc(i18n.resumeNotice || '') + '</div>') : '';

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

        var noticeEl = root.querySelector('.teqcidb-class-quiz__notice');
        if (noticeEl) {
            hasShownResumeNotice = true;
            window.setTimeout(function(){
                noticeEl.classList.add('is-fading-out');
            }, 10000);
        }
    }


    function toQueryPayload(){
        return buildProgressPayload();
    }

    function parseAjaxResponse(payload){
        if (!payload || !payload.success) {
            throw new Error((payload && payload.data && payload.data.message) || (i18n.saveError || 'Request failed.'));
        }
        return payload.data || {};
    }

    function parseRestResponse(payload){
        if (!payload || payload.ok !== true) {
            throw new Error((payload && payload.message) || (i18n.saveError || 'Request failed.'));
        }
        return payload;
    }

    function requestQuizEndpoint(restPath, ajaxAction, failureMessage){
        if (useRestQuizApi && runtime.restUrl) {
            return fetch(String(runtime.restUrl).replace(/\/$/, '') + restPath, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': runtime.restNonce || ''
                },
                body: JSON.stringify(toQueryPayload())
            }).then(function(resp){
                return resp.json().then(function(payload){
                    if (!resp.ok) {
                        throw new Error((payload && payload.message) || failureMessage);
                    }
                    return parseRestResponse(payload);
                });
            }).catch(function(err){
                if (!useRestQuizApi || !runtime.ajaxUrl) {
                    throw err;
                }
                return requestQuizEndpointFallback(ajaxAction, failureMessage);
            });
        }

        return requestQuizEndpointFallback(ajaxAction, failureMessage);
    }

    function requestQuizEndpointFallback(ajaxAction, failureMessage){
        var formData = new FormData();
        formData.append('action', ajaxAction);
        formData.append('_ajax_nonce', runtime.nonce || '');
        formData.append('quiz_id', runtime.quiz.id);
        formData.append('class_id', runtime.quiz.classId);
        formData.append('attempt_id', String(attemptId || 0));
        formData.append('current_index', String(currentIndex));
        formData.append('answers_json', JSON.stringify(answers));

        return fetch(runtime.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function(resp){
            return resp.json();
        }).then(function(payload){
            var data = parseAjaxResponse(payload);
            return {
                ok: true,
                attempt_id: data.attemptId || attemptId || 0,
                saved_at: data.savedAt || '',
                message: data.message || failureMessage,
                score: data.score,
                passThreshold: data.passThreshold,
                passed: data.passed,
                incorrectDetails: data.incorrectDetails
            };
        });
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
                markDirty();
                queueAutosave();
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
            markDirty();
            queueAutosave(true);
            render();
        });
    }

    function updateSaveStatus(message){
        var stateEl = root.querySelector('#teqcidb-quiz-save-state');

        if (!stateEl) {
            return;
        }

        stateEl.textContent = message || '';
        stateEl.classList.remove('is-fading-out');
    }

    function clearSaveStatus(){
        var stateEl = root.querySelector('#teqcidb-quiz-save-state');

        if (!stateEl) {
            return;
        }

        stateEl.classList.add('is-fading-out');

        window.setTimeout(function(){
            var refreshedEl = root.querySelector('#teqcidb-quiz-save-state');

            if (refreshedEl) {
                refreshedEl.textContent = '';
                refreshedEl.classList.remove('is-fading-out');
            }
        }, 350);
    }

    function showProgressSavedStatus(){
        if (saveMessageTimer) {
            clearTimeout(saveMessageTimer);
            saveMessageTimer = null;
        }

        updateSaveStatus(i18n.saved || 'Progress saved.');

        saveMessageTimer = setTimeout(function(){
            clearSaveStatus();
            saveMessageTimer = null;
        }, 3000);
    }

    function queueAutosave(immediate){
        if (isSubmitted) {
            return;
        }

        if (immediate) {
            if (saveTimer) {
                clearTimeout(saveTimer);
                saveTimer = null;
            }
            saveProgress({ reason: 'boundary' });
            return;
        }

        if (saveTimer) {
            return;
        }

        saveTimer = setTimeout(function(){
            saveTimer = null;
            saveProgress({ reason: 'interval' });
        }, autosaveIntervalMs);
    }

    function saveProgress(options){
        var saveOptions = options || {};
        var payloadHash = getProgressPayloadHash();

        if (isSubmitted) {
            return;
        }

        if (!isDirty || payloadHash === lastSavedHash) {
            metrics.skippedNoop += 1;
            recordMetric('quiz_save_noop', { reason: saveOptions.reason || 'unspecified' });
            return;
        }

        if (saveState.isSaving) {
            saveState.hasPending = true;
            return;
        }

        saveState.isSaving = true;
        metrics.saveAttempts += 1;
        recordMetric('quiz_save_attempt', { reason: saveOptions.reason || 'unspecified' });

        requestQuizEndpoint('/quiz/progress', 'teqcidb_save_quiz_progress', i18n.saveError || 'Save failed.').then(function(payload){
            attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
            lastSavedHash = payloadHash;
            isDirty = false;
            metrics.saveSuccess += 1;
            recordMetric('quiz_save_success', { reason: saveOptions.reason || 'unspecified' });
            if (saveOptions.reason === 'boundary') {
                showProgressSavedStatus();
            }
        }).catch(function(err){
            metrics.saveFailures += 1;
            recordMetric('quiz_save_failure', { reason: saveOptions.reason || 'unspecified', message: err.message || '' });
        }).finally(function(){
            saveState.isSaving = false;
            if (saveState.hasPending) {
                saveState.hasPending = false;
                saveProgress({ reason: 'pending' });
            }
        });
    }

    function submitQuiz(){
        if (saveTimer) {
            clearTimeout(saveTimer);
            saveTimer = null;
        }

        markDirty();

        requestQuizEndpoint('/quiz/submit', 'teqcidb_submit_quiz_attempt', i18n.submitError || 'Submit failed.').then(function(payload){
            attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
            isSubmitted = true;
            isDirty = false;
            lastSavedHash = getProgressPayloadHash();
            render({
                score: payload.score,
                passThreshold: payload.passThreshold,
                passed: payload.passed,
                incorrectDetails: payload.incorrectDetails || []
            });
        }).catch(function(err){
            var errorEl = root.querySelector('#teqcidb-quiz-error');
            if (errorEl) {
                errorEl.textContent = err.message || (i18n.submitError || 'Submit failed.');
            }
        });
    }

    document.addEventListener('visibilitychange', function(){
        if (document.visibilityState === 'hidden' && !isSubmitted) {
            saveProgress({ reason: 'visibility_hidden' });
        }
    });

    window.addEventListener('beforeunload', function(){
        if (!runtime.ajaxUrl || isSubmitted || !isDirty || getProgressPayloadHash() === lastSavedHash) {
            return;
        }

        recordMetric('quiz_beacon_attempt', { reason: 'beforeunload' });

        var body = new URLSearchParams();
        body.append('action', 'teqcidb_save_quiz_progress');
        body.append('_ajax_nonce', runtime.nonce || '');
        body.append('quiz_id', runtime.quiz.id);
        body.append('class_id', runtime.quiz.classId);
        body.append('attempt_id', String(attemptId || 0));
        body.append('current_index', String(currentIndex));
        body.append('answers_json', JSON.stringify(answers));

        if (navigator.sendBeacon) {
            navigator.sendBeacon(runtime.ajaxUrl, body);
        }
    });

    if (completedCount() >= totalQuestions) {
        currentIndex = totalQuestions - 1;
    }

    lastSavedHash = getProgressPayloadHash();

    render();
})();
