<?php 
    include 'api.php';

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    $sd = new Smashdocs($partner_url, $client_id, $client_key);

    $templates = array($sd->list_templates());

    $result = $sd->new_document();
    print_r($result) . "\n";
    $documentId = $result['documentId'];
    echo $documentId . "\n";

    $result = $sd->export_document($documentId, $user_id='ajung', $template_id='', $format='sdxml');
    echo $result . "\n";
    $result = $sd->export_document($documentId, $user_id='ajung', $template_id='', $format='html');
    echo $result . "\n";
    $result = $sd->export_document($documentId, $user_id='ajung', $template_id=$templates[0]['id'], $format='docx');
    echo $result . '\n';
?>
