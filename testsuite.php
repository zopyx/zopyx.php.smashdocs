<?php

use PHPUnit\Framework\TestCase;

final class SmashdocTests extends TestCase
{

    function __construct() {

        $this->partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
        $this->client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
        $this->client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];
        $this->sd = new Smashdocs($this->partner_url, $this->client_id, $this->client_key);
        parent::__construct();
    }

    function _createDocument() {

        $result = $this->sd->new_document();
        return $result['documentId'];
    }

    function testListTemplates()
    {
        $result = $this->sd->list_templates();
    }

    function testCreateAndDeleteDocument()
    {
        $documentId = $this->_createDocument();
        $this->sd->delete_document($documentId);
    }

    function testArchiveUnarchive()
    {
        $documentId = $this->_createDocument();
        $this->sd->archive_document($documentId);
        $this->sd->unarchive_document($documentId);
        $this->sd->delete_document($documentId);
    }

    function testCreateAndDeleteTwice()
    {
        $documentId = $this->_createDocument();
        $this->sd->delete_document($documentId);
        try {
            $this->sd->delete_document($documentId);
        } catch(DeletionError $e) { }
    }

    function testOpenDocument()
    {
        $documentId = $this->_createDocument();
        $result = $this->sd->open_document($documentId);
        $this->assertContains('https://', $result['documentAccessLink']); 
        $this->sd->delete_document($result['documentId']);
    }

    function testExportDOCX() {

        $templates = $sd->list_templates();
        $template_id = get_object_vars($templates[0])['id'];

        $result = $sd->new_document();
        $result = $sd->export_document($documentId, 'ajung', 'docx', $template_id);


    }
}

