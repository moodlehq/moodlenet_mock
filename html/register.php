<?php

$requestdata = json_decode(file_get_contents('php://input'), true);

$clientinfo = [
    "client_id" => "s6BhdRkqt3",
    "client_secret" => "cf136dc3c1fc93f31185e5885805d",
    "client_id_issued_at" => 2893256800,
    "client_secret_expires_at" => 2893276800,
];

$response = array_merge($requestdata, $clientinfo);


header('Content-Type: application/json');

echo json_encode($response);
