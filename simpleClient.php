<?php

(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('cli only');

$ch = curl_init();

$filename = 'tests/Fixtures/test1.png';

$data = array(
    'file1' => new CURLFile(realpath($filename)),
);

curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8081/api/upload");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("AUTHKEY: supersecretcode1@"));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
$result = curl_exec($ch);

echo 'response: ' . $result;
echo PHP_EOL;

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);
