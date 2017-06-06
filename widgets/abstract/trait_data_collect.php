<?php


trait trait_data_collect
{
    /**
     * Used in concrete instances of abstract_navigate to collect specific values from the $settings_array. If a setting
     * is required and not present, the method throws an error.
     *
     * @param array $setting_array The array passed to the concrete instance's constructor that holds settings data.
     * @param $index_name string The name of the index that needs to be checked.
     * @param bool $required If false and $setting_array[$index_name] is not set, will throw error.
     * @return mixed|string The value of $setting_array[$index_name] if it is set, else will return whitespace (or error).
     */
    protected function check_setting_element(array $setting_array, $index_name, $required = false)
    {
        if (isset($setting_array[$index_name]))
        {
            return $setting_array[$index_name];
        } elseif ($required == true) {
            $error_msg = "$index_name is not set in check_setting_element ";
            trigger_error($error_msg);
        }
        return '';
    }

    /**
     * If $var is whitespace, check $_GET[$get_index] to see if a value is set.
     *
     * @param $var string The variable being checked. If whitespace, look for a value at $_GET[$get_index]
     * @param $get_index string The index name being checked in $_GET if $var is whitespace.
     * @param bool $return_false Sets whether the method should return false if $var or $_GET[$get_index] are empty.
     *
     * @return bool|mixed   If $var is whitespace and data exists at $_GET[$get_index'] returns $_GET element. If $var
     *                      is NOT whitespace, returns $var. Else, returns false.
     */
    protected function check_get($var, $get_index, $return_false = true)
    {
        if (trim($var) == '')
        {
            if (isset($_GET[$get_index]))
            {
                return htmlspecialchars($_GET[$get_index]);
            }
        } else {
            return htmlspecialchars($var);
        }
        if ($return_false == true)
        {
            return false;
        }

    }

    protected function check_settings_and_get(array $settings_array, $setting_or_get_index, $required = false)
    {
        $value = $this->check_setting_element($settings_array, $setting_or_get_index, $required);

        if (trim($value) == '')
        {
            $value = $this->check_get($value, $setting_or_get_index);
        }
        if ($value == false && $required == true)
        {
            $error_msg = "No value found for $setting_or_get_index in check_settings_and_get ";
            trigger_error($error_msg);
        }
        return $value;
    }

}