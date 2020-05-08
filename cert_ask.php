<?php

// https://stackoverflow.com/a/834355
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

$doh_base = "https://resolver-eu.lelux.fi/dns-query";

$domain = $_GET["domain"];
$base = $_GET["base"];

if(empty($domain) || empty($base)) {
    http_response_code(400);
    die("domain or base missing");
}

function createDoh($domain) {
    global $base, $doh_base;

    $doh_reply = file_get_contents("$doh_base?name=$domain&type=A");

    if($doh_reply) {
        $doh_reply = json_decode($doh_reply, true)["Answer"];

        // Find CNAME
        // type = 5
        // https://en.wikipedia.org/wiki/List_of_DNS_record_types#Resource_records
        $domain = $doh_reply[array_search(5, array_column($doh_reply, 'type'))];

        // header("Content-Type: text/plain; charset=UTF-8");
        // print_r($domain);

        if(endsWith($domain["data"], ".$base.")) {
            http_response_code(200);
            die("Successful, using CNAME");
        }
    } else {
        http_response_code(500);
        die("DoH error");
    }
}

createDoh($domain);
createDoh("redirect.$domain");

http_response_code(500);
die("No CNAME record");
