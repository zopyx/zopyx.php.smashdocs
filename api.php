<?php 

    include 'jwt_helper.php';
    require 'vendor/autoload.php';

    use GuzzleHttp\Client;

    class SmashdocsError extends Exception{}
    class CreationFailed extends SmashdocsError{}
    class UploadError extends SmashdocsError{}
    class UnarchiveError extends SmashdocsError{}
    class ArchiveError extends SmashdocsError{}
    class DeletionError extends SmashdocsError{}
    class CopyError extends SmashdocsError{}
    class DocumentInfoError extends SmashdocsError{}
    class UpdateMetadataError extends SmashdocsError{}
    class OpenError extends SmashdocsError{}
    class ExportError extends SmashdocsError{}

    class Smashdocs {

        function __construct($portal_url, $client_id, $client_key, $verbose=0) {
            $this->partner_url = $portal_url;
            $this->client_id = $client_id;
            $this->client_key =$client_key;
            $this->verbose = $verbose;
        }

        private function uuid() 
        {
            $r = unpack('v*', fread(fopen('/dev/random', 'r'),16));
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
                    $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
            return $uuid;
        }

        private function gen_token() {

            $iss  = $this->uuid();
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

        private function check_http_response($response, $status_code_expected=200, $exc_name='SmashdocsError', $decode_json=true) {

            $httpcode = $response -> getStatusCode();
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


        public function list_templates() {    

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token()
            );
            $url = $this->partner_url . "/partner/templates/word";
            $client = new Client();
            $response = $client->get($url, [
                'debug' => $this->verbose,
                'headers' => $headers
            ]);
            return  (array) $this->check_http_response($response, 200, 'OpenError', true);
        }

        function delete_document($documentId) {    

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token(),
            );

            $url = $this->partner_url . "/partner/documents/" . $documentId;
            $client = new Client();
            $response = $client->delete($url, [
                'debug' => $this->verbose,
                'headers' => $headers
            ]);
            return $this->check_http_response($response, 200, 'DeletionError', false);
        }

        function open_document($documentId) {    

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token()
            );

            $user_data = array(
                "email" => "info@zopyx.com",
                "firstname" => "Andreas",
                "lastname" => "Jung",
                "userId" => "ajung",
                "company" => "ZOPYX"
            );

            $data = array(
                "user" => $user_data,
                "title" => "my title",
                "description" => "my description",
                "groupId" => "xxxx",
                "userRole" => "editor",
                "sectionHistory" => true
            );

            $url = $this->partner_url . "/partner/documents/" . $documentId;
            $client = new Client();
            $response = $client->post($url, [
                'debug' => $this->verbose,
                'json' => $data,
                'headers' => $headers
            ]);

            return  (array) $this->check_http_response($response, 200, 'OpenError', true);
        }

        function archive_document($documentId) {    

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token()
            );

            $url = $this->partner_url . "/partner/documents/" . $documentId . "/archive";
            $client = new Client();
            $response = $client->post($url, [
                'debug' => $this->verbose,
                'json' => $data,
                'headers' => $headers
            ]);

            return  (array) $this->check_http_response($response, 200, 'ArchiveError', true);
        }

        function unarchive_document($documentId) {    

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token()
            );

            $url = $this->partner_url . "/partner/documents/" . $documentId . "/unarchive";
            $client = new Client();
            $response = $client->post($url, [
                'debug' => $this->verbose,
                'json' => $data,
                'headers' => $headers
            ]);

            return  (array) $this->check_http_response($response, 200, 'UnarchiveError', true);
        }

        function export_document($documentId, $user_id, $format, $template_id='') {

            if (! in_array($format, array('docx', 'html', 'sdxml'))) {
                throw new SmashdocsError('Unknown export format ' . $format);
            }

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token()
            );

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
                $data['settings'] = (object) array();
            } 

            $client = new Client();
            $response = $client->post($url, [
                'debug' => $this->verbose,
                'json' => $data,
                'headers' => $headers
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

        function new_document() {

            $headers = array(
                "x-client-id" => $this->client_id,
                "content-type" => "application/json",
                "authorization" => "Bearer " . $this->gen_token(),
                "accept" => "*/*"
            );

            $user_data = array(
                "email" => "info@zopyx.com",
                "firstname" => "Andreas",
                "lastname" => "Jung",
                "userId" => "ajung",
                "company" => "ZOPYX"
            );

            $data = array(
                "user" => $user_data,
                "title" => "my title",
                "description" => "my description",
                "groupId" => "xxxx",
                "userRole" => "editor",
                "sectionHistory" => true
            );

            $url = $this->partner_url . "/partner/documents/create";
            $client = new Client();
            $response = $client->post($url, [
                'debug' => $this->verbose,
                'json' => $data,
                'headers' => $headers
            ]);

            return  (array) $this->check_http_response($response, 200, 'CreationFailed', true);
        }

        function upload_document($fn) {

            $headers = array(
                "x-client-id: ". $this->client_id,
                "authorization: ". "Bearer " . $this->gen_token()
            );

            $user_data = array(
                "email" => "info@zopyx.com",
                "firstname" => "Andreas",
                "lastname" => "Jung",
                "userId" => "ajung",
                "company" => "ZOPYX"
            );

            $data = array(
                "user" => $user_data,
                "title" => "my title",
                "description" => "my description",
                "groupId" => "xxxx",
                "userRole" => "editor",
                "sectionHistory" => true
            );

            $url = $this->partner_url . "/partner/imports/word/upload";

            $fp = fopen($fn, 'rb'); 
            $ch = curl_init();
                curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_INFILE => $fp,
                CURLOPT_INFILESIZE => filesize($fn),
                CURLOPT_POST => 1,
                CURLOPT_UPLOAD => 1,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_RETURNTRANSFER  =>true,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_VERBOSE => $this->verbose
                )
            );
            $out = $this->check_http_result($ch, 200, 'CreationFailed');
            curl_close ($ch);
            $result = (array) json_decode($out);
            return $result;        
        }
    }
?>
