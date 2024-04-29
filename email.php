<?php
function sendEmail($to, $subject, $message, $html = false)
{
    //use the sendgrid API key
    $type = "text/plain";
    if ($html) {
        $type = "text/html";
    }
    //read api key from sg.api
    $file = fopen("E:\projects\php\php\src\sg.api", "r");
    $sendgridAPIkey = fread($file, filesize("E:\projects\php\php\src\sg.api"));
    fclose($file);
    //sendgrid API URL
    $url = 'https://api.sendgrid.com/v3/mail/send';
    //sendgrid API headers
    $authheader = 'Authorization: Bearer ' . $sendgridAPIkey;
    $headers = array($authheader, 'Content-Type: application/json');
    //sendgrid API data
    $data = array(
        "personalizations" => array(
            array(
                "to" => array(
                    array(
                        "email" => $to
                    )
                )
            )
        ),
        "from" => array(
            "email" => "admin@samgosden.tech"
        ),
        "subject" => $subject,
        "content" => array(
            array(
                "type" => $type,
                "value" => $message
            )
        )
    );
    //send the email
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //turn off SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    //return the response
    return $response;
}
