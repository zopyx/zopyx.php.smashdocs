<?php
/*
use PHPUnit\Framework\TestCase;
*/

require_once 'PHPUnit/Autoload.php';

final class SmashdocTests extends TestCase
{

    function __construct() {

        $this->partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
        $this->client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
        $this->client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];
        $this->sd = new Smashdocs($this->partner_url, $this->client_id, $this->client_key);
        parent::__construct();
    }

    function testListTemplates()
    {
        $result = $this->sd->list_templates();
    }

    function testCreateAndDeleteDocument()
    {
        $result = $this->sd->new_document();
        $documentId = $result['documentId'];
        $this->sd->delete_document($documentId);
    }

    function testCreateAndDeleteTwice()
    {
        $result = $this->sd->new_document();
        $documentId = $result['documentId'];
        $this->sd->delete_document($documentId);
        try {
            $this->sd->delete_document($documentId);
        } catch(DeletionError $e) { }
    }
}

