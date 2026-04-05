(function(){
    'use strict';

    var toggleLabels = {
        showText: 'Show',
        hideText: 'Hide',
        showAria: 'Show password',
        hideAria: 'Hide password'
    };

    function handlePasswordToggle(button){
        if (!button) {
            return;
        }

        var targetId = button.getAttribute('data-teqcidb-toggle-target');

        if (!targetId) {
            return;
        }

        var input = document.getElementById(targetId);

        if (!input) {
            return;
        }

        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        var screenReaderText = button.querySelector('.screen-reader-text');

        if (screenReaderText) {
            screenReaderText.textContent = isPassword ? toggleLabels.hideText : toggleLabels.showText;
        }

        button.setAttribute('aria-label', isPassword ? toggleLabels.hideAria : toggleLabels.showAria);
        button.setAttribute('title', isPassword ? toggleLabels.hideAria : toggleLabels.showAria);
        button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

        var icon = button.querySelector('.dashicons');

        if (icon) {
            icon.classList.toggle('dashicons-visibility', !isPassword);
            icon.classList.toggle('dashicons-hidden', isPassword);
        }
    }

    document.addEventListener('click', function(event){
        var button = event.target.closest('.teqcidb-password-toggle');

        if (!button) {
            return;
        }

        handlePasswordToggle(button);
    });

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
    var slides = Array.isArray(runtime.slides) ? runtime.slides : [];
    var totalQuestions = questions.length;
    var answers = Object.assign({}, (runtime.attempt && runtime.attempt.answers) || {});
    var isSubmitted = runtime.attempt && (runtime.attempt.status === 0 || runtime.attempt.status === 1);
    var useRestQuizApi = runtime.useRestQuizApi !== false;
    var attemptId = parseInt((runtime.attempt && runtime.attempt.id) || 0, 10) || 0;
    var saveTimer = null;
    var saveMessageTimer = null;
    var periodicSaveTimer = null;
    var periodicSaveIntervalMs = (90000 + Math.floor(Math.random() * 60001));
    var saveState = { isSaving: false, hasPending: false };
    var isDirty = false;
    var hasQueuedSaveAfterChange = false;
    var lastSavedHash = JSON.stringify(answers || {});
    var isSubmitting = false;
    var slideIndex = 0;
    var slideViewedMap = {};
    var initialSlideProgress = runtime.slideProgress || {};
    var hasCompletedSlidesFromServer = !!initialSlideProgress.completed || (slides.length > 0 && (parseInt(initialSlideProgress.maxViewed || 0, 10) || 0) >= (slides.length - 1));
    var hasUnlockedQuiz = hasCompletedSlidesFromServer;
    var requiresSlidesFirst = runtime.quiz.classType === 'refresher' && slides.length > 0 && !hasCompletedSlidesFromServer;
    var slideAdvanceCooldownMs = 15000;
    var nextSlideUnlockedAt = 0;
    var slideCooldownTimer = null;
    var slideProgressState = { isSaving: false, hasPending: false };
    var slideProgressDirty = false;
    var slideLastSavedHash = '';
    var slideCooldownUnlockByIndex = {};
    var preloadedSlideUrls = {};
    var preloadInFlight = {};

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

    function viewedSlidesCount(){
        return Object.keys(slideViewedMap).length;
    }

    function slidesProgressPercent(){
        if (!slides.length) {
            return 0;
        }

        return Math.round(((Math.max(0, slideIndex) + 1) / slides.length) * 100);
    }

    function slidesStatusLine(){
        var viewed = viewedSlidesCount();
        return format(t('slidesCompletedRemaining', '%1$s completed / %2$s remaining'), String(viewed), String(Math.max(0, slides.length - viewed)));
    }

    function slidePositionLabel(){
        return format(t('slideOf', 'Slide %1$s of %2$s'), String(slideIndex + 1), String(slides.length));
    }

    function updateRefresherSectionCopy(showSlidesCopy){
        if (runtime.quiz.classType !== 'refresher' || !slides.length) {
            return;
        }

        var titleEl = document.getElementById('teqcidb-class-quiz-section-title');
        var descriptionEl = document.getElementById('teqcidb-class-quiz-section-description');

        if (titleEl) {
            titleEl.textContent = showSlidesCopy
                ? t('refresherSlidesSectionTitle', 'Refresher Class Slides')
                : t('refresherQuizSectionTitle', 'Refresher Quiz');
        }

        if (descriptionEl) {
            descriptionEl.innerHTML = showSlidesCopy
                ? t('refresherSlidesIntro', 'Please review each refresher slide before starting your quiz. The quiz will unlock after you have worked through every slide.')
                : t('refresherQuizIntro', 'Below is your QCI Refresher Quiz! A score of 80% or higher is considered passing. Anything below an 80% will be considered failing. If you fail, you will need to contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a> or <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a> to request another Refresher Quiz attempt. Only 1 additional attempt is granted! If you fail both Refresher Quiz attempts, you\'ll need to visit the <a href="/register-for-a-class-qci/">Register for a Class</a> page to register and pay for an upcoming Refresher Class. Good luck!');
        }
    }

    function getCurrentSelection(questionId){
        var selected = answers[String(questionId)];
        return Array.isArray(selected) ? selected : [];
    }

    function setCurrentSelection(questionId, selected){
        var normalizedQuestionId = String(questionId);
        answers[normalizedQuestionId] = selected;
    }

    function buildSlideProgressPayload(){
        return {
            quiz_id: runtime.quiz.id,
            class_id: runtime.quiz.classId,
            current_slide_index: Math.max(0, slideIndex),
            max_slide_index_viewed: Math.max(0, getMaxViewedSlideIndex()),
            slides_total: slides.length,
            completed: hasUnlockedQuiz
        };
    }

    function getSlideProgressPayloadHash(){
        return JSON.stringify(buildSlideProgressPayload());
    }

    function getMaxViewedSlideIndex(){
        var maxIndex = 0;

        Object.keys(slideViewedMap).forEach(function(key){
            if (!slideViewedMap[key]) {
                return;
            }

            var numericId = parseInt(key, 10);
            if (!isNaN(numericId)) {
                for (var i = 0; i < slides.length; i += 1) {
                    var rowId = parseInt(slides[i].id || i, 10);
                    if (rowId === numericId) {
                        maxIndex = Math.max(maxIndex, i);
                        return;
                    }
                }
            }
        });

        return Math.min(maxIndex, Math.max(0, slides.length - 1));
    }

    function markSlideProgressDirty(){
        slideProgressDirty = true;
    }


    function recordMetric(eventName, extra){
        if (window && typeof window.teqcidbQuizMetricHook === 'function') {
            window.teqcidbQuizMetricHook(eventName, extra || {});
        }
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

    function isSlideViewedAtIndex(index){
        if (index < 0 || index >= slides.length || !slides[index]) {
            return false;
        }

        var viewedKey = String(slides[index].id || index);
        return !!slideViewedMap[viewedKey];
    }

    function markCurrentSlideAsViewed(){
        if (!slides[slideIndex]) {
            return;
        }

        slideViewedMap[String(slides[slideIndex].id || slideIndex)] = true;
        if (!hasUnlockedQuiz && viewedSlidesCount() >= slides.length) {
            hasUnlockedQuiz = true;
        }
    }

    function clearSlideCooldownTimer(){
        if (slideCooldownTimer) {
            clearTimeout(slideCooldownTimer);
            slideCooldownTimer = null;
        }
    }

    function clearNextSlideCooldown(index){
        var targetIndex = typeof index === 'number' ? index : slideIndex;
        delete slideCooldownUnlockByIndex[String(targetIndex)];
        nextSlideUnlockedAt = 0;
        clearSlideCooldownTimer();
    }

    function setNextSlideCooldown(index){
        var cooldownIndex = typeof index === 'number' ? index : slideIndex;
        var cooldownKey = String(cooldownIndex);

        clearSlideCooldownTimer();
        slideCooldownUnlockByIndex[cooldownKey] = Date.now() + slideAdvanceCooldownMs;
        nextSlideUnlockedAt = slideCooldownUnlockByIndex[cooldownKey];
        slideCooldownTimer = setTimeout(function(){
            slideCooldownTimer = null;
            if (requiresSlidesFirst && root.querySelector('.teqcidb-class-slides')) {
                renderSlides();
            }
        }, Math.max(0, nextSlideUnlockedAt - Date.now()));
    }

    function syncCurrentSlideCooldown(){
        var cooldownKey = String(slideIndex);
        var unlockAt = parseInt(slideCooldownUnlockByIndex[cooldownKey] || 0, 10) || 0;

        clearSlideCooldownTimer();

        if (!unlockAt || unlockAt <= Date.now()) {
            delete slideCooldownUnlockByIndex[cooldownKey];
            nextSlideUnlockedAt = 0;
            return;
        }

        nextSlideUnlockedAt = unlockAt;
        slideCooldownTimer = setTimeout(function(){
            slideCooldownTimer = null;
            if (requiresSlidesFirst && root.querySelector('.teqcidb-class-slides')) {
                renderSlides();
            }
        }, Math.max(0, unlockAt - Date.now()));
    }

    // Preloading is cache-warm only and must not affect slide progression, cooldown timing, or persistence.
    function preloadSlideAtIndex(index){
        if (index < 0 || index >= slides.length || !slides[index]) {
            return;
        }

        var slideUrl = String(slides[index].url || '');
        if (!slideUrl || preloadedSlideUrls[slideUrl] || preloadInFlight[slideUrl]) {
            return;
        }

        var img = new Image();
        preloadInFlight[slideUrl] = true;
        img.onload = function(){
            delete preloadInFlight[slideUrl];
            preloadedSlideUrls[slideUrl] = true;
        };
        img.onerror = function(){
            delete preloadInFlight[slideUrl];
        };
        img.src = slideUrl;
    }

    function preloadUpcomingSlides(baseIndex){
        preloadSlideAtIndex(baseIndex + 1);
        preloadSlideAtIndex(baseIndex + 2);
    }

    function renderSlides(){
        updateRefresherSectionCopy(true);
        syncCurrentSlideCooldown();
        var currentSlide = slides[slideIndex] || {};
        var currentSlideAlt = currentSlide.alt || t('slideOf', 'Slide');
        var isFirst = slideIndex <= 0;
        var isLast = slideIndex >= (slides.length - 1);
        var isNextDisabled = Date.now() < nextSlideUnlockedAt;
        var nextTooltip = isNextDisabled ? t('slideWaitTooltip', 'Please study the slide and wait to proceed.') : '';
        var percent = slidesProgressPercent();

        root.innerHTML = '<div class="teqcidb-class-slides">' +
            '<div class="teqcidb-class-quiz__meta">' +
                '<strong>' + esc(slidePositionLabel()) + '</strong>' +
                '<span>' + esc(slidesStatusLine()) + '</span>' +
            '</div>' +
            '<div class="teqcidb-class-quiz__progress"><span style="width:' + percent + '%"></span></div>' +
            '<div class="teqcidb-class-slides__image-wrap">' +
                '<img class="teqcidb-class-slides__image" src="' + esc(currentSlide.url || '') + '" alt="' + esc(currentSlideAlt) + '" loading="lazy" decoding="async" />' +
            '</div>' +
            '<div class="teqcidb-class-slides__actions">' +
                '<button type="button" class="teqcidb-button" id="teqcidb-slide-prev" ' + (isFirst ? 'disabled' : '') + '>' + esc(t('previousSlide', 'Previous Slide')) + '</button>' +
                '<span class="teqcidb-class-slides__next-wrap ' + (isNextDisabled ? 'is-disabled' : '') + '" data-tooltip="' + esc(nextTooltip) + '">' +
                    '<button type="button" class="teqcidb-button teqcidb-button-primary" id="teqcidb-slide-next" ' + (isNextDisabled ? 'disabled' : '') + '>' + esc(isLast ? t('startQuiz', 'Start Quiz') : t('nextSlide', 'Next Slide')) + '</button>' +
                '</span>' +
            '</div>' +
        '</div>';

        bindSlideEvents();
    }

    function bindSlideEvents(){
        var prevBtn = root.querySelector('#teqcidb-slide-prev');
        var nextBtn = root.querySelector('#teqcidb-slide-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', function(){
                if (slideIndex <= 0) {
                    return;
                }

                slideIndex -= 1;
                markCurrentSlideAsViewed();
                markSlideProgressDirty();
                saveSlideProgress({ reason: 'slide_previous' });
                renderSlides();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(){
                if (nextBtn.disabled) {
                    return;
                }

                if (slideIndex >= (slides.length - 1)) {
                    if (hasUnlockedQuiz) {
                        render();
                    }
                    return;
                }

                var targetSlideIndex = slideIndex + 1;

                if (!isSlideViewedAtIndex(targetSlideIndex)) {
                    setNextSlideCooldown(targetSlideIndex);
                }

                slideIndex = targetSlideIndex;
                markCurrentSlideAsViewed();
                markSlideProgressDirty();
                saveSlideProgress({ reason: 'slide_next' });
                preloadUpcomingSlides(slideIndex);
                renderSlides();
            });
        }
    }


    function requestSlideProgressEndpoint(progressPayload){
        if (!runtime.restUrl) {
            return Promise.reject(new Error(i18n.slideProgressSaveError || 'Slide save failed.'));
        }

        return fetch(String(runtime.restUrl).replace(/\/$/, '') + '/slides/progress', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': runtime.restNonce || ''
            },
            body: JSON.stringify(progressPayload)
        }).then(function(resp){
            return resp.json().then(function(payload){
                if (!resp.ok || !payload || payload.ok !== true) {
                    throw new Error((payload && payload.message) || (i18n.slideProgressSaveError || 'Slide save failed.'));
                }
                return payload;
            });
        });
    }

    function saveSlideProgress(options){
        var saveOptions = options || {};
        var payload = buildSlideProgressPayload();
        var payloadHash = JSON.stringify(payload);

        if (!requiresSlidesFirst) {
            return;
        }

        if (!slideProgressDirty || payloadHash === slideLastSavedHash) {
            return;
        }

        if (slideProgressState.isSaving) {
            slideProgressState.hasPending = true;
            return;
        }

        slideProgressState.isSaving = true;

        requestSlideProgressEndpoint(payload).then(function(resp){
            slideProgressDirty = false;
            slideLastSavedHash = payloadHash;
            recordMetric('slide_progress_save_success', { reason: saveOptions.reason || 'unspecified' });
        }).catch(function(err){
            recordMetric('slide_progress_save_failure', { reason: saveOptions.reason || 'unspecified', message: err.message || '' });
        }).finally(function(){
            slideProgressState.isSaving = false;

            if (slideProgressState.hasPending) {
                slideProgressState.hasPending = false;
                saveSlideProgress({ reason: 'pending' });
            }
        });
    }

    function render(resultData){
        updateRefresherSectionCopy(false);
        if (isSubmitted && !resultData) {
            resultData = {
                passed: runtime.attempt && runtime.attempt.status === 0,
                score: runtime.attempt && typeof runtime.attempt.score === 'number' ? runtime.attempt.score : 0,
                passThreshold: runtime.quiz.passThreshold || 75,
                incorrectDetails: (runtime.attempt && Array.isArray(runtime.attempt.incorrectDetails)) ? runtime.attempt.incorrectDetails : []
            };
        }

        if (isSubmitted && resultData) {
            var showInitialPassedMessage = runtime.quiz.classType === 'initial' && !!resultData.passed;
            var hideIncorrectDetails = !resultData.passed;
            var dashboardUrl = String(runtime.dashboardCertificatesUrl || '/my-qci-dashboard/?tab=certificates-dates');
            var passedMessage = '';

            if (showInitialPassedMessage) {
                passedMessage = '<p>' +
                    esc(t('initialPassedMessageBeforeLink', 'Congratulations! Looks like you\'ve passed this class! Please ')) +
                    '<a href="' + esc(dashboardUrl) + '">' + esc(t('initialPassedMessageLinkText', 'visit your QCI Dashboard')) + '</a>' +
                    esc(t('initialPassedMessageAfterLink', ' for resources and information such as your QCI Certificate, Wallet Card, and important QCI expiration dates.')) +
                '</p>';
            }

            root.innerHTML = '<div class="teqcidb-class-quiz__result">' +
                '<h3>' + esc(resultData.passed ? (i18n.passed || 'Passed') : (i18n.failed || 'Failed')) + '</h3>' +
                passedMessage +
                '<p>' + esc(format(t('scoreSummary', 'Score: %1$s% (Required: %2$s%)'), String(resultData.score), String(resultData.passThreshold))) + '</p>' +
                (hideIncorrectDetails ? '' : buildIncorrectHtml(resultData.incorrectDetails || [])) +
            '</div>';
            return;
        }
        var questionBlocks = questions.map(function(question, index){
            return '<article class="teqcidb-class-quiz__question" data-question-id="' + esc(String(question.id)) + '">' +
                '<h3 class="teqcidb-class-quiz__question-title">' + esc(format(t('questionOf', 'Question %1$s of %2$s'), String(index + 1), String(totalQuestions))) + '</h3>' +
                '<div class="teqcidb-class-quiz__prompt">' + (question.prompt || '') + '</div>' +
                '<div class="teqcidb-class-quiz__choices">' + buildChoicesHtml(question) + '</div>' +
            '</article>';
        }).join('');

        root.innerHTML = '<div class="teqcidb-class-quiz">' +
            questionBlocks +
            '<div class="teqcidb-class-quiz__actions">' +
                '<button type="button" class="teqcidb-button teqcidb-button-primary" id="teqcidb-quiz-submit">' + esc(t('submitQuiz', 'Submit Quiz')) + '</button>' +
                '<span class="teqcidb-class-quiz__save-state" id="teqcidb-quiz-save-state" aria-live="polite"></span>' +
            '</div>' +
            '<div class="teqcidb-class-quiz__error" id="teqcidb-quiz-error" aria-live="polite"></div>' +
        '</div>';

        bindChoiceEvents();
        bindSubmitButton();
    }

    function parseAjaxResponse(payload){
        if (!payload || !payload.success) {
            throw new Error(getRequestErrorMessage(payload, i18n.saveError || i18n.submitError || 'Request failed.'));
        }
        return payload.data || {};
    }

    function parseRestResponse(payload){
        if (!payload || payload.ok !== true) {
            throw new Error(getRequestErrorMessage(payload, i18n.saveError || i18n.submitError || 'Request failed.'));
        }
        return payload;
    }

    function getRequestErrorMessage(payload, fallbackMessage){
        var payloadStatus = payload && payload.data && payload.data.status ? parseInt(payload.data.status, 10) : 0;
        var payloadCode = payload && payload.code ? String(payload.code) : '';

        if (payloadStatus === 429 || payloadCode.indexOf('rate_limited') !== -1) {
            return i18n.saveRateLimited || 'Please wait a few seconds before saving again.';
        }

        if (payload && payload.data && typeof payload.data.message === 'string' && payload.data.message.length) {
            return payload.data.message;
        }

        if (payload && typeof payload.message === 'string' && payload.message.length) {
            return payload.message;
        }

        return fallbackMessage;
    }

    function buildQuizRequestPayload(){
        return {
            quiz_id: runtime.quiz.id,
            class_id: runtime.quiz.classId,
            attempt_id: attemptId,
            answers: answers
        };
    }

    function buildQuizAjaxFormData(action){
        var formData = new FormData();
        formData.append('action', action);
        formData.append('_ajax_nonce', runtime.nonce || '');
        formData.append('quiz_id', runtime.quiz.id);
        formData.append('class_id', runtime.quiz.classId);
        formData.append('attempt_id', String(attemptId || 0));
        formData.append('current_index', '0');
        formData.append('answers_json', JSON.stringify(answers || {}));
        return formData;
    }

    function buildQuizProgressBeaconBody(){
        var body = new URLSearchParams();
        body.append('action', 'teqcidb_save_quiz_progress');
        body.append('_ajax_nonce', runtime.nonce || '');
        body.append('quiz_id', runtime.quiz.id);
        body.append('class_id', runtime.quiz.classId);
        body.append('attempt_id', String(attemptId || 0));
        body.append('current_index', '0');
        body.append('answers_json', JSON.stringify(answers || {}));
        return body;
    }

    function mapAjaxQuizResponse(ajaxAction, data, failureMessage){
        var base = {
            ok: true,
            attempt_id: data.attemptId || attemptId || 0,
            saved_at: data.savedAt || '',
            message: data.message || failureMessage
        };

        if (ajaxAction === 'teqcidb_submit_quiz_attempt') {
            base.score = data.score;
            base.passThreshold = data.passThreshold;
            base.passed = data.passed;
            base.incorrectDetails = data.incorrectDetails;
        }

        return base;
    }

    function requestQuizEndpoint(options){
        var requestOptions = options || {};
        var failureMessage = requestOptions.failureMessage || (i18n.saveError || i18n.submitError || 'Request failed.');
        var payload = buildQuizRequestPayload();

        if (useRestQuizApi && runtime.restUrl) {
            return fetch(String(runtime.restUrl).replace(/\/$/, '') + requestOptions.restPath, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': runtime.restNonce || ''
                },
                body: JSON.stringify(payload)
            }).then(function(resp){
                return resp.json().then(function(responsePayload){
                    if (!resp.ok) {
                        throw new Error(getRequestErrorMessage(responsePayload, failureMessage));
                    }
                    return parseRestResponse(responsePayload);
                });
            }).catch(function(err){
                if (!useRestQuizApi || !runtime.ajaxUrl) {
                    throw err;
                }
                return requestQuizEndpointFallback(requestOptions.ajaxAction, failureMessage);
            });
        }

        return requestQuizEndpointFallback(requestOptions.ajaxAction, failureMessage);
    }

    function requestQuizEndpointFallback(ajaxAction, failureMessage){
        return fetch(runtime.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: buildQuizAjaxFormData(ajaxAction)
        }).then(function(resp){
            return resp.json();
        }).then(function(payload){
            var data = parseAjaxResponse(payload);
            return mapAjaxQuizResponse(ajaxAction, data, failureMessage);
        });
    }

    function requestQuizSubmitEndpoint(failureMessage){
        return requestQuizEndpoint({
            restPath: '/quiz/submit',
            ajaxAction: 'teqcidb_submit_quiz_attempt',
            failureMessage: failureMessage
        });
    }

    function requestQuizProgressEndpoint(failureMessage){
        return requestQuizEndpoint({
            restPath: '/quiz/progress',
            ajaxAction: 'teqcidb_save_quiz_progress',
            failureMessage: failureMessage
        });
    }

    function mapChoiceValuesToLabels(values, choices){
        if (!Array.isArray(values) || !values.length) {
            return [];
        }

        var labelMap = {};
        (choices || []).forEach(function(choice){
            if (!choice || typeof choice.value === 'undefined') {
                return;
            }
            labelMap[String(choice.value)] = String(choice.label || choice.value || '');
        });

        return values.map(function(value){
            var key = String(value || '');
            return labelMap[key] || key;
        }).filter(function(label){
            return !!label;
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
            var selectedLabels = mapChoiceValuesToLabels(item.selected || [], item.choices || []);
            var correctLabels = mapChoiceValuesToLabels(item.correctSelections || [], item.choices || []);

            return '<article class="teqcidb-class-quiz__incorrect-item">' +
                '<h4>' + esc(item.prompt || '') + '</h4>' +
                '<p><strong>' + esc(t('yourAnswer', 'Your answer:')) + '</strong> ' + esc(selectedLabels.join(', ') || t('noAnswer', 'No answer')) + '</p>' +
                '<p><strong>' + esc(t('correctAnswer', 'Correct answer:')) + '</strong> ' + esc(correctLabels.join(', ')) + '</p>' +
                '<ul>' + choices + '</ul>' +
            '</article>';
        }).join('');

        return '<div class="teqcidb-class-quiz__incorrect"><h4>' + esc(t('questionsToReview', 'Questions to Review')) + '</h4>' + rows + '</div>';
    }

    function bindChoiceEvents(){
        questions.forEach(function(question){
            var choiceInputs = root.querySelectorAll('[name="teqcidb-question-' + String(question.id) + '"], [name="teqcidb-question-' + String(question.id) + '[]"]');

            choiceInputs.forEach(function(input){
                input.addEventListener('change', function(){
                    var selected = [];
                    choiceInputs.forEach(function(el){
                        if (el.checked) {
                            selected.push(el.value);
                        }
                    });
                    setCurrentSelection(question.id, normalizeSelected(question, selected));
                    markQuizDirty();
                    queueAutosave({ reason: 'answer_change' });
                });
            });
        });
    }

    function getCurrentAnswersHash(){
        return JSON.stringify(answers || {});
    }

    function markQuizDirty(){
        isDirty = true;
    }

    function setSaveStatus(message){
        var stateEl = root.querySelector('#teqcidb-quiz-save-state');

        if (!stateEl) {
            return;
        }

        stateEl.textContent = message || '';
    }

    function clearSaveStatusLater(delayMs){
        if (saveMessageTimer) {
            clearTimeout(saveMessageTimer);
            saveMessageTimer = null;
        }

        saveMessageTimer = setTimeout(function(){
            var stateEl = root.querySelector('#teqcidb-quiz-save-state');
            if (stateEl && stateEl.textContent === (i18n.saved || 'Progress saved.')) {
                stateEl.textContent = '';
            }
            saveMessageTimer = null;
        }, delayMs);
    }

    function queueAutosave(options){
        var queueOptions = options || {};

        if (isSubmitted || isSubmitting) {
            return;
        }

        if (queueOptions.immediate) {
            if (saveTimer) {
                clearTimeout(saveTimer);
                saveTimer = null;
            }
            saveProgress({ reason: queueOptions.reason || 'immediate' });
            return;
        }

        if (saveTimer) {
            clearTimeout(saveTimer);
        }

        hasQueuedSaveAfterChange = true;
        saveTimer = setTimeout(function(){
            saveTimer = null;
            hasQueuedSaveAfterChange = false;
            saveProgress({ reason: queueOptions.reason || 'debounce' });
        }, 10000);
    }

    function saveProgress(options){
        var saveOptions = options || {};
        var currentHash = getCurrentAnswersHash();
        var ignoreSubmittingGuard = !!saveOptions.ignoreSubmittingGuard;

        if (isSubmitted || (isSubmitting && !ignoreSubmittingGuard)) {
            return Promise.resolve();
        }

        if (!isDirty || currentHash === lastSavedHash) {
            return Promise.resolve();
        }

        if (saveState.isSaving) {
            saveState.hasPending = true;
            return Promise.resolve();
        }

        saveState.isSaving = true;
        setSaveStatus(i18n.saving || 'Saving…');

        return requestQuizProgressEndpoint(i18n.saveError || 'Save failed.').then(function(payload){
            attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
            lastSavedHash = currentHash;
            isDirty = false;
            setSaveStatus(i18n.saved || 'Progress saved.');
            clearSaveStatusLater(3000);
        }).catch(function(err){
            setSaveStatus(err && err.message ? err.message : (i18n.saveError || 'Save failed.'));
            clearSaveStatusLater(4000);
        }).finally(function(){
            saveState.isSaving = false;
            if (saveState.hasPending) {
                saveState.hasPending = false;
                saveProgress({ reason: 'pending' });
            }
        });
    }

    function waitForSaveToSettle(maxWaitMs, pollMs){
        var maxWait = Math.max(0, parseInt(maxWaitMs, 10) || 1500);
        var poll = Math.max(10, parseInt(pollMs, 10) || 50);

        if (!saveState.isSaving) {
            return Promise.resolve(true);
        }

        return new Promise(function(resolve){
            var startedAt = Date.now();
            var timer = setInterval(function(){
                var elapsed = Date.now() - startedAt;

                if (!saveState.isSaving) {
                    clearInterval(timer);
                    resolve(true);
                    return;
                }

                if (elapsed >= maxWait) {
                    clearInterval(timer);
                    resolve(false);
                }
            }, poll);
        });
    }

    function flushBeforeSubmit(){
        var flushResult = {
            settled: false,
            attemptedFlush: false,
            flushSucceeded: false
        };

        return waitForSaveToSettle(1500, 50).then(function(settled){
            flushResult.settled = !!settled;

            if (!isDirty) {
                return flushResult;
            }

            flushResult.attemptedFlush = true;

            return saveProgress({ reason: 'pre_submit_flush', ignoreSubmittingGuard: true }).then(function(){
                flushResult.flushSucceeded = true;
                return flushResult;
            }).catch(function(){
                return flushResult;
            });
        }).catch(function(){
            return flushResult;
        });
    }

    function startPeriodicAutosave(){
        if (periodicSaveTimer || isSubmitted) {
            return;
        }

        periodicSaveTimer = setInterval(function(){
            if (hasQueuedSaveAfterChange || isSubmitting || isSubmitted) {
                return;
            }
            saveProgress({ reason: 'periodic' });
        }, periodicSaveIntervalMs);
    }

    function getFirstUnansweredQuestionNumber(){
        for (var i = 0; i < questions.length; i += 1) {
            var selected = getCurrentSelection(questions[i].id);
            if (!Array.isArray(selected) || selected.length === 0) {
                return i + 1;
            }
        }

        return 0;
    }

    function bindSubmitButton(){
        var btn = root.querySelector('#teqcidb-quiz-submit');
        var err = root.querySelector('#teqcidb-quiz-error');

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function(){
            if (isSubmitting) {
                return;
            }

            var firstUnansweredQuestion = getFirstUnansweredQuestionNumber();
            if (firstUnansweredQuestion > 0) {
                err.textContent = format(i18n.validationAllAnswersRequired || 'Please answer Question %1$s before submitting.', String(firstUnansweredQuestion));
                return;
            }

            err.textContent = '';
            submitQuiz();
        });
    }

    function submitQuiz(){
        if (saveTimer) {
            clearTimeout(saveTimer);
            saveTimer = null;
            hasQueuedSaveAfterChange = false;
        }

        markQuizDirty();
        isSubmitting = true;
        setSaveStatus(i18n.submitting || 'Submitting quiz…');

        flushBeforeSubmit().then(function(){
            return requestQuizSubmitEndpoint(i18n.submitError || 'Submit failed.');
        }).then(function(payload){
                attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
                isSubmitted = true;
                isDirty = false;
                lastSavedHash = getCurrentAnswersHash();
                if (periodicSaveTimer) {
                    clearInterval(periodicSaveTimer);
                    periodicSaveTimer = null;
                }
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
                setSaveStatus('');
            }).finally(function(){
                isSubmitting = false;
            });
    }

    document.addEventListener('visibilitychange', function(){
        if (document.visibilityState === 'hidden') {
            queueAutosave({ immediate: true, reason: 'visibility_hidden' });
            if (requiresSlidesFirst) {
                saveSlideProgress({ reason: 'visibility_hidden' });
            }
        }
    });

    window.addEventListener('pagehide', function(){
        queueAutosave({ immediate: true, reason: 'pagehide' });
    });

    window.addEventListener('beforeunload', function(){
        if (!isSubmitted && runtime.ajaxUrl && isDirty && getCurrentAnswersHash() !== lastSavedHash) {
            if (navigator.sendBeacon) {
                navigator.sendBeacon(runtime.ajaxUrl, buildQuizProgressBeaconBody());
            }
        }

        if (requiresSlidesFirst && slideProgressDirty && runtime.restUrl) {
            var slidePayload = buildSlideProgressPayload();
            fetch(String(runtime.restUrl).replace(/\/$/, '') + '/slides/progress', {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': runtime.restNonce || ''
                },
                body: JSON.stringify(slidePayload)
            });
        }
    });

    if (requiresSlidesFirst) {
        var restoredSlideProgress = initialSlideProgress;
        var restoredCurrentIndex = Math.max(0, parseInt(restoredSlideProgress.currentIndex || 0, 10) || 0);
        var restoredMaxViewed = Math.max(restoredCurrentIndex, parseInt(restoredSlideProgress.maxViewed || 0, 10) || 0);

        slideIndex = Math.min(restoredCurrentIndex, Math.max(0, slides.length - 1));

        for (var restoredIndex = 0; restoredIndex <= Math.min(restoredMaxViewed, Math.max(0, slides.length - 1)); restoredIndex += 1) {
            var slideKey = String(slides[restoredIndex].id || restoredIndex);
            slideViewedMap[slideKey] = true;
        }

        hasUnlockedQuiz = !!restoredSlideProgress.completed || restoredMaxViewed >= (slides.length - 1);
        slideLastSavedHash = getSlideProgressPayloadHash();

        if (restoredMaxViewed > 0) {
            recordMetric('slide_progress_restored', { maxViewed: restoredMaxViewed, currentIndex: slideIndex });
        }

        markCurrentSlideAsViewed();
        preloadUpcomingSlides(slideIndex);
        renderSlides();
        return;
    }

    startPeriodicAutosave();
    render();
})();
