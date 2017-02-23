<?php 
    include 'api.php';

    $sd = new Smashdocs;

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

?>
