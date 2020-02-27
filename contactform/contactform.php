<?php
include('env.php');

function url_get_contents ($url) {
	if (!function_exists('curl_init')){
		die('CURL is not installed!');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}


try
{

    if(count($_POST) == 0) throw new \Exception('Form is empty');

    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = env('secret');
    $recaptcha_response = $_POST['recaptcha_response'];

    // Make and decode POST request:
    $recaptcha = url_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    

    /*
    *  CONFIGURE EVERYTHING HERE
    */

    // an email address that will be in the From field of the email.
    $from = 'Silbaka Contact Form <silbaka@hosting.rtl.ug>';

    // an email address that will receive the email with the output of the form
    $sendTo = 'Boss Man<kanye@silbaka.com>';

    // subject of the email
    $subject = 'New message from Silbaka: '.$_POST['subject'];

    // form field names and their translations.
    // array variable name => Text to appear in the email
    $fields = array('name' => 'Name', 'subject' => 'Subject', 'email' => 'Email', 'message' => 'Message'); 

    // message that will be displayed when everything is OK :)
    $okMessage = 'Contact form successfully submitted. Thank you, we will be in touch soon!';

    // If something goes wrong, we will display this message.
    $errorMessage = 'There was an error while submitting the form. Please try Submitting again. Code: ';


    /*
    *  LET'S DO THE SENDING
    */

    // if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
    // error_reporting(E_ALL & ~E_NOTICE);



    if($recaptcha->score <= 0.5) throw new \Exception('SPA-'.$recaptcha->score);

            
    $emailText = "New Message\t Score:".$recaptcha->score."\n";

    foreach ($_POST as $key => $value) {
        // If the field exists in the $fields array, include it in the email 
        if (isset($fields[$key])) {
            $emailText .= "$fields[$key]: $value\n";
        }
    }

    // All the neccessary headers for the email.
    $headers = array('Content-Type: text/plain; charset="UTF-8";',
        'From: ' . $from,
        'Reply-To: ' . $_POST['email'],
        'Return-Path: ' . $from,
    );
    
    // Send email if the code is run on a real server, not localhost.
    if ($_SERVER["HTTP_HOST"] != "localhost") {
        mail($sendTo, $subject, $emailText, implode("\n", $headers));
        
    }

    $responseArray = array('type' => 'success', 'message' => $okMessage);
}
catch (\Exception $e)
{
    $responseArray = array('type' => 'danger', 'message' => $errorMessage.$e->getMessage());
}


// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
}
// else just display the message
else {
    echo $responseArray['message'];
}