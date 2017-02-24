<?php

use PHPUnit\Framework\TestCase;


function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

final class SmashdocTests extends TestCase
{

    function __construct() {

        $this->partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
        $this->client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
        $this->client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];
        $verbose = 1;
        $this->sd = new Smashdocs($this->partner_url, $this->client_id, $this->client_key, $verbose);
        parent::__construct();
    }

    function _createDocument() {

        $result = $this->sd->new_document();
        return $result['documentId'];
    }

    function testListTemplates()
    {
        $result = $this->sd->list_templates();
        $this->assertEquals(0, 0);
    }

    function testCreateAndDeleteDocument()
    {
        $documentId = $this->_createDocument();
        $this->sd->delete_document($documentId);
        $this->assertEquals(0, 0);
    }

    function testArchiveUnarchive()
    {
        $documentId = $this->_createDocument();
        $this->sd->archive_document($documentId);
        $this->sd->unarchive_document($documentId);
        $this->sd->delete_document($documentId);
        $this->assertEquals(0, 0);
    }

    function testCreateAndDeleteTwice()
    {
        $documentId = $this->_createDocument();
        $this->sd->delete_document($documentId);
        try {
            $this->sd->delete_document($documentId);
        } catch(DeletionError $e) { }
        $this->assertEquals(0, 0);
    }

    function testOpenDocument()
    {
        $documentId = $this->_createDocument();
        $result = $this->sd->open_document($documentId);
        $this->assertContains('https://', $result['documentAccessLink']); 
        $this->sd->delete_document($result['documentId']);
        $this->assertEquals(0, 0);
    }

    function testExportDOCX() {

        $templates = $this->sd->list_templates();
        $template_id = get_object_vars($templates[0])['id'];

        $result = $this->sd->new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'docx', $template_id);
        $this->assertEquals(true, endsWith($fn, '.docx'));
    }

    function testExportSDXML() {

        $result = $this->sd->new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'sdxml');
        $this->assertEquals(true, endsWith($fn, '.sdxml.zip'));
    }

    function testExportHTML() {

        $result = $this->sd->new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'html');
        $this->assertEquals(true, endsWith($fn, '.html.zip'));
    }

}
