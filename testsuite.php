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


function make_user_data()
{
    return array(
        "email" => "test@foo.com",
        "firstname" => "Henry",
        "lastname" => "Miller",
        "userId" => "testuser",
        "company" => "Dummies Ltd");
}


final class SmashdocTests extends TestCase
{

    function __construct()
    {

        $this->partner_url = getenv('SMASHDOCS_PARTNER_URL');
        $this->client_id = getenv('SMASHDOCS_CLIENT_ID');
        $this->client_key = getenv('SMASHDOCS_CLIENT_KEY');
        $this->group_id = 'testing';
        $verbose = getenv('SMASHDOCS_DEBUG');
        $this->sd = new Smashdocs($this->partner_url, $this->client_id, $this->client_key, $this->group_id, $verbose);
        parent::__construct();
    }

    function _new_document()
    {
        return $this->sd->new_document('my title', 'my description', 'editor', make_user_data());
    }

    function _createDocument()
    {
        $result = $this->sd->new_document('my title', 'my description', 'editor', make_user_data());
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

    function XXXtestArchiveUnarchive()
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
        } catch (DeletionError $e) {
        }
        $this->assertEquals(0, 0);
    }

    function testOpenDocument()
    {
        $documentId = $this->_createDocument();
        $result = $this->sd->open_document($documentId, 'editor', make_user_data());
        $this->assertContains('https://', $result['documentAccessLink']);
        $this->sd->delete_document($result['documentId']);
        $this->assertEquals(0, 0);
    }

    function testDuplicateDocument()
    {
        $documentId = $this->_createDocument();
        $result = $this->sd->duplicate_document($documentId, 'my title', 'new description', 'testuser');
        $this->sd->delete_document($documentId);
        $this->sd->delete_document($result['documentId']);
        $this->assertEquals(0, 0);
    }

    function testUpdateMetadata()
    {
        $documentId = $this->_createDocument();
        $metadata = array(
            "title" => "new title",
            "description" => "new description"
        );
        $this->sd->update_metadata($documentId, $metadata);
        $document_info = $this->sd->document_info($documentId);
        $this->assertEquals($document_info["title"], "new title");
        $this->assertEquals($document_info["description"], "new description");
    }

    function testExportDOCX()
    {
        $templates = $this->sd->list_templates();
        $template_id = get_object_vars($templates[0])['id'];
        $result = $this->_new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'docx', $template_id);
        $this->assertEquals(true, endsWith($fn, '.docx'));
    }

    function testExportSDXML()
    {
        $result = $this->_new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'sdxml');
        $this->assertEquals(true, endsWith($fn, '.sdxml.zip'));
    }

    function testExportHTML()
    {
        $result = $this->_new_document();
        $documentId = $result['documentId'];
        $fn = $this->sd->export_document($documentId, 'ajung', 'html');
        $this->assertEquals(true, endsWith($fn, '.html.zip'));
    }

    function testReviewDocoument()
    {
        $result = $this->_new_document();
        $documentId = $result['documentId'];
        $this->sd->review_document($documentId);
    }

    function testGetDocuments() 
    {
        $result = $this->sd->get_documents('dummy_group', '');
        $result = $this->sd->get_documents('', 'testuser');
    }

    function testUploadDOCX()
    {
        $result = $this->sd->upload_document('test.docx', 'title', 'description', 'editor', make_user_data());
        $documentId = $result['documentId'];
        $this->sd->delete_document($result['documentId']);
        $this->assertEquals(0, 0);
    }

    function testUploadSDXML()
    {
        $result = $this->sd->upload_document('test_sdxml_large.zip', 'title', 'description', 'editor', make_user_data());
        $documentId = $result['documentId'];
        $this->sd->delete_document($result['documentId']);
        $this->assertEquals(0, 0);
    }
}
