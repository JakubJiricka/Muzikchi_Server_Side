jQuery(document).ready(function ($) {

    //Element Events
    $('body').on('click', '.tc-element-group-wrap .tc-element-group .tc-group-controls .tc-icon-copy', function (e) {
        e.preventDefault();
        tc_element.copy($(this));
    });

    $('body').on('click', '.tc-element-group-wrap .tc-element-group .tc-group-controls .tc-icon-trash', function (e) {
        e.preventDefault();
        tc_element.delete($(this));
    });
    $('body').on('click', '.tc-element-group-wrap .tc-group-controls .tc-icon-edit', function (e) {
        e.preventDefault();
        tc_element.edit_mode($(this));
    });
    $('body').on('click', '#tc_edit_element_button', function (e) {
        e.preventDefault();
        tc_element.edit();
    });
    $('body').on('click', '#tc_cancel_element_button', function (e) {
        e.preventDefault();
        tc_element.cancel_edit();
    });

    window.tc_element = {
        /**
         * Creates new element
         * @returns {undefined}
         */
        edit: function () {
            var holder = $('.tc-element-group-wrap.tc-edit-mode');

            var title = $('#tc_element_widget .tc_element_title').val();
           
            if (title == '') {
                title = '&nbsp;';
                holder.find('.tc-heading').addClass('tc-empty-header');
            } else {
                holder.find('.tc-heading').removeClass('tc-empty-header');
            }

            var icon = $('#tc_element_widget input[name=tc-element-selection]:checked').val();
            var color = $('#tc_element_widget .tc-element-color-picker .wp-color-result').css('background-color');
            var background_color = $('#tc_element_widget .tc-element-background-color-picker .wp-color-result').css('background-color');

            holder.find('.tc-heading h3').animate({'color': color});
            holder.find('.tc-heading h3').html(title);
            holder.find('.tc-object span').attr('class', icon);
            holder.find('.tc-object span').animate({'color': color}, 250);
            holder.find('.tc-element-group').animate({'background-color': background_color}, 250);
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
            var icon = $('#tc_element_widget input[name=tc-element-selection]:checked').val();
            var title = $('#tc_element_widget .tc_element_title').val();
            var text_html = '';
            var icon_color = $('#tc_element_widget .icon-color').val();
            var icon_background_color = $('#tc_element_widget .icon-background-color').val();
            var background_color_style = '';
            var color_style = '';
            var empty_class = 'tc-empty-header';

            if (icon_background_color !== '') {
                background_color_style = 'style="background-color:' + icon_background_color + '; -moz-box-shadow: 0 0 8px 1px rgba(0, 0, 0, 0.05); -webkit-box-shadow: 0 0 8px 1px rgba(0, 0, 0, 0.05);box-shadow: 0 0 8px 1px rgba(0, 0, 0, 0.05);"';
            }

            if (icon_color !== '') {
                color_style = 'style="color:' + icon_color + ' !important"';
            }
//var font_size = $('#tc_element_widget .tc_text_size').val();
//var color = $('#tc_element_widget .tc_text_color').val();
//var style = 'font-size: ' + font_size + 'px; color:' + color + ';';

            if (title == '') {
                title = '&nbsp;';
            } else {
                empty_class = '';
            }

            text_html += '<div class="tc-group-wrap tc-element-group-wrap tc-object-wrap tc-group-elements">';
            text_html += '<div class="tc-element-group" ' + background_color_style + '>'; //tc-group-background
            text_html += '<div class="tc-heading ' + empty_class + '"><h3 ' + color_style + '>' + title + '</h3></div>';
            text_html += '<div class="tc-object"><span class="' + icon + '" ' + color_style + '></span></div><!-- .tc-object -->';
            text_html += '<div class="tc-group-controls"><span class="tc-icon-edit"></span><span class="tc-icon-trash"></span><span class="tc-icon-copy"></span></div>';
            text_html += '</div></div>';
            var text = text_html;
            var new_element = tc_element.add_to_canvas(text, new Array(1, 1), true);

            tc_controls.center($(new_element));

            try {
                $(new_element).find('.tc-element-group').rotatable();
                $(new_element).find('.tc-element-group').resizable({
                    //alsoResize: ".tc-element-group, .tc-object",
                    handles: 'ne, se, sw, nw, n, e, s, w, all', //ne, se, nw, n, e, s, w
                    minHeight: 50,
                    minWidth: 50,
                    autoHide: true,
                    create: function (event, ui) {
                        $('.ui-resizable-se').removeClass('ui-icon-gripsmall-diagonal-se ui-icon');
                    }
                }).on('resize', function (e) {
                    $(this).find('.tc-element-group').height($(this).height());
                    $(this).find('.tc-element-group').width($(this).width());
                    $(this).find('.tc-object').height($(this).height() - 30);
                    $(this).find('.tc-object').width($(this).width());
                });

                $(new_element).draggable(
                        {
                            grid: [grid_size, grid_size],
                            handle: $(new_element).find('.tc-heading'),
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

            var title = holder.find('.tc-heading h3').html();
            if (title == '&nbsp;') {
                title = '';
            }

            var icon = holder.find('.tc-object span').attr('class');
            var icon_color = holder.find('.tc-object span').css('color');

            var background_color = holder.find('.tc-element-group').css('background-color');

            $('#tc_element_widget .tc_element_title').val(title);
            $('#tc_element_widget .tc-element-color-picker .wp-color-result').val(icon_color);
            $('#tc_element_widget .tc-element-background-color-picker .wp-color-result').css({'background-color': background_color});

            $('#tc_element_widget #tc-' + icon).prop('checked', true);

            $(".tc-sidebar").tabs({collapsible: true, active: 4});

            $('#tc_element_widget .tc_element_edit_controls').show();
            $('#tc_element_widget .tc_element_add_controls').hide();
            tc_controls.hide_ticket_type_box();
        },
        init: function () {
            var grid_size = 5;

            $('.tc-group-wrap.tc-group-elements').each(function () {
                $(this).find('.tc-icon-rotate').remove();
                $(this).find('.ui-resizable-handle').remove();

                $(this).find('.tc-element-group').rotatable();
                $(this).find('.tc-element-group').resizable({
                    handles: 'ne, se, sw, nw, n, e, s, w, all',
                    minHeight: 50,
                    minWidth: 50,
                    autoHide: true,
                    create: function (event, ui) {
                        $('.ui-resizable-se').removeClass('ui-icon-gripsmall-diagonal-se ui-icon');
                    }
                }).on('resize', function (e) {
                    $(this).find('.tc-element-group').height($(this).height());
                    $(this).find('.tc-element-group').width($(this).width());
                    $(this).find('.tc-object').height($(this).height() - 30);
                    $(this).find('.tc-object').width($(this).width());
                });

                $(this).draggable(
                        {
                            grid: [grid_size, grid_size],
                            handle: $(this).find('.tc-heading'),
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