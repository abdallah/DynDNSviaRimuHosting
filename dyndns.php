<?php
$user_domain = array(
    'abdallah' => array(
        'badawi',
        'tahwita'
    ),
    'jonathan' => array(
        'chekhtaba'
    )
);
$domain      = "example.com";
$apihost     = "https://rimuhosting.com/dns";
$apikey      = "getyourapikeyfromzonomicomcpapikeysjsp";

function checkip($ip)
{
    return inet_pton($ip) !== false;
}

$ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['REMOTE_USER'])) {
    $user = $_SERVER['REMOTE_USER'];
} else if (isset($_SERVER['PHP_AUTH_USER'])) {
    $user = $_SERVER['PHP_AUTH_USER'];
} else {
    syslog(LOG_WARN, "No user given by connection from $ip");
    exit(0);
}

if (isset($_REQUEST['DOMAIN'])) {
    $subdomain = $_REQUEST['DOMAIN'];
} else if (isset($_REQUEST['host'])) {
    $subdomain = $_REQUEST['host'];
} else {
    syslog(LOG_WARN, "User $user from $ip didn't provide any domain");
    exit(0);
}

if (isset($subdomain) && isset($ip) && isset($user)) {
    if (preg_match("/^(\d{1,3}\.){3}\d{1,3}$/", $ip) && checkip($ip) && $ip != "0.0.0.0" && $ip != "255.255.255.255") {
        if (preg_match("/^[\w\d-_\*\.]+$/", $subdomain)) {
            // check whether user is allowed to change domain
            if (in_array("*", $user_domain[$user]) or in_array($subdomain, $user_domain[$user])) {
                if ($subdomain != "-")
                    $subdomain = $subdomain . '.';
                else
                    $subdomain = '';
                
                // shell escape all values
                $subdomain = escapeshellcmd($subdomain);
                $user      = escapeshellcmd($user);
                $ip        = escapeshellcmd($ip);
                
                $cmd = "$apihost/dyndns.jsp?action=SET&name=$subdomain$domain&value=$ip&type=A&api_key=$apikey";
                // print $cmd;
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $cmd);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                // print $output;
                curl_close($ch);
                syslog(LOG_INFO, $output);
            }
        }
    }
}
?>
