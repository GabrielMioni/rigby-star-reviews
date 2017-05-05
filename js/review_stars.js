/**
 * Hide the HTML select drop down menu for star ratings and display JavaScript hover activated stars.
 */
function display_stars() {
    // The drop down element for selecting star reviews without JS.
    var star_vote   = $(document).find('#star_vote');
    // The containing element for JS stars.
    var star_select = $(document).find('#star_select');
    var blank_sel   = '<option selected="selected" value="0"></option>';

    /*
    star_vote.hide().promise().done(function ()
    {
        $(this).prepend(blank_sel);
        star_select.val('0');
        star_select.show();
    }); // end done
    */
    star_vote.prepend(blank_sel);
    star_select.val('0');
    star_select.show();
}

/**
 * Sets stars to display as 'filled' or 'empty' when a Rigby user hovers over a star.
 *
 * Hovering over a .star element will set all preceding stars to 'fill.' When the mouse leaves the
 * .star element, function star_empty() is called. Any .star elements without the 'clicked' class
 * will be returned to 'empty.'
 */
function star_hover()
{
    $('.star').on({
        mouseenter : function () {
            // Get the numerical id for the .star element and pass it to star_fill().
            var id = $(this).attr('id');
            star_fill(id);
        },
        mouseleave : function () {
            // Empty all .star elements that do not have a 'clicked' class.
            star_empty();
        }
    });
}

/**
 * Fills .star elements.
 *
 * Runs a loop to add the 'star_full' class to all .star elements with an id less than or equal to
 * the VAR id argument. 'star_full' is only added if the element already has a 'star_empty' class
 * (which is removed during the loop).
 *
 * @param {int} id - The numerical value of the id name of the clicked .star element.
 */
function star_fill(id)
{
    for (var s = 1 ; s <= id ; ++s)
    {
        // Build the jQuery selector and get the element.
        var target_id  = '#star_select #'+s;
        var target_elm = $(target_id);

        // If the element is empty, fill it!
        if (target_elm.hasClass('star_empty'))
        {
            target_elm.addClass('star_full');
            target_elm.removeClass('star_empty');
        }
    }
}

/**
 * Empties all .star_full elements that do not also have the 'clicked' class.
 *
 * Searches the #star_select container element for children with the .star_full class. If
 * the .star_full elements do not also have the 'clicked' class, the 'star_full' class is removed
 * and the 'star_empty' class is added.
 */
function star_empty()
{
    $('#star_select').find('.star_full').each(function(){

        var star_element = $(this);

        if (!star_element.hasClass('clicked'))
        {
            star_element.removeClass('star_full');
            star_element.addClass('star_empty');
        }
    }); // end each
}

/**
 * Adds the 'clicked' class to the .star element that's clicked on, and all proceeding .star elements.
 *
 * Also removes the 'clicked' class from any .star elements to the right of the clicked element. This frees
 * them so they can be emptied when the function star_empty() is called.
 *
 * Set the value for #star_row select and sets it to the numerical value of the .star element's id name.
 *
 * Removes any validation errors added during a bad submit (if the Rigby user didn't chose a star rating.)
 */
function star_click() {
    $('.star').on('click', function(){
        var star_clicked = $(this);

        star_clicked.addClass('clicked');
        star_clicked.prevAll().addClass('clicked');
        star_clicked.nextAll().removeClass('clicked');
        star_empty();

        var id = star_clicked.attr('id');
        $('#star_row').find('select').val(id);

        var parent = star_clicked.parent();
        var err    = $(parent).next('.err');
        remove_err_class(parent);
        $(err).fadeOut();
    });
}

function remove_err_class(target)
{
    if ($(target).hasClass('err_input')) {
        $(target).removeClass('err_input');
    }
}

$(document).ready(function () {
    display_stars();
    star_click();
    star_hover();
})