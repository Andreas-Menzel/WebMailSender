<?php
    // Show all errors (only for debugging)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    $api_key = NULL;

    $from = NULL;
    $to = NULL;
    $subject = NULL;
    $message = NULL;

    // Get arguments
    if(isset($_GET['api_key']) && $_GET['api_key'] != "")
        $api_key = $_GET['api_key'];

    if(isset($_GET['from']) && $_GET['from'] != "")
        $from = $_GET['from'];
    if(isset($_GET['to']) && $_GET['to'] != "")
        $to = $_GET['to'];
    if(isset($_GET['subject']) && $_GET['subject'] != "")
        $subject = $_GET['subject'];
    if(isset($_GET['message']) && $_GET['message'] != "")
        $message = $_GET['message'];


    $response = array(
        'error' => false
    );


    // Check if every argument was supplied
    if(is_null($api_key)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: api_key is required.';
        return_response($response);
    } else if(is_null($from)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: from is required.';
        return_response($response);
    } else if(is_null($to)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: to is required.';
        return_response($response);
    } else if(is_null($subject)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: subject is required.';
        return_response($response);
    } else if(is_null($message)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: message is required.';
        return_response($response);
    }


    // Check if $from and $to are valid email adresses
    if(!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument not valid: from must be a valid email address.';
        return_response($response);
    }
    if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument not valid: to must be a valid email address.';
        return_response($response);
    }


    send_mail($api_key, $from, $to, $subject, $message);


    // This should not be executed: send_mail(...) should have sent the response
    $response['error'] = true;
    $response['errmsg'] = 'Internal server error: email not sent.';
    return_response($response);

/******************************************************************************/
/********************************* FUNCTIONS **********************************/
/******************************************************************************/

    function send_mail($api_key, $from, $to, $subject, $message) {
        // $api_key valid?
        // $from allowed?
        // $to allowed?
        // $from credentials in database?

        // send email
    }


    function return_response($response) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

?>
