<?php

include 'api.php';

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    $sd = new Smashdocs($partner_url, $client_id, $client_key,1);

    $result = $sd->new_document();
    print_r($result) . "\n";
?>
