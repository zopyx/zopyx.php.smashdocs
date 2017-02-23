<?php 

    include 'jwt_helper.php';

    function uuid() 
    {
        $r = unpack('v*', fread(fopen('/dev/random', 'r'),16));
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
                $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
        return $uuid;
    }

    $partner_url = $_SERVER['SMASHDOCS_PARTNER_URL'];
    $client_id = $_SERVER['SMASHDOCS_CLIENT_ID'];
    $client_key = $_SERVER['SMASHDOCS_CLIENT_KEY'];

    echo $partner_url . "\n";
    echo $client_id . "\n";         
    echo $client_key . "\n";


    $iss  = uuid();
    echo $iss . "\n";

    $iat = time();
    echo $iat . "\n";


    $jti = uuid();
    echo $jti . "\n";

    $jwt_payload = array(
        "iat" => $iat,
        "iss" => $iss,
        "jti" => $jti,
    );

    echo print_r($jwt_payload) . "\n"; 

    $jwt = new JWT;
    echo print_r($jwt);

    $token = $jwt->encode($jwt_payload, $client_key, "HS256");
    echo $token . "\n";

?>
