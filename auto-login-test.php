<?php
/*
Template Name: Test Auto Login Template
*/

    // Define the URL where we will be sending a request for a random key
    $api_url = "http://cloud.xueba.fm/autologin-api/";
    $salt = 'SxvdhhipYePGaoPxrUDlHxhDMOuARFGaNbLsmEMDPmZYAKRCSYsONQRhejfPAifu';
    function generateRandomUserName($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    // If you are using WordPress on website A, you can do the following to get the currently logged in user:

    global $current_user;
    //$user_login = $current_user->user_login;
    $user_login = generateRandomUserName(8);
    
    $token = sha1($salt . $user_login);
    
    // Set the parameters
    $params = array(
        'action'            => 'get_login_key', // The name of the action on Website B
        'token'             => $token, // The key that was set on Website B for authentication purposes.
        'user_login'       => $user_login // Pass the user_login of the currently logged in user in Website A
    );
    
    // Send the data using cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $gbi_response = curl_exec($ch);
    curl_close($ch);
    
    // Parse the response
    parse_str($gbi_response);
    
    // Convert the response from Website B to an array
    $data = json_decode($gbi_response, true);
    
    // Set the received key to a variable
    $key = $data['key'];

    echo '<a href = "http://cloud.xueba.fm/autologin/?key='.$key.'">Xueba.fm Account</a>';

?>