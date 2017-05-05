/**
 * Allows Rigby user to reduce and expand elements with the 'box_cont' class by clicking on the
 * 'carrot' button set in the upper right of the element.
 */
function box_toggle() {
    $('.box_toggle').on('click', function(){
        var box_cont = $(this).parent().find('.box_cont');
        var tog = $(this).parent().find('.tog');
        
        if ($(this).hasClass('clicked')) {
            $(this).removeClass('clicked');
        } else {
            $(this).addClass('clicked');
        }
        if ($(this).hasClass('clicked')) {
            var click_state = 1;
        } else {
            var click_state = 0;
        }
        switch(click_state) {
            case 1:
                box_cont.hide();
                tog.removeClass('toggle_up');
                tog.addClass('toggle_down');
                break;
            case 0:
                box_cont.show();
                tog.removeClass('toggle_down');
                tog.addClass('toggle_up');
                break
        }
    });
}

$(document).ready(function(){
    new box_toggle();
});