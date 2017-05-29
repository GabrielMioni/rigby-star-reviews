/**
 * @author Gabriel Mioni
 *
 * This file is responsible for JS at ../products.php
 */

/**
 * Toggles all checkboxes in each child tr elements on the #product_table tbody.
 */
function toggle_checkboxes()
{
    var check_toggle = $('.checkbox_toggle');
    var table_body  =  $('#product_table').find('tbody');

    check_toggle.on('change', function(){

        var checked_state = check_toggle.is(':checked');

        table_body.find('tr').each(function(){

            var tr_checkbox = $(this).find('input[type="checkbox"]');

            tr_checkbox.prop('checked', checked_state);
        });
    });
}

/**
 * Clicks associated radio button if the user clicks a label for a radio button.
 */
function click_label()
{
    $('.date_type_select').find('label').on('click', function(){
        var radio = $(this).prev('input');
        radio.click();
    });
}

/**
 * Toggles display of date range / date set text inputs.
 */
function toggle_date_inputs()
{
    var date_radio = $('.date_rad input:radio');
    var date_start = $('.product_date_start');
    var date_end   = $('.product_date_end');
    var date_set   = $('.product_date_set');

    $(date_radio).on('click', function(){
        var value = $(this).val();
        switch (value)
        {
            case '0':
                date_start.hide();
                date_end.hide();
                date_set.css('display', 'block');
                break;
            case '1':
                date_start.css('display', 'inline-block');
                date_end.css('display', 'inline-block');
                date_set.hide();
                break;
        }
    });
}

/**
 * Toggles the UI for editing product data in the the tabel row associated with the edit button that's clicked.
 */
function toggle_edit() {
    $('.edit_button').find('a').on('click', function(){
        var edit_button = $(this).parent();
        var is_clicked = edit_button.hasClass('clicked');

        var parent_tr    = edit_button.parent();
        var prod_id_td   = parent_tr.find('.product_id');
        var prod_name_td = parent_tr.find('.product_name');
        var update_btn   = parent_tr.find('.edit_submit');

        if (is_clicked == false)
        {
            var prod_id_val   = prod_id_td.html();
            var prod_name_val = prod_name_td.html();

            var prod_id_input   = "<input class='prod_input' type='text' value='"+prod_id_val+"'>";
            var prod_name_input = "<input class='prod_input' type='text' value='"+prod_name_val+"'>";

            prod_id_td.empty();
            prod_id_td.append(prod_id_input);

            prod_name_td.empty();
            prod_name_td.append(prod_name_input);
            update_btn.show();
        } else {
            prod_id_val    = prod_id_td.find('input').val();
            prod_name_val = prod_name_td.find('input').val();

            prod_id_td.empty();
            prod_id_td.append(prod_id_val);

            prod_name_td.empty();
            prod_name_td.append(prod_name_val);
            update_btn.hide();
        }
    });
}

/**
 * Blank content can't be accepted to the products table. If the user tries to leave a form input blank on blur,
 * this function restores the original input value and provides an error message. Also works if the user tries
 * to submit with blank fields.
 */
function flag_changed() {

    $(document).on('focus', "input.prod_input", function(){

        var orig_value = $(this).val();
        var new_value  = '';

        var input_parent = $(this).parent();

        $(this).on('keyup', function(){

            // Evaluate whether 'changed' has already been added to the parent element.
            var not_changed = (!input_parent.hasClass('changed') == true);

            // Get the new value entered on every keyup.
            new_value = $(this).val();

            var compare = (orig_value !== new_value);

            /*
             *  Initialize flag. If false, 'changed' class is not added to parent element. Else,
             *  'changed' class is added.
             */
            var set_changed = false;

            /* If 'changed' class is already present, don't add it. */
            if (not_changed == true)
            {
                set_changed = true;
            }

            /* If the original input and new input match, do not add the the 'changed' class. */
            if (compare == false)
            {
                set_changed = false;
            }

            switch (set_changed) {
                case true:
                    input_parent.addClass('changed');
                    break;
                case false:
                    input_parent.removeClass('changed');
                    break;
                default:
                    break;
            }
        });
    });
}

/**
 * Blank content can't be accepted to the products table. If the user tries to leave a form input blank on blur,
 * this function restores the original input value and provides an error message. Also works if the user tries
 * to submit with blank fields.
 */
function restore_content_if_blank() {

    // On focus, collect data about the input element and its parent tr and td elements.
    $(document).on('focus', "input.prod_input", function(){

        this.input_elm   = $(this);
        this.orig_value  = $(this).val();
        this.td_parent   = $(this).parent();
        this.tr_parent   = $(this).closest('tr');

        // Get the class name and convert it into a pretty to read title.
        var column_type  = this.td_parent.attr('class').replace('_', ' ');
        this.column_type = title_case(column_type);

    }).on('blur', 'input.prod_input', function(){

        var new_value = this.input_elm.val();

        if (new_value == '')
        {
            // Reset the original value into the input value.
            this.input_elm.val(this.orig_value);

            // Find the .message element for the parent tr and empty it if it already has content.
            var msg_td = this.tr_parent.next('.message').find('td');
            if (msg_td.length > 0)
            {
                msg_td.empty();
            }

            // Build the error message and wrap it in an .error div.
            var msg_cont = this.column_type + ' cannot be blank.';
            var error = '<div class="error">' + msg_cont + "</div>";

            // Set the error message. Remove the 'changed' class from the input's parent element.
            msg_td.append(error);
            this.td_parent.removeClass('changed');
        }
    });
}

