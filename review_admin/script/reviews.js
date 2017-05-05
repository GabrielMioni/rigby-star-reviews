function edit_row() {
    $('.edit_button').on('click', function(e){
        e.preventDefault();

        var row_display = $(this).parent();
        var row_edit    = row_display.next('.row_edit');
        var tog = $(this).find('.tog');

        if ($(this).hasClass('clicked')) {
            $(this).removeClass('clicked');
        } else {
            $(this).addClass('clicked');
        }

        if ($(this).hasClass('clicked')) {
            var click_state = 0;
        } else {
            var click_state = 1;
        }

        switch(click_state) {
            case 1:
                tog.removeClass('stop_edit');
                tog.addClass('pencil');
                row_edit.hide();
                break;
            case 0:
                tog.removeClass('pencil');
                tog.addClass('stop_edit');
                row_edit.show();
                break
        }
    });
}

function set_radio_display() {
    $('#single_date_picker').css({'display':'none'});
    $('.date_row input[type="radio"], .date_row #date_range_span, .date_row #date_range_wrap').css({
        'display':'inline'
    });
}

function check_radio() {
    $('.date_radio').on('click', function(){
        $('#search_form input').on('change', function(){
            var radio_val = $('input[name=date_range]:checked', '#search_form').val();

            switch(radio_val) {
                case 'date_range':
                    hide_date_single();
                    break;
                case 'date_single':
                    hide_date_range();
                    break;
            }

        });
    });
}
function hide_date_single() {
    $('#single_date_picker').hide();
    $('#single_date_picker button').hide();
    $('#date_range_wrap').show();
    $('#single_date_picker input').val('');
}
function hide_date_range() {
    $('#single_date_picker').show();
    $('#single_date_picker button').show();
    $('#date_range_wrap').hide();
    $('#date_range_wrap input').val('');
}
function radio_click() {
    $('.date_radio span').on('click', function(){
        $(this).find('input').click();
    });
}
function form_change() {
    $('form :input').on('change input', function(){
        var form = $(this).closest('form');
        var change_input = form.find('input[name="change"]');
        $(change_input).val(1);
    });
}
function ajax_edit() {
    $('.edit_submit').on('click', function(e){
        e.preventDefault();

        var form = $(this).parent('form');
        var change_val = form.find('input[name="change"]').val();

        var form_act  = form.attr('action');
        var form_data = $(form).serialize();
        var submit_data = 'ajax_submit=1&'+form_data;

        var result_spin = $(form).find('.spinner');
        var result_chk  = $(form).find('.check_div');
        var result_chng = $(form).find('.no_change');
        var result_fail = $(form).find('.ajax_fail');

        var edit_tr = form.closest('tr');
        var disp_tr = $(edit_tr).prev();

        $(result_spin).hide();
        $(result_chng).hide();
        $(result_chk).hide();
        $(result_fail).hide();

        if (change_val !== '1') {
            result_chng.show();
        } else {
            $.ajax({
                type: 'POST',
                url: form_act,
                data: submit_data
            }).done(function(response) {
                console.log('Response:'+response);
                switch(response) {
                    case '1':
                        ajax_spinner(result_spin, result_chk);
                        var form_obj = new unserialize(submit_data);
                        update_td(disp_tr, form_obj);
                        form.find('input[name="change"]').val(0);
                        break;
                    default:
                        ajax_spinner(result_spin, result_fail);
                        set_fail_message(form, response);
                        break;
                }

            }); // end Ajax
        }
    });
}
function ajax_spinner(result_spin, reveal_elm) {
    $(result_spin).show().delay(1000).queue(function(){
        $(this).hide();
        $(reveal_elm).show();
        $(this).dequeue();
    });
}

/**
 * Deprecated
 *
 * @param form The form element where the error messages need to be displayed
 * @param response The json_encode string response from edit_quick_act.php
 */
function set_fail_message(form, response) {
    var response_array = JSON.parse(response);

    for (var r = 0 ; r < response_array.length ; ++r) {

        var error_input = response_array[r] + '_edit';
        var input_element = form.find('.'+error_input)
        $(input_element).addClass('error');
        console.log(error_input);
    }
}

/**
 * When the Rigby user starts typing in an input field
 * that's previously been set with the .error class, remove
 * the error class.
 */
function remove_error_border() {
    $('input, textarea').keypress(function(){
        var input = $(this);
        if (input.hasClass('error')) {
            input.removeClass('error');
        }
    }); // end keypress
}

function update_td(disp_tr, obj) {
    for (var p in obj) {
        if( obj.hasOwnProperty(p) ) {
            var disp_class = '.disp_'+p;
            var obj_val    = obj[p];
            var update_elm = $(disp_tr).find(disp_class);
            var cur_td_val = update_elm.html();
            var update_val = return_disp(disp_class, obj_val);

            update_chk(cur_td_val, update_val, update_elm);
        }
    }
}

function update_chk(cur_td_val, update_val, update_elm) {
    if (cur_td_val !== update_val) {

        $(update_elm).empty();
        $(update_elm).append(update_val);
        $(update_elm).css({'color':'#5FA9F5'}).delay(5000).queue(function(){
            $(this).stop().animate({'color':'#000000'}, 1000);
            $(this).dequeue();
        }); // end update
    }
}

function return_disp(disp_class, obj_val) {
    switch(disp_class) {
        case '.disp_stars':
            var star_divs = set_stars(obj_val);
            return star_divs;
        case '.disp_hidden':
            if (obj_val === 'on') {
                return 'Yes';
            } else {
                return 'No';
            }
        default:
            return obj_val;
    }
}
function set_stars(star_count) {
    var star_divs = '';
    for (var s = 0; s < star_count; ++s) {
        star_divs += '<div class="star_full"></div>';
    }
    return star_divs;
}

function unserialize(data) {
    var decode = decodeURI(data);
//    var decode = decode.replace('%40', '@');
    decode = decode.replace('%40', '@');
    var expl = decode.split('&');
    var tmp = {};
    tmp['hidden'] = -1;

    for (var i = 0, len = expl.length; i < len; i++) {
        var expl_elm = expl[i];
        var key_elm  = expl_elm.split('=');

        var key = key_elm[0].replace('_edit','');
        var elm = key_elm[1];

        switch(key) {
            case 'content':
                break;
            case 'ajax_submit':
                break;
            case 'id':
                break;
            default:
                tmp[key] = elm;
                break;
        }
    }
    return tmp;
}

$(document).ready(function(){
    new edit_row();
    new check_radio();
    new set_radio_display();
    new form_change();
    new remove_error_border();
    new ajax_edit();

    $(" .date_radio input[value='date_range']" ).click();

    $( function() {
        var dateFormat = "mm/dd/yy",
            from = $( "#date_start" )
                .datepicker({
                    numberOfMonths: 1,
                    showOn: 'button',
                    buttonText: "<div class='calendar'></div>"
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#date_end" ).datepicker({
                numberOfMonths: 1,
                showOn: 'button',
                buttonText: "<div class='calendar'></div>"
            })
                .on( "change", function() {
                    from.datepicker( "option", "maxDate", getDate( this ) );
                });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }
            return date;
        }
    } );

    $(function() {
        $( " #single_date" ).datepicker({
            showOn: "button",
            buttonText: "<div class='calendar'></div>"
        });
    });
});