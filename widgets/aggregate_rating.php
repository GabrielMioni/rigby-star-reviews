<?php
if (!defined('RIGBY_ROOT')) {
    require_once('../rigby_root.php');
}
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Creates an HTML aggregate review widget with structured data using schema.org markdown.
 */
class aggregate_rating
{
    protected $html;

    protected $product_id;
    protected $product_name;

    protected $currency;
    protected $price;
    protected $highprice;
    protected $lowprice;

    protected $review_data;

    public function __construct(array $setting_array = array())
    {
        $this->product_id   = $this->check_setting_element($setting_array, 'product_id');
        $this->currency     = $this->check_setting_element($setting_array, 'currency');
        $this->price        = $this->check_setting_element($setting_array, 'price');
        $this->highprice    = $this->check_setting_element($setting_array, 'highprice');
        $this->lowprice     = $this->check_setting_element($setting_array, 'lowprice');

        $this->product_name = $this->set_manual_name($setting_array, $this->product_id);

        $this->review_data  = $this->get_review_data($this->product_id);

        $this->html = $this->build_aggregate_rating_widget($this->product_name, $this->review_data, $this->currency, $this->price, $this->highprice, $this->lowprice);
    }

    /**
     * If the element in $setting_array[$index_name] is set, returns the element. Else returns whitespace.
     *
     * @param array $setting_array
     * @param $index_name string the index being checked.
     * @return string Returns the array element's value if set. Else, returns whitespace.
     */
    protected function check_setting_element(array $setting_array, $index_name)
    {
        if (isset($setting_array[$index_name]))
        {
            return htmlspecialchars($setting_array[$index_name]);
        }
        return '';
    }

    /**
     * Sets the name used for the rich snippet. If $setting_array['product_name'] is defined,
     * the snippet will use that data. Otherwise, it will try to get the product name by querying
     * the products.sql table.
     *
     * @param $setting_array array Sets the name for the product.
     * @param $product_id string The product_id if it's been defined.
     * @return string Either whitespace if no name is found, or a product name.
     */
    protected function set_manual_name(array $setting_array, $product_id)
    {
        if (isset($setting_array['product_name']))
        {
            return $setting_array['product_name'];
        } else {
            $product_name = $this->get_product_name($product_id);
            return $product_name;
        }
    }

    /**
     * Tries to get the product_name from products.sql based on the $product_id. If $product_id is
     * not defined or is whitespace, function does not return data.
     *
     * @param $product_id string Either whitespace or the defined product_id from $setting_array['product_id']
     * @return string
     */
    protected function get_product_name($product_id)
    {
        if (trim($product_id) !== '')
        {
            $query = "SELECT product_name FROM products WHERE product_id = ?";

            try
            {
                $result = sql_pdo::run($query, [$product_id])->fetchColumn();
                return $result;
            } catch (PDOException $e) {
                echo $e->getMessage();
                error_log($e->getMessage());
                return '';
            }
        }
    }

    /**
     * Gets the average review rating and review count for a given product (specified by argument $product_id).
     *
     * @param $product_id string
     * @return bool|array   If results are found, returns array with 'review_count' and 'average' elements. Else,
     *                      returns false.
     */
    protected function get_review_data($product_id)
    {
        $pdo = array();
        $query = "SELECT COUNT(*) as review_count, AVG(stars) as average FROM star_reviews";
        if ($product_id !== '')
        {
            $query .= " WHERE product = ?";
            $pdo[] = $product_id;
        }

        try {
            $result =  sql_pdo::run($query, $pdo)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Builds HTML for the aggregate rating widget.
     *
     * @param $product_name string
     * @param $review_data bool|array
     * @param $currency string
     * @param $price float
     * @param $highprice float
     * @param $lowprice float
     * @return string The HTML for the aggregate rating widget.
     */
    protected function build_aggregate_rating_widget($product_name, $review_data, $currency, $price, $highprice, $lowprice)
    {

        if ($review_data == false || empty($review_data))
        {
            $html = '<div class="error">No data was found to aggregate!</div>';
            $html = "<div id='aggregate_rating'>$html</div>";
            return $html;
        }

        $review_average = number_format((float)$review_data['average'], 2, '.', '');
        $review_count = $review_data['review_count'];

        $stars = $this->set_stars($review_average);
        $currency = $this->set_currency($currency);

        $html = "<div id='aggregate_rating'>
                    <div class='review_card'>
                        <div itemscope='' itemtype='https://schema.org/Product'>
                            <span class='product_name' itemprop='name'>$product_name</span>
                            <div itemprop='aggregateRating' itemscope='' itemtype='https://schema.org/AggregateRating'>
                            <span class='avg_rating'>Average Rating:</span>
                            <div class='rating'>$stars</div></div>
                            <div class='userVotes'>
                                <div class='rev_count'>
                                    <span itemprop='ratingValue'>$review_average</span> out of <span itemprop='bestRating'> 5 </span> based on <span itemprop='reviewCount'>$review_count</span> reviews.
                                </div>
                            </div>
                        </div>";

        if (trim($price !== ''))
        {
            $html .= "<div itemprop='offers' itemscope='' itemtype='http://schema.org/AggregateOffer'>
                        <meta itemprop='priceCurrency' content='$currency'> Price $<span itemprop='price'>$price</span>
                      </div>";
        } elseif (trim($lowprice) !== '' && trim($highprice) !== '') {
            $html .= "<div itemprop='offers' itemscope='' itemtype='http://schema.org/AggregateOffer'>
                        From <meta itemprop='priceCurrency' content='$currency'>$<span itemprop='lowPrice'>$lowprice</span> to $<span itemprop='highPrice'>$highprice</span>.
                      </div>";
        }
        $html .= "</div></div>";

        return $html;
    }

    /**
     * Sets HTML to display star ratings with .png stars.
     *
     * @param $review_average float The float value for the review average being used to set the stars HTML.
     * @return string HTML to display stars.
     */
    protected function set_stars($review_average)
    {
        $int = (int)$review_average;
        $round = round($review_average);

        $star_full = '<div class="star_full"></div>';
        $star_half = '<div class="star_half"></div>';

        $stars = '';

        $r = 0;
        while ($r < $int)
        {
            $stars .= $star_full;
            ++$r;
        }
        if ($round > $int)
        {
            $stars .= $star_full;
        } elseif ($round !== $int) {
            $stars .= $star_half;
        }

        return $stars;
    }

    /**
     * If currency is not set in __constructor::$setting_array, default currency is USD. Else, will set the structured
     * data with whatever string is entered in __constructor::$setting_array['currency']
     *
     * @param $currency string Sets the currency type for the structured data snippet.
     * @return string
     */
    protected function set_currency($currency)
    {
        if (trim($currency) == '')
        {
            return 'USD';
        } else {
            return htmlspecialchars($currency);
        }
    }

    /**
     * @return string Returns the HTML for the aggregate rating widget.
     */
    public function return_aggregate()
    {
        return $this->html;
    }
}

/*
$setting_array = array();
$setting_array['product_name'] = 'Moxie Balls';
$setting_array['price'] = '25.00';
$setting_array['lowprice'] = '50.00';
$setting_array['highprice'] = '100.00';
//$setting_array['product_id'] = 'xy3';

$build_aggregate = new aggregate_rating($setting_array);
echo $build_aggregate->return_aggregate();
*/