/**
 * Clicking the 'Add User' button reveals the hidden Add User form and its
 * containing low opacity canvas.
 */
function click_add_user() {
    $('#add_user a').on('click', function(e){
        e.preventDefault();

        // Get the #add_user link parent element.
        var click_elm = $(this).parent();

        // Does #add_user have the 'clicked' class?
        var check_click = $(click_elm).hasClass('clicked');

        // If #add_user has clicked class already, hide #canvas and the contained
        // Add User form. Else, show #canvas and the contained Add User form.
        switch(check_click) {
            case true:
                $(click_elm).removeClass('clicked');
                hide_form();
                break;
            case false:
                $(click_elm).addClass('clicked');
                show_form();
                break;
        }
    });
}

/**
 * Starts Ajax call to a form's action attribute.
 *
 * The response will either be '1' (if successful) or a JSON encoded string
 * built from a PHP array.
 *
 * Error messages are set in the form element by calling set_error_messages()
 *
 */
function ajax_edit() {
    $('#submit_user').on('click', function(e){
        e.preventDefault();

        var form_elm = $(this).closest('form');
        var form_action = form_elm.attr('action');
        var form_data = $(form_elm).serialize();
        var submit_data = 'ajax_submit=1&'+form_data;

        $.ajax({
            type: 'POST',
            url: form_action,
            data: submit_data
        }).done(function(response) {
            switch(response)  {
                case '1':
                    add_user_success();
                    break;
                default:
                    var error_object = JSON.parse(response);
                    set_error_messages(error_object, form_elm);
                    break;
            }
        }); // end done/ajax

    }); // end on click
} // end ajax_edit

function add_user_success() {
    location.reload();
}

/**
 * Sets error messages within the form element set by var form_elm.
 *
 * Finds error elements by appending the key name to '.error_' and then
 * adding the error message to that element.
 *
 * Used in:
 * - ajax_edit()
 *
 * @param error_object json string converted to JS object
 * @param form_elm Form element where the error messages should be displayed.
 */
function set_error_messages(error_object, form_elm) {
    for (var key in error_object) {
        var error_message   = error_object[key];
        var error_class     = '.error_'+key;
        var error_elm       = form_elm.find(error_class);

        error_elm.empty();
        error_elm.append(error_message);
    }
}

/**
 * Reveals the hidden form used to add new Rigby users.
 *
 * The form is nested within a low opacity canvas that occupies the whole viewport.
 */
function show_form() {
    $('#canvas').fadeIn();
}

/**
 * Hides the hidden form used to add new Rigby users.
 *
 * The form is nested within a low opacity canvas that occupies the whole viewport.
 */
function hide_form() {
    $('#canvas').fadeOut();
}

/**
 * This prevents clicks made on the actual form element from trickling to the containing
 * #canvas element. Important because clicking on #canvas hides both the canvas and the form.
 */
function stop_form_prop() {
    $('#add_user_form').click(function(e){
        e.stopPropagation();
    });
}

/**
 * Clicking on the #canvas element hides
 *
 */
function canvas_click() {
    $('#canvas').on('click', function(){
        var click_elm = $('#add_user');
        $(this).fadeOut();
        if (click_elm.hasClass('clicked')) {
            click_elm.removeClass('clicked');
        }
    });
}

/**
 * Keydown on escape causes click on #canvas, which hides the #canvas element and
 * the contained Add User form.
 */
function escape_click() {
    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            $('#canvas').trigger('click');
        }
    });
}

/**
 * On key press, fades out error element and then empties it to prepare it for a new one.
 *
 * Evaluates whether the keypress is a meta key (eg.: CMD + A or CTRL +C). If keypress is
 * metakey, do not clear the error message.
 */
function remove_error_message() {
    $('input, textarea').keypress(function(e){

        var input = $(this); // Current input element.

//        var non_chars = [9, 27];

        // Find the .error element for the form input.
        var error_msg = input.prev('.error');

        // Flag for whether the keypress is meta or not.
        var non_character;

        if (e.metaKey == true || e.keyCode == 9 || e.keyCode === 27) {
            non_character = true;
        } else {
            non_character = false;
        }

        // Flag for whether the error element already has a message.
        var error_empty = error_msg.is(':empty')

        // Flag for whether
        var clear_message;

        // If the error element is not empty and the keypress was not meta,
        // set flag to empty the error element.
        if (error_empty == false && non_character == false) {
            clear_message = true;
        } else {
            clear_message = false;
        }

        // milliseconds passed to fadeOut() and setTimeOut if the error message needs
        // to be cleared.
        var time_out = 500;

        // If clear_message if TRUE, fadeOut the error message and empty it afterwards to
        // prepare the element for any new error message. Else, nada.
        switch(clear_message) {
            case true:
                // Fade the error out.
                error_msg.fadeOut(time_out);
                // Wait .5 second and empty the error. Set the error div to display
                // again to prepare it for any new error.
                setTimeout(function() {
                    error_msg.empty();
                    error_msg.show();
                }, time_out);

                break;
            default:
                break;
        }
    }); // end keypress
}

function remove_just_added() {
    var just_added = $(document).find('.just_added');
    setTimeout(function() {
        just_added.removeClass('just_added');
    }, 3000);
}

$(document).ready(function(){
    new stop_form_prop();
    new click_add_user();
    new canvas_click();
    new remove_just_added();
    new ajax_edit();
    new remove_error_message();
});