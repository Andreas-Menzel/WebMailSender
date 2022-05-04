<?php
    // Show all errors (only for debugging)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer-6.6.0/src/Exception.php';
    require 'PHPMailer-6.6.0/src/PHPMailer.php';
    require 'PHPMailer-6.6.0/src/SMTP.php';


    $sql_host     = "localhost";
    $sql_dbname   = "WebMailSender";
    $sql_user     = "WebMailSender";
    $sql_password = "WebMailSenderPass";


    $api_key = NULL;

    $to = NULL;
    $subject = NULL;
    $message = NULL;

    // Get arguments
    if(isset($_GET['api_key']) && $_GET['api_key'] != "")
        $api_key = $_GET['api_key'];

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


    // Check if $to is a valid email address
    if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['errmsg'] = 'Argument not valid: to must be a valid email address.';

        write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

        return_response($response);
    }


    send_mail($api_key, $to, $subject, $message);


    // This should not be executed: send_mail(...) should have sent the response
    $response['error'] = true;
    $response['errmsg'] = 'Internal server error: email not sent.';

    write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

    return_response($response);

/******************************************************************************/
/********************************* FUNCTIONS **********************************/
/******************************************************************************/

    function send_mail($api_key, $to, $subject, $message) {
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

            write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

            return_response($response);
        }

        // Check if api key is valid
        // ... oder abgelaufen

        // Check if $to is allowed - check with regex
        if(preg_match('/' . $API_KEYS__returnvals['mail_to'] . '/', $to) !== 1) {
            $response['error'] = true;
            $response['errmsg'] = 'Permission error: You are not allowed to send emails to this address.';

            write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

            return_response($response);
        }


        // Get EMAIL_SETTINGS entry
        $sql = "SELECT * FROM EMAIL_SETTINGS WHERE email = :email;";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':email', $API_KEYS__returnvals['mail_from'], PDO::PARAM_STR);
        $stmt->execute();

        $EMAIL_SETTINGS__returnvals = $stmt->fetch(PDO::FETCH_ASSOC);


        $pdo = NULL;


        if($EMAIL_SETTINGS__returnvals == false) {
            $response['error'] = true;
            $response['errmsg'] = 'Internal server error: could not find credentials for from-mail in database.';

            write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

            return_response($response);
        }


        $mail = new PHPMailer(true);

        try {
            //Server settings
            /* TODO: remove */#$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $EMAIL_SETTINGS__returnvals['host'];
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $EMAIL_SETTINGS__returnvals['username'];
            $mail->Password   = $EMAIL_SETTINGS__returnvals['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $EMAIL_SETTINGS__returnvals['port']; /* TODO: convert to int? */

            //Recipients
            $mail->setFrom($API_KEYS__returnvals['mail_from'], $API_KEYS__returnvals['name_from']);
            $mail->addReplyTo($API_KEYS__returnvals['mail_replyto'], $API_KEYS__returnvals['name_replyto']);
            $mail->addAddress($to);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
        } catch (Exception $e) {
            $response['error'] = true;
            $response['errmsg'] = 'Internal server error: email not sent: ' . $mail->ErrorInfo;

            write_log($response['error'], $response['errmsg'], $api_key, NULL, NULL, NULL, NULL, $to, $subject, $message);

            return_response($response);
        }


        write_log(0, NULL, $api_key,
                    $API_KEYS__returnvals['mail_from'], $API_KEYS__returnvals['name_from'],
                    $API_KEYS__returnvals['mail_replyto'], $API_KEYS__returnvals['name_replyto'],
                    $to, $subject, $message);


        return_response($response);
    }


    function write_log($error, $errmsg, $api_key, $mail_from, $name_from, $mail_replyto, $name_replyto, $mail_to, $subject, $message) {
        global $sql_host;
        global $sql_dbname;
        global $sql_user;
        global $sql_password;


        // Connect to database
        try {
            $pdo = new PDO('mysql:host=' . $sql_host . ';dbname=' . $sql_dbname, $sql_user, $sql_password);
            $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // TODO: remove debug
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['errmsg'] = 'Internal server error: could not connect to database.';
            return_response($response);
        }


        // Write to log
        $sql = 'INSERT INTO LOG(error, errmsg, api_key, mail_from, name_from, mail_replyto, name_replyto, mail_to, subject, message) VALUES(:error, :errmsg, :api_key, :mail_from, :name_from, :mail_replyto, :name_replyto, :mail_to, :subject, :message);';
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':error', $error, PDO::PARAM_STR);
        $stmt->bindParam(':errmsg', $errmsg, PDO::PARAM_STR);
        $stmt->bindParam(':api_key', $api_key, PDO::PARAM_STR);
        $stmt->bindParam(':mail_from', $mail_from, PDO::PARAM_STR);
        $stmt->bindParam(':name_from', $name_from, PDO::PARAM_STR);
        $stmt->bindParam(':mail_replyto', $mail_replyto, PDO::PARAM_STR);
        $stmt->bindParam(':name_replyto', $name_replyto, PDO::PARAM_STR);
        $stmt->bindParam(':mail_to', $mail_to, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);

        $stmt->execute();


        // Close database connection
        $pdo = NULL;
    }


    function return_response($response) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

?>
