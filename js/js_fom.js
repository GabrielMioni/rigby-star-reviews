/* *****************
 * Form validation.
 * *****************/

/**
 * Validates form inputs on the review form. If validation fails, erros are displayed. Else, tries to submit
 * review data by passing the form element to the function ajax_submit().
 *
 */
function js_submit()
{
    $('#submit_btn').on('click', function(e){
        e.preventDefault();

        // Get the form element.
        var form = $(this).parent();

        // Array will hold results of validation checks.
        var input_check = [];

        // Check each form input value.
        var star_vote = test_stars(form.find('#star_vote').val());
        var title     = test_whitespace(form.find('#title_id').val());
        var comment   = test_whitespace(form.find('#comment').val());
        var name      = test_whitespace(form.find('#name').val());
        var email     = test_email(form.find('#email').val());
        var product   = test_whitespace(form.find('#product').val());

        input_check.push(star_vote);
        input_check.push(title);
        input_check.push(comment);
        input_check.push(name);
        input_check.push(email);
        input_check.push(product);

        // Check if any array elements have false values.
        var search_empty = input_check.indexOf(false);

        // If false is found in the input_check array, set error messages. Else, run ajax submit.
        if (search_empty !== -1) {
            set_error('star_select',  star_vote,  'You need to rate it first.');
            set_error('title_id',     title,      'Please enter a title.');
            set_error('comment',      comment,    'Please enter a comment.');
            set_error('name',         name,       'Please enter a name.');
            set_error('email',        email,      'Please enter a valid email.');
            set_error('product',      product,    '');
        } else {
            ajax_submit(form);
        }
    }); // end on click
}

/**
 * Checks if the argument VAR text_input is whitespace. If so, returns false. Else, returns VAR text_input.
 *
 * @param {string} text_input - The input value being tested.
 * @returns {boolean|string}
 */
function test_whitespace(text_input)
{
    var test = /^\s*$/.test(text_input);
    if (test === true)
    {
        return false;
    } else {
        return text_input;
    }
}

/**
 * Checks if the argument VAR star_val is not an integer, less than 0 or greater than 5. If any of those are true,
 * function will return false. Else, returns VAR star_val.
 *
 * @param star_val
 * @returns {boolean|int}
 */
function test_stars(star_val)
{
    var check_int = is_integer(star_val);

    if (star_val <= 0 || star_val > 5 || check_int == false )
    {
        return false;
    } else {
        return star_val;
    }
}

/**
 * Checks if VAR integer is an integer.
 *
 * @param integer - The value being checked.
 * @returns {boolean}
 */
function is_integer(integer)
{
    return integer % 1 === 0;
}

/**
 * Checks VAR email_input for email formatting. If email_input doesn't fit email formatting, returns false. Else,
 * returns email_input.
 *
 * @param {string} email_input
 * @returns {boolean|string}
 */
function test_email(email_input)
{
    var test_email = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i.test(email_input);
    if (test_email === false)
    {
        return false;
    } else {
        return email_input;
    }
}

/**
 * Checks VAR input_val. If the value is false, an error message is displayed in the form input's '.err' element.
 *
 * @param {string} input_id - The id of the input where an error needs to be displayed.
 * @param {boolean|string} input_val - The value of the validation check. If false, an error message will be displayed.
 * @param {string} error_content - The text that will be displayed in the error message.
 */
function set_error(input_id, input_val, error_content)
{
    if (input_val === false)
    {
        // Set the id name that is used as the selector.
        var selector_id = '#'+input_id;
        // Find the input element.
        var target_element = $(selector_id);
        // Find the parent and then the parent's child '.err' element.
        var parent_div  = target_element.parent();
        var error_elm   = parent_div.find('.err');

        target_element.addClass('err_input');

        // Append the error message.
        add_err(error_elm, error_content);
    }
}

/**
 * Checks the element in VAR error_elm. If it's empty, the function will append the message set in VAR error_content.
 *
 * @param {HTMLElement} error_elm - The '.err' element where the message in VAR error_content is being added.
 * @param {string} error_content - The error message being added.
 */
