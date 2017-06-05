/**
 * Controls display of both pagination and sidebar.
 *
 * Each time a link in either the pagination bar or the review count widget is clicked, the function checks for 'page'
 * and 'rating' values in the link's query string. If it finds values, var page_pointer and var rating_pointer are
 * updated appropriately. These values persist between clicks and are updated when new query string values are found.
 *
 * If a Rigby user clicks on a link in the Review Count Widget, the page_pointer is reset to 1 since the user is
 * requesting to see only ratings of a specific value.
 *
 */
function navigate_reviews()
{
    // Hold the value for 'page' and 'rating' parameters for the clicked link's href attribute. Only updated if a new parameter is encountered.
    var page_pointer = '';
    var rating_pointer = '';

    this.click_listener = function() {
        $(document).on('click', '#pagination_bar a, #review_widget a', function(e) {
            e.preventDefault();

            /** @param {string} The clicked element. */
            var anchor = $(this);
            /** @param {string} The href for the clicked element. */
            var href = anchor.attr('href');

            // Search for 'page' and 'rating' parameters in VAR href.
            var page_param   = getParameterByName('page', href);
            var rating_param = getParameterByName('rating', href);

            // If the clicked href has no page or rating parameters, set page_pointer and rating_pointer to whitespace.
            // This causes the pagination bar and sidebar to display all reviews.
            if (rating_param == null && page_param == null)
            {
                page_pointer = '';
                rating_pointer = '';
            }

            // Get ID of the container element of the clicked element.
            var parent_id = anchor.closest('[id]').attr('id');

            // Reset page_param if the Rigby user chooses to view a specific review rating value.
            if (parent_id == 'review_widget')
            {
                page_param =1;
                $(document).find('#review_widget').find('.clicked').removeClass('clicked');
                anchor.addClass('clicked');
            }

            // Update page_pointer and rating_pointer if new parameters are found in the VAR href.
            if (page_param !== null)
            {
                page_pointer = page_param;
            }

            if (rating_param !== null)
            {
                rating_pointer = rating_param;
            }

            // Update #pagination_bar and #review_col
            /*
            navigation_ajax(page_pointer, rating_pointer, 'php/paginator_ajax.php', '#pagination_bar', set_element_callback);
            navigation_ajax(page_pointer, rating_pointer, 'php/sidebar_ajax.php', '#review_col', set_element_callback);
            */
            navigation_ajax(page_pointer, rating_pointer, 'widgets/ajax/sidebar_ajax.php', '#review_col', set_element_callback);
            navigation_ajax(page_pointer, rating_pointer, 'widgets/ajax/paginator_ajax.php', '#pagination_bar', set_element_callback);

            // Scroll back to the top of the page to show results.
            window.scrollTo(0,0);
        }); // end on click
    }; // end this.click_listener()

    this.click_listener();
}

/**
 * Function called to update HTML for the #pagination and #review_widget elements.
 *
 * @param {int|string} page - Page of review results requested by Rigby user.
 * @param {int|string} rating - Review rating requested by Rigby user.
 * @param {string} ajax_url - The URL post data is sent to.
 * @param {string} elm - The element passed to the callback function. Passed to callback.
 * @param {set_element_callback} callback - Callback to update HTML.
 */
function navigation_ajax(page, rating, ajax_url, elm, callback)
{
    var submit_data = 'ajax=1&page='+page+'&rating='+rating;
    $.ajax({
        type: 'POST',
        url: ajax_url,
        data: submit_data,
        success: function (response) {
            callback(elm, response);
        }
    });
}

/**
 * Clears the element specified by VAR elm and appends VAR response to the same element. Used as a callback during
 * a successful Ajax call to retireve new HTML.
 *
 * @callback set_element_callback
 * @param {string} elm - The element where response should be appended.
 * @param {string} response - HTML data from the Ajax call.
 */
function set_element_callback(elm, response)
{
    var elmement = $(document).find(elm);
    elmement.empty();
    elmement.append(response);
}

/**
 * Returns parameter data from a URL query string.
 *
 * @param {string} name - The parameter name from the VAR url query string.
 * @param {string} url - The URL being searched for the parameter specified by VAR name
 * @returns {*}
 */
function getParameterByName(name, url)
{
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

$(document).ready(function(){
    new navigate_reviews();
});