/**
 * Formats var str to have an uppercase on every word present in the str.
 *
 * @param str String that needs formatting.
 * @returns {void|string|XML} The formatted string.
 */
function title_case(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

/**
 * Look for parent elements of text inputs that have the 'changed' class flag. Build a string that holds post
 * data and submit that to products_update.php by calling the update_products_table function.
 *
 * If no elements have the 'changed' class, display an error message.
 */
function submit_update() {
    $('.edit_submit').on('click', function(){

        // Identify the parent tr element.
        var parent_tr = $(this).closest('tr');

        // Get the real sql id from the hidden class set on the parent tr.
        var sql_id  = parent_tr.attr('class').replace(/\D/g,'');

        // Identify each input's parent element that has the 'changed' class.
        var changed = parent_tr.find('.changed');

        // Initialize the post_data string. This will be passed to products_update.php during the Ajax call.
        var post_data = 'ajax_submit=1&id=' + sql_id;

        /*  ******************************************************************
         *  - Check to make sure there are elements with the 'changed' class.
         *  - If there are 'changed' elements, loop through each element.
         *  *****************************************************************/
        if (changed.length > 0) {
            changed.each(function(){

                // Initialize input_type var. This will hold the column name that needs new
                // post data sent to products_update.php
                var input_type;

                var input_td = $(this);

                // Get the child input element and its value.
                var input_elm = input_td.find('.prod_input');
                var input_val = input_elm.val();

                // Set var input_type by evaluating which column class is present.
                if (input_td.hasClass('product_id')) {
                    input_type = 'product_id';
                }
                if (input_td.hasClass('product_name')) {
                    input_type = 'product_name';
                }

                // Add data to the post_data var.
                post_data += '&' + input_type + '=' + input_val;

            });
            // Finish the loop and submit the Ajax call.
            update_products_table(parent_tr, post_data);

        } else {
            // If no elements have the 'changed' class, let the user know that no submit was made
            // by displaying an error message.

            var message_td = $(this).closest('tr').next('.message').find('td');

            if (message_td.length > 0)
            {
                message_td.empty();
            }
            message_td.append('<div class="error">No change detected</div>');
        }
        // Remove the 'changed' class from all elements where that class is set.
        changed.removeClass('changed');
    });
}

/**
 * Starts an Ajax call to products_update.php. The function also processes the response and displays a message
 * in the '.message' element's child td.
 *
 * @param tr_elm    Parent tr of the '.edit_submit' button that's clicked to update a record. This is passed to
 *                  the display_response() function to display a message in the closest '.message' td element.
 * @param submit_data The post data string set in the submit_update function.
 */
function update_products_table(tr_elm, submit_data) {

    $.ajax({
        type: 'POST',
        url: 'php/products_update.php',
        data: submit_data
    }).done(function(response) {
        // response is a JS array that's JSON encoded.
        display_response(tr_elm, response);
    });
}

/**
 * Displays a message to the user letting them know whether the update was successful or not.
 *
 * @param tr_elm    The parent tr element of the '.edit_submit' button that's clicked to update the products
 *                  record. This is used to find the associated '.message' child td element.
 * @param response JSON encoded array sent from products_update.php
 */
function display_response(tr_elm, response) {

    // Get the '.message' tr element. The child td is where the response will be appended.
    var message_tr = tr_elm.next('.message');
    var message_td = message_tr.find('td');

    // If the td element has content, empty it.
    if (message_td.length > 0)
    {
        message_td.empty();
    }

    // Initialize the class type. This will be used to hold the name of either '.good' or '.error' class
    // depending on the var response data.
    var class_type;

    // Decode the JSON data.
    var response_array = JSON.parse(response);

    // Get the first element from response_array. This is either 0 (submit failed) or 1 (submit successful).
    var msg_type = response_array[0];

    // Initialize variable to hold message content.
    var msg_cont = '';

    switch (msg_type)
    {
        case 0:
            class_type = 'error';
            break;
        case 1:
            class_type = 'good';
            break;
        default:
            class_type = '';
            break;
    }

    var m = 1;
    while (m < response_array.length)
    {
        msg_cont += response_array[m] + ' ';
        ++m;
    }
    msg_cont = msg_cont.trim();

    // Wrap the message content in an element with either the .good or .error class. Append the content
    // to the message_td element.
    var response_elm = '<div class="'+class_type+'">'+msg_cont+'</div>';
    message_td.append(response_elm);
}

/**
 * Entering return when on a product input will submit a call to update the record by simulating a click
 * on the '.update_submit' button.
 */
function edit_enter() {
    $(document).keypress(function (e) {
        if (e.which == 13) {

            var focused = $(':focus');
            if (focused.hasClass('prod_input'))
            {
                e.preventDefault();
                var update_button = focused.parent().next('.update_submit').find('button');
                update_button.click();
            }
        }
    });
}

$(document).ready(function () {
    // Display all edit buttons
    $('.edit_button').find('a').show();
    $('.checkbox_toggle').show();
    toggle_date_inputs();
    click_label();
    toggle_checkboxes();
    toggle_edit();
    edit_enter();
    flag_changed();
    restore_content_if_blank();
    submit_update();
});