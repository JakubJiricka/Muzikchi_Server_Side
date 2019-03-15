jQuery(document).ready(function ($) {

    //Seats events
    $('body').on('click', '.tc-seat-group .tc-group-controls .tc-icon-trash', function (e) {
        e.preventDefault();
        tc_seats.delete($(this));
    });

    $('body').on('click', '.tc-seat-group .tc-group-controls .tc-icon-copy', function (e) {
        e.preventDefault();
        tc_seats.copy_group($(this));
    });

    $('body').on('click', '#tc_add_seats_button', function (e) {
        e.preventDefault();
        tc_seats.create_group();
    });

    $('body').on('click', '.tc-group-seats .tc-group-controls .tc-icon-edit', function (e) {
        e.preventDefault();
        tc_seats.edit_mode($(this));
    });
    $('body').on('click', '#tc_edit_seats_button', function (e) {
        e.preventDefault();
        tc_seats.edit();
    });
    $('body').on('click', '#tc_cancel_seat_button', function (e) {
        e.preventDefault();
        tc_seats.cancel_edit();
    });


    window.tc_seats = {
        /**
         * Creates new seat group
         * @returns {undefined}
         */
        edit: function () {
            var holder = $('.tc-group-seats.tc-edit-mode');
            var title = $('#tc_seating_group_widget #tc_seating_group_title').val();
            var rows_prev = holder.find('.tc-group-content .tc-seat-row').length;
            var cols_prev = holder.find('.tc-group-content .tc-seat-row:first-child .tc_seat_unit').length;
            var rows = $('#tc_seating_group_widget #tc_seat_add_seats_rows').val();
            var cols = $('#tc_seating_group_widget #tc_seat_add_seats_cols').val();
            var seat_group_html = '';

            if (title == '') {
                title = '&nbsp;';
                holder.find('.tc-heading').addClass('tc-empty-header');
            } else {
                holder.find('.tc-heading').removeClass('tc-empty-header');
            }

            if (rows == rows_prev && cols == cols_prev) {
                //do nothing
            } else {
                if (rows != rows_prev) {
                    if (rows > rows_prev) {//add rows
                        seat_group_html = '';
                        rows_to_add = rows - rows_prev;
                        last_id = holder.find('.tc-seat-row:last-child .tc_seat_unit:last-child').attr('id');
                        next_part_no = parseInt(last_id.split('_')[2]);
                        no = parseInt(last_id.split('_')[3]) + 1;

                        for (i = 1; i < (parseInt(rows_to_add) + 1); i++) {
                            seat_group_html += '<div class="tc-seat-row selectable_row" style="position: relative;">';
                            for (j = 1; j < (parseInt(cols_prev) + 1); j++) {
                                seat_group_html += '<span class="tc_seat_unit" id="tc_seat_' + next_part_no + '_' + no + '"></span>';
                                no++;
                            }
                            seat_group_html += '</div><!--tc-seat-row-->';
                        }

                        holder.find('.tc-group-content').append(seat_group_html);

                    } else {//delete rows
                        deleted_rows = 0;
                        rows_to_delete = rows_prev - rows;
                        $(holder.find('.tc-seat-row').get().reverse()).each(function () {
                            if (rows_to_delete > deleted_rows) {
                                $(this).remove();
                                deleted_rows++;
                            }

                        });
                    }
                }
                if (cols != cols_prev) {
                    if (cols > cols_prev) {
                        //add cols
                        seat_group_html = '';
                        cols_to_add = cols - cols_prev;
                        last_id = holder.find('.tc-seat-row:last-child .tc_seat_unit:last-child').attr('id');
                        next_part_no = parseInt(last_id.split('_')[2]);
                        no = parseInt(last_id.split('_')[3]) + 1;

                        $.each(holder.find('.tc-seat-row'), function () {
                            seat_group_html = '';
                            for (j = 1; j < (parseInt(cols_to_add) + 1); j++) {
                                seat_group_html += '<span class="tc_seat_unit" id="tc_seat_' + next_part_no + '_' + no + '"></span>';
                                no++;
                            }
                            $(this).append(seat_group_html);
                        });
                        var group_size = (parseInt($('#tc_square_size').val()) * parseInt(cols));
                        holder.css('width', group_size);
                    } else {
                        //delete cols
                        cols_to_delete = cols_prev - cols;
                        $(holder.find('.tc-seat-row').get()).each(function () {
                            row = $(this);
                            deleted_cols = 0;

                            $(row.find('.tc_seat_unit').get().reverse()).each(function () {
                                if (cols_to_delete > deleted_cols) {
                                    $(this).remove();
                                    deleted_cols++;
                                }
                            });
                        });
                        var group_size = (parseInt($('#tc_square_size').val()) * parseInt(cols));
                        holder.css('width', group_size);
                    }
                }
            }

            //title
            holder.find('.tc-heading h3').html(title);
        },
        cancel_edit: function () {
            $('#tc-seat-labels-settings').hide();
            $(".tc-sidebar").tabs({collapsible: true, active: false});
            tc_controls.hide_ticket_type_box();
            tc_controls.unselect_all();
        },
        create_group: function ()
        {
            var rows = $('#tc_seat_add_seats_rows').val();
            var cols = $('#tc_seat_add_seats_cols').val();
            var group_size = (parseInt($('#tc_square_size').val()) * parseInt(cols));
            var header_title = $('#tc_seating_group_title').val();
            var seat_group_html = '';
            var grid_size = 5;
            var next_part_no = tc_controls.next_part_number();
            var no = 1;
            var empty_class = 'tc-empty-header';

            if (header_title == '') {
                header_title = '&nbsp;';
            } else {
                empty_class = '';
            }

            seat_group_html += '<div class="tc-group-wrap tc-group-seats" style="width:' + group_size + 'px" data-init-width=' + group_size + '><div class="tc-seat-group tc-group-background">';
            seat_group_html += '<div class="tc-heading ' + empty_class + '"><h3>' + header_title + '</h3></div><!-- .tc-heading --><div class="tc-group-content">';

            for (i = 1; i < (parseInt(rows) + 1); i++) {
                seat_group_html += '<div class="tc-seat-row selectable_row" style="position: relative;">';
                for (j = 1; j < (parseInt(cols) + 1); j++) {
                    seat_group_html += '<span class="tc_seat_unit" id="tc_seat_' + next_part_no + '_' + no + '"></span>';
                    no++;
                }
                seat_group_html += '</div><!--tc-seat-row-->';
            }

            seat_group_html += '</div><!-- .tc-group-content --><div class="tc-group-controls"><span class="tc-icon-edit"></span><span class="tc-icon-trash"></span><span class="tc-icon-copy"></span></div></div><!-- .tc-seat-group --><div class="tc-clear"></div></div>';

            var group = seat_group_html;
            var new_element = tc_seats.add_to_canvas(group, new Array(1, 1), true);

            tc_controls.center($(new_element));

            try {
                $(new_element).find('.tc-seat-group').rotatable();
                $(new_element).find('.tc-group-content').selectable(
                        {
                            filter: '.tc_seat_unit',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );


                $(new_element).draggable({
                    grid: [grid_size * window.tc_seat_zoom_level, grid_size * window.tc_seat_zoom_level],
                    handle: $(new_element).find('.tc-seat-group .tc-heading'),
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
                });//grid_size / 16, grid_size / 16
            } catch (e) {
                return null;
            }
        },
        /**
         * Delete seat group
         * @param {type} group_obj
         * @returns {undefined}
         */
        delete: function (group_obj) {
            tc_controls.delete_confirmation(group_obj.parent().parent().parent());
        },
        /**
         * Make a copy of a group
         * @param {type} group_obj
         * @returns {undefined}
         */
        copy_group: function (group_obj) {
            //to do
        },
        /**
         * Appends HTML to the wrapper / adds new element on the canvas
         * @param {type} html
         * @param {type} position
         * @param {type} draggable
         * @returns {window.tc_seats.add_to_canvas.element|Window.tc_seats.add_to_canvas.element|window.$|$}
         */
        add_to_canvas: function (html, position, draggable) {
            var element = $(html);
            element.appendTo('.tc-wrapper .tc-pan-wrapper');
            return element;
        },
        zoom: function (old_zoom, new_zoom) {

            var zoom_level = 1.25;

            if (old_zoom < new_zoom) {
                zoom_level = ((new_zoom - old_zoom)) + 1;
            } else {
                zoom_level = ((old_zoom - new_zoom)) + 1;
            }

            $('.tc-group-wrap').each(function () {
                //console.log(zoom_level);
                var position = $(this).position();

                //var group_height = parseFloat($(this).attr('data-init-height'));
                var group_width = parseFloat($(this).attr('data-init-width'));
                var font_size = 29;
                var margin = 3;
                var top = $(this).css('top');
                var left = $(this).css('left');

                var seats_font_size = $(this).find('.tc-group-content span').css('font-size');
                seats_font_size = parseFloat(seats_font_size.split('px')[0]);

                var seats_font_margin = $(this).find('.tc-group-content span').css('margin');
                seats_font_margin = parseFloat(seats_font_margin.split('px')[0]);

                $(this).width((group_width * new_zoom));
                $(this).find('.tc-group-content span').css('font-size', font_size * new_zoom + 'px');
                $(this).find('.tc-group-content span').css('margin', margin * new_zoom + 'px');

                if (old_zoom < new_zoom) {
                    $(this).css('top', (position.top * zoom_level).toFixed(2));
                    $(this).css('left', (position.left * zoom_level).toFixed(2));
                } else {
                    $(this).css('top', (position.top / zoom_level).toFixed(2));
                    $(this).css('left', (position.left / zoom_level).toFixed(2));
                }
            });

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

            var rows = holder.find('.tc-group-content .tc-seat-row').length;
            var cols = holder.find('.tc-group-content .tc-seat-row:first-child .tc_seat_unit').length;

            holder.addClass('tc-edit-mode');

            if (holder.find('.tc_seat_reserved').length) {//check if there are some reserved seats
                $('#tc_seating_group_widget .tc-seat-rows-slider').hide();
                $('#tc_seating_group_widget .tc-seat-cols-slider').hide();
            } else {
                $('#tc_seating_group_widget .tc-seat-rows-slider').show();
                $('#tc_seating_group_widget .tc-seat-cols-slider').show();
            }

            $('#tc_seating_group_widget #tc_seating_group_title').val(title);

            $("#tc_seating_group_widget .tc-seat-rows-slider .tc-slider-value").val(rows);
            $("#tc_seating_group_widget .tc-seat-rows-slider .tc-number-slider").slider('value', rows);

            $("#tc_seating_group_widget .tc-seat-cols-slider .tc-slider-value").val(cols);
            $("#tc_seating_group_widget .tc-seat-cols-slider .tc-number-slider").slider('value', cols);

            $(".tc-sidebar").tabs({collapsible: true, active: 1});

            $('#tc_seating_group_widget .tc_seat_edit_controls').show();
            $('#tc_seating_group_widget .tc_seat_add_controls').hide();
            tc_controls.hide_ticket_type_box();
        },
        init: function () {
            var grid_size = 5;

            $('.tc-group-wrap.tc-group-seats').each(function () {

                $(this).find('.tc-icon-rotate').remove();

                $(this).find('.tc-seat-group').rotatable();
                $(this).find('.tc-group-content').selectable(
                        {
                            filter: '.tc_seat_unit',
                            cancel: '.tc_seat_reserved',
                            stop: function (event, ui) {
                                tc_controls.show_ticket_type_box(event, ui);
                            }
                        }
                );

                $(this).draggable({
                    grid: [grid_size * window.tc_seat_zoom_level, grid_size * window.tc_seat_zoom_level],
                    handle: $(this).find('.tc-seat-group .tc-heading'),
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