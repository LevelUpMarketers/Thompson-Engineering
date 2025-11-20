jQuery(document).ready(function($){
    function handleForm(selector, action){
        $(selector).on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = $form.serialize();
            var spinnerHideTimer = $form.data('spinnerHideTimer');
            var $spinner = $form.find('.teqcidb-feedback-area .spinner').first();
            var $feedback = $form.find('.teqcidb-feedback-area [role="status"]').first();
            if ($feedback.length) {
                $feedback.removeClass('is-visible').text('');
            }
            if (spinnerHideTimer) {
                clearTimeout(spinnerHideTimer);
            }
            $spinner.addClass('is-active');
            $.post(teqcidbAjax.ajaxurl, data + '&action=' + action + '&_ajax_nonce=' + teqcidbAjax.nonce)
                .done(function(response){
                    if ($feedback.length && response && response.data) {
                        var message = response.data.message || response.data.error;
                        if (message) {
                            $feedback.text(message).addClass('is-visible');
                        }
                    }
                })
                .fail(function(){
                    if ($feedback.length && teqcidbAdmin.error) {
                        $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                    }
                })
                .always(function(){
                    spinnerHideTimer = setTimeout(function(){
                        $spinner.removeClass('is-active');
                    }, 150);
                    $form.data('spinnerHideTimer', spinnerHideTimer);
                });
        });
    }
    handleForm('#teqcidb-create-form','teqcidb_save_student');
    handleForm('#teqcidb-general-settings-form','teqcidb_save_general_settings');
    handleForm('#teqcidb-style-settings-form','teqcidb_save_student');
    handleForm('.teqcidb-api-settings__form','teqcidb_save_api_settings');
    handleForm('#teqcidb-legacy-upload-form','teqcidb_upload_legacy_student');

    function extractPhoneDigits(value){
        var digits = (value || '').replace(/\D/g, '');

        if (digits.length > 10 && digits.charAt(0) === '1'){
            digits = digits.substring(1);
        }

        return digits.substring(0, 10);
    }

    function formatDigitsAsPhone(digits){
        if (!digits){
            return '';
        }

        var area = digits.substring(0, 3);
        var prefix = digits.substring(3, 6);
        var line = digits.substring(6, 10);
        var formatted = '';

        if (area){
            formatted = '(' + area;

            if (area.length === 3){
                formatted += ')';
            }
        }

        if (prefix){
            formatted += area.length === 3 ? ' ' + prefix : prefix;

            if (prefix.length === 3 && line){
                formatted += '-';
            }
        }

        if (line){
            formatted += line;
        }

        return formatted;
    }

    function maskPhoneInputs(){
        var selector = 'input[name="phone_cell"], input[name="phone_office"], input[name="representative_phone"], input[name="fax"]';

        var formatInput = function(input){
            if (!input){
                return;
            }

            var digits = extractPhoneDigits(input.value);
            input.value = formatDigitsAsPhone(digits);
        };

        $(document).on('input change blur', selector, function(){
            formatInput(this);
        });

        $(selector).each(function(){
            formatInput(this);
        });
    }

    maskPhoneInputs();

    function handleLogActionForms(){
        $('.teqcidb-log-actions__form').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var ajaxAction = $form.data('ajaxAction');

            if (!ajaxAction){
                return;
            }

            var serialized = $form.serialize();
            var spinnerHideTimer = $form.data('spinnerHideTimer');
            var $spinner = $form.find('.teqcidb-feedback-area .spinner').first();
            var $feedback = $form.find('.teqcidb-feedback-area [role="status"]').first();
            var logAction = $form.data('logAction');
            var targetSelector = $form.data('logTarget');

            if ($feedback.length){
                $feedback.removeClass('is-visible').text('');
            }

            if (spinnerHideTimer){
                clearTimeout(spinnerHideTimer);
            }

            if ($spinner.length){
                $spinner.addClass('is-active');
            }

            $.post(teqcidbAjax.ajaxurl, serialized + '&action=' + ajaxAction + '&_ajax_nonce=' + teqcidbAjax.nonce)
                .done(function(response){
                    var message = '';
                    var wasSuccessful = response && response.success;

                    if (response && response.data){
                        if (response.data.message){
                            message = response.data.message;
                        } else if (response.data.error){
                            message = response.data.error;
                        }

                        if (wasSuccessful && targetSelector && typeof response.data.content !== 'undefined'){
                            var $target = $(targetSelector);

                            if ($target.length){
                                $target.val(response.data.content);
                            }
                        }

                        if (wasSuccessful && logAction === 'download'){
                            var filename = response.data.filename || 'teqcidb-log.txt';
                            var content = typeof response.data.content === 'string' ? response.data.content : '';
                            var blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                            var url = window.URL.createObjectURL(blob);
                            var link = document.createElement('a');
                            link.href = url;
                            link.download = filename;
                            document.body.appendChild(link);
                            link.click();
                            setTimeout(function(){
                                document.body.removeChild(link);
                                window.URL.revokeObjectURL(url);
                            }, 100);

                            if (!message && teqcidbAdmin.logDownloadReady){
                                message = teqcidbAdmin.logDownloadReady;
                            }
                        }
                    }

                    if ($feedback.length){
                        if (message){
                            $feedback.text(message).addClass('is-visible');
                        } else if (!wasSuccessful && teqcidbAdmin.error){
                            $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                        }
                    }
                })
                .fail(function(){
                    if ($feedback.length && teqcidbAdmin.error){
                        $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                    }
                })
                .always(function(){
                    spinnerHideTimer = setTimeout(function(){
                        if ($spinner.length){
                            $spinner.removeClass('is-active');
                        }
                    }, 150);
                    $form.data('spinnerHideTimer', spinnerHideTimer);
                });
        });
    }

    handleLogActionForms();

    function formatString(template){
        if (typeof template !== 'string') {
            return '';
        }

        var args = Array.prototype.slice.call(arguments, 1);
        var usedIndexes = {};
        var result = template.replace(/%(\d+)\$s/g, function(match, number){
            var index = parseInt(number, 10) - 1;

            if (typeof args[index] === 'undefined') {
                usedIndexes[index] = true;
                return '';
            }

            usedIndexes[index] = true;
            return args[index];
        });

        var sequentialIndex = 0;

        return result.replace(/%s/g, function(){
            while (usedIndexes[sequentialIndex]) {
                sequentialIndex++;
            }

            var value = typeof args[sequentialIndex] !== 'undefined' ? args[sequentialIndex] : '';
            usedIndexes[sequentialIndex] = true;
            sequentialIndex++;
            return value;
        });
    }

    $(document).on('click','.teqcidb-upload',function(e){
        e.preventDefault();
        var target=$(this).data('target');
        var frame=wp.media({title:teqcidbAdmin.mediaTitle,button:{text:teqcidbAdmin.mediaButton},multiple:false});
        frame.on('select',function(){
            var attachment=frame.state().get('selection').first().toJSON();
            $(target).val(attachment.id);
            $(target+'-preview').html('<img src="'+attachment.url+'" style="max-width:100px;height:auto;" />');
        });
        frame.open();
    });

    if($('#teqcidb-entity-list').length){
        var $entityTableBody = $('#teqcidb-entity-list');
        var perPage = parseInt($entityTableBody.data('per-page'), 10) || 20;
        var columnCount = parseInt($entityTableBody.data('column-count'), 10) || 6;
        var $pagination = $('#teqcidb-entity-pagination');
        var $paginationContainer = $pagination.closest('.tablenav');
        var $entityFeedback = $('#teqcidb-entity-feedback');
        var $searchForm = $('#teqcidb-student-search');
        var $searchSpinner = $('#teqcidb-entity-search-spinner');
        var $searchFeedback = $('#teqcidb-entity-search-feedback');
        var $clearSearchButton = $('#teqcidb-entity-search-clear');
        var placeholderMap = teqcidbAdmin.placeholderMap || {};
        var placeholderList = Array.isArray(teqcidbAdmin.placeholders) ? teqcidbAdmin.placeholders : [];
        var entityFields = Array.isArray(teqcidbAdmin.entityFields) ? teqcidbAdmin.entityFields : [];
        var pendingFeedbackMessage = '';
        var currentPage = 1;
        var emptyValue = '—';
        var currentFilters = {
            placeholder_1: '',
            placeholder_2: '',
            placeholder_3: ''
        };

        if ($entityFeedback.length){
            $entityFeedback.hide().removeClass('is-visible');
        }

        if ($paginationContainer.length){
            $paginationContainer.hide();
        }

        function clearFeedback(){
            if ($entityFeedback.length){
                $entityFeedback.text('').hide().removeClass('is-visible');
            }
        }

        function clearSearchFeedback(){
            if ($searchFeedback.length){
                $searchFeedback.removeClass('is-visible').text('');
            }
        }

        function showSearchFeedback(message){
            if (!$searchFeedback.length){
                return;
            }

            if (message){
                $searchFeedback.text(message).addClass('is-visible');
            } else {
                clearSearchFeedback();
            }
        }

        function setSearchLoading(isLoading){
            if (!$searchSpinner.length){
                return;
            }

            if (isLoading){
                $searchSpinner.addClass('is-active');
            } else {
                $searchSpinner.removeClass('is-active');
            }
        }

        function isSearchActive(){
            return Object.keys(currentFilters).some(function(key){
                var value = currentFilters[key];

                return typeof value === 'string' && value.trim() !== '';
            });
        }

        function showFeedback(message){
            if (!$entityFeedback.length){
                return;
            }

            if (message){
                $entityFeedback.text(message).show().addClass('is-visible');
            } else {
                clearFeedback();
            }
        }

        function getPlaceholderLabel(index){
            var mapKey = 'placeholder_' + index;

            if (Object.prototype.hasOwnProperty.call(placeholderMap, mapKey) && placeholderMap[mapKey]){
                return placeholderMap[mapKey];
            }

            if (placeholderList.length >= index){
                return placeholderList[index - 1];
            }

            return 'Placeholder ' + index;
        }

        function formatValue(value){
            if (value === null || typeof value === 'undefined' || value === ''){
                return emptyValue;
            }

            return String(value);
        }

        function getFieldValue(entity, key){
            if (!entity || typeof entity !== 'object'){
                return '';
            }

            if (Object.prototype.hasOwnProperty.call(entity, key) && entity[key] !== null && typeof entity[key] !== 'undefined'){
                return entity[key];
            }

            return '';
        }

        function parseItemsValue(value){
            if (Array.isArray(value)){
                return value;
            }

            if (!value || value === ''){
                return [];
            }

            if (typeof value === 'string'){
                try {
                    var parsed = JSON.parse(value);

                    if (Array.isArray(parsed)){
                        return parsed;
                    }
                } catch (err) {
                    // Ignore JSON parse errors and fall back to splitting.
                }

                return value.split(/\r?\n/).filter(function(item){
                    return item !== '';
                });
            }

            return [];
        }

        function appendFieldInput($container, field, value, entity, entityId){
            var type = field.type || 'text';
            var fieldName = field.name;
            var stringValue = value === null || typeof value === 'undefined' ? '' : value;
            var baseId = fieldName + '-' + entityId;
            var addAnotherLabel = teqcidbAdmin.addAnotherItem || '+ Add Another Item';

            switch (type){
                case 'select':
                    var options = field.options || {};
                    var $select = $('<select/>', { name: fieldName });
                    Object.keys(options).forEach(function(optionValue){
                        var optionLabel = options[optionValue];
                        var $option = $('<option/>', { value: optionValue, text: optionLabel });

                        if (optionValue === ''){
                            $option.prop('disabled', true);

                            if (!stringValue){
                                $option.prop('selected', true);
                            }
                        } else if (String(stringValue) === String(optionValue)){
                            $option.prop('selected', true);
                        }

                        $select.append($option);
                    });
                    $container.append($select);
                    break;
                case 'state':
                    var states = Array.isArray(field.options) ? field.options : [];
                    var $stateSelect = $('<select/>', { name: fieldName });
                    var placeholderOption = $('<option/>', {
                        value: '',
                        text: teqcidbAdmin.makeSelection || ''
                    }).prop('disabled', true);

                    if (!stringValue){
                        placeholderOption.prop('selected', true);
                    }

                    $stateSelect.append(placeholderOption);

                    states.forEach(function(stateValue){
                        var $stateOption = $('<option/>', { value: stateValue, text: stateValue });

                        if (String(stateValue) === String(stringValue)){
                            $stateOption.prop('selected', true);
                        }

                        $stateSelect.append($stateOption);
                    });

                    $container.append($stateSelect);
                    break;
                case 'radio':
                    var radioOptions = field.options || {};

                    Object.keys(radioOptions).forEach(function(optionValue){
                        var option = radioOptions[optionValue] || {};
                        var $label = $('<label/>', { 'class': 'teqcidb-radio-option' });
                        var $input = $('<input/>', {
                            type: 'radio',
                            name: fieldName,
                            value: optionValue
                        });

                        if (String(optionValue) === String(stringValue)){
                            $input.prop('checked', true);
                        }

                        $label.append($input);
                        $label.append(' ');
                        $label.append($('<span/>', {
                            'class': 'teqcidb-tooltip-icon dashicons dashicons-editor-help',
                            'data-tooltip': option.tooltip || ''
                        }));
                        $label.append(document.createTextNode(option.label || ''));
                        $container.append($label);
                    });
                    break;
                case 'opt_in':
                    var optInOptions = Array.isArray(field.options) ? field.options : [];
                    var $fieldset = $('<fieldset/>');

                    optInOptions.forEach(function(option){
                        var optionName = option.name || '';
                        var isChecked = entity && (entity[optionName] === '1' || entity[optionName] === 1 || entity[optionName] === true);
                        var $label = $('<label/>', { 'class': 'teqcidb-opt-in-option' });
                        var $input = $('<input/>', {
                            type: 'checkbox',
                            name: optionName,
                            value: '1'
                        });

                        if (isChecked){
                            $input.prop('checked', true);
                        }

                        $label.append($input);
                        $label.append(' ');
                        $label.append($('<span/>', {
                            'class': 'teqcidb-tooltip-icon dashicons dashicons-editor-help',
                            'data-tooltip': option.tooltip || ''
                        }));
                        $label.append(document.createTextNode(option.label || ''));
                        $fieldset.append($label);
                    });

                    $container.append($fieldset);
                    break;
                case 'checkboxes':
                    var checkboxOptions = field.options || {};
                    var selectedValues = parseItemsValue(stringValue).map(function(item){
                        return String(item);
                    });
                    var $checkboxFieldset = $('<fieldset/>', { 'class': 'teqcidb-checkbox-group' });

                    Object.keys(checkboxOptions).forEach(function(optionValue){
                        var optionLabel = checkboxOptions[optionValue];
                        var optionId = baseId + '-' + String(optionValue).toLowerCase().replace(/[^a-z0-9]+/g, '-');
                        var $label = $('<label/>', { 'class': 'teqcidb-checkbox-option', 'for': optionId });
                        var $input = $('<input/>', {
                            type: 'checkbox',
                            id: optionId,
                            name: fieldName + '[]',
                            value: optionValue
                        });

                        if (selectedValues.indexOf(String(optionValue)) !== -1){
                            $input.prop('checked', true);
                        }

                        $label.append($input);
                        $label.append(' ');
                        $label.append(document.createTextNode(optionLabel || ''));
                        $checkboxFieldset.append($label);
                    });

                    $container.append($checkboxFieldset);
                    break;
                case 'items':
                    var containerId = baseId + '-container';
                    var $itemsContainer = $('<div/>', {
                        id: containerId,
                        'class': 'teqcidb-items-container',
                        'data-placeholder': fieldName
                    });
                    var items = parseItemsValue(stringValue);

                    if (!items.length){
                        items = [''];
                    }

                    items.forEach(function(itemValue, index){
                        var $row = $('<div/>', {
                            'class': 'teqcidb-item-row',
                            style: 'margin-bottom:8px; display:flex; align-items:center;'
                        });
                        var placeholderText = teqcidbAdmin.itemPlaceholder ? formatString(teqcidbAdmin.itemPlaceholder, index + 1) : '';
                        var $input = $('<input/>', {
                            type: 'text',
                            name: fieldName + '[]',
                            'class': 'regular-text teqcidb-item-field',
                            placeholder: placeholderText,
                            value: itemValue
                        });
                        $row.append($input);
                        var $removeButton = $('<button/>', {
                            type: 'button',
                            'class': 'teqcidb-delete-item',
                            'aria-label': 'Remove',
                            style: 'background:none;border:none;cursor:pointer;margin-left:8px;'
                        }).append($('<span/>', { 'class': 'dashicons dashicons-no-alt' }));
                        $row.append($removeButton);
                        $itemsContainer.append($row);
                    });

                    $container.append($itemsContainer);

                    var $addButton = $('<button/>', {
                        type: 'button',
                        'class': 'button teqcidb-add-item',
                        'data-target': '#' + containerId,
                        'data-field-name': fieldName,
                        style: 'margin-top:8px;'
                    }).text(addAnotherLabel);

                    $container.append($addButton);
                    break;
                case 'textarea':
                    var $textareaField = $('<textarea/>', { name: fieldName }).text(stringValue);

                    if (field.attrs){
                        field.attrs.replace(/([\w-]+)="([^"]*)"/g, function(match, attrName, attrValue){
                            $textareaField.attr(attrName, attrValue);
                            return match;
                        });
                    }

                    $container.append($textareaField);
                    break;
                case 'image':
                    var inputId = baseId;
                    var $hidden = $('<input/>', {
                        type: 'hidden',
                        name: fieldName,
                        id: inputId,
                        value: stringValue
                    });
                    var $button = $('<button/>', {
                        type: 'button',
                        'class': 'button teqcidb-upload',
                        'data-target': '#' + inputId
                    }).text(teqcidbAdmin.mediaTitle);
                    var previewId = inputId + '-preview';
                    var $preview = $('<div/>', {
                        id: previewId,
                        style: 'margin-top:10px;'
                    });
                    var urlKey = fieldName + '_url';

                    if (entity && entity[urlKey]){
                        $preview.append($('<img/>', {
                            src: entity[urlKey],
                            alt: field.label || '',
                            style: 'max-width:100px;height:auto;'
                        }));
                    }

                    $container.append($hidden, $button, $preview);
                    break;
                case 'editor':
                    var editorId = baseId;
                    var $textarea = $('<textarea/>', {
                        name: fieldName,
                        id: editorId,
                        'class': 'teqcidb-editor-field'
                    }).val(stringValue);
                    $container.append($textarea);
                    break;
                default:
                    var $inputField = $('<input/>', {
                        type: type,
                        name: fieldName
                    }).val(stringValue);

                    if (field.attrs){
                        field.attrs.replace(/([\w-]+)="([^"]*)"/g, function(match, attrName, attrValue){
                            $inputField.attr(attrName, attrValue);
                            return match;
                        });
                    }

                    $container.append($inputField);
                    break;
            }
        }

        function buildEntityForm(entity){
            var entityId = entity && entity.id ? entity.id : 0;
            var $form = $('<form/>', {
                'class': 'teqcidb-entity-edit-form',
                'data-entity-id': entityId
            });
            var $flex = $('<div/>', { 'class': 'teqcidb-flex-form' });

            $form.append($('<input/>', { type: 'hidden', name: 'id', value: entityId }));
            $form.append($('<input/>', { type: 'hidden', name: 'name', value: entity && entity.name ? entity.name : '' }));

            entityFields.forEach(function(field){
                if (!field || !field.name){
                    return;
                }

                var value = getFieldValue(entity, field.name);
                var fieldClasses = 'teqcidb-field';

                if (field.fullWidth){
                    fieldClasses += ' teqcidb-field-full';
                }

                var $fieldWrapper = $('<div/>', { 'class': fieldClasses });
                var $label = $('<label/>');

                $label.append($('<span/>', {
                    'class': 'teqcidb-tooltip-icon dashicons dashicons-editor-help',
                    'data-tooltip': field.tooltip || ''
                }));
                $label.append(document.createTextNode(field.label || ''));
                $fieldWrapper.append($label);
                appendFieldInput($fieldWrapper, field, value, entity, entityId);
                $flex.append($fieldWrapper);
            });

            $form.append($flex);

            var $actions = $('<p/>', { 'class': 'teqcidb-entity__actions submit' });
            var $saveButton = $('<button/>', {
                type: 'submit',
                'class': 'button button-primary teqcidb-entity-save'
            }).text(teqcidbAdmin.saveChanges || 'Save Changes');
            var $deleteButton = $('<button/>', {
                type: 'button',
                'class': 'button button-secondary teqcidb-delete',
                'data-id': entityId
            }).text(teqcidbAdmin.delete);
            var $feedbackArea = $('<span/>', { 'class': 'teqcidb-feedback-area teqcidb-feedback-area--inline' });
            var $spinner = $('<span/>', { 'class': 'spinner teqcidb-entity-spinner', 'aria-hidden': 'true' });
            var $feedback = $('<span/>', { 'class': 'teqcidb-entity-feedback', 'role': 'status', 'aria-live': 'polite' });
            $feedbackArea.append($spinner).append($feedback);
            $actions.append($saveButton).append(' ').append($deleteButton).append($feedbackArea);
            $form.append($actions);

            return $form;
        }

        function updatePagination(total, totalPages, page){
            if (!$pagination.length){
                return;
            }

            if (!total || total <= 0){
                $pagination.empty();

                if ($paginationContainer.length){
                    $paginationContainer.hide();
                }

                return;
            }

            var totalPagesSafe = totalPages && totalPages > 0 ? totalPages : 1;
            var pageSafe = page && page > 0 ? page : 1;
            var html = '<span class="displaying-num">' + formatString(teqcidbAdmin.totalRecords, total) + '</span>';

            if (totalPagesSafe > 1){
                html += '<span class="pagination-links">';

                if (pageSafe > 1){
                    html += '<a class="first-page button teqcidb-entity-page" href="#" data-page="1"><span class="screen-reader-text">' + teqcidbAdmin.firstPage + '</span><span aria-hidden="true">&laquo;</span></a>';
                    html += '<a class="prev-page button teqcidb-entity-page" href="#" data-page="' + (pageSafe - 1) + '"><span class="screen-reader-text">' + teqcidbAdmin.prevPage + '</span><span aria-hidden="true">&lsaquo;</span></a>';
                } else {
                    html += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
                    html += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
                }

                html += '<span class="tablenav-paging-text">' + formatString(teqcidbAdmin.pageOf, pageSafe, totalPagesSafe) + '</span>';

                if (pageSafe < totalPagesSafe){
                    html += '<a class="next-page button teqcidb-entity-page" href="#" data-page="' + (pageSafe + 1) + '"><span class="screen-reader-text">' + teqcidbAdmin.nextPage + '</span><span aria-hidden="true">&rsaquo;</span></a>';
                    html += '<a class="last-page button teqcidb-entity-page" href="#" data-page="' + totalPagesSafe + '"><span class="screen-reader-text">' + teqcidbAdmin.lastPage + '</span><span aria-hidden="true">&raquo;</span></a>';
                } else {
                    html += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
                    html += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
                }

                html += '</span>';
            } else {
                html += '<span class="tablenav-paging-text">' + formatString(teqcidbAdmin.pageOf, pageSafe, totalPagesSafe) + '</span>';
            }

            $pagination.html(html);

            if ($paginationContainer.length){
                $paginationContainer.show();
            }
        }

        function renderEntities(data){
            var entities = data && Array.isArray(data.entities) ? data.entities : [];
            currentPage = data && data.page ? data.page : 1;
            var total = data && typeof data.total !== 'undefined' ? data.total : 0;
            var totalPages = data && data.total_pages ? data.total_pages : 1;

            $entityTableBody.empty();

            if (!entities.length){
                var $emptyRow = $('<tr class="no-items"></tr>');
                var $emptyCell = $('<td/>').attr('colspan', columnCount).text(teqcidbAdmin.none);
                $emptyRow.append($emptyCell);
                $entityTableBody.append($emptyRow);
                updatePagination(total, totalPages, currentPage);
                return;
            }

            entities.forEach(function(entity){
                var entityId = entity.id || 0;
                var headerId = 'teqcidb-entity-' + entityId + '-header';
                var panelId = 'teqcidb-entity-' + entityId + '-panel';

                var $summaryRow = $('<tr/>', {
                    id: headerId,
                    'class': 'teqcidb-accordion__summary-row',
                    tabindex: 0,
                    role: 'button',
                    'aria-expanded': 'false',
                    'aria-controls': panelId
                });

                var $titleCell = $('<td/>', {'class': 'teqcidb-accordion__cell teqcidb-accordion__cell--title'});
                var $titleText = $('<span/>', {'class': 'teqcidb-accordion__title-text'}).text(formatValue(entity.placeholder_1));
                $titleCell.append($titleText);
                $summaryRow.append($titleCell);

                for (var index = 2; index <= 5; index++) {
                    var label = getPlaceholderLabel(index);
                    var valueKey = 'placeholder_' + index;
                    var value = formatValue(entity[valueKey]);
                    var $metaCell = $('<td/>', {'class': 'teqcidb-accordion__cell teqcidb-accordion__cell--meta'});
                    var $metaText = $('<span/>', {'class': 'teqcidb-accordion__meta-text'});
                    $metaText.append($('<span/>', {'class': 'teqcidb-accordion__meta-label'}).text(label + ':'));
                    $metaText.append(' ');
                    $metaText.append($('<span/>', {'class': 'teqcidb-accordion__meta-value'}).text(value));
                    $metaCell.append($metaText);
                    $summaryRow.append($metaCell);
                }

                var $actionsCell = $('<td/>', {'class': 'teqcidb-accordion__cell teqcidb-accordion__cell--actions'});
                var $editText = $('<span/>', {'class': 'teqcidb-accordion__action-link', 'aria-hidden': 'true'}).text(teqcidbAdmin.editAction);
                var $icon = $('<span/>', {'class': 'dashicons dashicons-arrow-down-alt2 teqcidb-accordion__icon', 'aria-hidden': 'true'});
                var $srText = $('<span/>', {'class': 'screen-reader-text'}).text(teqcidbAdmin.toggleDetails);
                $actionsCell.append($editText);
                $actionsCell.append($icon).append($srText);
                $summaryRow.append($actionsCell);
                $entityTableBody.append($summaryRow);

                var $panelRow = $('<tr/>', {
                    id: panelId,
                    'class': 'teqcidb-accordion__panel-row',
                    role: 'region',
                    'aria-labelledby': headerId,
                    'aria-hidden': 'true'
                }).hide();

                var $panelCell = $('<td/>').attr('colspan', columnCount);
                var $panel = $('<div/>', {'class': 'teqcidb-accordion__panel'}).hide();
                var $form = buildEntityForm(entity);

                $panel.append($form);
                $panelCell.append($panel);
                $panelRow.append($panelCell);
                $entityTableBody.append($panelRow);
            });

            updatePagination(total, totalPages, currentPage);

            if (typeof wp !== 'undefined' && wp.editor && typeof wp.editor.initialize === 'function'){
                $entityTableBody.find('.teqcidb-editor-field').each(function(){
                    var editorId = $(this).attr('id');

                    if (!editorId){
                        return;
                    }

                    if (typeof wp.editor.remove === 'function'){
                        try {
                            wp.editor.remove(editorId);
                        } catch (removeError) {
                            // Ignore errors when removing editors that were not initialized yet.
                        }
                    }

                    var editorSettings = $.extend(true, {}, teqcidbAdmin.editorSettings || {});

                    if (typeof editorSettings.tinymce === 'undefined'){
                        editorSettings.tinymce = true;
                    }

                    if (typeof editorSettings.quicktags === 'undefined'){
                        editorSettings.quicktags = true;
                    }

                    wp.editor.initialize(editorId, editorSettings);
                });
            }
        }

        function fetchEntities(page){
            var targetPage = page || 1;
            clearFeedback();
            clearSearchFeedback();
            setSearchLoading(true);

            $.post(teqcidbAjax.ajaxurl, {
                action: 'teqcidb_read_student',
                _ajax_nonce: teqcidbAjax.nonce,
                page: targetPage,
                per_page: perPage,
                search: {
                    placeholder_1: currentFilters.placeholder_1,
                    placeholder_2: currentFilters.placeholder_2,
                    placeholder_3: currentFilters.placeholder_3
                }
            })
                .done(function(response){
                    if (response && response.success && response.data){
                        renderEntities(response.data);
                        if (pendingFeedbackMessage){
                            showFeedback(pendingFeedbackMessage);
                            pendingFeedbackMessage = '';
                        }

                        if (isSearchActive()){
                            showSearchFeedback(teqcidbAdmin.searchFiltersApplied || '');
                        } else {
                            clearSearchFeedback();
                        }
                    } else {
                        showFeedback(teqcidbAdmin.loadError || teqcidbAdmin.error);
                        showSearchFeedback(teqcidbAdmin.loadError || teqcidbAdmin.error);
                    }
                })
                .fail(function(){
                    showFeedback(teqcidbAdmin.loadError || teqcidbAdmin.error);
                    showSearchFeedback(teqcidbAdmin.loadError || teqcidbAdmin.error);
                    pendingFeedbackMessage = '';
                })
                .always(function(){
                    setSearchLoading(false);
                });
        }

        fetchEntities(1);

        if ($pagination.length){
            $pagination.on('click', '.teqcidb-entity-page', function(e){
                e.preventDefault();
                var targetPage = parseInt($(this).data('page'), 10);

                if (!targetPage || targetPage === currentPage){
                    return;
                }

                fetchEntities(targetPage);
            });
        }

        if ($searchForm.length){
            $searchForm.on('submit', function(e){
                e.preventDefault();

                var newFilters = {
                    placeholder_1: '',
                    placeholder_2: '',
                    placeholder_3: ''
                };

                $searchForm.serializeArray().forEach(function(field){
                    if (!Object.prototype.hasOwnProperty.call(newFilters, field.name)){
                        return;
                    }

                    var value = typeof field.value === 'string' ? field.value.trim() : '';
                    newFilters[field.name] = value;
                });

                currentFilters = newFilters;
                fetchEntities(1);
            });
        }

        if ($clearSearchButton.length){
            $clearSearchButton.on('click', function(e){
                e.preventDefault();

                var hadActiveFilters = isSearchActive();

                if ($searchForm.length && typeof $searchForm[0].reset === 'function'){
                    $searchForm[0].reset();
                } else if ($searchForm.length){
                    $searchForm.find('input[type="text"]').val('');
                }

                currentFilters = {
                    placeholder_1: '',
                    placeholder_2: '',
                    placeholder_3: ''
                };

                clearSearchFeedback();

                if (hadActiveFilters || currentPage !== 1){
                    fetchEntities(1);
                }
            });
        }

        $entityTableBody.on('submit', '.teqcidb-entity-edit-form', function(e){
            e.preventDefault();
            e.stopPropagation();

            var $form = $(this);
            var $spinner = $form.find('.teqcidb-entity-spinner');
            var $feedback = $form.find('.teqcidb-entity-feedback');

            if ($spinner.length){
                $spinner.addClass('is-active');
            }

            if ($feedback.length){
                $feedback.removeClass('is-visible').text('');
            }

            var formData = $form.serialize();
            formData += '&action=teqcidb_save_student&_ajax_nonce=' + encodeURIComponent(teqcidbAjax.nonce);

            $.post(teqcidbAjax.ajaxurl, formData)
                .done(function(resp){
                    if (resp && resp.success){
                        pendingFeedbackMessage = resp.data && resp.data.message ? resp.data.message : '';
                        fetchEntities(currentPage);
                    } else {
                        var message = resp && resp.data && resp.data.message ? resp.data.message : (teqcidbAdmin.error || '');

                        if ($feedback.length && message){
                            $feedback.text(message).addClass('is-visible');
                        }
                    }
                })
                .fail(function(){
                    if ($feedback.length && teqcidbAdmin.error){
                        $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                    }
                })
                .always(function(){
                    if ($spinner.length){
                        setTimeout(function(){
                            $spinner.removeClass('is-active');
                        }, 150);
                    }
                });
        });

        $entityTableBody.on('click', '.teqcidb-delete', function(e){
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');

            if (!id){
                return;
            }

            clearFeedback();

            $.post(teqcidbAjax.ajaxurl, {
                action: 'teqcidb_delete_student',
                id: id,
                _ajax_nonce: teqcidbAjax.nonce
            })
                .done(function(resp){
                    if (resp && resp.success){
                        pendingFeedbackMessage = resp.data && resp.data.message ? resp.data.message : '';
                        fetchEntities(currentPage);
                    } else {
                        showFeedback(teqcidbAdmin.error);
                    }
                })
                .fail(function(){
                    showFeedback(teqcidbAdmin.error);
                });
        });
    }

    $('.teqcidb-accordion').on('click','.item-header',function(){
        $(this).next('.item-content').slideToggle();
        $(this).parent().toggleClass('open');
    });

    $(document).on('click', '.teqcidb-api-settings__toggle-visibility', function(e){
        e.preventDefault();

        var $button = $(this);
        var targetSelector = $button.data('target');
        var $input = targetSelector ? $(targetSelector) : $button.closest('.teqcidb-api-settings__input-group').find('input').first();

        if (!$input.length) {
            return;
        }

        var wasPassword = $input.attr('type') === 'password';
        var showLabel = $button.data('label-show') || $button.data('labelShow');
        var hideLabel = $button.data('label-hide') || $button.data('labelHide');

        if (wasPassword) {
            $input.attr('type', 'text');

            if (hideLabel) {
                $button.text(hideLabel);
            }

            $button.attr('aria-pressed', 'true');
        } else {
            $input.attr('type', 'password');

            if (showLabel) {
                $button.text(showLabel);
            }

            $button.attr('aria-pressed', 'false');
        }
    });

    function initAccordionGroups(){
        $('[data-teqcidb-accordion-group]').each(function(){
            var $group = $(this);

            if ($group.data('teqcidbAccordionInitialized')) {
                return;
            }

            $group.data('teqcidbAccordionInitialized', true);

            function closeRow($summary, $panelRow){
                if (!$summary.length || !$panelRow.length) {
                    return;
                }

                $summary.removeClass('is-open').attr('aria-expanded', 'false');

                var $panel = $panelRow.find('.teqcidb-accordion__panel');

                $panel.stop(true, true).slideUp(200, function(){
                    $panelRow.hide();
                });

                $panelRow.attr('aria-hidden', 'true');
            }

            function toggleRow($summary){
                var panelId = $summary.attr('aria-controls');
                var $panelRow = $('#' + panelId);

                if (!$panelRow.length) {
                    return;
                }

                if ($summary.hasClass('is-open')) {
                    closeRow($summary, $panelRow);
                    return;
                }

                $group.find('.teqcidb-accordion__summary-row.is-open').each(function(){
                    var $openSummary = $(this);
                    var openPanelId = $openSummary.attr('aria-controls');
                    var $openPanelRow = $('#' + openPanelId);

                    closeRow($openSummary, $openPanelRow);
                });

                $summary.addClass('is-open').attr('aria-expanded', 'true');
                $panelRow.show().attr('aria-hidden', 'false');
                $panelRow.find('.teqcidb-accordion__panel').stop(true, true).slideDown(200);
            }

            $group.find('.teqcidb-accordion__summary-row').each(function(){
                var $summary = $(this);
                var panelId = $summary.attr('aria-controls');
                var $panelRow = $('#' + panelId);

                if (!$panelRow.length) {
                    return;
                }

                $summary.removeClass('is-open').attr('aria-expanded', 'false');
                $panelRow.hide().attr('aria-hidden', 'true');
                $panelRow.find('.teqcidb-accordion__panel').hide();
            });

            $group.on('click', '.teqcidb-accordion__summary-row', function(e){
                if ($(e.target).closest('a, button, input, textarea, select, label').length) {
                    return;
                }

                toggleRow($(this));
            });

            $group.on('keydown', '.teqcidb-accordion__summary-row', function(e){
                var key = e.key || e.keyCode;

                if (key === 'Enter' || key === ' ' || key === 13 || key === 32) {
                    e.preventDefault();
                    toggleRow($(this));
                }
            });
        });
    }

    initAccordionGroups();

    $(document).on('click', '.teqcidb-add-item', function(e){
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this);
        var targetSelector = $button.data('target');
        var $container = targetSelector ? $(targetSelector) : $button.closest('.teqcidb-field').find('.teqcidb-items-container').first();

        if (!$container.length){
            return;
        }

        var fieldName = $button.data('field-name') || $container.data('placeholder') || 'placeholder_25';
        var count = $container.find('.teqcidb-item-row').length + 1;
        var placeholderText = teqcidbAdmin.itemPlaceholder ? formatString(teqcidbAdmin.itemPlaceholder, count) : '';
        var $row = $('<div/>', {
            'class': 'teqcidb-item-row',
            style: 'margin-bottom:8px; display:flex; align-items:center;'
        });
        var $input = $('<input/>', {
            type: 'text',
            name: fieldName + '[]',
            'class': 'regular-text teqcidb-item-field',
            placeholder: placeholderText
        });
        var $removeButton = $('<button/>', {
            type: 'button',
            'class': 'teqcidb-delete-item',
            'aria-label': 'Remove',
            style: 'background:none;border:none;cursor:pointer;margin-left:8px;'
        }).append($('<span/>', { 'class': 'dashicons dashicons-no-alt' }));

        $row.append($input).append($removeButton);
        $container.append($row);
    });

    $(document).on('click', '.teqcidb-delete-item', function(e){
        e.preventDefault();
        e.stopPropagation();

        var $row = $(this).closest('.teqcidb-item-row');
        var $container = $row.parent('.teqcidb-items-container');
        $row.remove();

        if ($container && $container.length && teqcidbAdmin.itemPlaceholder){
            $container.find('.teqcidb-item-row').each(function(index){
                var $input = $(this).find('.teqcidb-item-field');

                if ($input.length){
                    $input.attr('placeholder', formatString(teqcidbAdmin.itemPlaceholder, index + 1));
                }
            });
        }
    });

    var $activeTokenTarget = null;

    function resolveTokenTarget($button){
        var selector = $button.data('token-target');

        if (selector){
            var $explicitTarget = $(selector);

            if ($explicitTarget.length){
                return $explicitTarget;
            }
        }

        if ($activeTokenTarget && $activeTokenTarget.length){
            return $activeTokenTarget;
        }

        var $editor = $button.closest('.teqcidb-template-editor');

        if ($editor.length){
            var $fallback = $editor.find('.teqcidb-token-target').first();

            if ($fallback.length){
                return $fallback;
            }
        }

        return $();
    }

    function insertTokenIntoField($field, token){
        if (!$field || !$field.length || !token){
            return;
        }

        var field = $field.get(0);

        if (!field){
            return;
        }

        if (typeof field.value === 'string'){
            var start = field.selectionStart;
            var end = field.selectionEnd;
            var value = field.value;

            if (typeof start === 'number' && typeof end === 'number'){
                field.value = value.slice(0, start) + token + value.slice(end);
                var newPosition = start + token.length;
                field.selectionStart = newPosition;
                field.selectionEnd = newPosition;
            } else {
                field.value = value + token;
            }

            $field.trigger('input');
            $field.trigger('change');

            if (typeof field.focus === 'function'){
                field.focus();
            }

            return;
        }

        if (window.tinyMCE && typeof field.id === 'string'){ // Fallback for rich text editors.
            var editor = window.tinyMCE.get(field.id);

            if (editor && typeof editor.execCommand === 'function'){
                editor.execCommand('mceInsertContent', false, token);
            }
        }
    }

    $(document).on('focus', '.teqcidb-token-target', function(){
        $activeTokenTarget = $(this);
    });

    $(document).on('click', '.teqcidb-token-button', function(e){
        e.preventDefault();

        var $button = $(this);
        var token = $button.data('token');
        var $target = resolveTokenTarget($button);

        insertTokenIntoField($target, token);
    });

    var previewEntity = teqcidbAdmin.previewEntity || {};
    var previewEmptyMessage = teqcidbAdmin.previewEmptyMessage || '';
    var previewUnavailableMessage = teqcidbAdmin.previewUnavailableMessage || '';
    var testEmailRequired = teqcidbAdmin.testEmailRequired || '';
    var testEmailSuccess = teqcidbAdmin.testEmailSuccess || '';
    var previewEntityKeys = Object.keys(previewEntity);
    var previewHasEntity = previewEntityKeys.length > 0;

    function applyPreviewTokens(template){
        if (typeof template !== 'string' || !template){
            return '';
        }

        return template.replace(/\{([^\{\}\s]+)\}/g, function(match, token){
            if (Object.prototype.hasOwnProperty.call(previewEntity, token)){
                return previewEntity[token];
            }

            return '';
        });
    }

    function formatPreviewBody(content){
        if (!content){
            return '';
        }

        if (/<[a-z][\s\S]*>/i.test(content)){
            return content;
        }

        return String(content).replace(/\r?\n/g, '<br>');
    }

    function updateTemplatePreview($editor){
        if (!$editor || !$editor.length){
            return;
        }

        var $notice = $editor.find('.teqcidb-template-preview__notice');
        var $content = $editor.find('.teqcidb-template-preview__content');

        if (!$content.length || !$notice.length){
            return;
        }

        if (!previewHasEntity){
            $content.removeClass('is-visible');

            if (previewUnavailableMessage){
                $notice.text(previewUnavailableMessage).show();
            } else {
                $notice.show();
            }

        } else {
            var $subjectField = $editor.find('[data-token-context="subject"]').first();
            var $bodyField = $editor.find('[data-token-context="body"]').first();
            var subjectValue = $subjectField.length ? $subjectField.val() : '';
            var bodyValue = $bodyField.length ? $bodyField.val() : '';
            var hasSubject = subjectValue && subjectValue.trim() !== '';
            var hasBody = bodyValue && bodyValue.trim() !== '';

            if (!hasSubject && !hasBody){
                $content.removeClass('is-visible');

                if (previewEmptyMessage){
                    $notice.text(previewEmptyMessage).show();
                } else {
                    $notice.show();
                }

                return;
            }

            var renderedSubject = applyPreviewTokens(subjectValue);
            var renderedBody = applyPreviewTokens(bodyValue);

            $notice.hide();

            $content.find('[data-preview-field="subject"]').text(renderedSubject);
            $content.find('[data-preview-field="body"]').html(formatPreviewBody(renderedBody));

            $content.addClass('is-visible');
        }
    }

    $(document).on('click', '.teqcidb-template-test-send', function(e){
        e.preventDefault();

        var $button = $(this);

        if ($button.prop('disabled')){
            return;
        }

        var templateId = $button.data('template');
        var $editor = $button.closest('.teqcidb-template-editor');

        if (!templateId || !$editor.length){
            return;
        }

        var spinnerSelector = $button.data('spinner');
        var feedbackSelector = $button.data('feedback');
        var emailInputSelector = $button.data('emailInput') || $button.data('email-input');
        var $spinner = spinnerSelector ? $(spinnerSelector) : $editor.find('.teqcidb-template-spinner').first();
        var $feedback = feedbackSelector ? $(feedbackSelector) : $editor.find('.teqcidb-template-feedback').first();
        var $emailInput = emailInputSelector ? $(emailInputSelector) : $editor.find('.teqcidb-template-test-email').first();
        var emailValue = $emailInput.length ? $emailInput.val() : '';

        emailValue = emailValue ? emailValue.trim() : '';

        if (!emailValue){
            if (testEmailRequired){
                window.alert(testEmailRequired);
            } else if (typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.error){
                window.alert(teqcidbAdmin.error);
            } else {
                window.alert('Please enter an email address.');
            }

            if ($emailInput.length){
                $emailInput.focus();
            }

            return;
        }

        if ($feedback.length){
            $feedback.removeClass('is-visible').text('');
        }

        if ($spinner.length){
            $spinner.addClass('is-active');
        }

        $button.prop('disabled', true);

        var payload = {
            action: 'teqcidb_send_test_email',
            _ajax_nonce: teqcidbAjax.nonce,
            template_id: templateId,
            to_email: emailValue,
            from_name: $editor.find('[data-template-field="from_name"]').first().val() || '',
            from_email: $editor.find('[data-template-field="from_email"]').first().val() || '',
            subject: $editor.find('[data-token-context="subject"]').first().val() || '',
            body: $editor.find('[data-token-context="body"]').first().val() || ''
        };

        $.post(teqcidbAjax.ajaxurl, payload)
            .done(function(response){
                var isSuccess = response && response.success;
                var message = '';

                if (response && response.data){
                    if (isSuccess && response.data.message){
                        message = response.data.message;
                    } else if (!isSuccess && (response.data.error || response.data.message)){
                        message = response.data.error || response.data.message;
                    }
                }

                if (isSuccess && !message && testEmailSuccess){
                    message = testEmailSuccess;
                }

                if (!isSuccess && !message && typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.error){
                    message = teqcidbAdmin.error;
                }

                if ($feedback.length){
                    if (message){
                        $feedback.text(message).addClass('is-visible');
                    } else {
                        $feedback.removeClass('is-visible').text('');
                    }
                }
            })
            .fail(function(){
                if ($feedback.length && typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.error){
                    $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                }
            })
            .always(function(){
                if ($spinner.length){
                    setTimeout(function(){
                        $spinner.removeClass('is-active');
                    }, 150);
                }

                $button.prop('disabled', false);
            });
    });

    $(document).on('click', '.teqcidb-template-save', function(e){
        e.preventDefault();

        var $button = $(this);

        if ($button.prop('disabled')){
            return;
        }

        var templateId = $button.data('template');
        var $editor = $button.closest('.teqcidb-template-editor');

        if (!templateId || !$editor.length){
            return;
        }

        var spinnerSelector = $button.data('spinner');
        var feedbackSelector = $button.data('feedback');
        var $spinner = spinnerSelector ? $(spinnerSelector) : $editor.find('.teqcidb-template-spinner').first();
        var $feedback = feedbackSelector ? $(feedbackSelector) : $editor.find('.teqcidb-template-feedback').first();

        if ($feedback.length){
            $feedback.removeClass('is-visible').text('');
        }

        if ($spinner.length){
            $spinner.addClass('is-active');
        }

        $button.prop('disabled', true);

        var payload = {
            action: 'teqcidb_save_email_template',
            _ajax_nonce: teqcidbAjax.nonce,
            template_id: templateId,
            from_name: $editor.find('[data-template-field="from_name"]').first().val() || '',
            from_email: $editor.find('[data-template-field="from_email"]').first().val() || '',
            subject: $editor.find('[data-token-context="subject"]').first().val() || '',
            body: $editor.find('[data-token-context="body"]').first().val() || '',
            sms: $editor.find('[data-token-context="sms"]').first().val() || ''
        };

        $.post(teqcidbAjax.ajaxurl, payload)
            .done(function(response){
                var isSuccess = response && response.success;
                var message = '';

                if (response && response.data){
                    if (isSuccess && response.data.message){
                        message = response.data.message;
                    } else if (!isSuccess && (response.data.error || response.data.message)){
                        message = response.data.error || response.data.message;
                    }
                }

                if (!isSuccess && !message && typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.error){
                    message = teqcidbAdmin.error;
                }

                if ($feedback.length){
                    if (message){
                        $feedback.text(message).addClass('is-visible');
                    } else {
                        $feedback.removeClass('is-visible').text('');
                    }
                }

                if (isSuccess){
                    updateTemplatePreview($editor);
                }
            })
            .fail(function(){
                if ($feedback.length && typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.error){
                    $feedback.text(teqcidbAdmin.error).addClass('is-visible');
                }
            })
            .always(function(){
                if ($spinner.length){
                    setTimeout(function(){
                        $spinner.removeClass('is-active');
                    }, 150);
                }

                $button.prop('disabled', false);
            });
    });

    $(document).on('click', '.teqcidb-email-log__clear', function(e){
        e.preventDefault();

        var $button = $(this);

        if ($button.prop('disabled')){
            return;
        }

        var spinnerSelector = $button.data('spinner');
        var feedbackSelector = $button.data('feedback');
        var $spinner = spinnerSelector ? $(spinnerSelector) : $button.siblings('.spinner').first();
        var $feedback = feedbackSelector ? $(feedbackSelector) : $button.siblings('.teqcidb-email-log__feedback').first();
        var $list = $('#teqcidb-email-log-list');
        var $empty = $('#teqcidb-email-log-empty');
        var emptyMessage = '';

        if ($list.length){
            emptyMessage = $list.data('emptyMessage');
        }

        if (!emptyMessage && typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.emailLogEmpty){
            emptyMessage = teqcidbAdmin.emailLogEmpty;
        }

        var successMessage = (typeof teqcidbAdmin !== 'undefined' && teqcidbAdmin.emailLogCleared) ? teqcidbAdmin.emailLogCleared : '';
        var errorMessage = '';

        if (typeof teqcidbAdmin !== 'undefined'){
            if (teqcidbAdmin.emailLogError){
                errorMessage = teqcidbAdmin.emailLogError;
            } else if (teqcidbAdmin.error){
                errorMessage = teqcidbAdmin.error;
            }
        }

        if ($feedback.length){
            $feedback.removeClass('is-visible').text('');
        }

        if ($spinner.length){
            $spinner.addClass('is-active');
        }

        $button.prop('disabled', true);

        $.post(teqcidbAjax.ajaxurl, {
            action: 'teqcidb_clear_email_log',
            _ajax_nonce: teqcidbAjax.nonce
        }).done(function(response){
            var isSuccess = response && response.success;
            var message = '';

            if (isSuccess){
                message = successMessage;

                if ($list.length){
                    $list.find('.teqcidb-email-log__entry').remove();
                }

                if ($empty.length){
                    $empty.text(emptyMessage || '');
                    $empty.removeAttr('hidden').addClass('is-visible');
                } else if ($list.length){
                    $empty = $('<p/>', {
                        id: 'teqcidb-email-log-empty',
                        'class': 'teqcidb-email-log__empty is-visible',
                        text: emptyMessage || ''
                    });
                    $list.prepend($empty);
                }
            } else if (response && response.data){
                message = response.data.message || response.data.error || '';
            }

            if (!message && !isSuccess){
                message = errorMessage;
            }

            if ($feedback.length){
                if (message){
                    $feedback.text(message).addClass('is-visible');
                } else {
                    $feedback.removeClass('is-visible').text('');
                }
            }
        }).fail(function(){
            if ($feedback.length){
                $feedback.text(errorMessage).addClass('is-visible');
            }
        }).always(function(){
            if ($spinner.length){
                setTimeout(function(){
                    $spinner.removeClass('is-active');
                }, 150);
            }

            $button.prop('disabled', false);

            if ($empty && $empty.length && !$empty.text()){ // ensure a placeholder message exists
                $empty.text(emptyMessage || '');
            }
        });
    });

    $(document).on('blur', '.teqcidb-template-editor [data-token-context="subject"], .teqcidb-template-editor [data-token-context="body"]', function(){
        var $editor = $(this).closest('.teqcidb-template-editor');
        updateTemplatePreview($editor);
    });

    $('.teqcidb-template-editor').each(function(){
        updateTemplatePreview($(this));
    });
});
