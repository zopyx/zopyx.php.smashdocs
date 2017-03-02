<?php

include 'jwt_helper.php';
require 'vendor/autoload.php';

use GuzzleHttp\Client;

class SmashdocsError extends Exception
{
}

class CreationFailed extends SmashdocsError
{
}

class UploadError extends SmashdocsError
{
}

class UnarchiveError extends SmashdocsError
{
}

class ArchiveError extends SmashdocsError
{
}

class DeletionError extends SmashdocsError
{
}

class CopyError extends SmashdocsError
{
}

class DocumentInfoError extends SmashdocsError
{
}

class UpdateMetadataError extends SmashdocsError
{
}

class OpenError extends SmashdocsError
{
}

class ExportError extends SmashdocsError
{
}

class GetDocumentsError extends SmashdocsError
{
}


function ends_with($string, $test)
{
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}


function check_role($role)
{
    $allowed_sd_roles = array('editor', 'reader', 'approver', 'commentator');
    
    if (! in_array($role, $allowed_sd_roles)) {
        throw new Exception('Unkown Smashdocs role: ' . $role);
    }
}


function check_length($s, $max_len) {
    if (strlen($s) > $max_len) {
        throw new Exception('String too long');
    }
}

function check_title($s) {
    return check_length($s, 200);
}

function check_description($s) {
    return check_length($s, 400);
}

function check_email($s) {
    return check_length($s, 150);
}

function check_firstname($s) {
    return check_length($s, 150);
}

function check_lastname($s) {
    return check_length($s, 150);
}

function check_company($s) {
    return check_length($s, 150);
}

function check_document_id($document_id) {

}

function check_user_data($ud) {

}

class Smashdocs
{

    function __construct($portal_url, $client_id, $client_key, $group_id = 'default', $verbose = 0)
    {
        $this->partner_url = $portal_url;
        $this->client_id = $client_id;
        $this->client_key = $client_key;
        $this->group_id = $group_id;
        $this->verbose = $verbose;
    }

    private function uuid()
    {
        $r = unpack('v*', fread(fopen('/dev/random', 'r'), 16));
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
            $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
        return $uuid;
    }

    private function gen_token()
    {

        $iss = $this->uuid();
        $iat = time();
        $jti = $this->uuid();

        $jwt_payload = array(
            "iat" => $iat,
            "iss" => $iss,
            "jti" => $jti,
        );

        $jwt = new JWT;
        $token = $jwt->encode($jwt_payload, $this->client_key, "HS256");
        return $token;
    }

    private function standard_headers()
    {
        return array(
            "x-client-id" => $this->client_id,
            "content-type" => "application/json",
            "authorization" => "Bearer " . $this->gen_token(),
        );
    }

    private function check_http_response($response, $status_code_expected = 200, $exc_name = 'SmashdocsError', $decode_json = true)
    {

        $httpcode = $response->getStatusCode();
        if ($httpcode != $status_code_expected) {
            $msg = 'HTTP call returned with status ' . $httpcode . ' (expected: ' . $status_code_expected . ', ' . $out . ')';
            $exc = new $exc_name($msg);
            $exc->status_code_got = $httpcode;
            $exc->status_code_expected = $status_code_expected;
            $exc->error_msg = $out;
            throw $exc;
        }
        if ($decode_json) {
            return json_decode($response->getBody());
        } else {
            return $response->getBody();
        }
    }

    public function get_documents($group_id=null, $user_id=null)
    {

        $data = array();
        if ($group_id) 
            $data["groupId"] = $group_id;
        if ($user_id) 
            $data["userId"] = $user_id;

        $url = $this->partner_url . "/partner/documents/list";
        $client = new Client();
        $response = $client->get($url, [
            'debug' => $this->verbose,
            'headers' => $this->standard_headers()
        ]);
        return (array)$this->check_http_response($response, 200, 'GetDocumentsError', true);
    }

    public function list_templates()
    {

        $url = $this->partner_url . "/partner/templates/word";
        $client = new Client();
        $response = $client->get($url, [
            'debug' => $this->verbose,
            'headers' => $this->standard_headers()
        ]);
        return (array)$this->check_http_response($response, 200, 'OpenError', true);
    }

    function delete_document($documentId)
    {

        check_document_id($documentId);

        $url = $this->partner_url . "/partner/documents/" . $documentId;
        $client = new Client();
        try {
            $response = $client->delete($url, [
                'debug' => $this->verbose,
                'headers' => $this->standard_headers()
            ]);
        } catch (Exception $e) {
            throw new DeletionError($e->getMessage());
        }
        return $this->check_http_response($response, 200, 'DeletionError', false);
    }

