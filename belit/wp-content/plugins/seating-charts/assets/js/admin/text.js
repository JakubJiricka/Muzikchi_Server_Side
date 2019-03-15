jQuery(document).ready(function ($) {
    //Text element events
    $('body').on('click', '.tc-text-group .tc-group-controls .tc-icon-copy', function (e) {
        e.preventDefault();
        tc_text.copy($(this));
    });

    $('body').on('click', '.tc-text-group .tc-group-controls .tc-icon-trash', function (e) {
        e.preventDefault();
        tc_text.delete($(this));
    });
    $('body').on('click', '.tc-text-group .tc-group-controls .tc-icon-edit', function (e) {
        e.preventDefault();
        tc_text.edit_mode($(this));
    });
    $('body').on('click', '#tc_edit_text_button', function (e) {
        e.preventDefault();
        tc_text.edit();
    });
    $('body').on('click', '#tc_cancel_text_button', function (e) {
        e.preventDefault();
        tc_text.cancel_edit();
    });

    window.tc_text = {
        /**
         * Creates new text element
         * @returns {undefined}
         */
        edit: function () {
            var text_size = $('#tc_text_widget .tc_text_size').val() + 'px';
            var color = $('#tc_text_widget .tc_text_color').val();

            $('.tc-caption-wrap.tc-edit-mode .tc-caption span').html($('#tc_text_widget .tc_text_title').val());
            $('.tc-caption-wrap.tc-edit-mode').css({'height': 'auto', 'width': 'auto'});
            $('.tc-caption-wrap.tc-edit-mode .tc-caption span').animate({'font-size': text_size, 'line-height': text_size}, 250);
            $('.tc-caption-wrap.tc-edit-mode .tc-caption span').animate({'color': color}, 250);
        },
        cancel_edit: function () {
            $('#tc-seat-labels-settings').hide();
            $(".tc-sidebar").tabs({collapsible: true, active: false});
            tc_controls.hide_ticket_type_box();
            tc_controls.unselect_all();
        },
        create: function ()
        {

            var grid_size = 5;
            var title = $('#tc_text_widget .tc_text_title').val();
            var text_html = '';
            var font_size = $('#tc_text_widget .tc_text_size').val();
            var color = $('#tc_text_widget .tc_text_color').val();
            var style = 'font-size: ' + font_size + 'px; line-height: ' + font_size + 'px; color:' + color + ';';

            if (title == '') {
                return;
            }

            text_html += '<div class="tc-group-wrap tc-group-text tc-text-group tc-caption-wrap">';
            text_html += '<div class="tc-caption-group tc-group-background"><div class="tc-caption"><span style="' + style + '">' + title + '</span></div><!-- tc-caption -->';
            text_html += '<div class="tc-group-controls"><span class="tc-icon-edit"></span><span class="tc-icon-trash"></span><span class="tc-icon-copy"></span></div></div>';
            text_html += '</div>';

            var text = text_html;
            var new_element = tc_text.add_to_canvas(text, new Array(1, 1), true);

            tc_controls.center($(new_element));

            try {
                $(new_element).find('.tc-caption-group').rotatable();
                $(new_element).draggable(
                        {
                            grid: [grid_size, grid_size],
                            handle: $(new_element).find('.tc-caption'),
                            start: function (event, ui) {
                                ui.position.left = 0;
                                ui.position.top = 0;
                            },
                            drag: function (event, ui) {

                                var changeLeft = ui.position.left - ui.originalPosition.left; // find change in left
                                var newLeft = (ui.originalPosition.left + changeLeft) / window.tc_seat_zoom_level; // adjust new left by our zoomScale

                                var changeTop = ui.position.top - ui.originalPosition.top; // find change in top
                                var newTop = (ui.originalPosition.top + changeTop) / window.tc_seat_zoom_level; // adjust new top by our zoomScale

                                ui.position.left = newLeft;
                                ui.position.top = newTop;
                            }
                        });
            } catch (e) {
                return null;
            }
        },
        /**
         * Delete an element
         * @param {type} element_obj
         * @returns {undefined}
         */
        delete: function (element_obj) {
            tc_controls.delete_confirmation(element_obj.parent().parent().parent());
        },
        /**
         * Make a copy of an element
         * @param {type} element_obj
         * @returns {undefined}
         */
        copy: function (element_obj) {
            //to do
        },
        /**
         * Appends HTML to the wrapper / adds new element on the canvas
         * @param {type} html
         * @param {type} position
         * @param {type} draggable
         * @returns {Window.tc_text.add_to_canvas.element|window.tc_text.add_to_canvas.element|window.$|$}
         */
        add_to_canvas: function (html, position, draggable) {
            var element = $(html);
            element.appendTo('.tc-wrapper .tc-pan-wrapper');
            return element;
        },
        edit_mode: function (obj) {
            //remove all previous "edit mode" classes
            $('.tc-group-wrap').removeClass('tc-edit-mode');
            //mark that object is in the edit mode
            var holder = obj.parent().parent().parent();
            holder.addClass('tc-edit-mode');

            var title = holder.find('.tc-caption span').html();
            if (title == '&nbsp;') {
                title = '';
            }
            var color = holder.find('.tc-caption span').css('color');
            var font_size = parseInt(holder.find('.tc-caption span').css('font-size'));

            $("#tc_text_widget .tc-slider-value.tc_text_size").val(font_size);
            $("#tc_text_widget .tc-number-slider").slider('value', font_size);

            $('#tc_text_widget .tc_text_title').val(title);
            $('#tc_text_widget .tc_text_color').val(color);
            $('#tc_text_widget .wp-color-result').css({'background-color': color});


            $(".tc-sidebar").tabs({collapsible: true, active: 5});

            $('#tc_text_widget .tc_text_edit_controls').show();
            $('#tc_text_widget .tc_text_add_controls').hide();
            tc_controls.hide_ticket_type_box();
        },
        init: function () {
            var grid_size = 5;

            $('.tc-group-wrap.tc-group-text').each(function () {

                $(this).find('.tc-icon-rotate').remove();

                $(this).find('.tc-caption-group').rotatable();
                $(this).draggable(
                        {
                            grid: [grid_size, grid_size],
                            handle: $(this).find('.tc-caption'),
                            start: function (event, ui) {
                                ui.position.left = 0;
                                ui.position.top = 0;
                            },
                            drag: function (event, ui) {

                                var changeLeft = ui.position.left - ui.originalPosition.left; // find change in left
                                var newLeft = (ui.originalPosition.left + changeLeft) / window.tc_seat_zoom_level; // adjust new left by our zoomScale

                                var changeTop = ui.position.top - ui.originalPosition.top; // find change in top
                                var newTop = (ui.originalPosition.top + changeTop) / window.tc_seat_zoom_level; // adjust new top by our zoomScale

                                ui.position.left = newLeft;
                                ui.position.top = newTop;
                            }
                        });
            });
        }
    }
});