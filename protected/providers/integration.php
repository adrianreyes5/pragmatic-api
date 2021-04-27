<?php

function authenticate($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (isset($body['hash']) && isset($body['token']) && isset($body['providerId'])) {

        $data = [
            "userId" => "421",
            "currency" => "USD",
            "cash" => "99999.99",
            "bonus" => "99.99",
            "token" => "1234",
            "country" => "VE",
            "jurisdiction" => "UK",
            "betLimits" => [
                "defaultBet" => 0.10,
                "minBet" => 0.02,
                "maxBet" => 10.00,
                "minTotalBet" => 0.50,
                "maxtTotalBet" => 250.00,
            ],
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function balance($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (isset($body['hash']) && isset($body['userId']) && isset($body['providerId'])) {

        $data = [
            "currency" => "USD",
            "cash" => 99999.99,
            "bonus" => 99.99,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function bet($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['gameId']) &&
        isset($body['roundId']) &&
        isset($body['amount']) &&
        isset($body['reference']) &&
        isset($body['providerId']) &&
        isset($body['timestamp'])
    ) {

        $data = [
            "transactionId" => "123456789",
            "currency" => "USD",
            "cash" => 9999.99,
            "bonus" => 99.99,
            "usePromo" => 0,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}
