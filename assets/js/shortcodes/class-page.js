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

    var runtimeQuestions = Array.isArray(runtime.questions) ? runtime.questions : [];
    var runtimeSlides = Array.isArray(runtime.slides) ? runtime.slides : [];
    var runtimeClassType = runtime && runtime.quiz ? String(runtime.quiz.classType || '') : '';
    var hasSlideOnlyRefresherRuntime = runtimeClassType === 'refresher' && runtimeSlides.length > 0 && runtimeQuestions.length === 0;

    if (!runtime || !runtime.quiz || (!runtimeQuestions.length && !hasSlideOnlyRefresherRuntime)) {
        return;
    }

    var i18n = runtime.i18n || {};
    var questions = runtimeQuestions;
    var slides = runtimeSlides;
    var totalQuestions = questions.length;
    var isSlideOnlyRefresher = hasSlideOnlyRefresherRuntime;
    var answers = Object.assign({}, (runtime.attempt && runtime.attempt.answers) || {});
    var isSubmitted = runtime.attempt && (runtime.attempt.status === 0 || runtime.attempt.status === 1);
    var useRestQuizApi = runtime.useRestQuizApi !== false;
    var attemptId = parseInt((runtime.attempt && runtime.attempt.id) || 0, 10) || 0;
    var idempotencyToken = String(runtime.idempotencyToken || '');
    var isSubmitting = false;
    var isSavingProgress = false;
    var isSavingSlideProgress = false;
    var isCompletingSlides = false;
    var slideIndex = 0;
    var slideViewedMap = {};
    var initialSlideProgress = runtime.slideProgress || {};
    var hasUnlockedQuiz = false;
    var requiresSlidesFirst = runtime.quiz.classType === 'refresher' && slides.length > 0;
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
        var completed = Math.min(slides.length, Math.max(0, slideIndex) + 1);
        return format(t('slidesCompletedRemaining', '%1$s completed / %2$s remaining'), String(completed), String(Math.max(0, slides.length - completed)));
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
            if (showSlidesCopy && isSlideOnlyRefresher) {
                descriptionEl.innerHTML = t('refresherSlideOnlyIntro', 'Please review each refresher slide. This refresher will be complete after you have worked through every slide.');
            } else {
                descriptionEl.innerHTML = showSlidesCopy
                    ? t('refresherSlidesIntro', 'Please review each refresher slide before starting your quiz. The quiz will unlock after you have worked through every slide.')
                    : t('refresherQuizIntro', 'Below is your QCI Refresher Quiz! A score of 80% or higher is considered passing. Anything below an 80% will be considered failing. If you fail, you will need to contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a> or <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a> to request another Refresher Quiz attempt. Only 1 additional attempt is granted! If you fail both Refresher Quiz attempts, you\'ll need to visit the <a href="/register-for-a-class-qci/">Register for a Class</a> page to register and pay for an upcoming Refresher Class. Good luck!');
            }
        }
    }

    function renderSlideOnlyComplete(){
        updateRefresherSectionCopy(false);

        var titleEl = document.getElementById('teqcidb-class-quiz-section-title');
        var descriptionEl = document.getElementById('teqcidb-class-quiz-section-description');

        if (titleEl) {
            titleEl.textContent = t('slideOnlyCompleteTitle', 'Refresher Slides Complete');
        }

        if (descriptionEl) {
            descriptionEl.textContent = t('slideOnlyCompleteMessage', 'You have completed all refresher slides. There are no quiz questions to answer for this refresher.');
        }

        root.innerHTML = '<div class="teqcidb-class-quiz teqcidb-class-quiz--slide-only-complete">' +
            '<div class="teqcidb-class-quiz__result">' +
                '<h3>' + esc(t('slideOnlyCompleteTitle', 'Refresher Slides Complete')) + '</h3>' +
                '<p>' + esc(t('slideOnlyCompleteMessage', 'You have completed all refresher slides. There are no quiz questions to answer for this refresher.')) + '</p>' +
            '</div>' +
        '</div>';
    }

    function getCurrentSelection(questionId){
        var selected = answers[String(questionId)];
        return Array.isArray(selected) ? selected : [];
    }

    function setCurrentSelection(questionId, selected){
        var normalizedQuestionId = String(questionId);
        answers[normalizedQuestionId] = selected;
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

    function buildChoicesHtml(question, options){
        var renderOptions = options || {};
        var isDisabled = !!renderOptions.disabled;
        var selected = getCurrentSelection(question.id);
        var type = question.type === 'multi_select' ? 'checkbox' : 'radio';

        return (question.choices || []).map(function(choice, idx){
            var value = String(choice.value || '').toLowerCase();
            var isChecked = selected.indexOf(value) !== -1;
            var inputName = 'teqcidb-question-' + question.id + (type === 'checkbox' ? '[]' : '');
            return '<label class="teqcidb-class-quiz__choice">' +
                '<input type="' + type + '" name="' + esc(inputName) + '" value="' + esc(value) + '" ' + (isChecked ? 'checked' : '') + ' ' + (isDisabled ? 'disabled' : '') + ' />' +
                '<span>' + esc(choice.label || (t('optionLabel', 'Option %s').replace('%s', String(idx + 1)))) + '</span>' +
            '</label>';
        }).join('');
    }

    function markCurrentSlideAsViewed(){
        if (!slides[slideIndex]) {
            return;
        }

        slideViewedMap[String(slides[slideIndex].id || slideIndex)] = true;
        if (!hasUnlockedQuiz && (viewedSlidesCount() >= slides.length || slideIndex >= (slides.length - 1))) {
            hasUnlockedQuiz = true;
        }
    }

    // Preloading is cache-warm only and must not affect slide progression.
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
        var currentSlide = slides[slideIndex] || {};
        var currentSlideAlt = currentSlide.alt || t('slideOf', 'Slide');
        var isFirst = slideIndex <= 0;
        var isLast = slideIndex >= (slides.length - 1);
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
                '<span class="teqcidb-class-slides__next-wrap" data-tooltip="">' +
                    '<button type="button" class="teqcidb-button teqcidb-button-primary" id="teqcidb-slide-next">' + esc(isLast ? (isSlideOnlyRefresher ? t('completeSlides', 'Complete Slides') : t('startQuiz', 'Start Quiz')) : t('nextSlide', 'Next Slide')) + '</button>' +
                '</span>' +
                '<button type="button" class="teqcidb-button teqcidb-button-secondary" id="teqcidb-slide-save-progress">' + esc(t('slideSaveProgress', 'Save Progress')) + '</button>' +
                '<span class="teqcidb-class-slides__save-status" id="teqcidb-slide-save-status" aria-live="polite"></span>' +
            '</div>' +
        '</div>';

        bindSlideEvents();
    }

    function bindSlideEvents(){
        var prevBtn = root.querySelector('#teqcidb-slide-prev');
        var nextBtn = root.querySelector('#teqcidb-slide-next');
        var saveSlideBtn = root.querySelector('#teqcidb-slide-save-progress');
        var saveSlideStatus = root.querySelector('#teqcidb-slide-save-status');

        if (prevBtn) {
            prevBtn.addEventListener('click', function(){
                if (slideIndex <= 0) {
                    return;
                }

                slideIndex -= 1;
                markCurrentSlideAsViewed();
                renderSlides();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(){
                if (slideIndex >= (slides.length - 1)) {
                    if (hasUnlockedQuiz) {
                        if (isSlideOnlyRefresher) {
                            completeSlideOnlyRefresher(nextBtn, saveSlideBtn, saveSlideStatus);
                        } else {
                            render();
                        }
                    }
                    return;
                }

                slideIndex += 1;
                markCurrentSlideAsViewed();
                preloadUpcomingSlides(slideIndex);
                renderSlides();
            });
        }

        if (saveSlideBtn) {
            saveSlideBtn.addEventListener('click', function(){
                if (isSavingSlideProgress) {
                    return;
                }

                isSavingSlideProgress = true;
                saveSlideBtn.disabled = true;

                if (saveSlideStatus) {
                    saveSlideStatus.textContent = '';
                }

                requestSlideProgressSaveEndpoint(i18n.slideProgressSaveError || 'We could not save your slide progress. Please try again.').then(function(payload){
                    if (saveSlideStatus) {
                        saveSlideStatus.textContent = payload.message || t('slideProgressSaved', 'Slide progress saved.');
                    }
                }).catch(function(error){
                    if (saveSlideStatus) {
                        saveSlideStatus.textContent = error.message || t('slideProgressSaveError', 'We could not save your slide progress. Please try again.');
                    }
                }).finally(function(){
                    isSavingSlideProgress = false;
                    saveSlideBtn.disabled = false;
                });
            });
        }
    }

    function buildSlideProgressSavePayload(){
        return {
            quiz_id: runtime.quiz.id,
            class_id: runtime.quiz.classId,
            current_slide_number: Math.max(1, slideIndex + 1),
            slides_total: slides.length
        };
    }

    function requestSlideProgressSaveEndpoint(failureMessage){
        return requestSlideEndpoint('/slides/progress', failureMessage);
    }

    function requestSlideCompletionEndpoint(failureMessage){
        return requestSlideEndpoint('/slides/complete', failureMessage);
    }

    function requestSlideEndpoint(path, failureMessage){
        if (!runtime.restUrl) {
            return Promise.reject(new Error(failureMessage));
        }

        return fetch(String(runtime.restUrl).replace(/\/$/, '') + path, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': runtime.restNonce || ''
            },
            body: JSON.stringify(buildSlideProgressSavePayload())
        }).then(function(resp){
            return resp.json().then(function(payload){
                if (!resp.ok) {
                    throw new Error(getRequestErrorMessage(payload, failureMessage));
                }

                return parseRestResponse(payload);
            });
        });
    }

    function completeSlideOnlyRefresher(nextBtn, saveSlideBtn, saveSlideStatus){
        if (isCompletingSlides) {
            return;
        }

        isCompletingSlides = true;

        if (nextBtn) {
            nextBtn.disabled = true;
        }

        if (saveSlideBtn) {
            saveSlideBtn.disabled = true;
        }

        if (saveSlideStatus) {
            saveSlideStatus.textContent = '';
        }

        requestSlideCompletionEndpoint(i18n.slideCompletionError || 'We could not complete your refresher slides. Please try again.').then(function(){
            renderSlideOnlyComplete();
        }).catch(function(error){
            if (saveSlideStatus) {
                saveSlideStatus.textContent = error.message || t('slideCompletionError', 'We could not complete your refresher slides. Please try again.');
            }

            if (nextBtn) {
                nextBtn.disabled = false;
            }

            if (saveSlideBtn) {
                saveSlideBtn.disabled = false;
            }
        }).finally(function(){
            isCompletingSlides = false;
        });
    }


    function render(resultData){
        if (isSlideOnlyRefresher) {
            renderSlideOnlyComplete();
            return;
        }

        updateRefresherSectionCopy(false);
        if (isSubmitted && !resultData) {
            resultData = {
                passed: runtime.attempt && runtime.attempt.status === 0,
                score: runtime.attempt && typeof runtime.attempt.score === 'number' ? runtime.attempt.score : 0,
                passThreshold: runtime.quiz.passThreshold || 75,
                incorrectDetails: (runtime.attempt && Array.isArray(runtime.attempt.incorrectDetails)) ? runtime.attempt.incorrectDetails : []
            };
        }

        var questionBlocks = questions.map(function(question, index){
            return '<article class="teqcidb-class-quiz__question" data-question-id="' + esc(String(question.id)) + '">' +
                '<h3 class="teqcidb-class-quiz__question-title">' + esc(format(t('questionOf', 'Question %1$s of %2$s'), String(index + 1), String(totalQuestions))) + '</h3>' +
                '<div class="teqcidb-class-quiz__prompt">' + (question.prompt || '') + '</div>' +
                '<div class="teqcidb-class-quiz__choices">' + buildChoicesHtml(question, { disabled: !!isSubmitted }) + '</div>' +
            '</article>';
        }).join('');

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

            root.innerHTML = '<div class="teqcidb-class-quiz is-submitted">' +
                '<div class="teqcidb-class-quiz__result">' +
                    '<h3>' + esc(resultData.passed ? (i18n.passed || 'Passed') : (i18n.failed || 'Failed')) + '</h3>' +
                    passedMessage +
                    '<p>' + esc(format(t('scoreSummary', 'Score: %1$s% (Required: %2$s%)'), String(resultData.score), String(resultData.passThreshold))) + '</p>' +
                    (hideIncorrectDetails ? '' : buildIncorrectHtml(resultData.incorrectDetails || [])) +
                '</div>' +
                '<div class="teqcidb-class-quiz__submitted-questions">' + questionBlocks + '</div>' +
            '</div>';
            return;
        }

        root.innerHTML = '<div class="teqcidb-class-quiz">' +
            questionBlocks +
            '<div class="teqcidb-class-quiz__actions">' +
                '<button type="button" class="teqcidb-button teqcidb-button-primary" id="teqcidb-quiz-submit">' + esc(t('submitQuiz', 'Submit Quiz')) + '</button>' +
                (runtime.quiz.classType === 'initial'
                    ? '<button type="button" class="teqcidb-button teqcidb-button-secondary" id="teqcidb-quiz-save-progress">' + esc(t('saveProgress', 'Save Progress')) + '</button>'
                    : '') +
            '</div>' +
            '<div class="teqcidb-class-quiz__error" id="teqcidb-quiz-error" aria-live="polite"></div>' +
        '</div>';

        bindChoiceEvents();
        bindSaveProgressButton();
        bindSubmitButton();
    }

    function parseAjaxResponse(payload){
        if (!payload || !payload.success) {
            throw new Error(getRequestErrorMessage(payload, i18n.submitError || 'Request failed.'));
        }
        return payload.data || {};
    }

    function parseRestResponse(payload){
        if (!payload || payload.ok !== true) {
            throw new Error(getRequestErrorMessage(payload, i18n.submitError || 'Request failed.'));
        }
        return payload;
    }

    function getRequestErrorMessage(payload, fallbackMessage){
        var payloadStatus = payload && payload.data && payload.data.status ? parseInt(payload.data.status, 10) : 0;
        var payloadCode = payload && payload.code ? String(payload.code) : '';

        if (payloadStatus === 429 || payloadCode.indexOf('rate_limited') !== -1) {
            return 'Please wait a few seconds before saving again.';
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
            idempotency_token: idempotencyToken,
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
        formData.append('idempotency_token', idempotencyToken);
        formData.append('answers_json', JSON.stringify(answers || {}));
        return formData;
    }

    function mapAjaxQuizResponse(ajaxAction, data, failureMessage){
        var base = {
            ok: true,
            attempt_id: data.attemptId || attemptId || 0,
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
        var failureMessage = requestOptions.failureMessage || (i18n.submitError || 'Request failed.');
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

    function requestQuizSaveEndpoint(failureMessage){
        return requestQuizEndpoint({
            restPath: '/quiz/save',
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
                });
            });
        });
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
        var saveBtn = root.querySelector('#teqcidb-quiz-save-progress');
        var err = root.querySelector('#teqcidb-quiz-error');

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function(){
            if (isSubmitting || isSavingProgress) {
                return;
            }

            var firstUnansweredQuestion = getFirstUnansweredQuestionNumber();
            if (firstUnansweredQuestion > 0) {
                err.textContent = format(i18n.validationAllAnswersRequired || 'Please answer Question %1$s before submitting.', String(firstUnansweredQuestion));
                return;
            }

            err.textContent = '';
            btn.disabled = true;

            if (saveBtn) {
                saveBtn.disabled = true;
            }

            submitQuiz();
        });
    }

    function bindSaveProgressButton(){
        var saveBtn = root.querySelector('#teqcidb-quiz-save-progress');
        var submitBtn = root.querySelector('#teqcidb-quiz-submit');
        var err = root.querySelector('#teqcidb-quiz-error');

        if (!saveBtn) {
            return;
        }

        saveBtn.addEventListener('click', function(){
            if (isSubmitted || isSubmitting || isSavingProgress) {
                return;
            }

            isSavingProgress = true;
            saveBtn.disabled = true;

            if (submitBtn) {
                submitBtn.disabled = true;
            }

            err.textContent = '';

            requestQuizSaveEndpoint(i18n.saveProgressError || 'We could not save your progress. Please try again.').then(function(payload){
                attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
                err.textContent = payload.message || (i18n.saveProgressSuccess || 'Progress saved.');
            }).catch(function(error){
                err.textContent = error.message || (i18n.saveProgressError || 'We could not save your progress. Please try again.');
            }).finally(function(){
                isSavingProgress = false;
                saveBtn.disabled = false;

                if (submitBtn && !isSubmitted) {
                    submitBtn.disabled = false;
                }
            });
        });
    }

    function scrollToQuizTop(){
        var scrollTarget = Math.max(0, (root && root.offsetTop ? root.offsetTop : 0) - 24);

        if (window && typeof window.scrollTo === 'function') {
            window.scrollTo({
                top: scrollTarget,
                behavior: 'smooth'
            });
        }
    }

    function submitQuiz(){
        isSubmitting = true;

        requestQuizSubmitEndpoint(i18n.submitError || 'Submit failed.').then(function(payload){
                attemptId = parseInt(payload.attempt_id || attemptId || 0, 10) || 0;
                isSubmitted = true;
                render({
                    score: payload.score,
                    passThreshold: payload.passThreshold,
                    passed: payload.passed,
                    incorrectDetails: payload.incorrectDetails || []
                });
                scrollToQuizTop();
            }).catch(function(err){
                var errorEl = root.querySelector('#teqcidb-quiz-error');
                if (errorEl) {
                    errorEl.textContent = err.message || (i18n.submitError || 'Submit failed.');
                }
                var submitButton = root.querySelector('#teqcidb-quiz-submit');
                if (submitButton) {
                    submitButton.disabled = false;
                }

                var saveButton = root.querySelector('#teqcidb-quiz-save-progress');
                if (saveButton && !isSubmitted) {
                    saveButton.disabled = false;
                }
            }).finally(function(){
                isSubmitting = false;
            });
    }

    if (requiresSlidesFirst) {
        var savedSlideNumber = parseInt(initialSlideProgress.currentSlideNumber || 0, 10) || 0;
        var savedSlideIndex = savedSlideNumber > 0
            ? savedSlideNumber - 1
            : (parseInt(initialSlideProgress.currentIndex || 0, 10) || 0);

        slideIndex = Math.min(Math.max(0, savedSlideIndex), Math.max(0, slides.length - 1));
        markCurrentSlideAsViewed();
        preloadUpcomingSlides(slideIndex);
        renderSlides();
        return;
    }

    if (isSlideOnlyRefresher) {
        renderSlideOnlyComplete();
        return;
    }

    render();
})();
