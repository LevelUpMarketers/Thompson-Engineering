(function () {
    'use strict';

    var namespace = (window.TEQCIDB_AcceptHostedDebug = window.TEQCIDB_AcceptHostedDebug || {});
    namespace.DEBUG = true;

    var config = window.teqcidbAcceptHostedDebug || {};
    var iframeContextMap = {};
    var lastSubmittedIframeId = '';

    function nowIso() {
        return new Date().toISOString();
    }

    function log() {
        if (!namespace.DEBUG) {
            return;
        }

        var args = Array.prototype.slice.call(arguments);
        args.unshift('[TEQCIDB AcceptHosted DEBUG]');
        console.log.apply(console, args);
    }

    function logError() {
        if (!namespace.DEBUG) {
            return;
        }

        var args = Array.prototype.slice.call(arguments);
        args.unshift('[TEQCIDB AcceptHosted DEBUG]');
        console.error.apply(console, args);
    }

    function getRoot() {
        return document.querySelector('section[data-teqcidb-registration="true"]');
    }

    function parseCommunicatorQueryString(queryString) {
        var raw = typeof queryString === 'string' ? queryString : '';
        var pairs = raw.split('&');
        var result = {};

        pairs.forEach(function (pair) {
            if (!pair) {
                return;
            }

            var splitIndex = pair.indexOf('=');
            var rawKey = splitIndex >= 0 ? pair.slice(0, splitIndex) : pair;
            var rawValue = splitIndex >= 0 ? pair.slice(splitIndex + 1) : '';

            var key = rawKey;
            var value = rawValue;

            try {
                key = decodeURIComponent(rawKey || '');
            } catch (errKey) {
                key = rawKey || '';
            }

            try {
                value = decodeURIComponent(rawValue || '');
            } catch (errValue) {
                value = rawValue || '';
            }

            result[key] = value;
        });

        return result;
    }

    function redactResponseForLogging(data) {
        var clone = {};

        if (data && typeof data === 'object') {
            Object.keys(data).forEach(function (key) {
                clone[key] = data[key];
            });
        }

        if (clone && typeof clone.token === 'string') {
            var token = clone.token;
            var preview = token.length >= 8 ? token.slice(0, 4) + '...' + token.slice(-4) : token;

            delete clone.token;
            clone.token_redacted = true;
            clone.token_length = token.length;
            clone.token_preview = preview;
        }

        return clone;
    }

    function formatErrors(errors) {
        if (!Array.isArray(errors) || !errors.length) {
            return '';
        }

        return errors
            .map(function (item) {
                if (!item || typeof item !== 'object') {
                    return '';
                }

                var code = typeof item.code === 'string' ? item.code : '';
                var message = typeof item.text === 'string' ? item.text : '';

                if (!code && !message) {
                    return '';
                }

                if (!code) {
                    return message;
                }

                if (!message) {
                    return code;
                }

                return code + ': ' + message;
            })
            .filter(function (line) {
                return '' !== line;
            })
            .join(' | ');
    }

    function updatePanelHeight(paymentBlock, reason) {
        if (!paymentBlock) {
            return;
        }

        var panel = paymentBlock.closest('.teqcidb-registration-class-panel');
        if (!panel) {
            return;
        }

        panel.style.maxHeight = panel.scrollHeight + 'px';

        log('PANEL HEIGHT UPDATED', {
            timestamp_ms: Date.now(),
            timestamp_iso: nowIso(),
            reason: reason || 'unspecified',
            panel_id: panel.id || '',
            scroll_height: panel.scrollHeight,
            applied_max_height: panel.style.maxHeight
        });
    }

    function clearProcessingTimeout(context) {
        if (!context || !context.processingTimerId) {
            return;
        }

        window.clearTimeout(context.processingTimerId);
        context.processingTimerId = null;
    }

    function startProcessingTimeout(context) {
        if (!context) {
            return;
        }

        clearProcessingTimeout(context);

        context.processingTimerId = window.setTimeout(function () {
            if (context.receivedTerminalEvent) {
                return;
            }

            if (context.statusEl) {
                context.statusEl.textContent = 'Still processing - check Authorize.net transaction list. If it succeeded, we may be missing transactResponse handling.';
            }

            updatePanelHeight(context.paymentBlock, 'processing_timeout');

            log('PROCESSING TIMEOUT', {
                timestamp_ms: Date.now(),
                timestamp_iso: nowIso(),
                iframe_id: context.iframeId || '',
                class_id: context.classId || '',
                invoice_number: context.invoiceNumber || ''
            });
        }, 60000);
    }

    function getContextForMessage() {
        if (lastSubmittedIframeId && iframeContextMap[lastSubmittedIframeId]) {
            return iframeContextMap[lastSubmittedIframeId];
        }

        var keys = Object.keys(iframeContextMap);
        if (!keys.length) {
            return null;
        }

        return iframeContextMap[keys[keys.length - 1]] || null;
    }

    function extractTransactionSummary(responseObject) {
        var summary = {
            transId: '',
            responseCode: '',
            resultCode: '',
            invoiceNumber: '',
            errors: [],
            topLevelKeys: []
        };

        if (!responseObject || typeof responseObject !== 'object') {
            return summary;
        }

        summary.topLevelKeys = Object.keys(responseObject);

        var transactionResponse = responseObject.transactionResponse || {};
        var messages = responseObject.messages || {};
        var order = responseObject.order || {};

        summary.transId = transactionResponse.transId || responseObject.transId || '';
        summary.responseCode = transactionResponse.responseCode || responseObject.responseCode || '';
        summary.resultCode = messages.resultCode || responseObject.resultCode || '';
        summary.invoiceNumber = order.invoiceNumber || responseObject.invoiceNumber || '';

        if (Array.isArray(transactionResponse.errors)) {
            summary.errors = transactionResponse.errors.map(function (err) {
                return {
                    code: err && (err.errorCode || err.code || ''),
                    text: err && (err.errorText || err.text || '')
                };
            });
        } else if (transactionResponse.errors && Array.isArray(transactionResponse.errors.error)) {
            summary.errors = transactionResponse.errors.error.map(function (err) {
                return {
                    code: err && (err.errorCode || err.code || ''),
                    text: err && (err.errorText || err.text || '')
                };
            });
        }

        return summary;
    }

    function determineSuccess(summary) {
        var responseCode = String(summary.responseCode || '');
        var resultCode = String(summary.resultCode || '').toUpperCase();
        return responseCode === '1' || resultCode === 'OK';
    }

    function bestErrorMessage(summary) {
        if (summary && Array.isArray(summary.errors) && summary.errors.length) {
            var firstError = summary.errors[0];
            if (firstError) {
                var code = firstError.code || '';
                var text = firstError.text || '';
                if (code && text) {
                    return code + ': ' + text;
                }
                if (text) {
                    return text;
                }
                if (code) {
                    return code;
                }
            }
        }

        if (summary && summary.resultCode) {
            return 'Result: ' + summary.resultCode;
        }

        if (summary && summary.responseCode) {
            return 'Response code: ' + summary.responseCode;
        }

        return 'Unknown error.';
    }

    function registerGlobalReceiver() {
        window.AuthorizeNetIFrame = window.AuthorizeNetIFrame || {};

        window.AuthorizeNetIFrame.onReceiveCommunication = function (queryString) {
            try {
                var payload = parseCommunicatorQueryString(queryString);
                var action = payload.action || '';
                var context = getContextForMessage();

                log('COMMUNICATION RECEIVED', {
                    timestamp_ms: Date.now(),
                    timestamp_iso: nowIso(),
                    action: action,
                    raw_query_length: (queryString || '').length,
                    payload_keys: Object.keys(payload),
                    width: payload.width || '',
                    height: payload.height || '',
                    has_response: typeof payload.response === 'string' && payload.response.length > 0,
                    target_iframe_id: context ? context.iframeId : ''
                });

                if (!context) {
                    logError('No iframe context found for communicator message', {
                        action: action,
                        raw_query_length: (queryString || '').length
                    });
                    return;
                }

                if (action === 'resizeWindow') {
                    var heightValue = parseInt(payload.height, 10);
                    if (Number.isNaN(heightValue)) {
                        heightValue = 0;
                    }

                    var appliedHeight = Math.max(500, heightValue);

                    if (context.iframeEl) {
                        context.iframeEl.style.height = appliedHeight + 'px';
                    }

                    updatePanelHeight(context.paymentBlock, 'communicator_resizeWindow');

                    log('RESIZE WINDOW HANDLED', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        iframe_id: context.iframeId || '',
                        class_id: context.classId || '',
                        width: payload.width || '',
                        height_raw: payload.height || '',
                        height_applied: appliedHeight
                    });

                    return;
                }

                if (action === 'cancel') {
                    context.receivedTerminalEvent = true;
                    clearProcessingTimeout(context);

                    if (context.statusEl) {
                        context.statusEl.textContent = 'Payment cancelled.';
                    }

                    if (context.buttonEl) {
                        context.buttonEl.disabled = false;
                    }

                    updatePanelHeight(context.paymentBlock, 'communicator_cancel');

                    log('CANCEL HANDLED', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        iframe_id: context.iframeId || '',
                        class_id: context.classId || ''
                    });

                    return;
                }

                if (action === 'transactResponse') {
                    var responseRaw = payload.response || '';
                    var responseObject = null;

                    try {
                        responseObject = JSON.parse(responseRaw);
                    } catch (transactParseError) {
                        logError('transactResponse JSON parse failed', {
                            timestamp_ms: Date.now(),
                            timestamp_iso: nowIso(),
                            iframe_id: context.iframeId || '',
                            class_id: context.classId || '',
                            error_message: transactParseError && transactParseError.message ? transactParseError.message : transactParseError,
                            response_length: responseRaw.length
                        });

                        if (context.statusEl) {
                            context.statusEl.textContent = 'Payment response received but could not be parsed.';
                        }

                        if (context.buttonEl) {
                            context.buttonEl.disabled = false;
                        }

                        updatePanelHeight(context.paymentBlock, 'communicator_transact_parse_error');
                        return;
                    }

                    var summary = extractTransactionSummary(responseObject);
                    var isSuccess = determineSuccess(summary);

                    context.receivedTerminalEvent = true;
                    clearProcessingTimeout(context);

                    log('TRANSACT RESPONSE (SAFE)', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        iframe_id: context.iframeId || '',
                        class_id: context.classId || '',
                        invoice_number: summary.invoiceNumber || context.invoiceNumber || '',
                        transId: summary.transId || '',
                        responseCode: summary.responseCode || '',
                        resultCode: summary.resultCode || '',
                        errors: summary.errors,
                        top_level_keys: summary.topLevelKeys
                    });

                    if (context.statusEl) {
                        if (isSuccess) {
                            context.statusEl.textContent = 'Payment complete. Transaction ID: ' + (summary.transId || 'N/A');
                        } else {
                            context.statusEl.textContent = 'Payment failed: ' + bestErrorMessage(summary);
                        }
                    }

                    if (context.buttonEl) {
                        if (isSuccess) {
                            context.buttonEl.disabled = true;
                            context.buttonEl.textContent = 'Paid';
                        } else {
                            context.buttonEl.disabled = false;
                        }
                    }

                    updatePanelHeight(context.paymentBlock, 'communicator_transactResponse');
                    return;
                }

                log('COMMUNICATION ACTION UNHANDLED', {
                    timestamp_ms: Date.now(),
                    timestamp_iso: nowIso(),
                    action: action,
                    iframe_id: context ? context.iframeId : '',
                    class_id: context ? context.classId : ''
                });
            } catch (receiveError) {
                logError('RECEIVE ERROR', {
                    timestamp_ms: Date.now(),
                    timestamp_iso: nowIso(),
                    error_message: receiveError && receiveError.message ? receiveError.message : receiveError,
                    error_object: receiveError
                });
            }
        };

        log('AuthorizeNetIFrame receiver registered', {
            timestamp_ms: Date.now(),
            timestamp_iso: nowIso()
        });
    }

    function init() {
        var root = getRoot();

        if (!root) {
            log('INIT: registration root not found; skipping Accept Hosted debug initialization.');
            return;
        }

        registerGlobalReceiver();

        var nonce = root.getAttribute('data-anet-token-nonce') || '';
        var environment = root.getAttribute('data-authorizenet-environment') || '';

        log('INIT:', {
            timestamp_ms: Date.now(),
            timestamp_iso: nowIso(),
            environment: environment,
            nonce_present: nonce.length > 0,
            nonce_length: nonce.length,
            ajax_url_present: !!config.ajax_url
        });

        root.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-teqcidb-pay-button="1"]');

            if (!button || !root.contains(button)) {
                return;
            }

            event.preventDefault();

            var clickStartMs = Date.now();
            var classId = button.getAttribute('data-class-id') || '';
            var targetFormId = button.getAttribute('data-target-form') || '';
            var targetIframeId = button.getAttribute('data-target-iframe') || '';
            var paymentBlock = button.closest('.teqcidb-registration-class-payment');
            var statusEl = paymentBlock ? paymentBlock.querySelector('.teqcidb-registration-anet-status') : null;
            var formEl = targetFormId ? document.getElementById(targetFormId) : null;
            var iframeEl = targetIframeId ? document.getElementById(targetIframeId) : null;

            log('CLICK START', {
                timestamp_ms: clickStartMs,
                timestamp_iso: nowIso(),
                class_id: classId,
                target_form_id: targetFormId,
                target_iframe_id: targetIframeId,
                environment: environment,
                nonce_present: nonce.length > 0,
                nonce_length: nonce.length,
                dom_found: {
                    payment_block: !!paymentBlock,
                    status: !!statusEl,
                    form: !!formEl,
                    iframe: !!iframeEl
                }
            });

            if (!statusEl) {
                logError('Status element not found for class_id', classId);
                return;
            }

            button.disabled = true;
            statusEl.textContent = 'Loading secure payment form...';
            updatePanelHeight(paymentBlock, 'click_start_loading');

            var params = new URLSearchParams();
            params.set('action', 'teqcidb_anet_get_token');
            params.set('class_id', classId);
            params.set('nonce', nonce);

            var fetchStartMs = Date.now();
            log('FETCH START', {
                timestamp_ms: fetchStartMs,
                timestamp_iso: nowIso(),
                class_id: classId,
                ajax_url: config.ajax_url || ''
            });

            fetch(config.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: params.toString(),
                credentials: 'same-origin'
            })
                .then(function (response) {
                    var elapsed = Date.now() - fetchStartMs;
                    log('FETCH RESPONSE', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        status: response.status,
                        ok: response.ok,
                        elapsed_ms: elapsed
                    });

                    return response.text().then(function (responseText) {
                        var parsed;

                        try {
                            parsed = JSON.parse(responseText);
                        } catch (parseError) {
                            logError('JSON PARSE ERROR', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                elapsed_ms: Date.now() - clickStartMs,
                                response_text: responseText,
                                error_message: parseError && parseError.message ? parseError.message : parseError
                            });

                            statusEl.textContent = 'Unable to read payment server response. Please try again.';
                            button.disabled = false;

                            log('CLICK END (ERROR)', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                elapsed_ms: Date.now() - clickStartMs,
                                reason: 'json_parse_error'
                            });

                            throw parseError;
                        }

                        log('AJAX RESPONSE (REDACTED)', redactResponseForLogging(parsed));

                        if (!parsed || parsed.success !== true || typeof parsed.token !== 'string' || !parsed.token.length) {
                            var errorMessage = (parsed && parsed.message) ? parsed.message : 'Unable to load secure payment form. Please try again.';
                            var errorDetails = parsed && parsed.errors ? formatErrors(parsed.errors) : '';
                            var combinedMessage = errorDetails ? errorMessage + ' ' + errorDetails : errorMessage;

                            logError('AJAX APPLICATION ERROR', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                elapsed_ms: Date.now() - clickStartMs,
                                response: redactResponseForLogging(parsed)
                            });

                            statusEl.textContent = combinedMessage;
                            button.disabled = false;
                            updatePanelHeight(paymentBlock, 'application_error');

                            log('CLICK END (ERROR)', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                elapsed_ms: Date.now() - clickStartMs,
                                reason: 'application_error'
                            });

                            return;
                        }

                        var hostedPaymentUrl = environment === 'live'
                            ? 'https://accept.authorize.net/payment/payment'
                            : 'https://test.authorize.net/payment/payment';

                        log('HOSTED PAYMENT URL CHOSEN', {
                            timestamp_ms: Date.now(),
                            timestamp_iso: nowIso(),
                            class_id: classId,
                            environment: environment,
                            hosted_payment_url: hostedPaymentUrl
                        });

                        formEl = targetFormId ? document.getElementById(targetFormId) : null;
                        iframeEl = targetIframeId ? document.getElementById(targetIframeId) : null;

                        if (!formEl || !iframeEl) {
                            logError('Required payment elements missing before submit', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                form_found: !!formEl,
                                iframe_found: !!iframeEl,
                                target_form_id: targetFormId,
                                target_iframe_id: targetIframeId
                            });

                            statusEl.textContent = 'Payment form elements were not found. Please refresh and try again.';
                            button.disabled = false;

                            log('CLICK END (ERROR)', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                elapsed_ms: Date.now() - clickStartMs,
                                reason: 'missing_form_or_iframe'
                            });

                            return;
                        }

                        var tokenInput = formEl.querySelector('input[name="token"]');

                        if (!tokenInput) {
                            logError('Hidden token input missing from target form', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                target_form_id: targetFormId
                            });

                            statusEl.textContent = 'Payment form setup is incomplete. Please refresh and try again.';
                            button.disabled = false;

                            log('CLICK END (ERROR)', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                elapsed_ms: Date.now() - clickStartMs,
                                reason: 'missing_token_input'
                            });

                            return;
                        }

                        var context = {
                            classId: classId,
                            invoiceNumber: parsed.invoiceNumber || '',
                            iframeId: targetIframeId,
                            formId: targetFormId,
                            statusEl: statusEl,
                            buttonEl: button,
                            paymentBlock: paymentBlock,
                            iframeEl: iframeEl,
                            formEl: formEl,
                            receivedTerminalEvent: false,
                            processingTimerId: null
                        };

                        iframeContextMap[targetIframeId] = context;
                        lastSubmittedIframeId = targetIframeId;

                        iframeEl.addEventListener('load', function () {
                            var iframeLocation = 'inaccessible';

                            try {
                                iframeLocation = iframeEl.contentWindow && iframeEl.contentWindow.location
                                    ? iframeEl.contentWindow.location.href
                                    : 'unavailable';
                            } catch (locationError) {
                                iframeLocation = 'cross-origin_or_inaccessible';
                            }

                            updatePanelHeight(paymentBlock, 'iframe_load');
                            startProcessingTimeout(context);

                            log('IFRAME LOAD', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                target_iframe_id: targetIframeId,
                                iframe_location: iframeLocation,
                                elapsed_ms: Date.now() - clickStartMs
                            });
                        }, { once: true });

                        iframeEl.addEventListener('error', function (iframeErrorEvent) {
                            logError('IFRAME ERROR', {
                                timestamp_ms: Date.now(),
                                timestamp_iso: nowIso(),
                                class_id: classId,
                                target_iframe_id: targetIframeId,
                                elapsed_ms: Date.now() - clickStartMs,
                                event: iframeErrorEvent
                            });
                        }, { once: true });

                        formEl.action = hostedPaymentUrl;
                        tokenInput.value = parsed.token;

                        iframeEl.hidden = false;
                        formEl.hidden = false;
                        iframeEl.style.width = '100%';

                        updatePanelHeight(paymentBlock, 'iframe_revealed_before_submit');

                        if (typeof ResizeObserver !== 'undefined') {
                            var resizeObserver = new ResizeObserver(function () {
                                updatePanelHeight(paymentBlock, 'iframe_resize_observer');
                            });
                            resizeObserver.observe(iframeEl);
                        }

                        formEl.submit();

                        var resizeAttempts = 0;
                        var resizeInterval = window.setInterval(function () {
                            resizeAttempts += 1;
                            updatePanelHeight(paymentBlock, 'post_submit_interval_' + resizeAttempts);

                            if (resizeAttempts >= 15) {
                                window.clearInterval(resizeInterval);
                            }
                        }, 400);

                        log('FORM SUBMITTED', {
                            timestamp_ms: Date.now(),
                            timestamp_iso: nowIso(),
                            class_id: classId,
                            target_form_id: targetFormId,
                            target_iframe_id: targetIframeId,
                            invoice_number: parsed.invoiceNumber || '',
                            elapsed_ms: Date.now() - clickStartMs
                        });

                        statusEl.textContent = 'Secure payment form loaded.';
                    });
                })
                .catch(function (error) {
                    if (error instanceof SyntaxError) {
                        return;
                    }

                    logError('NETWORK/FETCH ERROR', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        class_id: classId,
                        elapsed_ms: Date.now() - clickStartMs,
                        error_message: error && error.message ? error.message : error,
                        error_object: error
                    });

                    statusEl.textContent = 'Unable to load secure payment form right now. Please try again.';
                    button.disabled = false;
                    updatePanelHeight(paymentBlock, 'network_error');

                    log('CLICK END (ERROR)', {
                        timestamp_ms: Date.now(),
                        timestamp_iso: nowIso(),
                        class_id: classId,
                        elapsed_ms: Date.now() - clickStartMs,
                        reason: 'network_error'
                    });
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
