<?php

/**
 * Print Response through JSON
 */
function response ( $response ) {
    echo json_encode( $response );
}

/**
 * Get POST data to object
 */
function post () {
    if( isset( $_POST ) ) {
        $_POST = json_decode(file_get_contents("php://input"),true);
        return (object) $_POST;
    }
    return "";
}

/**
 * Convert to string with single quote
 */

function toString( $param ) {
    return "'" . $param . "'";
}