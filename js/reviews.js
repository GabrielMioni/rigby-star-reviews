/* jQuery Input Validation */
function text_input_err_remove() {
    $('.text_input, .selectprod').on('input',function(e){
        if (($(this).hasClass('err_input'))&&($(this).attr('name') !== 'email')) {
            $(this).removeClass('err_input');
            var err = $(this).prev('.err');
            $(err).fadeOut();
        }
    });
}
function remove_err_class(target) {
    if ($(target).hasClass('err_input')) {
        $(target).removeClass('err_input');
    }
}
function check_email(email_var) {
    // console.log(email_var);
    var email_regex = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
    var email_val = email_var.value;
    // console.log(email_val);
    if (email_regex.test(email_val)) {
        if ($(email_var).hasClass('err_input')) {
            $(email_var).removeClass('err_input');
            var err_div = $(email_var).parent().find('.err');
            err_div.fadeOut(function(){
                err_div.empty();
            });
        }
    } else {
        if (email_val !== '') {
            $(email_var).addClass('err_input');
            var err_obj = $(email_var).parent().find('.err');
            add_err(err_obj, 'Please enter a valid email address');
        }
    }
}
function add_err(err_var, err_content) {
    err_val = $(err_var).html();
    // console.log(err_val);
    // console.log('Error Value: '+err_val);
    if (err_val === '') {
//        $(err_var).append('Please enter a valid email.');
        $(err_var).append(err_content);
    }
    $(err_var).fadeIn();
}
function validate_email_keyup() {
    $('#email').on('focus', function(){
        if ($(this).hasClass('err_input')) {
            $(this).keyup(function(){
                new check_email(this);            
            }); // end keyup
        } // end if
    }); // end on
}
function validate_email() {
    $('#email').blur(function() {
        var email_val = $(this).val();
        if (email_val !== '') {
            new check_email(this);
        }
    });
}

/* JS Submit */
function test_whitespace(text_input) {
    var test = /^\s*$/.test(text_input);
    if (test === true) {
        return -1;
    } else {
        return text_input;
    }
}
function test_email(email_input) {
    var test_email = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i.test(email_input);
    if (test_email === false) {
        return -1;
    } else {
        return email_input;
    }
}
function test_stars(star_val) {
    if (star_val == 0) {
        return -1;
    } else {
        return star_val;
    }
}
function check_input(input_id, input_val, err_content) {
    if (input_val === -1) {
        var id_div = '#'+input_id;
        var parent_div  = $(id_div).parent();
        var err_div     = parent_div.find('.err');        
        $(id_div).addClass('err_input');
        add_err(err_div, err_content);
    }
}

//function ajax_submit(form, form_act, form_data) {
function ajax_submit(form) {
    var form_act  = $(form).attr('action');
    var form_data = $(form).serialize();    
    var submit_data = 'submit=1&'+form_data;

    $.ajax({
        type: 'POST',
        url: form_act,
        data: submit_data
    }).done(function(response) {
        // console.log(response);
        // Clear the form.
        $(form).fadeOut(200,function(){
            new clear_form(form);
            new append_thanks(this);
            
            var get_page = 1;            
            new get_reviews(get_page);    
            new get_sidebar();
            new set_pagecount(get_page);
        });
    });
}
function clear_form(form) {
    $(form).find('.text_input').val('');
    $('#star_select .star_full').each(function(){
        if ($(this).hasClass('clicked')) {
            $(this).removeClass('clicked');
        }        
    }); // end each
    new star_empty();
    $('#product :nth-child(1)').prop('selected', true);
}
function append_thanks(form) {
    var thanks_msg = "<div id='ajax_success'>\n\
                          <h2>Thank you!</h2>\n\
                          <p>Your review has been received.</p>\n\
                      </div>";
    $(form).parent().append(thanks_msg);
    $('#ajax_success').fadeIn(500);    
}
function js_submit() {
    $('#submit_btn').on('click', function(e){
        e.preventDefault();
        
        var form = $(this).parent();
        
        var input_check = [];
        
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

        var search_empty = input_check.indexOf(-1);
        if (search_empty !== -1) {
            check_input('star_select',  star_vote,  'You need to rate it first.');
            // console.log(star_vote);
            check_input('title_id',     title,      'Please enter a title.');
            check_input('comment',      comment,    'Please enter a comment.');
            check_input('name',         name,       'Please enter a name.');
            check_input('email',        email,      'Please enter a valid email.');
            check_input('product',      product,    '');
        } else {
            new ajax_submit(form);
        }
    }); // end on click
}

/* Navigation */



/* Rating Select */





$(document).ready(function(){
    new display_js_stars();
    new star_hover();
    new star_click();
    new text_input_err_remove();
    new validate_email();
    new validate_email_keyup();
    new set_selected_star();
    new js_submit();
    
    new rating_select();
//    new pagination_click();
    new rating_hover();
    new reset_reviews();

}); // end ready


        
