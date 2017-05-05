<?php
require_once('random_compat/lib/random.php');

/**
 * @package     Rigby
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

/**
 * Build a random string of alphanumeric characters. Alphabetic characters can be upper and lower case.
 *
 * This class is used to create a token for a cookie when the Rigby user wants to remain logged in.
 *
 * The three arguments are each integers that set the highest possible desired number for the corresponding character
 * type. The minimum will
 *
 * @param integer $max_upper_alph integer Highest possible count for uppercase characters.
 * @param integer $max_lower_alph integer Highest possible count for lowercase characters.
 * @param integer $max_number integer Highest possible count for numeric characters.
 * @used-by login_remember
 */
class build_token
{

    /**
     * @var string Holds random string of lower case alphabets.
     */
     protected $alphabet_lower_string;

    /**
     * @var string Holds random string of upper case alphabets.
     */
    protected $alphabet_upper_string;

    /**
     * @var string Holds random numbers.
     */
     protected $number_string;

    /**
     * @var string Holds password string
     */
     protected $password;

    /**
     * build_token constructor.
     * @param $max_upper_alph   integer Number for max count of uppercase alphabets
     * @param $max_lower_alph   integer Number for max count of lowercase alphabets
     * @param $max_number       integer Number for max count of numeric characters.
     */
    public function __construct($max_upper_alph, $max_lower_alph, $max_number)
    {

        $alphabet_upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet_lower   = 'abcdefghijklmnopqrstuvwxyz';
        $numbers          = '1234567890';

        // Set the requested number of characters for each type.
        $count_alphabets_upper = random_int(3, $this->set_max($max_upper_alph));
        $count_alphabets_lower = random_int(3, $this->set_max($max_lower_alph));
        $count_numbers         = random_int(3, $this->set_max($max_number));

        // Set random characters for each character type.
        $this->alphabet_lower_string = $this->set_chars($count_alphabets_upper, $alphabet_upper);
        $this->alphabet_upper_string = $this->set_chars($count_alphabets_lower, $alphabet_lower);
        $this->number_string         = $this->set_chars($count_numbers,  $numbers);

        $this->password = $this->randomize_character_order( $this->alphabet_lower_string,
                                                            $this->alphabet_upper_string,
                                                            $this->number_string);
    }

     /**
      * Sets the maximum character count requested.
      *
      * Minimum value is always 3.
      *
      * @param $max_character_count
      * @return int     If lower than 3, returns method argument integer. Else, returns a random number between 4 and the
      *                 method argument's integer value.
      */
     protected function set_max($max_character_count)
     {
         if ($max_character_count <= 3) {
             $character_count = 3;
         } else {
             $character_count = random_int(4, $max_character_count);
         }
         return $character_count;
     }

    /**
     * Return a random selection of characters from $possible_characters. Number of characters returned
     * is set by $count.
     * 
     * @param $count integer Number of random characters requested.
     * @param $possible_characters string String of characters to choose from.  
     * @return string Chosen characters.
     */
     protected function set_chars($count, $possible_characters)
     {
         $return = '';
         for ($v = 0 ; $v < $count ; ++$v) {
             $length = strlen($possible_characters);
             $char_num  = random_int(0, $length);
             $char_rand = substr($possible_characters, $char_num, 1);
             $return .= $char_rand;
         }
         return $return;
     }

    /**
     * Combine strings set in method arguments, shuffle the character and return a new string with the
     * randomly ordered characters.
     *
     * @param $lower_random
     * @param $upper_random
     * @param $number_random
     * @return string Randomly characters from the method arguments.
     */
     protected function randomize_character_order($lower_random, $upper_random, $number_random)
     {
        $combine = $lower_random . $number_random . $upper_random;
         
        $split = str_split($combine);
        
        $return = '';

        while (count($split) >0) {
            $count = count($split);
            $rand = random_int(0, $count-1);
            $return .= $split[$rand];
            array_splice($split, $rand, 1);
        }
        return $return;
     }

     /**
      * Public access for the password character string.
      *
      * @return string Returns a password token.
      */
     public function return_token()
     {
         return $this->password;
     }
}
