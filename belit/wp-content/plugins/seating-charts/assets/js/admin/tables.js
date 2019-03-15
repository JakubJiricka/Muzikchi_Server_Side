jQuery(document).ready(function ($) {

    $('body').on('click', '#tc-table .tc_seat_table_type_square', function (e) {
        var init_seat_num = parseInt($('#tc_table_seats_num').val());
        var init_max_end_seats = Math.floor(init_seat_num / 2);
        var init_end_seats = parseInt($('#tc_table_end_seats').val());
        if (init_end_seats > init_max_end_seats) {
            $('#tc_table_end_seats').val('0');
            $("#tc-table .tc-number-slider.tc_table_end_seats").slider('value', '0');
        }
        $("#tc-table .tc-number-slider.tc_table_end_seats").slider('option', {max: init_max_end_seats});

    });

    $('body').on('click', '#tc-table #tc_add_table_button', function (e) {
        e.preventDefault();
        if ($('input:radio[name=tc_seat_table_type]:checked').val() == 'circle') {
            tc_table.create_rounded_table(false);
        } else {
            tc_table.create_square_table(false);
        }
    });

    $('body').on('click', '.tc-table-wrap .tc-group-controls .tc-icon-edit', function (e) {
        e.preventDefault();
        tc_table.edit_mode($(this));
    });

    $('body').on('click', '#tc_edit_table_button', function (e) {
        e.preventDefault();
        tc_table.edit();
    });
    $('body').on('click', '#tc_cancel_table_button', function (e) {
        e.preventDefault();
        tc_table.cancel_edit();
    });

    window.tc_table = {
        edit: function () {
            if ($('input:radio[name=tc_seat_table_type]:checked').val() == 'circle') {
                tc_table.create_rounded_table(true);
            } else {
                tc_table.create_square_table(true);
            }
        },
        cancel_edit: function () {
            $('#tc-seat-labels-settings').hide();
            $(".tc-sidebar").tabs({collapsible: true, active: false});
            tc_controls.hide_ticket_type_box();
            tc_controls.unselect_all();
        },
        /**
         * Creates new text element
         * @returns {undefined}
         */
        create_square_table: function (edit) {
            var empty_class = 'tc-empty-header';
            var rotation = 0;

            if (edit == true) {
                var holder = $('.tc-table-wrap.tc-edit-mode');
                var rotation = tc_table.getRotationDegrees(holder.find('.tc-table-group'));
                var holder_top = holder.css('top');
                var holder_left = holder.css('left');
            }

            var grid_size = 5;
            var title = $('#tc-table .tc_table_title').val();
            var table_html = '';
            var numNodes = parseInt($('#tc_table_seats_num').val());
            var nodeSize = 24;
            var seat_margin = 3;
            var end_seats_count = parseInt($('.tc_table_end_seats_value').val());
            var left_seats_count = Math.floor((numNodes - end_seats_count * 2) / 2);
            var right_seats_count = numNodes - ((end_seats_count * 2) + left_seats_count);
            //var tc_table_color = jQuery('.tc-table-color-picker a').css('backgroundColor');
            var tc_table_color = $('.tc-table-color-picker .tc-color-picker').val();
            
            var side_seats_count = 0;
            if (right_seats_count >= left_seats_count) {
                side_seats_count = right_seats_count;
            } else {
                side_seats_count = left_seats_count;
            }

            var table_width = (end_seats_count * 30) - 6;
            var table_height = (side_seats_count * 30) - 6;
            if (table_width <= 24) {
                table_width = 24;
            }

            if (table_height <= 24) {
                table_height = 24;
            }

            if (title == '') {
                title = '&nbsp;';
            } else {
                empty_class = '';
            }

            var seat_size = 24; //$('.tc-table-chair').width();
            var style_sizes = 'style="width: ' + ((table_width) + 34) + 'px; height: ' + ((table_height) + 50) + 'px;"';
            var rotation_style = '-moz-transform:rotate(' + rotation + 'deg);-webkit-transform:rotate(' + rotation + 'deg);-o-transform:rotate(' + rotation + 'deg);-ms-transform:rotate(' + rotation + 'deg);';
            var group_style = 'width: ' + ((table_width) + 34) + 'px; height: ' + ((table_height) + 130) + 'px;';
            var tc_table_style_sizes = 'style="width: ' + ((table_width) + 34) + 'px; height: ' + ((table_height) + 50) + 'px;"';
            var tc_table_group_sizes = 'style="width: ' + ((table_width) + 34) + 'px; height: ' + ((table_height) + 130) + 'px;"';
            var table_seats = tc_table.draw_square_table_seats(end_seats_count, left_seats_count, right_seats_count, nodeSize, seat_margin, table_width, table_height, edit);
            //var table_element_size = (radius * 2) - seat_size - 4;
            table_html += '<div class="tc-group-wrap tc-group-tables-square tc-table-wrap" ' + style_sizes + ' data-end-seats="' + end_seats_count + '">';
            table_html += '<div class="tc-table-group tc-group-background" style="' + group_style + ' ' + rotation_style + '">';
            table_html += '<div class="tc-heading ' + empty_class + '">';
            table_html += '<h3>' + title + '</h3>';
            table_html += '</div>';
            table_html += '<div class="tc-table-wrap"><div class="tc-table" ' + tc_table_style_sizes + '>';
            table_html += '<div class="tc-table-square-element" style="width: ' + table_width + 'px; height: ' + table_height + 'px; background-color:' + tc_table_color + ';">' + table_seats + '</div>';
            //table_html += table_seats;

            table_html += '</div><!-- .tc-object --></div>';
            table_html += '<div class="tc-group-controls"><span class="tc-icon-edit"></span><span class="tc-icon-trash"></span><span class="tc-icon-copy"></span></div></div>';
            table_html += '</div>';
            table_html += '</div>';
            var table = table_html;
            var new_element = tc_table.add_to_canvas(table, new Array(1, 1), true);

            var title_height = $(new_element).find('.tc-heading').height();
            var current_height = $(new_element).height();
            //console.log(title_height + current_height);
            $(new_element).height(current_height + title_height + 120);
            $(new_element).find('.tc-table-group').height(current_height + title_height + 80);

            if (edit == true) {
                holder.remove();
                new_element.css({
                    'top': holder_top,
                    'left': holder_left,
                    'position': 'absolute',
                    'z-index': 0
                });
                new_element.addClass('tc-edit-mode');
            } else {
                tc_controls.center($(new_element));
            }

            try {
                $(new_element).find('.tc-table-group').rotatable();
                $(new_element).find('.tc-table').selectable(
                        {
                            filter: '.tc-table-chair',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );
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
        create_rounded_table: function (edit)
        {
            var rotation = 0;
            var empty_class = 'tc-empty-header';

            if (edit == true) {
                var holder = $('.tc-table-wrap.tc-edit-mode');
                var rotation = tc_table.getRotationDegrees(holder.find('.tc-table-group'));
                var holder_top = holder.css('top');
                var holder_left = holder.css('left');
            }

            var grid_size = 5;
            var title = $('#tc-table .tc_table_title').val();
            var table_html = '';
            var numNodes = $('#tc_table_seats_num').val();
            var radius = (numNodes * 24) / 5;
            if (radius < 40) {
                radius = 40;
            }

            if (title == '') {
                title = '&nbsp;';
            } else {
                empty_class = '';
            }

            var seat_size = 24; //$('.tc-table-chair').width();

            var style_sizes = 'style="width: ' + ((radius * 2) + 34) + 'px; height: ' + ((radius * 2) + 130) + 'px;"';

            var group_style = 'width: ' + ((radius * 2) + 34) + 'px; height: ' + ((radius * 2) + 130) + 'px;';
            var rotation_style = '-moz-transform:rotate(' + rotation + 'deg);-webkit-transform:rotate(' + rotation + 'deg);-o-transform:rotate(' + rotation + 'deg);-ms-transform:rotate(' + rotation + 'deg);';

            var tc_table_style_sizes = 'style="width: ' + ((radius * 2) + 34) + 'px; height: ' + (((radius * 2) + 30)) + 'px;"';
            //var tc_table_color = jQuery('.tc-table-color-picker a').css('backgroundColor');
            var tc_table_color = $('.tc-table-color-picker .tc-color-picker').val();
            var table_seats = tc_table.draw_rounded_table_seats(numNodes, radius, edit);
            var table_element_size = (radius * 2) - seat_size - 4;
            table_html += '<div class="tc-group-wrap tc-group-tables-rounded tc-table-wrap" ' + style_sizes + '>';
            table_html += '<div class="tc-table-group tc-rounded-table-group tc-group-background" style="' + group_style + ' ' + rotation_style + '">';
            table_html += '<div class="tc-heading ' + empty_class + '">';
            table_html += '<h3>' + title + '</h3>';
            table_html += '</div>';
            table_html += '<div class="tc-table-wrap"><div class="tc-table" ' + tc_table_style_sizes + '>';
            table_html += table_seats;
            table_html += '<div class="tc-table-element" style="width: ' + table_element_size + 'px; height: ' + table_element_size + 'px; background-color: ' + tc_table_color + ';">';
            table_html += '</div><!--tc-table-element-->';
            table_html += '</div><!-- .tc-table --></div>';
            table_html += '<div class="tc-group-controls"><span class="tc-icon-edit"></span><span class="tc-icon-trash"></span><span class="tc-icon-copy"></span></div></div>';
            table_html += '</div>';
            table_html += '</div>';
            var table = table_html;

            var new_element = tc_table.add_to_canvas(table, new Array(1, 1), true);

            if (edit == true) {
                holder.remove();
                new_element.css({
                    'top': holder_top,
                    'left': holder_left,
                    'position': 'absolute',
                    'z-index': 0
                });
                new_element.addClass('tc-edit-mode');
            } else {
                tc_controls.center($(new_element));
            }

            try {
                $(new_element).find('.tc-table-group').rotatable();
                $(new_element).find('.tc-table').selectable(
                        {
                            filter: '.tc-table-chair',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );
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
        draw_rounded_table_seats: function (numNodes, radius, edit) {

            if (edit == true) {
                var holder = $('.tc-table-wrap.tc-edit-mode');
                var old_items = new Array();
                var current_old_item = 1;

                holder.find('.tc-table-chair').each(function () {
                    old_items[current_old_item] = new Array();
                    item_classes = $(this).attr('class');
                    item_classes = item_classes.replace("tc-table-chair", "");
                    item_classes = item_classes.replace("ui-selectee", "");

                    background_color = $(this).css('background-color');

                    ticket_type = $(this).attr('data-tt-id');

                    if (ticket_type == null || ticket_type == 'undefined') {
                        background_color = false;
                        ticket_type = false;
                    }

                    old_items[current_old_item].push(item_classes + '|' + background_color + '|' + ticket_type);

                    current_old_item++;
                });
            }


            var nodes = tc_table.create_rounded_table_nodes(numNodes, radius);
            var html = '';
            var node_style = '';
            var next_part_no = tc_controls.next_part_number();
            var no = 1;
            for (var key in nodes) {
                node_style = '';
                if (!nodes.hasOwnProperty(key))
                    continue;
                var obj = nodes[key];
                if (edit == true) {

                    last_id = holder.find('.tc-table-chair').attr('id');
                    next_part_no = parseInt(last_id.split('_')[2]);

                    if (typeof old_items[no] === 'undefined') {
                        node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;"';
                        html += '<div class="tc-table-chair" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '"></div>';
                    } else {//old item exists
                        old_items_values = old_items[no].toString();
                        old_items_values = old_items_values.split('|');
                        item_classes = old_items_values[0];
                        background_color = old_items_values[1];
                        ticket_type = old_items_values[2];

                        if (background_color != 'false') {
                            background_color = 'background-color:' + background_color + ';';
                        } else {
                            background_color = '';
                        }

                        if (ticket_type !== 'false') {
                            ticket_type = 'data-tt-id="' + ticket_type + '"';
                        } else {
                            ticket_type = '';
                        }

                        node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;' + background_color + '"';
                        html += '<div class="tc-table-chair ' + item_classes + '" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '" ' + ticket_type + '></div>';
                    }
                } else {
                    node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;"';
                    html += '<div class="tc-table-chair" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '"></div>';
                }
                no++;
            }

            return html;
        },
        create_rounded_table_nodes: function (numNodes, radius) {
            var nodes = [],
                    width = (radius * 2),
                    height = (radius * 2),
                    angle,
                    x,
                    y,
                    i;
            for (i = 0; i < numNodes; i++) {
                angle = (i / (numNodes / 2)) * Math.PI; // Calculate the angle at which the element will be placed.

                x = (radius * Math.cos(angle)) + (width / 2); // Calculate the x position of the element.
                y = (radius * Math.sin(angle)) + (width / 2); // Calculate the y position of the element.
                nodes.push({'id': i, 'x': x + 5, 'y': (y + 3), 'angle': angle, 'radius': radius});
            }
            return nodes;
        },
        draw_square_table_seats: function (end_seats_count, left_seats_count, right_seats_count, nodeSize, seat_margin, table_width, table_height, edit) {

            var rotation = 0;

            if (edit == true) {
                var holder = $('.tc-table-wrap.tc-edit-mode');
                var old_items = new Array();
                var current_old_item = 1;

                holder.find('.tc-table-chair').each(function () {
                    old_items[current_old_item] = new Array();
                    item_classes = $(this).attr('class');
                    item_classes = item_classes.replace("tc-table-chair", "");
                    item_classes = item_classes.replace("ui-selectee", "");

                    background_color = $(this).css('background-color');

                    ticket_type = $(this).attr('data-tt-id');

                    if (ticket_type == null || ticket_type == 'undefined') {
                        background_color = false;
                        ticket_type = false;
                    }

                    old_items[current_old_item].push(item_classes + '|' + background_color + '|' + ticket_type);

                    current_old_item++;
                });
            }

            var nodes = tc_table.create_square_table_nodes(end_seats_count, left_seats_count, right_seats_count, nodeSize, seat_margin, table_width, table_height);
            var html = '';
            var node_style = '';
            var next_part_no = tc_controls.next_part_number();
            var no = 1;
            for (var key in nodes) {
                node_style = '';
                if (!nodes.hasOwnProperty(key))
                    continue;
                var obj = nodes[key];

                if (edit == true) {

                    last_id = holder.find('.tc-table-chair').attr('id');
                    next_part_no = parseInt(last_id.split('_')[2]);

                    if (typeof old_items[no] === 'undefined') {
                        node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;"';
                        html += '<div class="tc-table-chair" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '"></div>';
                    } else {//old item exists
                        old_items_values = old_items[no].toString();
                        old_items_values = old_items_values.split('|');
                        item_classes = old_items_values[0];
                        background_color = old_items_values[1];
                        ticket_type = old_items_values[2];

                        if (background_color != 'false') {
                            background_color = 'background-color:' + background_color + ';';
                        } else {
                            background_color = '';
                        }

                        if (ticket_type !== 'false') {
                            ticket_type = 'data-tt-id="' + ticket_type + '"';
                        } else {
                            ticket_type = '';
                        }

                        node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;' + background_color + '"';
                        html += '<div class="tc-table-chair ' + item_classes + '" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '" ' + ticket_type + '></div>';
                    }
                } else {
                    node_style = 'style="top: ' + (obj['y']).toFixed(2) + 'px; left: ' + (obj['x']).toFixed(2) + 'px;"';
                    html += '<div class="tc-table-chair" ' + node_style + ' id="tc_seat_' + next_part_no + '_' + no + '"></div>';
                }

                no++;
            }

            return html;
        },
        create_square_table_nodes: function (end_seats_count, left_seats_count, right_seats_count, nodeSize, seat_margin, table_width, table_height) {
            var nodes = [],
                    x,
                    y,
                    t,
                    b,
                    l,
                    r;
            //top chairs
            var t_x = 0;
            for (t = 0; t < end_seats_count; t++) {
                nodes.push({'id': t, 'x': t_x, 'y': -30});
                t_x = t_x + 30;
            }

            //bottom chairs
            var b_x = 0;
            for (b = 0; b < end_seats_count; b++) {
                nodes.push({'id': b, 'x': b_x, 'y': table_height + 5});
                b_x = b_x + 30;
            }

            //left chairs
            var l_y = 0;
            for (l = 0; l < left_seats_count; l++) {
                nodes.push({'id': l, 'x': -30, 'y': l_y});
                l_y = l_y + 30;
            }

            //right chairs
            var r_y = 0;
            for (r = 0; r < right_seats_count; r++) {
                nodes.push({'id': r, 'x': table_width + 5, 'y': r_y});
                r_y = r_y + 30;
            }

            return nodes;
        },
        getRotationDegrees: function (obj) {
            var matrix = obj.css("-webkit-transform") ||
                    obj.css("-moz-transform") ||
                    obj.css("-ms-transform") ||
                    obj.css("-o-transform") ||
                    obj.css("transform");
            if (matrix !== 'none') {
                var values = matrix.split('(')[1].split(')')[0].split(',');
                var a = values[0];
                var b = values[1];
                var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
            } else {
                var angle = 0;
            }
            return angle;
        },
        edit_mode: function (obj) {
            //remove all previous "edit mode" classes
            $('.tc-group-wrap').removeClass('tc-edit-mode');
            //mark that object is in the edit mode
            var holder = obj.parent().parent().parent();
            var title = holder.find('.tc-heading h3').html();

            if (title == '&nbsp;') {
                title = '';
            }

            if (holder.find('.tc_seat_reserved').length) {//check if there are some reserved seats
                $('#tc-table .tc-input-slider').hide();
            } else {
                $('#tc-table .tc-input-slider').show();
            }

            var seats = holder.find('.tc-table-chair').length;
            var end_seats = holder.attr('data-end-seats');
            var background_color = holder.find('.tc-table-element').css('background-color');

            if (background_color == null || background_color == 'undefined') {
                background_color = holder.find('.tc-table-square-element').css('background-color');
            }

            $('#tc-table .wp-picker-input-wrap .tc-color-picker').val(background_color);
            $('#tc-table .wp-color-result').css({'background-color': background_color});

            if (isNaN(end_seats)) {
                tc_seat_table_type = 'circle';
                $('.tc-select-shape-round').click();
            } else {
                tc_seat_table_type = 'square';
                $('.tc-select-shape-box').click();
                $("#tc-table .tc-slider-value.tc_table_end_seats_value").val(end_seats);

                var max_end_seats = Math.floor(seats / 2);

                $("#tc-table .tc-number-slider.tc_table_end_seats").slider('option', {max: max_end_seats});
                $("#tc-table .tc-number-slider.tc_table_end_seats").slider('value', end_seats);
            }

            $("#tc-table .tc-slider-value.tc_table_seats_num_value").val(seats);
            $("#tc-table .tc-number-slider.tc_table_seats_num").slider('value', seats);

            //var rows = holder.find('.tc-group-content .tc-seat-row').length;
            //var cols = holder.find('.tc-group-content .tc-seat-row:first-child .tc_seat_unit').length;

            holder.addClass('tc-edit-mode');

            $('#tc-table .tc_table_title').val(title);

            /*$("#tc_seating_group_widget .tc-seat-rows-slider .tc-slider-value").val(rows);
             $("#tc_seating_group_widget .tc-seat-rows-slider .tc-number-slider").slider('value', rows);
             
             $("#tc_seating_group_widget .tc-seat-cols-slider .tc-slider-value").val(cols);
             $("#tc_seating_group_widget .tc-seat-cols-slider .tc-number-slider").slider('value', cols);*/

            $(".tc-sidebar").tabs({collapsible: true, active: 3});

            $('#tc-table .tc_table_edit_controls').show();
            $('#tc-table .tc_table_add_controls').hide();
            tc_controls.hide_ticket_type_box();
        },
        init: function () {
            var grid_size = 5;
            //Init square table
            $('.tc-group-wrap.tc-group-tables-square').each(function () {
                $(this).find('.tc-icon-rotate').remove();
                $(this).find('.tc-table-group').rotatable();
                $(this).find('.tc-table').selectable(
                        {
                            filter: '.tc-table-chair',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );
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
            //Init rounded table
            $('.tc-group-wrap.tc-group-tables-rounded').each(function () {
                $(this).find('.tc-icon-rotate').remove();
                $(this).find('.tc-table-group').rotatable();
                $(this).find('.tc-table').selectable(
                        {
                            filter: '.tc-table-chair',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );
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