<?php
    // Show all errors (only for debugging)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    $sql_host     = "localhost";
    $sql_dbname   = "WebMailSender";
    $sql_user     = "WebMailSender";
    $sql_password = "WebMailSenderPass";


    $api_key = NULL;

    $from = NULL;
    $replyto = NULL;
    $to = NULL;
    $subject = NULL;
    $message = NULL;

    // Get arguments
    if(isset($_GET['api_key']) && $_GET['api_key'] != "")
        $api_key = $_GET['api_key'];

    if(isset($_GET['from']) && $_GET['from'] != "")
        $from = $_GET['from'];
    if(isset($_GET['replyto']) && $_GET['replyto'] != "")
        $replyto = $_GET['replyto'];
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
    } else if(is_null($replyto)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument missing: replyto is required.';
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
    if(!filter_var($replyto, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument not valid: replyto must be a valid email address.';
        return_response($response);
    }
    if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument not valid: to must be a valid email address.';
        return_response($response);
    }


    send_mail($api_key, $from, $replyto, $to, $subject, $message);


    // This should not be executed: send_mail(...) should have sent the response
    $response['error'] = true;
    $response['errmsg'] = 'Internal server error: email not sent.';
    return_response($response);

/******************************************************************************/
/********************************* FUNCTIONS **********************************/
/******************************************************************************/

    function send_mail($api_key, $from, $replyto, $to, $subject, $message) {
        global $response;

        global $sql_host;
        global $sql_dbname;
        global $sql_user;
        global $sql_password;

        global $api_key;

        // Connect to database
        try {
            $pdo = new PDO('mysql:host=' . $sql_host . ';dbname=' . $sql_dbname, $sql_user, $sql_password);
            $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // TODO: remove debug
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['errmsg'] = 'Internal server error: could not connect to database.';
            return_response($response);
        }

        // Get API_KEYS entry
        $sql = "SELECT * FROM API_KEYS WHERE api_key = :api_key;";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':api_key', $api_key, PDO::PARAM_STR);
        $stmt->execute();

        $API_KEYS__returnvals = $stmt->fetch(PDO::FETCH_ASSOC);

        if($API_KEYS__returnvals == false) {
            $response['error'] = true;
            $response['errmsg'] = 'API key not valid: could not find api key.';
            return_response($response);
        }

        // Check if api key is valid
        // ... oder abgelaufen

        // Check if $from is allowed
        // TODO: regex
        if($from != $API_KEYS__returnvals['mail_from']) {
            $response['error'] = true;
            $response['errmsg'] = 'Permission error: You are not allowed to send emails from this address.';
            return_response($response);
        }

        // Check if $replyto is allowed
        // TODO: regex
        if($replyto != $API_KEYS__returnvals['mail_replyto']) {
            $response['error'] = true;
            $response['errmsg'] = 'Permission error: You are not allowed to set this replyto address.';
            return_response($response);
        }

        // Check if $to is allowed
        // TODO: regex
        if($to != $API_KEYS__returnvals['mail_to']) {
            $response['error'] = true;
            $response['errmsg'] = 'Permission error: You are not allowed to send emails to this address.';
            return_response($response);
        }


        // Get EMAIL_CREDENTIALS entry
        $sql = "SELECT * FROM EMAIL_CREDENTIALS WHERE email = :email;";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':email', $from, PDO::PARAM_STR);
        $stmt->execute();

        $EMAIL_CREDENTIALS__returnvals = $stmt->fetch(PDO::FETCH_ASSOC);

        if($EMAIL_CREDENTIALS__returnvals == false) {
            $response['error'] = true;
            $response['errmsg'] = 'Internal server error: could not find credentials for from-mail in database.';
            return_response($response);
        }

        // TODO: send mail...

        $pdo = NULL;

        return_response($response);
    }


    function return_response($response) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

?>
