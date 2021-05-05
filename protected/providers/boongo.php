<?php

function login($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound($body['uid']);

    if (isset($body['uid']) && isset($body['args']['token'])) {

        $uid = $body['uid'];
        $token = $body['args']['token'];
        $session = getUserSession($token);


        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "uid" =>  $uid,
                    "player" => array(
                        "id" => $userId,
                        "nick" => $user->GCCA_Nombre,
                        "currency" =>  $user->pcdi->PCDI_Cod
                    ),
                    "balance" => array(
                        "value" => $balance,
                        "version" => 0
                    ),
                    "settings" => array(
                        "profile" => $user->GCCA_Nombre
                    )
                ];
            } else {
                $data = userNotFound($uid);
            }
        } else {
            $data = sessionNotFound($uid);
        }
    }

    return $data;
}

function transaction($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound($body['uid']);

    if (isset($body['uid']) && isset($body['args']['token'])) {

        $uid = $body['uid'];
        $token = $body['args']['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



            if (isset($user)) {
                $rounds = $body['args']['rounds'];

                if (count($rounds)) {
                    if (isset($body['args']['win']) && !isset($body['args']['bet'])) {

                        $win = $body['args']['win'];

                        $compra = $user->addToBalance(
                            $win,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control =  $rounds,
                            $fechaRef = date("Y-m-d H:i")
                        );
                    }

                    if (isset($body['args']['bet'])  && !isset($body['args']['win'])) {
                        $bet = $body['args']['bet'];

                        $compra = $user->addToBalance(
                            $bet * -1,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control =  CJSON::encode($rounds),
                            $fechaRef = date("Y-m-d H:i")
                        );
                    }

                    if (isset($body['args']['bet'])  && isset($body['args']['win'])) {
                        $bet = $body['args']['bet'];
                        $win = $body['args']['win'];

                        $compra = $user->addToBalance(
                            $bet * -1,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control =  CJSON::encode($rounds),
                            $fechaRef = date("Y-m-d H:i")
                        );

                        $compra = $user->addToBalance(
                            $win,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control = CJSON::encode($rounds),
                            $fechaRef = date("Y-m-d H:i")
                        );
                    }
                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));



                    $data = [
                        "uid" =>  $uid,
                        "balance" => array(
                            "value" => $balance,
                            "version" => 0
                        ),

                    ];
                } else {
                    $data = roundsEmpty($uid);
                }
            } else {
                $data = userNotFound($uid);
            }
        } else {
            $data = sessionNotFound($uid);
        }
    }

    return $data;
}

function rollback($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound($body['uid']);

    if (isset($body['uid']) && isset($body['args']['token'])) {

        $uid = $body['uid'];
        $token = $body['args']['token'];
        $session = getUserSession($token);

        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



            if (isset($user)) {
                $rounds = $body['args']['rounds'];

                if (count($rounds)) {
                    if (isset($body['args']['win']) && !isset($body['args']['bet'])) {

                        $win = $body['args']['win'];

                        $compra = $user->addToBalance(
                            $win,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control =  $rounds,
                            $fechaRef = date("Y-m-d H:i")
                        );
                    }

                    if (isset($body['args']['bet'])  && !isset($body['args']['win'])) {
                        $bet = $body['args']['bet'];

                        $compra = $user->addToBalance(
                            $bet * -1,
                            15, //debito livesport
                            2, //invitado
                            CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                            $user->pcdi->PCDI_Cod,
                            $status = 1,
                            $control =  CJSON::encode($rounds),
                            $fechaRef = date("Y-m-d H:i")
                        );
                    }

                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));



                    $data = [
                        "uid" =>  $uid,
                        "balance" => array(
                            "value" => $balance,
                            "version" => 0
                        ),

                    ];
                } else {
                    $data = roundsEmpty($uid);
                }
            } else {
                $data = userNotFound($uid);
            }
        } else {
            $data = sessionNotFound($uid);
        }
    }

    return $data;
}


function getBalance($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound($body['uid']);

    if (isset($body['uid']) && isset($body['args']['token'])) {

        $uid = $body['uid'];
        $token = $body['args']['token'];
        $session = getUserSession($token);


        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "uid" =>  $uid,
                    "balance" => array(
                        "value" => $balance,
                        "version" => 0
                    ),
                ];
            } else {
                $data = userNotFound($uid);
            }
        } else {
            $data = sessionNotFound($uid);
        }
    }

    return $data;
}

function logout($entityBody)
{
    $body = $entityBody;

    $data = paramsNotFound($body['uid']);

    if (isset($body['uid']) && isset($body['args']['token'])) {

        $uid = $body['uid'];
        $token = $body['args']['token'];
        $session = getUserSession($token);


        if ($session) {
            $userId = str_replace("A-", "", $session['iduser']);
            $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

            if (isset($user)) {
                $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                $data = [
                    "uid" =>  $uid,
                ];
            } else {
                $data = userNotFound($uid);
            }
        } else {
            $data = sessionNotFound($uid);
        }
    }

    return $data;
}

/** helpers */

function paramsNotFound($uid)
{
    return array(
        "uid" => $uid,
        "error" => array(
            "code" => "Params not found",
            "message" => "",
        ),
    );
}

function userNotFound($uid)
{
    return array(
        "uid" => $uid,
        "error" => array(
            "code" => "User not found",
            "message" => "",
        ),
    );
}

function roundsEmpty($uid)
{
    return array(
        "uid" => $uid,
        "error" => array(
            "code" => "There aren't rounds",
            "message" => "",
        ),
    );
}

function sessionNotFound($uid)
{
    return array(
        "uid" => $uid,
        "error" => array(
            "code" => "Session not found",
            "message" => "",
        ),
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
