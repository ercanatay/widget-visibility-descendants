/**
 * Widget Visibility with Descendants - Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initVisibilityUI();
    });

    // Re-initialize when widgets are updated (AJAX)
    $(document).on('widget-updated widget-added', function(event, widget) {
        initVisibilityUI(widget);
    });

    /**
     * Initialize visibility UI for all widgets or a specific widget
     */
    function initVisibilityUI(container) {
        var $wrappers = container
            ? $(container).find('.wvd-visibility-wrapper')
            : $('.wvd-visibility-wrapper');

        $wrappers.each(function() {
            var $wrapper = $(this);

            // Skip if already initialized
            if ($wrapper.data('wvd-initialized')) {
                return;
            }

            $wrapper.data('wvd-initialized', true);
            setupWidget($wrapper);
        });
    }

    /**
     * Setup a single widget's visibility UI
     */
    function setupWidget($wrapper) {
        var $button = $wrapper.find('.wvd-visibility-button');
        var $panel = $wrapper.find('.wvd-visibility-panel');
        var $dataInput = $wrapper.find('.wvd-visibility-data');
        var $content = $wrapper.find('.wvd-visibility-content');

        // Toggle panel
        $button.on('click', function(e) {
            e.preventDefault();
            if ($panel.is(':visible')) {
                $panel.slideUp(200);
            } else {
                renderPanel($content, $dataInput);
                $panel.slideDown(200);
            }
        });
    }

    /**
     * Render the visibility panel content
     */
    function renderPanel($content, $dataInput) {
        var data = getVisibilityData($dataInput);
        var html = '';

        // Action row (Show/Hide)
        html += '<div class="wvd-action-row">';
        html += '<select class="wvd-action-select">';
        html += '<option value="show"' + (data.action === 'show' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.show) + '</option>';
        html += '<option value="hide"' + (data.action === 'hide' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.hide) + '</option>';
        html += '</select>';
        html += '<span class="wvd-rule-label">' + escapeHtml(wvdData.i18n.if) + ':</span>';
        html += '</div>';

        // Rules container
        html += '<div class="wvd-rules">';
        if (data.rules && data.rules.length > 0) {
            data.rules.forEach(function(rule, index) {
                html += renderRule(rule, index);
            });
        }
        html += '</div>';

        // Add condition link
        html += '<a href="#" class="wvd-add-rule">' + escapeHtml(wvdData.i18n.addCondition) + '</a>';

        // Match all checkbox
        html += '<div class="wvd-match-all">';
        html += '<label>';
        html += '<input type="checkbox" class="wvd-match-all-checkbox"' + (data.match_all ? ' checked' : '') + '>';
        html += ' ' + escapeHtml(wvdData.i18n.matchAll);
        html += '</label>';
        html += '</div>';

        // Footer
        html += '<div class="wvd-panel-footer">';
        html += '<a href="#" class="wvd-delete-rules">' + escapeHtml(wvdData.i18n.delete) + '</a>';
        html += '<button type="button" class="button wvd-done-button">' + escapeHtml(wvdData.i18n.done) + '</button>';
        html += '</div>';

        $content.html(html);

        // Bind events
        bindPanelEvents($content, $dataInput);
    }

    /**
     * Render a single rule
     */
    function renderRule(rule, index) {
        var html = '<div class="wvd-rule" data-index="' + index + '">';

        // Remove button
        html += '<a href="#" class="wvd-rule-remove" title="' + escapeHtml(wvdData.i18n.remove) + '">&times;</a>';

        // Type select
        html += '<select class="wvd-rule-type">';
        html += '<option value="page"' + (rule.type === 'page' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.page) + '</option>';
        html += '<option value="category"' + (rule.type === 'category' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.category) + '</option>';
        html += '<option value="post_type"' + (rule.type === 'post_type' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.postType) + '</option>';
        html += '<option value="front_page"' + (rule.type === 'front_page' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.frontPage) + '</option>';
        html += '<option value="blog"' + (rule.type === 'blog' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.blog) + '</option>';
        html += '<option value="archive"' + (rule.type === 'archive' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.archive) + '</option>';
        html += '<option value="search"' + (rule.type === 'search' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.search) + '</option>';
        html += '<option value="404"' + (rule.type === '404' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.notFound) + '</option>';
        html += '<option value="single"' + (rule.type === 'single' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.single) + '</option>';
        html += '<option value="logged_in"' + (rule.type === 'logged_in' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.loggedIn) + '</option>';
        html += '<option value="logged_out"' + (rule.type === 'logged_out' ? ' selected' : '') + '>' + escapeHtml(wvdData.i18n.loggedOut) + '</option>';
        html += '</select>';

        // Label
        html += '<span class="wvd-rule-label">' + escapeHtml(wvdData.i18n.is) + '</span>';

        // Value select (depends on type)
        html += renderValueSelect(rule);

        // Options (checkboxes)
        if (rule.type === 'page' || rule.type === 'category') {
            html += renderRuleOptions(rule);
        }

        html += '</div>';
        return html;
    }

    /**
     * Render value select based on rule type
     */
    function renderValueSelect(rule) {
        var html = '';
        var items = [];
        var placeholder = '';

        switch (rule.type) {
            case 'page':
                items = wvdData.pages;
                placeholder = escapeHtml(wvdData.i18n.selectPage);
                break;
            case 'category':
                items = wvdData.categories;
                placeholder = escapeHtml(wvdData.i18n.selectCategory);
                break;
            case 'post_type':
                items = wvdData.postTypes;
                placeholder = escapeHtml(wvdData.i18n.selectPostType);
                break;
            default:
                return '<span class="wvd-rule-value-na">—</span>';
        }

        html += '<select class="wvd-rule-value">';
        html += '<option value="">' + placeholder + '</option>';
        items.forEach(function(item) {
            var selected = (String(rule.value) === String(item.id)) ? ' selected' : '';
            html += '<option value="' + escapeHtml(item.id) + '"' + selected + ' data-has-children="' + (item.hasChildren ? '1' : '0') + '">';
            html += escapeHtml(item.title);
            html += '</option>';
        });
        html += '</select>';

        return html;
    }

    /**
     * Render rule options (checkboxes)
     */
    function renderRuleOptions(rule) {
        var html = '<div class="wvd-rule-options">';

        // Include children
        html += '<label>';
        html += '<input type="checkbox" class="wvd-include-children"' + (rule.include_children ? ' checked' : '') + '>';
        html += ' ' + escapeHtml(wvdData.i18n.includeChildren);
        html += '</label>';

        // Include all descendants
        html += '<label class="wvd-descendants-option">';
        html += '<input type="checkbox" class="wvd-include-descendants"' + (rule.include_descendants ? ' checked' : '') + '>';
        html += ' ' + escapeHtml(wvdData.i18n.includeDescendants);
        html += '</label>';

        html += '</div>';
        return html;
    }

    /**
     * Bind events to panel elements
     */
    function bindPanelEvents($content, $dataInput) {
        var $wrapper = $content.closest('.wvd-visibility-wrapper');
        var $panel = $wrapper.find('.wvd-visibility-panel');

        // Action change
        $content.on('change', '.wvd-action-select', function() {
            updateData($content, $dataInput);
        });

        // Rule type change
        $content.on('change', '.wvd-rule-type', function() {
            var $rule = $(this).closest('.wvd-rule');
            var type = $(this).val();

            // Update value select
            var rule = { type: type, value: '', include_children: false, include_descendants: false };
            var $valueContainer = $rule.find('.wvd-rule-value, .wvd-rule-value-na');
            $valueContainer.replaceWith($(renderValueSelect(rule)));

            // Update options
            var $optionsContainer = $rule.find('.wvd-rule-options');
            if (type === 'page' || type === 'category') {
                if ($optionsContainer.length === 0) {
                    $rule.append(renderRuleOptions(rule));
                }
            } else {
                $optionsContainer.remove();
            }

            updateData($content, $dataInput);
        });

        // Value change
        $content.on('change', '.wvd-rule-value', function() {
            updateData($content, $dataInput);
        });

        // Checkbox changes
        $content.on('change', '.wvd-include-children, .wvd-include-descendants', function() {
            var $this = $(this);
            var $rule = $this.closest('.wvd-rule');

            // If descendants is checked, also check children
            if ($this.hasClass('wvd-include-descendants') && $this.is(':checked')) {
                $rule.find('.wvd-include-children').prop('checked', true);
            }

            // If children is unchecked, also uncheck descendants
            if ($this.hasClass('wvd-include-children') && !$this.is(':checked')) {
                $rule.find('.wvd-include-descendants').prop('checked', false);
            }

            updateData($content, $dataInput);
        });

        // Match all change
        $content.on('change', '.wvd-match-all-checkbox', function() {
            updateData($content, $dataInput);
        });

        // Add rule
        $content.on('click', '.wvd-add-rule', function(e) {
            e.preventDefault();
            var $rules = $content.find('.wvd-rules');
            var index = $rules.find('.wvd-rule').length;
            var newRule = { type: 'page', value: '', include_children: false, include_descendants: false };
            $rules.append(renderRule(newRule, index));
            updateData($content, $dataInput);
        });

        // Remove rule
        $content.on('click', '.wvd-rule-remove', function(e) {
            e.preventDefault();
            $(this).closest('.wvd-rule').remove();
            updateData($content, $dataInput);
        });

        // Delete all rules
        $content.on('click', '.wvd-delete-rules', function(e) {
            e.preventDefault();
            $content.find('.wvd-rules').empty();
            updateData($content, $dataInput);
            updateStatus($wrapper, false);
        });

        // Done button
        $content.on('click', '.wvd-done-button', function(e) {
            e.preventDefault();
            $panel.slideUp(200);
            var data = getVisibilityData($dataInput);
            updateStatus($wrapper, data.rules && data.rules.length > 0);
        });
    }

    /**
     * Update the hidden data input
     */
    function updateData($content, $dataInput) {
        var data = {
            action: $content.find('.wvd-action-select').val() || 'show',
            match_all: $content.find('.wvd-match-all-checkbox').is(':checked'),
            rules: []
        };

        $content.find('.wvd-rule').each(function() {
            var $rule = $(this);
            var rule = {
                type: $rule.find('.wvd-rule-type').val(),
                value: $rule.find('.wvd-rule-value').val() || '',
                include_children: $rule.find('.wvd-include-children').is(':checked'),
                include_descendants: $rule.find('.wvd-include-descendants').is(':checked')
            };
            data.rules.push(rule);
        });

        $dataInput.val(JSON.stringify(data));

        // Trigger change to mark widget as needing save
        $dataInput.trigger('change');
    }

    /**
     * Get visibility data from input
     */
    function getVisibilityData($dataInput) {
        var val = $dataInput.val();
        if (!val) {
            return { action: 'show', match_all: false, rules: [] };
        }
        try {
            return JSON.parse(val);
        } catch (e) {
            return { action: 'show', match_all: false, rules: [] };
        }
    }

    /**
     * Update status indicator
     */
    function updateStatus($wrapper, hasRules) {
        var $status = $wrapper.find('.wvd-visibility-status');
        if (hasRules) {
            if ($status.length === 0) {
                $wrapper.find('.wvd-visibility-toggle').append(
                    '<span class="wvd-visibility-status wvd-has-rules">' + escapeHtml(wvdData.i18n.configured) + '</span>'
                );
            } else {
                $status.addClass('wvd-has-rules').text(wvdData.i18n.configured);
            }
        } else {
            $status.remove();
        }
    }

    /**
     * Escape HTML securely
     */
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})(jQuery);