function add_err(error_elm, error_content)
{
    var err_val = $(error_elm).html();

    if (err_val === '')
    {
        $(error_elm).append(error_content);
    }
    $(error_elm).fadeIn();
}

/* *****************
 * Ajax Submit
 * *****************/

/**
 * Serializes form input values and attempts an Ajax submit to the form's action.
 *
 * If successful, a thank you message is displayed and the pagination bar and review sidebar are reloaded. This
 * uses the function navigation_ajax() which is in review_navigation.js. That file must be called before js_form.js.
 *
 * @param {HTMLElement} form - The review form element.
 * @todo Add error handling.
 */
function ajax_submit(form)
{
    var form_act  = $(form).attr('action');
    var form_data = $(form).serialize();
    var submit_data = 'submit=1&ajax=1&'+form_data;

    $.ajax({
        type: 'POST',
        url: form_act,
        data: submit_data,
        success: function (e) {
            console.log(e);
            switch (e)
            {
                case '1':
                    submit_succesful(form);
                    break;
                case '2':
                    append_post_submit_message(form, '<h2>There was a problem validating your review.</h2><p>Please make sure you\'ve filled in all the info and your email is correct.</p>');
                    break;
                case '3':
                    append_post_submit_message(form, '<h2>You are submitting too many reviews too quickly.</h2><p>Please try again in a bit.</p>');
                    break;
                case '4':
                    append_post_submit_message(form, '<h2>There was a problem submitting your review.</h2><p>Please try again in a bit.</p>');
                    break;
                default:
                    append_post_submit_message(form, '<h2>There was a problem submitting your review.</h2><p>Please try again in a bit.</p>');
                    break;
            }
        },
        error: function() {
//            console.log('Something bad happened');
        }
    }); // end Ajax
}

function submit_succesful(form)
{   var success_msg = '<h2>Thank you!</h2><p>Your review has been received.</p>';
    append_post_submit_message(form, success_msg)
    navigation_ajax('', '', 'php/paginator_ajax.php', '#pagination_bar', set_element_callback);
    navigation_ajax('', '', 'php/sidebar_ajax.php', '#review_col', set_element_callback);
}

/**
 * Removes form elment passed to the function in VAR form from DOM. Appends a thank you message to the form's parent
 * element afterwards.
 *
 * @param {HTMLElement} form The form element that needs to be removed.
 */
function append_post_submit_message(form, message)
{
    var set_message = "<div id='ajax_success'>" + message + "</div>";
    $(form).fadeOut('fast', function(){
        var parent = $(form).parent();
        $(form).remove();
        parent.append(set_message);
        $('#ajax_success').fadeIn(500);
    });
}

/* ***************************************************
 * Removing errors when new inputs values are entered.
 * ***************************************************/

/**
 * Clears an error message for the input element set by VAR elm.
 *
 * Called by functions where validation is taking place, like validate_email() and validate_text().
 *
 * @param {HTMLElement} elm - The input element where the error needs to be removed.
 */
function clear_error(elm)
{
    var error_element = elm.parent().find('.err');
    var error_msg = error_element.html();
    if (error_msg != '')
    {
        error_element.fadeOut('fast', function(){
            $(this).empty();
            $(this).show();
        });
    }
}

/**
 * While the Rigby user enters data into the #email input element, the function calls function test_email() to
 * check the input value.
 *
 * If test_email() returns true, the element's error message is removed.
 */
function validate_email()
{
    $('#email').on('input', function () {
        var email_elm = $(this);
        var email_val = email_elm.val();
        var result = test_email(email_val);

        if (result == false)
        {
            clear_error(email_elm)
        }
    });
}

/**
 * Checks if data is entered into any input. If an error is present and the input is not for email, clear the error.
 * Email validation is handled by the function validate_email().
 */
function validate_text()
{
    $('input, textarea, select').on('input', function(){
        var input_element = $(this);
        var has_error     = input_element.hasClass('err_input');
        var input_name    = input_element.attr('name')

        if (has_error == true && input_name != 'email')
        {
            clear_error(input_element);
        }
    }); // end on input
}


$(document).ready(function(){
    js_submit();
    validate_email();
    validate_text();
});