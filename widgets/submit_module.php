<?php
if (!defined('RIGBY_ROOT'))
{
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/products_array_set.php');

class submit_module
{
    protected $product_id;
    protected $product_array;
    protected $thank_you;
    protected $review_form;

    public function __construct($product_id = false)
    {
        $this->product_id     = $this->set_product_id($product_id);
        $this->product_array  = $this->set_product_array($this->product_id);
        $this->thank_you      = isset($_GET['thankyou']) ? true : false;
        $this->review_form    = $this->build_review_form($this->product_id, $this->product_array, $this->thank_you);
    }

    protected function set_product_id($product_id) {
        if (trim($product_id == ''))
        {
            return false;
        } else {
            return htmlspecialchars($product_id);
        }
    }

    protected function set_product_array($product_id)
    {
        if ($product_id == false)
        {
            return product_array_set::get_product_array();
        } else {
            return array();
        }
    }

    /**
     * Sets validation errors if the Rigby user tries to submit bad data to the review form.
     *
     * @return array Contains validation errors for the review form. Array elements will be white space if no validation
     * errors were found for the key.
     */
    protected function set_form_errors()
    {
        $empty = '';
        $error_array = array();
        $error_array['email']    = $empty;
        $error_array['name']     = $empty;
        $error_array['product']  = $empty;
        $error_array['star_rev'] = $empty;
        $error_array['title']    = $empty;
        $error_array['comment']  = $empty;
        $error_array['timeout']  = $empty;

        foreach ($_SESSION as $key => $value) {
            if ($value === -1) {
                switch ($key) {
                    case 'email':
                        $error_array['email'] = 'Please enter a valid email';
                        break;
                    case 'name':
                        $error_array['name'] = 'Please enter a name.';
                        break;
                    case 'product':
                        $error_array['product'] = 'Please choose a product';
                        break;
                    case 'star_rev':
                        $error_array['star_rev'] = 'You need to rate it first!';
                        break;
                    case 'title':
                        $error_array['title'] = 'Please enter a title';
                        break;
                    case 'comment':
                        $error_array['comment'] = 'Please enter a comment';
                        break;
                    case 'timeout':
                        $error_array['timeout'] = 'Please try again later';
                        break;
                    default:
                        break;
                }
            }
        }
        return $error_array;
    }

    /**
     * Looks for $_GET['thankyou]. If found, returns true. Else, false.
     *
     * @return bool True if $_GET['thank_you'] is set. Else, false.
     */
    protected function set_thank_you()
    {
        if (isset($_GET['thank_you']))
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an array that's used to set a 'selected' value for the drop down star review select
     * element. That element is only visible if JavaScript is turned off.
     *
     * @return array 5 element array with keys representing each select option for a star review.
     */
    protected function set_selected_star()
    {
        $empty = '';
        $selected_star_array = array();
        $selected_star_array['sel_1'] = $empty;
        $selected_star_array['sel_2'] = $empty;
        $selected_star_array['sel_3'] = $empty;
        $selected_star_array['sel_4'] = $empty;
        $selected_star_array['sel_5'] = $empty;

        if (isset($_SESSION['star_rev'])) {
            $star_selected  = true;
            $star_value     = $_SESSION['star_rev'];
        } else {
            $star_selected  = false;
            $star_value     = 0;
        }

        if ($star_selected == TRUE && $star_value >= 1 && $star_value <= 5) {
            $set_sel   = 'sel_' . $star_value;
            $selected_star_array[$set_sel] = ' selected';
        }
        return $selected_star_array;
    }

    /**
     * Used to return 'err_input' to populate input elements. The class puts a red border around the element.
     *
     * @param $error_element
     * @return string
     */
    protected function set_form_input_red($error_element)
    {
        if ($error_element !== '') {
            return 'err_input';
        }
    }

    /**
     * Populates an array with $_SESSION data storing previous text inputs. Used to populate input elements when
     * build_review_form::build_review_form() sets HTML.
     *
     * @return array Returns array holds values passed from $_SESSION variables from previous inputs.
     */
    protected function set_form_input_values()
    {
        $form_input_array = array();

        $empty = '';
        $form_input_array['title']   = $empty;
        $form_input_array['comment'] = $empty;
        $form_input_array['name']    = $empty;
        $form_input_array['email']   = $empty;
        $form_input_array['rating']  = $empty;

        if (isset($_SESSION['title'])) {
            $form_input_array['title'] = $_SESSION['title'];
        }
        if (isset($_SESSION['comment'])) {
            $form_input_array['comment'] = $_SESSION['comment'];
        }
        if (isset($_SESSION['name'])) {
            $form_input_array['name'] = $_SESSION['name'];
        }
        if (isset($_SESSION['email'])) {
            $form_input_array['email'] = $_SESSION['email'];
        }
        /* Set rating request */
        if (isset($_GET['rating'])) {
            $form_input_array['email'] = $_GET['rating'];
        }

        return $form_input_array;
    }

    /**
     * If $session_element is -1, returns whitespace. Else, returns value of $session_element. Used to set values for
     * inputs. Parses elements from the array produced by build_review_form::set_form_input_values().
     *
     * @param $session_element int|string The element from build_review_form::set_form_input_values()
     * @return string If $session_element is -1, returns whitespace. Else, returns $session_element.
     */
    protected function session_form($session_element)
    {
        if ($session_element !== -1)
        {
            return htmlspecialchars($session_element);
        } else {
            return '';
        }
    }

    /**
     * Looks at $_SESSION['product'] to see if a product option has been previously selected. Builds HTML for the
     * options in the product select element, including a 'selected' value for the element corresponding to
     * $_SESSION['product'] if found.
     *
     * @param $product_id bool|string
     * @param array $product_array The array holding product names and codes.
     * @return string HTML for the option elements nested in the product select option.
     */
    protected function set_product_input($product_id, array $product_array, $error_class_product)
    {
        if ($product_id == false)
        {
            $options = '';

            if (isset($_SESSION['product']))
            {
                $product = $_SESSION['product'];
            } else {
                $product = -1;
            }

            foreach ($product_array as $key => $value) {
                if ($product === $key) {
                    $options .= "<option selected='selected' value='$key'>$value</option>";
                } else {
                    $options .= "<option value='$key'>$value</option>";
                }
            }

            $select_element =   "<label><select id='product' class='selectprod $error_class_product' name='product'>
                                        <option class='first_opt' value=''>Please select a product or service to review</option>
                                        $options
                                    </select></label>";

            return $select_element;
        } else {
            $hidden_input = "<input type=hidden name='product' value='$product_id'>";
            return $hidden_input;
        }
    }

    /**
     * @param $thank_you
     * @return string
     */
    protected function build_review_form($product_id, $product_array, $thank_you)
    {
        if ($thank_you == true)
        {
            return "<div id='rev_success'><h2>Thank you!</h2><p>Your review has been received</p></div>";
        }
        if (isset($_GET['tooquick'])) {
            return "<div id='rev_success'><h2>You are submitting too reviews too fast.</h2><p>Please wait a bit and try again.</p></div>";
        }

        $selected_star_array = $this->set_selected_star();
        $sel_5 = $selected_star_array['sel_5'];
        $sel_4 = $selected_star_array['sel_4'];
        $sel_3 = $selected_star_array['sel_3'];
        $sel_2 = $selected_star_array['sel_2'];
        $sel_1 = $selected_star_array['sel_1'];

        $error_array = $this->set_form_errors();

        $error_star    = $error_array['star_rev'];
        $error_title   = $error_array['title'];
        $error_comment = $error_array['comment'];
        $error_name    = $error_array['name'];
        $error_email   = $error_array['email'];
        $error_product = $error_array['product'];

        $error_class_star    = $this->set_form_input_red($error_star);
        $error_class_title   = $this->set_form_input_red($error_title);
        $error_class_comment = $this->set_form_input_red($error_comment);
        $error_class_name    = $this->set_form_input_red($error_name);
        $error_class_email   = $this->set_form_input_red($error_email);
        $error_class_product = $this->set_form_input_red($error_product);

        $input_values_array = $this->set_form_input_values();

        $input_title   = $this->session_form($input_values_array['title']);
        $input_comment = $this->session_form($input_values_array['comment']);
        $input_name    = $this->session_form($input_values_array['name']);
        $input_email   = $this->session_form($input_values_array['email']);

        $product_input = $this->set_product_input($product_id, $product_array, $error_class_product);

        $url = $this->get_full_url();

        $review_stars_js = $this->get_js_filepath('review_stars');
        $review_form_js  = $this->get_js_filepath('js_form');


        $form_html = "    <form id='review_form' class='' name='contact' action='php/review_submit_act.php' method='post'>
                            <h2>Leave a review!</h2>
                            <div class='row'>
                                <div id='star_row'>
                                    <select id='star_vote' name='star_rev'>
                                        <option value='5'$sel_5>&#9733;&#9733;&#9733;&#9733;&#9733;</option>
                                        <option value='4'$sel_4>&#9733;&#9733;&#9733;&#9733;</option>
                                        <option value='3'$sel_3>&#9733;&#9733;&#9733;</option>
                                        <option value='2'$sel_2>&#9733;&#9733;</option>
                                        <option value='1'$sel_1>&#9733;</option>
                                    </select>
                                    <script type='text/javascript'>
                                        var star_vote = $('#star_vote');
                                        star_vote.hide();
                                    </script>
                                    <div id='star_select' class='$error_class_star'>
                                        <div class='star star_empty' id='1'></div>
                                        <div class='star star_empty' id='2'></div>
                                        <div class='star star_empty' id='3'></div>
                                        <div class='star star_empty' id='4'></div>
                                        <div class='star star_empty' id='5'></div>
                                    </div>
                                    <div class='err'>$error_star</div>
                                </div>
                            </div>
                            <div class='row'>
                                <label for='title_id' id='title_label'>Title</label><div class='err'>$error_title</div>
                                <input id='title_id' name='title' value='$input_title' class='$error_class_title' type='text'>
                            </div>
                            <div class='text_row'>
                                <label for='comment'>Review</label><div class='err'>$error_comment</div>
                                <textarea id='comment' name='comment' placeholder='Write your review here!' class='text_input $error_class_comment'>$input_comment</textarea>
                            </div>
                            <div class='row'>
                                <label for='name' id='name_label'>Name</label><div class='err'>$error_name</div>
                                <input id='name' name='name' placeholder='First name and last initial, please.' value='$input_name' type='text' class='text_input $error_class_name'>
                            </div>
                            <div class='row'>
                                <label for='email' id='email_label'>Email</label><div class='err'>$error_email</div>
                                <input name='email' id='email' placeholder='Your email address will not be published.' value='$input_email' class='text_input $error_class_email' type='text'>                        
                            </div>
                            <div class='row'>
                                $product_input
                            </div>
                            <input name='url' type='hidden' value='$url'>
                            <input name='submit' class='button' id='submit_btn' value='Submit' type='submit'>
                            <input name='convefe' class='convefe' tabindex='-1' type='text'>
                        </form>
                        <script type='text/javascript' src='$review_stars_js'></script>
                        <script type='text/javascript' src='$review_form_js'></script>";


        return $form_html;
    }

    function get_js_filepath($js_file_name)
    {
        $path = RIGBY_ROOT;
        $approot = substr($path,strlen($_SERVER['DOCUMENT_ROOT']));
        $url  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $js_file = $_SERVER["SERVER_NAME"] . $approot . "/js/$js_file_name.js";

        return $url . '://' . $js_file;
    }

    function get_full_url()
    {
        $url  = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $url .= '://' . $_SERVER['HTTP_HOST'];
        $url .= strtok($_SERVER["REQUEST_URI"],'?');

        return $url;
    }

    public function return_submit_module()
    {
        return $this->review_form;
    }

}
