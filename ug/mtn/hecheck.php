<!--<meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src http://*; child-src 'none';">-->
<?php
include "dbdetail.php";
// var_dump(getallheaders());

function decrypt_msisdn($encrypted_text, $enc_key, $enc_iv) 
{
    $ciphertext = base64_decode($encrypted_text);
    return rtrim(openssl_decrypt(
    $ciphertext,
    'AES-128-CBC',
    $enc_key,
    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
    $enc_iv
), "\0");
}

$getHeaders = getallheaders();

$msisdn = decrypt_msisdn($getHeaders["Z-Msisdn"], $enc_key, $enc_iv);
echo "<b>decrypted msisdn: " . $msisdn . "</b>";
echo "<br><br>All headers<br><br><pre>";
print_r($getHeaders);


exit;
