<?php 
    include 'api.php';

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    $sd = new Smashdocs($partner_url, $client_id, $client_key);
    $templates = $sd->list_templates();

    $template_id = get_object_vars($templates[0])['id'];

    $result = $sd->new_document();
    print_r($result) . "\n";
    $documentId = $result['documentId'];
    echo $documentId . "\n";

    $result = $sd->export_document($documentId, $user_id='ajung', $format='sdxml');
    echo $result . "\n";
    $result = $sd->export_document($documentId, $user_id='ajung', $format='html');
    echo $result . "\n";

    $result = $sd->export_document($documentId, 'ajung', 'docx', $template_id);
    echo $result . "\n";
?>
