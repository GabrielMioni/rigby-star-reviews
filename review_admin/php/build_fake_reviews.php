<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

session_start();

if (!defined('RIGBY_ROOT')) {
    include_once('../../rigby_root.php');
}

require_once(RIGBY_ROOT . '/php/sql_pdo/sql_define.php');
require_once(RIGBY_ROOT . '/php/sql_pdo/sql_pdo.php');
require_once('Faker/src/autoload.php');


class build_fake_reviews {
    protected $time_slots  = array();
    protected $fake_review = array();

    protected $faker_obj;

    protected $is_ajax;
    protected $date_start;
    protected $date_end;
    protected $review_count;

    protected $created_count = 0;

    protected $problem_msg  = null;
    protected $ajax_msg = 1;

    protected $insert_successful;

    public function __construct()
    {
        $this->is_ajax      = isset($_POST['is_ajax'])    ? true                 : false;
        $this->date_start   = isset($_POST['date_start']) ? $_POST['date_start'] : false;
        $this->date_end     = isset($_POST['date_end'])   ? $_POST['date_end']   : false;
        $this->review_count = isset($_POST['fake_review_count']) ? $_POST['fake_review_count'] : false;

        $this->problem_msg = $this->check_start_end_dates($this->date_start, $this->date_end);
        $this->problem_msg = $this->check_for_empty_inputs($this->date_start, $this->date_end, $this->review_count);

        $this->check_problem($this->problem_msg, $this->is_ajax);

        $this->faker_obj = Faker\Factory::create();

        $this->time_slots  = $this->build_timeslots_array($this->date_start, $this->date_end, $this->review_count);
        $this->fake_review = $this->build_fake_reviews($this->faker_obj, $this->time_slots);
        $this->insert_successful = $this->insert_fake_reviews($this->fake_review);

        $this->process_success($this->insert_successful, $this->created_count);
        $this->check_problem($this->problem_msg, $this->is_ajax);

    }

    protected function check_for_empty_inputs($start, $end, $count)
    {
        if (trim($start) == '' || trim($end) == '' || trim($count) == '')
        {
            return 'Fields cannot be blank.';
        }
    }

    protected function check_start_end_dates($start, $end)
    {
        $error_state = 0;
        $valid_start = $this->validate_date($start);
        $valid_end   = $this->validate_date($end);

        if ($valid_start == false || $valid_end == false)
        {
            if ($valid_start == false)
            {
                $error_state = 1;
            }
            if ($valid_end == false)
            {
                $error_state = 2;
            }
            if ($valid_start == false && $valid_end == false)
            {
                $error_state = 3;
            }
        }

        if (strtotime($start) > strtotime($end))
        {
            $error_state = 4;
        }

        switch ($error_state)
        {
            case 1:
                return 'Start date is invalid.';
            case 2:
                return 'End date is invalid.';
            case 3:
                return 'End and start dates are invalid';
            case 4:
                return 'The start date cannot be later than the end date';
            case 0:
            default:
                break;
        }
    }


    protected function validate_date($date)
    {
        $nineteen_eight = strtotime('06/30/1980');
        $unix = strtotime($date);

        if ($unix < $nineteen_eight)
        {
            return false;
        }
    }

    protected function validate_fake_review_count($count_req)
    {
        $is_int = is_int($count_req);

        $less_than_5k = $count_req < 10000;

        if ($is_int == false)
        {
            return 'The review count requested must be an integer';
        }
        if ($less_than_5k == false)
        {
            return 'The review count requested must be less than 5000.';
        }

    }

    protected function check_problem($problems, $ajax_post)
    {
        if ($problems !== null)
        {
            switch ($ajax_post)
            {
                case true:
                    $this->ajax_msg = $problems;
                    break;
                case false:
                default:
                    $_SESSION['fake_reviews_msg'] = $problems;
                    $referrer = $_SERVER['HTTP_REFERER'];
                    header('Location: ' .$referrer);
                    exit;
            }
        }
    }

    protected function insert_fake_reviews(array $fake_reviews)
    {
        try {
            $stmt = sql_pdo::prepare("INSERT INTO star_reviews (title, date, stars, name, email, ip, cont, hidden) VALUES (?, ?, ?, ?, ?, ?, ?,0)");
            foreach ($fake_reviews as $review)
            {
                $pdo_array = array();
                foreach ($review as $review_data) {
                    $pdo_array[] = $review_data;
                }

                $stmt->execute($pdo_array);
            }
            return true;
        } catch (PDOException $e) {
            $this->problem_msg = "There was a problem processing your request. Please try again in a bit.";
        }
    }

