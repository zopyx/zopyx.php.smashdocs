<?php 
    include 'api.php';

    print_r(list_templates()) . "\n";

    $result = new_document();
    print_r($result) . "\n";
    $documentId = $result['documentId'];
    echo $documentId . "\n";
    

    $result = open_document($documentId);
    print_r($result) . "\n";
    $url = $result['documentAccessLink'];
    echo $url . "\n";

    $result = archive_document($documentId);
    $result = unarchive_document($documentId);

    $result = delete_document($documentId);

?>
