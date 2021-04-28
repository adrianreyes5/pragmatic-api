<?php
include 'protected/providers/alternar.php';

class AlternarController extends Controller
{

    const APPLICATION_ID = 'GECKO';
    private $format = 'xml';

    public function actionIndex()
    {
        try {
            $entityBody = file_get_contents('php://input');
            $mybody = simplexml_load_string($entityBody) or die("Error: Cannot create object");
            $jsonm = json_encode($mybody);
            $body = json_decode($jsonm, TRUE);

            // print_r($body);
            // $data = array(
            //     'Result' => array(
            //         '@attributes' => array(
            //             'Name' => 'Authenticate',
            //             'Success' => 0
            //         ),
            //         'Returnset' => array(
            //             'Error' => array(
            //                 '@attributes' => array(
            //                     'Type' => 'String',
            //                     'Value' => 'xxxxxxx'
            //                 ),
            //             ),
            //             'ErrorCode' => array(
            //                 '@attributes' => array(
            //                     'Type' => 'int',
            //                     'Value' => 0
            //                 ),
            //             )
            //         ),
            //     )
            // );

            $this->_sendResponse(200, $this->_getObjectEncoded('PKT', $body), 'application/xml');
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
                    $result .= "    <$key ";
                    foreach ($value as $at => $attri) {
                        $mix = false;
                        if ($at == "@attributes") {
                            foreach ($attri as $pa => $param) {
                                $result .= "$pa=\"$param\" ";
                            }
                            $result .= ">";
                        } else {
                            $result .= "\n\t<$at  d ";
                            foreach ($attri as $pa => $param) {
                                if (is_array($param)) {
                                    if ($pa == "@attributes") {
                                        $mix = true;
                                        foreach ($param as $par => $params) {
                                            $result .= "$par=\"$params\" ";
                                        }
                                        
                                        $result .= " />";
                                    } else {
                                        $result .= "><$pa";
                                        foreach ($param as $par => $params) {
                                            if (is_array($params)) {
                                                if ($par == "@attributes") {
                                                    $mix = true;
                                                    foreach ($params as $pas => $paras) {
                                                        $result .= " $pas=\"$paras\" ";
                                                    }
                                                    $result .= "/>";
                                                }
                                            } else {
                                                $result .= "<$par>$params</$par>";
                                            }
                                        }
                                        // $result .= "</$pa> f";
                                    }
                                } else {
                                    if ($mix) $result = substr($result, 0, -2);
                                    $result .= ">";
                                    $result .= "\n\t$param";
                                    if ($mix) $result .= "\n    </$at>\n";
                                    // foreach ($attri as $pa => $param) {
                                    //     $result.="$pa=\"$param\" ";
                                    // }

                                }
                            }
                            if (!$mix) $result .= "\n    </$at>\n";
                        }
                    }
                    $result .= "\n    </$key>\n";
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