    function open_document($documentId, $role = 'editor', array $user_data = null)
    {

        check_role($role);
        check_document_id($documentId);
        check_user_data($user_data);

        $data = array(
            "user" => $user_data,
            "groupId" => $this->group_id,
            "userRole" => $role,
            "sectionHistory" => true
        );

        $url = $this->partner_url . "/partner/documents/" . $documentId;
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'OpenError', true);
    }

    function archive_document($documentId)
    {

        $url = $this->partner_url . "/partner/documents/" . $documentId . "/archive";
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'ArchiveError', true);
    }

    function update_metadata($documentId, array $metadata = null)
    {
        check_document_id($documentId);

        $url = $this->partner_url . "/partner/documents/" . $documentId . "/metadata";
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $metadata,
            'headers' => $this->standard_headers()
        ]);
    }

    function document_info($documentId)
    {
        check_document_id($documentId);

        $url = $this->partner_url . "/partner/documents/" . $documentId;
        $client = new Client();
        $response = $client->get($url, [
            'debug' => $this->verbose,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'DocumentInfoError', true);
    }

    function unarchive_document($documentId)
    {
        check_document_id($documentId);

        $url = $this->partner_url . "/partner/documents/" . $documentId . "/unarchive";
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'UnarchiveError', true);
    }

    function export_document($documentId, $user_id, $format, $template_id = '')
    {
        check_document_id($documentId);

        if (!in_array($format, array('docx', 'html', 'sdxml'))) {
            throw new SmashdocsError('Unknown export format ' . $format);
        }

        $data = array(
            "userId" => $user_id,
        );

        if ($format == 'sdxml') {
            $url = $this->partner_url . '/partner/documents/' . $documentId . '/export/sdxml';
        } elseif ($format == 'html') {
            $url = $this->partner_url . '/partner/documents/' . $documentId . '/export/html';
        } elseif ($format == 'docx') {
            $url = $this->partner_url . '/partner/documents/' . $documentId . '/export/word';
            $data['templateId'] = $template_id;
            $data['settings'] = (object)array();
        }

        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        $out = $this->check_http_response($response, 200, 'ExportError', false);

        $fn = tempnam(sys_get_temp_dir(), '');
        if ($format == 'docx') {
            $fn = $fn . '.docx';
        } else {
            $fn = $fn . '.' . $format . '.zip';
        }

        $fp = fopen($fn, "wb");
        fwrite($fp, $out);
        fclose($fp);
        return $fn;
    }

    function new_document($title = null, $description = null, $role = 'editor', array $user_data = null)
    {
        check_title($title);
        check_description($title);
        check_role($role);
        check_user_data($user_data);

        $data = array(
            "user" => $user_data,
            "title" => $title,
            "description" => $description,
            "groupId" => $this->group_id,
            "userRole" => $role,
            "sectionHistory" => true
        );

        $url = $this->partner_url . "/partner/documents/create";
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'CreationFailed', true);
    }

    function duplicate_document($document_id, $title = null, $description = null, $creator_id = null)
    {
        check_title($title);
        check_description($title);

        $data = array(
            "description" => $description,
            "title" => $title,
            "creatorUserId" => $creator_id
        );

        $url = $this->partner_url . "/partner/documents/" . $document_id . "/duplicate";
        $client = new Client();
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'json' => $data,
            'headers' => $this->standard_headers()
        ]);

        return (array)$this->check_http_response($response, 200, 'DuplicationFailed', true);
    }

    function upload_document($fn, $title = null, $description = null, $role = 'editor', array $user_data = null)
    {
        check_title($title);
        check_description($title);
        check_role($role);
        check_user_data($user_data);

        $headers = array(
            "x-client-id" => $this->client_id,
            "authorization" => "Bearer " . $this->gen_token()
        );

        $data = array(
            "user" => $user_data,
            "title" => $title,
            "description" => $description,
            "groupId" => $this->group_id,
            "userRole" => $role,
            "sectionHistory" => true
        );

        if (ends_with($fn, '.docx')) {
            $url = $this->partner_url . "/partner/imports/word/upload";
        } else {
            $url = $this->partner_url . "/partner/imports/sdxml/upload";
        }

        $client = new Client();
        $fp = fopen($fn, 'rb');
        $response = $client->post($url, [
            'debug' => $this->verbose,
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'data',
                    'contents' => json_encode($data),
                    'headers' => ['content-type' => 'application/json']
                ],
                [
                    'name' => 'file',
                    'Content-type' => 'multipart/form-data',
                    'contents' => $fp
                ]
            ]
        ]);

        return (array)$this->check_http_response($response, 200, 'UploadError', true);
    }
}

?>
