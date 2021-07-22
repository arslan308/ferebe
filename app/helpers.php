<?php

use Illuminate\Support\Facades\Auth;

function install($shop){
// Set variables for our request
$api_key = "e03bc9dbea81f5e42ec47cef977cea01";
$scopes = "read_orders,write_products";
$redirect_uri = "https://7e5f9ea300f2.ngrok.io/authenticate";

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

return $install_url;

}

function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {

// Build URL
$url = "https://" . $shop . $api_endpoint;
if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

// Configure cURL
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, TRUE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

// Setup headers
$request_headers[] = "";
if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
    if (is_array($query)) $query = http_build_query($query);
    curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
}

// Send request to Shopify and capture any errors
$response = curl_exec($curl);
$error_number = curl_errno($curl);
$error_message = curl_error($curl);

// Close cURL to be nice
curl_close($curl);

// Return an error is cURL has a problem
if ($error_number) {
    return $error_message;
} else {

    // No error, return Shopify's response by parsing out the body and the headers
    $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

    // Convert headers into an array
    $headers = array();
    $header_data = explode("\n",$response[0]);
    $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
    array_shift($header_data); // Remove status, we've already set it above
    foreach($header_data as $part) {
        $h = explode(":", $part);
        $headers[trim($h[0])] = trim($h[1]);
    }

    // Return headers and Shopify's response
    return array('headers' => $headers, 'response' => $response[1]);

}

}

function generate_token($params){
$api_key = "e03bc9dbea81f5e42ec47cef977cea01";
$shared_secret = "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";
$hmac = $params['hmac']; // Retrieve HMAC request parameter

$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
ksort($params); // Sort params lexographically

$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

// Use hmac data to check that the response is from Shopify or not
if (hash_equals($hmac, $computed_hmac)) {

// Set variables for our request
$query = array(
    "client_id" => $api_key, // Your API key
    "client_secret" => $shared_secret, // Your app credentials (secret key)
    "code" => $params['code'] // Grab the access key from the URL
);

// Generate access token URL
$access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

// Configure curl client and execute request
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $access_token_url);
curl_setopt($ch, CURLOPT_POST, count($query));
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
$result = curl_exec($ch);
curl_close($ch);

// Store the access token
$result = json_decode($result, true);
$access_token = $result['access_token'];

// Show the access token (don't do this in production!)
echo $access_token;

} else {
// Someone is trying to be shady!
die('This request is NOT from Shopify!');
}
}