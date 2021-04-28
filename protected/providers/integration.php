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
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function result($entityBody)
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
        isset($body['timestamp']) &&
        isset($body['roundDetails'])
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

function bonusWin($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
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
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function jackpotWin($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['providerId']) &&
        isset($body['timestamp']) &&
        isset($body['userId']) &&
        isset($body['gameId']) &&
        isset($body['roundId']) &&
        isset($body['jackpotId']) &&
        isset($body['amount']) &&
        isset($body['reference'])
    ) {

        $data = [
            "transactionId" => "123456789",
            "currency" => "USD",
            "cash" => 9999.99,
            "bonus" => 99.99,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function endRound($entityBody)
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
        isset($body['providerId'])
    ) {

        $data = [
            "cash" => 9999.99,
            "bonus" => 99.99,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function refund($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['reference']) &&
        isset($body['providerId']) &&
        isset($body['platform'])
    ) {

        $data = [
            "transactionId" => "123456789",
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function withdraw($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['providerId'])
    ) {

        $data = [
            "userId" => "421",
            "currency" => "USD",
            "cash" => 999.99,
            "bonus" => 99.99,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function getBalancePerGame($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['providerId']) &&
        isset($body['gameIdList']) &&
        isset($body['token']) &&
        isset($body['platform'])
    ) {

        $data = [
            "gamesBalances" => [
                ["gameId" => "vs20cd", "cash" => 25.02, "bonus" => 0.00],
                ["gameId" => "vs9c", "cash" => 12.02, "bonus" => 0.00],
            ],
        ];
    }

    return $data;
}

function promoWin($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['providerId']) &&
        isset($body['timestamp']) &&
        isset($body['userId']) &&
        isset($body['campaingId']) &&
        isset($body['campaingType']) &&
        isset($body['amount']) &&
        isset($body['currency']) &&
        isset($body['reference'])
    ) {

        $data = [
            "transactionId" => "123456789",
            "currency" => "USD",
            "cash" => 9999.99,
            "bonus" => 99.99,
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}

function sessionExpired($entityBody)
{
    $body = $entityBody;

    $data = array(
        "error" => "1",
        "description" => "No fue exitoso",
    );


    if (
        isset($body['hash']) &&
        isset($body['providerId']) &&
        isset($body['sessionId']) &&
        isset($body['playerId'])
    ) {

        $data = [
            "error" => 0,
            "description" => "Success",
        ];
    }

    return $data;
}