    protected function process_success($insert_successful, $created_count)
    {
        if ($insert_successful == true)
        {
            $_SESSION['fake_reviews_msg'] = "Added $created_count fake reviews";
            $referrer = $_SERVER['HTTP_REFERER'];
            header('Location: ' .$referrer);
            exit;
        }
    }

    protected function build_timeslots_array($date_start, $date_end, $review_count)
    {
        $time_slots = array();

        $unix_start = strtotime($date_start);
        $unix_end   = strtotime($date_end);

        $seconds_between = $unix_end - $unix_start;

        if ($review_count == 0)
        {
            $review_count = 1;
        }

        $avg_seconds = $seconds_between / $review_count;

        $init_start = 0;

        while ($init_start < $seconds_between)
        {
            // Get a random number of seconds between 0 and $avg_seconds
            $rand_second_interval = rand(0, $avg_seconds);

            // Flip a coin to see if $rand_second_interval should be added too or subtracted from the average seconds.
            $random_more_less = rand(1,2);

            // Heads - Add
            if ($random_more_less === 1)
            {
                $random_new_seconds = $avg_seconds + $rand_second_interval;
            }
            // Tails - Subtract
            if ($random_more_less === 2)
            {
                $random_new_seconds = $avg_seconds - $rand_second_interval;

                // If the result would be negative, set $random_new_seconds to a random number between
                // 0 and half of the average seconds.
                if ($random_new_seconds < 0)
                {
                    $random_new_seconds = rand(0, ($avg_seconds / 2 ));
                }
            }

            // Add the random seconds to the starting number of seconds.
            $set_seconds = $init_start + $random_new_seconds;

            // Add the $unix_start to $set_seconds to get a unix timestamp that will make sense as a date.
            $time_slots[] = $set_seconds + $unix_start;

            // Add the value of $random_new_seconds to $init_start so that $init_start will begin the next loop
            // with the new value.
            $init_start += $random_new_seconds;
        }

        return $time_slots;
    }

    protected function build_fake_reviews(Faker\Generator $faker_obj, array $time_slots)
    {
        $review_array = array();
        foreach ($time_slots as $unix)
        {
            $name  = $this->random_name($faker_obj);
            $email = $this->random_email($faker_obj, $name);
            $cont  = $this->lorem_ipsum($faker_obj);
            $ip    = $faker_obj->ipv4;
            $title = $this->random_title($faker_obj);

            $fake_review = array();
            $fake_review['title'] = $title;
            $fake_review['date']  = date('Y-m-d H:i:s', $unix);
            $fake_review['stars'] = rand(1, 5);
            $fake_review['name']  = $name;
            $fake_review['email'] = $email;
            $fake_review['ip']    = $ip;
            $fake_review['cont']  = $cont;

            $review_array[] = $fake_review;
        }
        $this->created_count = count($review_array);

        return $review_array;
    }

    protected function lorem_ipsum(Faker\Generator $faker_obj)
    {
        $rand_paragraphs = rand(1,3);
        $paragraphs = $faker_obj->paragraphs($nb = $rand_paragraphs, $asText = false);

        $text = '';
        foreach ($paragraphs as $p)
        {
            $text .= "$p<br><br>";
        }
        return $text;
    }

    protected function random_name(Faker\Generator $faker_obj)
    {
        $first_name   = $faker_obj->firstName();
        $last_initial = strtoupper($faker_obj->randomLetter);

        return "$first_name $last_initial.";
    }

    protected function random_email(Faker\Generator $faker_obj, $name)
    {
        $email_name = strtolower(substr($name, 0, strrpos($name, ' ')));
        $domain = $faker_obj->domainName;
        $email = "$email_name@$domain";

        return $email;
    }

    protected function random_title(Faker\Generator $faker_obj)
    {
        $word_count = rand(1, 3);
        $title = $faker_obj->sentence($nbWords = $word_count, $variableNbWords = true);
        return $title;
    }
    public function return_ajax_msg()
    {
        return $this->ajax_msg;
    }
}

$worker = new build_fake_reviews();

if (isset($_POST['is_ajax']))
{
    echo $worker->return_ajax_msg();
}