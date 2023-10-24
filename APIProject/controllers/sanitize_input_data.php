<?php
function sanitize_input_data($input_data, $db)
{
    $sanitized_data = array();

    foreach ($input_data as $key => $value) {
        // Using real_escape_string to prevent SQL injections
        $escaped_value = $db->real_escape_string($value);

        // Using htmlentities to prevent XSS attacks
        $sanitized_value = htmlentities($escaped_value, ENT_QUOTES, 'UTF-8');

        $sanitized_data[$key] = $sanitized_value;
    }

    return $sanitized_data;
}
