<?php

class abstract_widget
{
    protected $page;
    protected $per_page;
    protected $rating;
    protected $product_id;

    protected $start;
    protected $review_count;
    protected $last_page;

    public function __construct($page, $reviews_per_page, $rating, $product_id)
    {
        $this->rating       = $this->set_rating($rating);
        $this->product_id   = $this->set_product_id($product_id);
        $this->review_count = $this->set_review_count($this->rating, $this->product_id);
        $this->per_page     = $this->set_reviews_per_page($reviews_per_page);
        $this->last_page    = $this->set_last_page($this->review_count, $this->per_page);
        $this->page         = $this->set_page($page, $this->last_page);
        $this->start        = $this->set_start($this->page, $this->per_page);
    }

    protected function set_rating($rating)
    {
        if (trim($rating) == '')
        {
            if (isset($_GET['rating']))
            {
                return htmlspecialchars($_GET['rating']);
            }
        } else {
            return htmlspecialchars($rating);
        }
        return false;
    }

    protected function set_product_id($product_id) {
        if (trim($product_id == ''))
        {
            return false;
        } else {
            return htmlspecialchars($product_id);
        }
    }

    protected function set_review_count($rating, $product_id)
    {
        $query = 'SELECT count(*) FROM star_reviews';

        $pdo_array = array();

        if ($rating !== false || $product_id !== false)
        {
            $query .= ' WHERE ';
        }
        if ($rating !== false)
        {
            $query .= ' stars LIKE ? AND';
            $pdo_array[] = $rating;
        }
        if ($product_id !== false)
        {
            $query .= ' product LIKE ?';
            $pdo_array[] = $product_id;
        }

        $query = rtrim($query, 'AND');

        try {
            $result = sql_pdo::run($query, $pdo_array)->fetchColumn();
            return $result;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    protected function set_reviews_per_page($reviews_per_page)
    {
        $out = 8;
        if (trim($reviews_per_page !== ''))
        {
            $out = (int)$reviews_per_page;
        }
        if ($out < 1)
        {
            $out = 1;
        }
        return $out;
    }

    protected function set_last_page($review_count, $reviews_per_page)
    {
        if ($review_count <= 0)
        {
            return 1;
        } else {
            return ceil($review_count / $reviews_per_page);
        }
    }

    protected function set_page($page, $last_page)
    {
        if (trim($page) !== '')
        {
            $out = htmlspecialchars($page);
        } else {
            $out = 1;
        }

        if (trim($page) == '')
        {
            if (isset($_GET['page']))
            {
                $out = htmlspecialchars($_GET['page']);
            } else {
                $out = 1;
            }
        }

        /* Rigby user can never occupy a page greater than the last page. */
        if ($out > $last_page)
        {
            $out = $last_page;
        }

        /* Page can never be less than 1*/
        if ($out < 1)
        {
            $out = 1;
        }

        return $out;
    }

    protected function set_start($page, $reviews_per_page)
    {
        return ($page - 1) * $reviews_per_page;
    }
}
