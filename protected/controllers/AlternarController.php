<?php
include 'protected/providers/alternar.php';

class AlternarController extends Controller
{

    const APPLICATION_ID = 'GECKO';
    private $format = 'xml';

    public function actionIndex()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $entityBody = file_get_contents('php://input');
                $mybody = simplexml_load_string($entityBody) or die("Error: Cannot create object");
                $jsonm = json_encode($mybody);
                $body = json_decode($jsonm, TRUE);

                // print_r($body);
                $method = $body['Method']['@attributes']['Name'];

                switch ($method) {
                    case 'GetBalance':

                        if (!isset($body['Method']['Params']['Token']) || !isset($body['Method']['Params']['ExternalUserID']) || !isset($body['Method']['Params']['SiteId'])) {

                            $data = paramsNotFound('GetBalance');
                        } else {
                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            // $externalUserId = $body['Method']['Params']['ExternalUserID']['@attributes']['Value'];
                            // $siteId = $body['Method']['Params']['SiteId']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));


                                if (isset($user)) {
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'GetBalance',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'Currency' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $user->pcdi->PCDI_Cod
                                                    ),
                                                ),
                                                'ExternalUserID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $userId
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('GetBalance');
                                }
                            } else {
                                $data = sessionNotFound('GetBalance');
                            }
                        }


                        break;
                    case 'PlaceBet':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['BetAmount']) ||
                            !isset($body['Method']['Params']['BetReferenceNum']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['BetMode']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['FrontendType'])
                        ) {
                            $data = array(
                                'Result' => array(
                                    '@attributes' => array(
                                        'Name' => 'PlaceBet',
                                        'Success' => 0
                                    ),
                                    'Returnset' => array(
                                        'Error' => array(
                                            '@attributes' => array(
                                                'Type' => 'string',
                                                'Value' => 'Params not found'
                                            ),
                                        ),
                                        'ErrorCode' => array(
                                            '@attributes' => array(
                                                'Type' => 'int',
                                                'Value' => 010
                                            ),
                                        ),
                                        'CallbackParameter' => array(
                                            '@attributes' => array(
                                                'Type' => 'string',
                                                'Value' => 'Error'
                                            ),
                                        )
                                    ),
                                )
                            );
                        } else {
                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];
                            $betAmount = $body['Method']['Params']['BetAmount']['@attributes']['Value'];
                            // $betRefenceNum = $body['Method']['Params']['BetReferenceNum']['@attributes']['Value'];
                            // $gameReference = $body['Method']['Params']['GameReference']['@attributes']['Value'];
                            // $betMode = $body['Method']['Params']['BetMode']['@attributes']['Value'];
                            // $description = $body['Method']['Params']['Description']['@attributes']['Value'];
                            // $externalUserId = $body['Method']['Params']['ExternalUserID']['@attributes']['Value'];
                            // $siteId = $body['Method']['Params']['SiteId']['@attributes']['Value'];
                            // $frontendType = $body['Method']['Params']['FrontendType']['@attributes']['Value'];

                            $session = getUserSession($token);


                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

                                $compra = $user->addToBalance(
                                    ($betAmount / 100) * -1,
                                    15, //debito livesport
                                    2, //invitado
                                    CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                    $user->pcdi->PCDI_Cod,
                                    $status = 1,
                                    $control =  $transactionId,
                                    $fechaRef = date("Y-m-d H:i")
                                );


                                if (isset($user)) {
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'PlaceBet',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'int',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => 1
                                                    ),
                                                ),
                                                'BonusAmount' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => "0"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('PlaceBet');
                                }
                            } else {
                                $data = sessionNotFound('PlaceBet');
                            }
                        }
                        break;
                    case 'GetAffiliatePlayers':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId'])
                        ) {

                            $data = paramsNotFound('GetAffiliatePlayers');
                        } else {
                            $login_name = $body['Method']['Auth']['@attributes']['Login'];
                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            // $externalUserId = $body['Method']['Params']['ExternalUserID']['@attributes']['Value'];
                            // $siteId = $body['Method']['Params']['SiteId']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

                                // $compra = $user->addToBalance(
                                //     ($betAmount / 100) * -1,
                                //     15, //debito livesport
                                //     2, //invitado
                                //     CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                //     $user->pcdi->PCDI_Cod,
                                //     $status = 1,
                                //     $control =  $transactionId,
                                //     $fechaRef = date("Y-m-d H:i")
                                // );

                                if (isset($user)) {
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'GetAffiliatePlayers',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'LoginName' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $login_name
                                                    ),
                                                ),
                                                'Currency' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $user->pcdi->PCDI_Cod
                                                    ),
                                                ),
                                                'Country' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $user->GCCA_Country
                                                    ),
                                                ),
                                                'ExternalUserID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'UserBalance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'IsDefault' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => 1
                                                    ),
                                                ),
                                                'IsActive' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => $session['status']
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('GetAffiliatePlayers');
                                }
                            } else {
                                $data = sessionNotFound('GetAffiliatePlayers');
                            }
                        }
                    case 'AwardWinnings':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['WinReferenceNum']) ||
                            !isset($body['Method']['Params']['WinAmount']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('AwardWinnings');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $winAmount = $body['Method']['Params']['WinAmount']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        ($winAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'AwardWinnings',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('AwardWinnings');
                                }
                            } else {
                                $data = sessionNotFound('AwardWinnings');
                            }
                        }
                        break;
                    case 'RefundBet':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['BetReferenceNum']) ||
                            !isset($body['Method']['Params']['RefundAmount']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('RefundBet');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $refundAmount = $body['Method']['Params']['RefundAmount']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        ($refundAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );

                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'RefundBet',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('RefundBet');
                                }
                            } else {
                                $data = sessionNotFound('RefundBet');
                            }
                            break;
                        }
                    case 'NewCredit':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['NewCreditAmount']) ||
                            !isset($body['Method']['Params']['NewCreditReferenceNum']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['FrontendType']) ||
                            !isset($body['Method']['Params']['BetStatus']) ||
                            !isset($body['Method']['Params']['SportIDs']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('NewCredit');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $winAmount = $body['Method']['Params']['NewCreditAmount']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        ($winAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'NewCredit',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => "true"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('NewCredit');
                                }
                            } else {
                                $data = sessionNotFound('NewCredit');
                            }
                        }
                        break;

                    case 'NewDebit':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['NewDebitAmount']) ||
                            !isset($body['Method']['Params']['NewDebitReferenceNum']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['FrontendType']) ||
                            !isset($body['Method']['Params']['BetStatus']) ||
                            !isset($body['Method']['Params']['SportIDs']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('NewDebit');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $newDebitAmount = $body['Method']['Params']['NewDebitAmount']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        ($newDebitAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'NewDebit',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => "true"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('NewDebit');
                                }
                            } else {
                                $data = sessionNotFound('NewDebit');
                            }
                        }
                        break;

                    case 'StakeDecrease':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['stakeDecreaseReferenceNum']) ||
                            !isset($body['Method']['Params']['stakeDecreaseAmount']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['FrontendType']) ||
                            !isset($body['Method']['Params']['SportIDs']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('StakeDecrease');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            $stakeDecreaseAmount = $body['Method']['Params']['stakeDecreaseAmount']['@attributes']['Value'];
                            $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        ($stakeDecreaseAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'StakeDecrease',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => "true"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('StakeDecrease');
                                }
                            } else {
                                $data = sessionNotFound('StakeDecrease');
                            }
                        }
                        break;

                    case 'GetBanners':
                        if (
                            !isset($body['Method']['Params']['Skin']) ||
                            !isset($body['Method']['Params']['GameType']) ||
                            !isset($body['Method']['Params']['Language'])
                        ) {
                            $data = paramsNotFound('GetBanners');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];
                            // $login = $body['Method']['Auth']['@attributes']['Login'];
                            // $password = $body['Method']['Auth']['@attributes']['Password'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));



                                if (isset($user)) {
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'GetBanners',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Skin' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => "90"
                                                    ),
                                                ),
                                                'GameType' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => "PreMatch"
                                                    ),
                                                ),
                                                'Language' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => "en"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('GetBanners');
                                }
                            } else {
                                $data = sessionNotFound('GetBanners');
                            }
                        }
                        break;

                    case 'LossSignal':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['BetReferenceNum']) ||
                            !isset($body['Method']['Params']['BetAmount']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['FrontendType']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('LossSignal');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));

                                if (isset($user)) {
                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'LossSignal',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array([z]),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('LossSignal');
                                }
                            } else {
                                $data = sessionNotFound('LossSignal');
                            }
                        }
                        break;

                    case 'CashoutBet':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['BetReferenceNum']) ||
                            !isset($body['Method']['Params']['CashoutAmount']) ||
                            !isset($body['Method']['Params']['GameReference']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['FrontendType']) ||
                            !isset($body['Method']['Params']['BetMode'])
                        ) {
                            $data = paramsNotFound('CashoutBet');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));
                                $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        // ($winAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'CashoutBet',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => "true"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('CashoutBet');
                                }
                            } else {
                                $data = sessionNotFound('CashoutBet');
                            }
                        }
                        break;

                    case 'AwardBonus':
                        if (
                            !isset($body['Method']['Params']['Token']) ||
                            !isset($body['Method']['Params']['TransactionID']) ||
                            !isset($body['Method']['Params']['BonusAmount']) ||
                            !isset($body['Method']['Params']['Description']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['BonusAccountId']) ||
                            !isset($body['Method']['Params']['SiteId'])
                        ) {
                            $data = paramsNotFound('AwardBonus');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));
                                $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];



                                if (isset($user)) {
                                    $compra = $user->addToBalance(
                                        // ($winAmount),
                                        15, //debito livesport
                                        2, //invitado
                                        CJSON::encode(array($body, $user->pcdi->PCDI_Cod)),
                                        $user->pcdi->PCDI_Cod,
                                        $status = 1,
                                        $control =  $transactionId,
                                        $fechaRef = date("Y-m-d H:i")
                                    );
                                    $balance = floatval(number_format($user->balance * 100, 0, ".", ""));

                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'AwardBonus',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(
                                                'Token' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $token
                                                    ),
                                                ),
                                                'Balance' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $balance
                                                    ),
                                                ),
                                                'ExtTransactionID' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'string',
                                                        'Value' => $compra['GCCS_Id']
                                                    ),
                                                ),
                                                'AlreadyProcessed' => array(
                                                    '@attributes' => array(
                                                        'Type' => 'bool',
                                                        'Value' => "true"
                                                    ),
                                                ),
                                            ),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('AwardBonus');
                                }
                            } else {
                                $data = sessionNotFound('AwardBonus');
                            }
                        }
                        break;

                    case 'BonusBalance':
                        if (
                            !isset($body['Method']['Params']['BonusPlanId']) ||
                            !isset($body['Method']['Params']['BonusAccountId']) ||
                            !isset($body['Method']['Params']['BonusBalance']) ||
                            !isset($body['Method']['Params']['BonusStatus']) ||
                            !isset($body['Method']['Params']['ExternalUserID']) ||
                            !isset($body['Method']['Params']['BonusTypeId']) ||
                            !isset($body['Method']['Params']['SiteId'])
                        ) {
                            $data = paramsNotFound('BonusBalance');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));
                                $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];



                                if (isset($user)) {
                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'BonusBalance',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('BonusBalance');
                                }
                            } else {
                                $data = sessionNotFound('BonusBalance');
                            }
                        }
                        break;

                    case 'GetAccountByExternalID':
                        if (
                            !isset($body['Method']['Params']['SiteId']) ||
                            !isset($body['Method']['Params']['ExternalUserID'])
                        ) {
                            $data = paramsNotFound('GetAccountByExternalID');
                        } else {

                            $token = $body['Method']['Params']['Token']['@attributes']['Value'];

                            $session = getUserSession($token);

                            if (isset($session)) {
                                $userId = str_replace("A-", "", $session['iduser']);

                                $user = Gcca::model()->find('GCCA_Id=:id', array(':id' => $userId));
                                $transactionId = $body['Method']['Params']['TransactionID']['@attributes']['Value'];



                                if (isset($user)) {
                                    $data = array(
                                        'Result' => array(
                                            '@attributes' => array(
                                                'Name' => 'GetAccountByExternalID',
                                                'Success' => 1
                                            ),
                                            'Returnset' => array(),
                                        )
                                    );
                                } else {
                                    $data = userNotFound('GetAccountByExternalID');
                                }
                            } else {
                                $data = sessionNotFound('GetAccountByExternalID');
                            }
                        }
                        break;


                        break;
                    default:
                        $data = array(
                            'Result' => array(
                                '@attributes' => array(
                                    'Name' => 'Authenticate',
                                    'Success' => 0
                                ),
                                'Returnset' => array(
                                    'Error' => array(
                                        '@attributes' => array(
                                            'Type' => 'String',
                                            'Value' => 'xxxxxxx'
                                        ),
                                    ),
                                    'ErrorCode' => array(
                                        '@attributes' => array(
                                            'Type' => 'int',
                                            'Value' => 0
                                        ),
                                    )
                                ),
                            )
                        );
                        break;
                }
            } else {
                $data = array(
                    'Result' => array(
                        '@attributes' => array(
                            'Name' => 'Authenticate',
                            'Success' => 0
                        ),
                        'Returnset' => array(
                            'Error' => array(
                                '@attributes' => array(
                                    'Type' => 'String',
                                    'Value' => 'xxxxxxx'
                                ),
                            ),
                            'ErrorCode' => array(
                                '@attributes' => array(
                                    'Type' => 'int',
                                    'Value' => 0
                                ),
                            )
                        ),
                    )
                );
            }



            $this->_sendResponse(200, $this->_getObjectEncoded('PKT', $data), 'application/xml');
        } catch (Exception $th) {
            $this->_sendResponse(200, $this->_getObjectEncoded('PKT', $th), 'application/xml');
        }
    }

    private function _getObjectEncoded($model, $array)
    {
        if (isset($_GET['format'])) {
            $this->format = $_GET['format'];
        }

        if ($this->format == 'json') {
            return CJSON::encode($array);
        } elseif ($this->format == 'xml') {
            $result = '<?xml version="1.0" ?>';
            $result .= "\n<$model>\n";

            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    /** result */
                    $result .= "    <$key  ";
                    foreach ($value as $at => $attri) {
                        $mix = false;
                        if ($at == "@attributes") {
                            foreach ($attri as $pa => $param) {
                                $result .= "$pa=\"$param\" ";
                            }
                            $result .= ">";
                        } else {
                            /** childs */
                            $result .= "\n\t<$at>";
                            foreach ($attri as $pa => $param) {
                                if (is_array($param)) {
                                    if ($pa == "@attributes") {
                                        $mix = true;
                                        foreach ($param as $par => $params) {
                                            $result .= "$par=\"$params\" ";
                                        }

                                        $result .= "  />";
                                    } else {
                                        $result .= "<$pa";
                                        foreach ($param as $par => $params) {
                                            if (is_array($params)) {
                                                if ($par == "@attributes") {
                                                    $mix = true;
                                                    foreach ($params as $pas => $paras) {
                                                        $result .= " $pas=\"$paras\" ";
                                                    }
                                                }
                                            } else {
                                                $result .= "<$par>$params</$par>";
                                            }
                                        }
                                        // $result .= "</$pa>";
                                        $result .= "/>";
                                    }
                                } else {
                                    if ($mix) $result = substr($result, 0, -2);
                                    $result .= ">";
                                    $result .= "\n\t$param";
                                    if ($mix) $result .= "\n    </$at >\n";
                                    // foreach ($attri as $pa => $param) {
                                    //     $result.="$pa=\"$param\" ";
                                    // }

                                }
                            }
                            if ($mix) $result .= "\n    </$at>\n";
                        }
                    }
                    /** end result */
                    $result .= "\n   </$key>\n";
                } else {

                    $result .= "    <$key>" . utf8_encode($value) . "</$key>\n";
                }
            }

            $result .= '</' . $model . '>';
            return $result;
        } else {
            return;
        }
    }
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

