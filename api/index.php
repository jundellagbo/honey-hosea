<?php

require "config.php";
require "AltoRouter.php";
require "functions.php";

/**
 * Fix access API from front end Allowed by CORS
 */
header('Access-Control-Allow-Origin: *');

/**
 * Convert output to JSON type
 */
header("Content-type: application/json");

$router = new AltoRouter();

$router->setBasePath('/app/api');

/**
 * Authentication
 */

$router->map( 'POST', '/auth/login', "auth_login");


/**
 * Students
 */

$router->map( 'GET', '/registrar/students', "students_retrieve");

$router->map( 'POST', '/registrar/student', "students_store");

/**
 * Requirements
 */

$router->map( 'POST', '/registrar/student/requirement', "requirements_store");

$router->map( 'GET', '/registrar/student/[i:id]/requirement', "requirements_retrieve");

$router->map( 'GET', '/registrar/student/requirement/[i:id]', function( $id ) {

    remove_("sp_requirements_remove", $id);

});

/**
 * Classes
 */

$router->map( 'POST', '/registrar/class', "class_store");

$router->map( 'GET', '/registrar/classes', "class_retrieve");

$router->map( 'GET', '/registrar/class/[i:id]', function( $id ) {

    remove_("sp_class_remove", $id);

});

/**
 * Sections
 */

$router->map( 'POST', '/registrar/class/section', "section_store");

$router->map( 'GET', '/registrar/class/[i:id]/sections', "section_retrieve");

$router->map( 'GET', '/registrar/class/section/[i:id]', function( $id ) {

    remove_("sp_section_remove", $id);

});


/**
 * Meta API
 */

$router->map( 'POST', '/registrar/meta', "meta_store");

$router->map( 'GET', '/registrar/meta/[:type]', "meta_retrieve");

$router->map( 'GET', '/registrar/meta/remove/[i:id]', function( $id ) {

    remove_("sp_meta_remove", $id);

});


/**
 * Autocomplete for Records
 */

$router->map( 'GET', '/registrar/records/autocomplete', "records_autocomplete");


/**
 * Records
 */

$router->map( 'POST', '/registrar/records', "records_store");

$router->map( 'GET', '/registrar/record/[i:id]', "records_retrieve");

$router->map( 'GET', '/registrar/record/remove/[i:id]', function( $id ) {

    remove_("sp_records_remove", $id);

});


/**
 * Enrollments
 */

$router->map( 'GET', '/registrar/enrollment/[i:id]', "enrollment_retrieve");

$router->map( 'POST', '/registrar/enrollment/', "enrollment_store");

/**
 * User Settings
 */

$router->map( 'POST', '/user/settings/', "user_settings" );

$router->map( 'GET', '/users/[i:id]', "user_retrieve");


/**
 * Pricing Setup
 */

$router->map( 'GET', '/registrar/pricing', "accountant_pricing");

$router->map( 'POST', '/registrar/pricing/store', "accountant_pricing_store");

$router->map( 'POST', '/registrar/book/pricing', "accountant_book_pricing");

$router->map( 'GET', '/registrar/statement/[i:id]', "accountant_statement");

$router->map( 'GET', '/registrar/enrolled/[i:id]', "accountant_student_enrolled" );

/**
 * Transaction
 */

$router->map( 'POST', '/registrar/transac', "transac_store");

/**
 * Payment
 */

$router->map( 'POST', '/registrar/newpayment', "payment_new");

$match = $router->match();

if( !$match ) {
    response(["response_error" => "404 not found"]);
    die();
}

if( $match && is_callable( $match['target'] ) ) {
    call_user_func_array( $match['target'], $match['params'] ); 
}