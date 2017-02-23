<?php 

    include 'jwt_helper.php';

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    function uuid() 
    {
        $r = unpack('v*', fread(fopen('/dev/random', 'r'),16));
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
                $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
        return $uuid;
    }

    function gen_token() {

        global $client_key; 

        $iss  = uuid();
        $iat = time();
        $jti = uuid();

        $jwt_payload = array(
            "iat" => $iat,
            "iss" => $iss,
            "jti" => $jti,
        );

        $jwt = new JWT;
        $token = $jwt->encode($jwt_payload, $client_key, "HS256");
        return $token;
    }

    function list_templates() {    

        global $client_id, $partner_url;

        $headers = array(
            "x-client-id: ". $client_id,
            "content-type: ". "application/json",
            "authorization: ". "Bearer " . gen_token()
        );

        $url = $partner_url . "/partner/templates/word";
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER  => $headers,
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 0
        ));
        $out = curl_exec($ch);
        curl_close($ch);
        $result = (array) json_decode($out);
        return $result;        
    }

    function open_document($documentId) {    

        global $client_id, $partner_url;

        $headers = array(
            "x-client-id: ". $client_id,
            "content-type: ". "application/json",
            "authorization: ". "Bearer " . gen_token()
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

        $data_string = json_encode($data);        
        $url = $partner_url . "/partner/documents/" . $documentId;

        $ch = curl_init();
            curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 0
            )
        );
        $out = curl_exec ($ch);
        curl_close ($ch);
        $result = (array) json_decode($out);
        return $result;        

    }

    function new_document() {

        global $client_id, $partner_url;

        $headers = array(
            "x-client-id: ". $client_id,
            "content-type: ". "application/json",
            "authorization: ". "Bearer " . gen_token()
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

        $data_string = json_encode($data);        
        $url = $partner_url . "/partner/documents/create";

        $ch = curl_init();
            curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 0
            )
        );
        $out = curl_exec ($ch);
        curl_close ($ch);
        $result = (array) json_decode($out);
        return $result;        
    }

    print_r(list_templates()) . "\n";

    $result = new_document();
    print_r($result) . "\n";
    $documentId = $result['documentId'];
    echo $documentId . "\n";

    $result = open_document($documentId);
    print_r($result) . "\n";
    $url = $result['documentAccessLink'];
    echo $url . "\n";

?>
