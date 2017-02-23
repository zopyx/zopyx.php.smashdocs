<?php 
    include 'api.php';

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    $sd = new Smashdocs($partner_url, $client_id, $client_key);

    print_r($sd->list_templates()) . "\n";

    $result = $sd->new_document();
    print_r($result) . "\n";
    $documentId = $result['documentId'];
    echo $documentId . "\n";

    $result = $sd -> open_document($documentId);
    print_r($result) . "\n";
    $url = $result['documentAccessLink'];
    echo $url . "\n";

    $result = $sd -> archive_document($documentId);
    $result = $sd -> unarchive_document($documentId);
    $result = $sd -> delete_document($documentId);
    $result = $sd -> delete_document($documentId);

?>