function paramsNotFound($result)
{
    return array(
        'Result' => array(
            '@attributes' => array(
                'Name' => $result,
                'Success' => 0
            ),
            'Returnset' => array(
                'Error' => array(
                    '@attributes' => array(
                        'Type' => 'string',
                        'Value' => 'Params not found'
                    ),
                ),
                'ErrorCode' => array(
                    '@attributes' => array(
                        'Type' => 'int',
                        'Value' => 010
                    ),
                )
            ),
        )
    );
}

function userNotFound($result)
{
    return array(
        'Result' => array(
            '@attributes' => array(
                'Name' => $result,
                'Success' => 0
            ),
            'Returnset' => array(
                'Error' => array(
                    '@attributes' => array(
                        'Type' => 'string',
                        'Value' => 'User not found'
                    ),
                ),
                'ErrorCode' => array(
                    '@attributes' => array(
                        'Type' => 'int',
                        'Value' => 002
                    ),
                )
            ),
        )
    );
}

function sessionNotFound($result)
{
    return array(
        'Result' => array(
            '@attributes' => array(
                'Name' => $result,
                'Success' => 0
            ),
            'Returnset' => array(
                'Error' => array(
                    '@attributes' => array(
                        'Type' => 'string',
                        'Value' => 'Session not found'
                    ),
                ),
                'ErrorCode' => array(
                    '@attributes' => array(
                        'Type' => 'int',
                        'Value' => 002
                    ),
                )
            ),
        )
    );
}
