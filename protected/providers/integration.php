<?php

function authenticate($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();


    if (isset($body['hash']) && isset($body['token']) && isset($body['providerId'])) {

        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "userId" =>  $userId,
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" =>  $balance,
                    "bonus" => 0,
                    "token" => $token,
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
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function balance($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (isset($body['hash']) && isset($body['token']) && isset($body['userId']) && isset($body['providerId'])) {

        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }


    return $data;
}

function bet($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

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
        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amount = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                ($amount / 100) * -1,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "usedPromo" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function result($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

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
        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amount = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                $amount,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" =>  $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function bonusWin($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['amount']) &&
        isset($body['reference']) &&
        isset($body['providerId']) &&
        isset($body['timestamp'])
    ) {
        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amount = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                $amount,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));


                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function jackpotWin($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

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
        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amount = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                $amount,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function endRound($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['gameId']) &&
        isset($body['roundId']) &&
        isset($body['providerId'])
    ) {

        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function refund($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['reference']) &&
        isset($body['providerId']) &&
        isset($body['amount']) &&
        isset($body['platform'])
    ) {


        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amountRefund = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                $amountRefund,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function withdraw($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['userId']) &&
        isset($body['token']) &&
        isset($body['providerId'])
    ) {
        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "userId" => $userId,
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

/** Me falto aqui */
function getBalancePerGame($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

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

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['providerId']) &&
        isset($body['timestamp']) &&
        isset($body['userId']) &&
        isset($body['campaingId']) &&
        isset($body['campaingType']) &&
        isset($body['amount']) &&
        isset($body['currency']) &&
        isset($body['token']) &&
        isset($body['reference'])
    ) {



        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            $amontWin = $body['amount'];
            $reference = $body['reference'];

            $compra = $user->addToBalance(
                $amontWin,
                15, //debito livesport
                2, //invitado
                CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                $user->pcdi->PCDI_Cod,
                $status = 1,
                $control =  $reference,
                $fechaRef = date("Y-m-d H:i")
            );

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "transactionId" => $compra['GCCS_Id'],
                    "currency" => $user->pcdi->PCDI_Cod,
                    "cash" => $balance,
                    "bonus" => 0,
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}

function sessionExpired($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound();

    if (
        isset($body['hash']) &&
        isset($body['providerId']) &&
        isset($body['sessionId']) &&
        isset($body['playerId'])
    ) {


        $token = $body['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {

                $data = [
                    "error" => 0,
                    "description" => "Success",
                ];
            } else {
                $data = userNotFound();
            }
        } else {
            $data = sessionNotFound();
        }
    }

    return $data;
}




/** helpers */

function paramsNotFound()
{
    return array(
        "error" => "1",
        "description" => "Params not found",
    );
}

function userNotFound()
{
    return array(
        "error" => "1",
        "description" => "User not found",
    );
}

function sessionNotFound()
{
    return array(
        "error" => "1",
        "description" => "Session not found",
    );
}

function getUserSession($token)
{
    return  Yii::app()->db->createCommand()
        ->select('*')
        ->from('cruge_session')
        ->where(
            'ipaddressout=:hash',
            array(':hash' => $token)
        )->queryRow();
}
