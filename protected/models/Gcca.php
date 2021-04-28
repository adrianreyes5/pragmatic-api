<?php

/* * * **
* This is the model class for table "gcca".
*
* The followings are the available columns in table 'gcca':
* @property integer $GCCA_Id
* @property string $GCCA_Cod
* @property string $GCCA_Nombre
* @property string $GCCA_Date
* @property string $GCCA_Country
* @property string $GCCA_Email
* @property string $GCCA_Tv
* @property string $GCCA_Type
* @property integer $GCCD_Id
* @property string $GCCA_Address
* @property integer $GCCA_status
* @property string $GCCA_RIF
* @property string $GCCA_Fullname
* @property string $GCCA_Phone
* @property string $PCDI_Id
*
* The followings are the available model relations:
* @property Gccd $gccd
* @property Pcdi $pcdi
* @property Gcce[] $gcces
* @property Gcci[] $gccis
* @property Gccn[] $gccns
* @property Gccn[] $gccns1
* @property Gccs[] $gccs
* @property Gcut[] $gcuts
* @property Gcut[] $gcuts1

*/

class Gcca extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Gcca the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'gcca';
    }

    public function getUsername()
    {
        $acc = Gccn::model()->find('GCCA_Id = :id and GCCN_Deleted is null order by GCCN_Id asc', array(':id' => $this->GCCA_Id));
        if(isset($acc)){
            return $acc->GCCN_Login;
        }else{
            return "Sin Usuario Asignado";
        }
    }
    public function getSuperStatus($grupo = null)
    {

        if (!$grupo) return $this->getSuperStatus($this->gccd);

        if ($grupo->GCCD_IdSuperior) {
            return $this->getSuperStatus($grupo->gCCDIdSuperior) * $grupo->GCCD_Estado;
        } else {
            return $grupo->GCCD_Estado;
        }
    }
    public function getSecurity()
    {

        $array = null;
        $inseguras = array(
            '81dc9bdb52d04dc20036dbd8313ed055'
        );
        $cuentas = $this->gccns;
        foreach ($cuentas as $value) {
            if (array_search($value->GCCN_Clave, $inseguras) !== NULL && array_search($value->GCCN_Clave, $inseguras) !== false && array_search($value->GCCN_Clave, $inseguras) !== "") {
                $array = array(
                    'error' => "Esta cuenta es insegura, actualice las clave de los usuarios"
                );
            }
        }
        return $array;
    }
    public function getUpdated()
    {

        $array = true;
        $modeles = Gcce::model()->findAll("GCCA_Id=:id and GCCE_Currency != '0' ", array(':id' => $this->GCCA_Id));
        if (!isset($modeles)) {
            $array = false;
        }
        return $array;
    }
    public function getActivated()
    {

        return $this->lastAccess['created'] > strtotime(date("Y-m-d H:i") . "-60 days") || strtotime($this->GCCA_Date) > strtotime(date("Y-m-d H:i") . "-60 days");
    }
    public function getLastAccess()
    {
        $user = Yii::app()->db->createCommand()
            ->select('*')
            ->from('cruge_session')
            ->where(
                'iduser=:user',
                array(
                    ':user' => "A-" . $this->GCCA_Id,
                )
            )
            ->order('idsession desc')
            ->queryRow();

        $use['expire'] = strtotime('2000-01-01 00:00');
        $use['created'] = strtotime('2000-01-01 00:00');

        if (isset($user)) {
            $use['expire'] = $user['expire'];
            $use['created'] = $user['created'];
        }
        return $use;
    }
    public function getCompleted()
    {
        $modele = Gcce::model()->find('GCCA_Id=' . $this->GCCA_Id . ' order by GCCE_Fecha desc');

        return isset($modele);
    }


    public function getConcatened()
    {
        $result = $this->GCCA_Cod . ' - ' . $this->GCCA_Nombre;

        return $result;
    }
    public function getConcatenedSaldo()
    {
        return $this->GCCA_Cod . ' - ' . $this->GCCA_Nombre . " ( $" . number_format($this->balance, 2) . " )";
    }
    public function sBalance($currency)
    {

        $currency = isset($currency) ? $currency : $this->pcdi->PCDI_Cod;


        $modele = Gcce::model()->find('GCCA_Id=' . $this->GCCA_Id . ' order by GCCE_Fecha asc');
        if (isset($modele)) {
            $initDate = $modele->GCCE_Fecha;
            $GCCO_SaldoF = 0;
        } else {
            $initDate = date('Y-m-d H:i:s');
            $GCCO_SaldoF = 1;
        }



      

        $gccs = Gccs::model()->find(
            array(
                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                'condition' => '
                    GCCA_Id=:id 
                    and ( GCCS_Status = 1 )
                    and GCCS_Currency=:currency ', //todos
                'params' => array(
                    ':id' => $this->GCCA_Id,
                    ':currency' => $currency,

                )
            )
        );
        $pines = Gccs::model()->find(
            array(
                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                'condition' => '
                    GCCS_Usuario=:idX
                    and GCCS_Status = 1 
                    and GCCS_Currency=:currency ', //todos
                'params' => array(

                    ':idX' => "A-" . $this->GCCA_Id,
                    ':currency' => $currency,

                )
            )
        );



        // $gccs['GCCS_Monto']
        $wallet = $gccs['GCCS_Monto'] - $pines['GCCS_Monto'];
        


        return  floatval($wallet);
    }

    public function getLegal()
    {
        $title = 'Correo No Confirmado';
        $icon = 'user times';
        $color = 'red';
        $note = 'Por favor comprueba tu bandeja de entrada para confirmar tu correo, luego de confirmado no lo podras cambiar. Si tienes problemas, puede cambiarlo ahora o contactar a soporte.';
        // $note = 'ok';

        if ($this->GCCA_Step == 1) {
            $icon = 'user times';
            $color = 'red';
            $note = 'Completa el formulario para verificar tu cuenta en KINGDEPORTES';
            $title = 'Cuenta No Verificada'; //email confirmed
        } else if ($this->GCCA_Step == 2) {
            $icon = 'wait';
            $color = 'orange';
            $note = 'Estamos revisando los datos enviados, pronto seras notificado por correo';
            $title = 'Esperando Verificacion'; //doc verificated
        } else if ($this->GCCA_Step == 3) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Principiante'; // 1 title
        } else if ($this->GCCA_Step == 4) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Regular'; // 1 title
        }else if ($this->GCCA_Step == 5) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Profesional'; // 1 title
        }
        else if ($this->GCCA_Step == 6) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Experto'; // 1 title
        }
        else if ($this->GCCA_Step == 7) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Maestro'; // 1 title
        }
        else if ($this->GCCA_Step == 8) {
            $icon = 'user';
            $color = 'green';
            $note = 'ok';
            $title = 'Verificado - Nivel: Leyenda'; // 1 title
        }
        return array(
            'key' => $this->GCCA_Step,
            'value' => $title,
            'icon' => $icon,
            'color' => $color,
            'note' => $note
        );
    }

    public function getIsOnline(){
        $sql = "SELECT * FROM gcca, cruge_session 
        WHERE cruge_session.iduser = CONCAT('A-',".$this->GCCA_Id.") 
        AND cruge_session.created > '" . strtotime(date("Y-m-d H:i") . '-30 minutes') . "'";
    
        return count(Yii::app()->db->createCommand($sql)->queryAll()) > 0;

    }

    public function getSaldos()
    {
        $monedas = Pcdi::model()->findAll();
        $saldos = array();
        foreach ($monedas as $value) {
            $saldos[$value->PCDI_Cod] = number_format($this->sBalance($value->PCDI_Cod), 2) . " " . $value->PCDI_Cod;
        }
        return $saldos;
    }

    public function getBalance()
    {
        return  $this->sBalance(null);
    }

    public function getPermisos()
    {
        $array = [];
        $modele = Gcce::model()->findAll(
            'GCCD_Id = :gccd_id and GCCA_Id = :gcca_id and GCCE_Currency = :currency and GCCE_Enabled = 1 group by GCCP_Id order by GCCP_Id asc',
            array(
                ':gcca_id' => $this->GCCA_Id,
                ':gccd_id' => $this->GCCD_Id,
                ':currency' => $this->pcdi->PCDI_Cod
            )
        );

        foreach ($modele as $value) {
            if ($value->gccp->GCCP_Enabled) {
                $array[] = array(
                    "id" => $value->gccp->GCCP_Id,
                    "producto" => $value->gccp->GCCP_Nombre,
                    "icon" => $value->gccp->GCCP_Icon,
                    "link" => $value->gccp->GCCP_Link,

                );
            }
        }

        return $array;
    }

    public function getMaestro($inicio = null, $fin = null, $save = false)
    {


        if (isset($inicio) && isset($fin)) {
            $inicio = date("Y-m-d", strtotime($inicio));
            $fin = date("Y-m-d", strtotime($fin));
        } else {
            $inicio = date("Y-m-d");
            $fin = date("Y-m-d");
        }

        $inicio = date("Y-m-d H:i", strtotime($inicio . ' +4 hours'));
        $fin = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i", strtotime($fin . ' +28 hours')) . '-1 second'));

        $record = Gccs::model()->find('(GCCA_Id =:gcca_id or GCCS_Usuario = :user) and GCCS_Fecha between :ini and :fin order by GCCS_Fecha desc ', array(':gcca_id' => $this->GCCA_Id, ':user' => "A-" . $this->GCCA_Id, ':ini' => $inicio, ':fin' => $fin));
        $tickets = Pccu::model()->find('GCCA_Id =:gcca_id and PCCU_Tiempo between :ini and :fin order by PCCU_Tiempo desc ', array(':gcca_id' => $this->GCCA_Id, ':ini' => $inicio, ':fin' => $fin));

        if (isset($record) || isset($tickets) || $save) {
            $present = true;
        } else {
            $present = false;
        }

        $metodo = 2;

        $array = [];
        
        if ($metodo == 2 && $present) {
            //Solo calculo lo permitido 
            $modele = Gcce::model()->findAll(
                'GCCD_Id = :gccd_id and GCCA_Id = :gcca_id 
                group by GCCP_Id, GCCE_Currency 
                order by GCCE_Currency desc, GCCE_Id desc',
                array(
                    ':gcca_id' => $this->GCCA_Id,
                    ':gccd_id' => $this->GCCD_Id,

                )
            );

            foreach ($modele as $value) {

                $ingresos = 0;
                $cantidad = 0;
                $comisiones = 0;
                $egresos = 0;

                if (isset($array[$value->GCCE_Currency])) {

                    // if($value->gccp->GCCP_Enabled){    

                    $per = $value->GCCE_PorcentajeVentasP / 100;
                    $pero = $value->GCCE_PorcentajeVentasD / 100;

                    // Apuestas Invader
                    if ($value->gccp->GCCP_Id == 1) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=3 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=4 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Apuestas King
                    else  if ($value->gccp->GCCP_Id == 11) {


                        if ($value->GCCE_Currency == "0") {
                            $array[0] = array(
                                "Ingresos" => 0,
                                "Egresos" => 0,
                                "Comisiones" => 0,
                            );
                            $opIngress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                            and PCCU_Currency is null
                                            and PCCU_External_Id = 11
                                            and GCCA_Id=:id 
                                            and PCCU_Tiempo between :inicio and :fin',
                                    'params' => array(
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                            $opEgress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                            and PCCU_Currency is null
                                            and PCCU_External_Id = 11
                                            and GCCA_Id=:id 
                                            and PCCU_Cierre between :inicio and :fin',
                                    'params' => array(
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                        } else {
                            $opIngress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                            and PCCU_Currency=:currency 
                                            and GCCA_Id=:id 
                                            and PCCU_External_Id = 11
                                            and PCCU_Tiempo between :inicio and :fin',
                                    'params' => array(

                                        ':currency' => $value->GCCE_Currency,
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                            $opEgress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                            and PCCU_Currency=:currency 
                                            and GCCA_Id=:id 
                                            and PCCU_External_Id = 11
                                            and PCCU_Cierre between :inicio and :fin',
                                    'params' => array(
                                        ':currency' => $value->GCCE_Currency,
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                        }

                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->PCCU_Monto);
                            $cantidad = $opIngress->PCCU_Id;
                            $comisiones = -1 * $opIngress->PCCU_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos = -1 * $opEgress->PCCU_Ganancia;
                        }
                    }
                    // Sorteos King
                    else  if ($value->gccp->GCCP_Id == 13) {



                        $opIngress = Pccu::model()->find(
                            array(
                                'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                        and PCCU_Currency=:currency 
                                        and GCCA_Id=:id 
                                        and PCCU_External_Id = 13
                                        and PCCU_Tiempo between :inicio and :fin',
                                'params' => array(

                                    ':currency' => $value->GCCE_Currency,
                                    ':id' => $this->GCCA_Id,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Pccu::model()->find(
                            array(
                                'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                        and PCCU_Currency=:currency 
                                        and GCCA_Id=:id 
                                        and PCCU_External_Id = 13
                                        and PCCU_Cierre between :inicio and :fin',
                                'params' => array(
                                    ':currency' => $value->GCCE_Currency,
                                    ':id' => $this->GCCA_Id,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );


                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->PCCU_Monto);
                            $cantidad = $opIngress->PCCU_Id;
                            $comisiones = -1 * $opIngress->PCCU_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos = -1 * $opEgress->PCCU_Ganancia;
                        }
                    }
                    // Pines
                    else  if ($value->gccp->GCCP_Id == 12) {
                        //recargas
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=0 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin
                                        and GCCS_Status = 1 
                                        and GCCS_Usuario=:id',
                                'params' => array(
                                    ':id' => "A-" . $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        //retiros
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin
                                        and GCCS_Status = 1 
                                        and GCCS_Usuario=:id',
                                'params' => array(
                                    ':id' => "A-" . $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->GCCS_Monto);
                            $comisiones = -1 * $opIngress->GCCS_Monto * $per;
                            $cantidad = $opIngress->GCCS_Id;
                        }
                        if (isset($opEgress)) {
                            $egresos = floatval($opEgress->GCCS_Monto);
                            $comisiones += $opEgress->GCCS_Monto * $pero;
                            $cantidad += $opEgress->GCCS_Id;
                        }
                    }
                    // Directas
                    else  if ($value->gccp->GCCP_Id == 10) {
                    }
                    // Maquinitas
                    else  if ($value->gccp->GCCP_Id == 2) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=7 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=8 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Carreras
                    else  if ($value->gccp->GCCP_Id == 3) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=9 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=10 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Sorteos triples
                    else  if ($value->gccp->GCCP_Id == 8) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=5 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=6
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Live SPORTS
                    else  if ($value->gccp->GCCP_Id == 5) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=15 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=16
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Horses Center
                    else  if ($value->gccp->GCCP_Id == 4) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=11 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=12
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Live Games
                    else  if ($value->gccp->GCCP_Id == 6) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=13 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=14
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }


                    $array[$value->GCCE_Currency]["Ingresos"] +=  $ingresos;
                    $array[$value->GCCE_Currency]["Egresos"] += $egresos;
                    $array[$value->GCCE_Currency]["Comisiones"] += 0;

                    if ($ingresos + $comisiones + $egresos  != 0 || $save)
                        $array[$value->GCCE_Currency]["Productos"][$value->gccp->GCCP_Id] = array(
                            "id" => $value->gccp->GCCP_Id,
                            "Producto" => "<i class=' " . $value->gccp->GCCP_Icon . "'></i> " . $value->gccp->GCCP_Nombre,
                            "icon" => $value->gccp->GCCP_Icon,
                            "link" => $value->gccp->GCCP_Link,
                            "Ingresos" => $ingresos,
                            "Cantidad" => $cantidad,
                            "PorcentajeIn" => $per,
                            "PorcentajeOut" => $pero,
                            "Comisiones" => 0,
                            "Participacion" => 0,
                            "Gastos" => 0,
                            "Egresos" => $egresos,
                            ':inicio' => $inicio,
                            ':fin' => $fin,
                            "Utilidad" => $ingresos + $comisiones + $egresos
                        );
                    if (isset($array[$value->GCCE_Currency]["Productos"]))
                        ksort($array[$value->GCCE_Currency]["Productos"], SORT_STRING);

                    // }

                } else {

                    // if($value->gccp->GCCP_Enabled){    

                    $per = $value->GCCE_PorcentajeVentasP / 100;
                    $pero = $value->GCCE_PorcentajeVentasD / 100;

                    // Apuestas Invader
                    if ($value->gccp->GCCP_Id == 1) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=3 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=4 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Apuestas King
                    else  if ($value->gccp->GCCP_Id == 11) {


                        if ($value->GCCE_Currency == "0") {
                            $array[0] = array(
                                "Ingresos" => 0,
                                "Egresos" => 0,
                                "Comisiones" => 0,
                            );
                            $opIngress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                            and PCCU_Currency is null
                                            and PCCU_External_Id = 11
                                            and GCCA_Id=:id 
                                            and PCCU_Tiempo between :inicio and :fin',
                                    'params' => array(
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                            $opEgress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                            and PCCU_Currency is null
                                            and PCCU_External_Id = 11
                                            and GCCA_Id=:id 
                                            and PCCU_Cierre between :inicio and :fin',
                                    'params' => array(
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                        } else {
                            $opIngress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                            and PCCU_Currency=:currency 
                                            and GCCA_Id=:id 
                                            and PCCU_External_Id = 11
                                            and PCCU_Tiempo between :inicio and :fin',
                                    'params' => array(

                                        ':currency' => $value->GCCE_Currency,
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                            $opEgress = Pccu::model()->find(
                                array(
                                    'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                    'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                            and PCCU_Currency=:currency 
                                            and GCCA_Id=:id 
                                            and PCCU_External_Id = 11
                                            and PCCU_Cierre between :inicio and :fin',
                                    'params' => array(
                                        ':currency' => $value->GCCE_Currency,
                                        ':id' => $this->GCCA_Id,
                                        ':inicio' => $inicio,
                                        ':fin' => $fin,
                                    )
                                )
                            );
                        }

                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->PCCU_Monto);
                            $cantidad = $opIngress->PCCU_Id;
                            $comisiones = -1 * $opIngress->PCCU_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos = -1 * $opEgress->PCCU_Ganancia;
                        }
                    }
                    // Sorteos King
                    else  if ($value->gccp->GCCP_Id == 13) {



                        $opIngress = Pccu::model()->find(
                            array(
                                'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Monto) as PCCU_Monto',
                                'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (1,2,3,5,7,8) 
                                        and PCCU_Currency=:currency 
                                        and GCCA_Id=:id 
                                        and PCCU_External_Id = 13
                                        and PCCU_Tiempo between :inicio and :fin',
                                'params' => array(

                                    ':currency' => $value->GCCE_Currency,
                                    ':id' => $this->GCCA_Id,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Pccu::model()->find(
                            array(
                                'select' => 'count(PCCU_Id) as PCCU_Id, SUM(PCCU_Ganancia) as PCCU_Ganancia',
                                'condition' => 'PCCU_Tipo!=1 and PCCD_Id in (2,5,7,8) 
                                        and PCCU_Currency=:currency 
                                        and GCCA_Id=:id 
                                        and PCCU_External_Id = 13
                                        and PCCU_Cierre between :inicio and :fin',
                                'params' => array(
                                    ':currency' => $value->GCCE_Currency,
                                    ':id' => $this->GCCA_Id,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );


                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->PCCU_Monto);
                            $cantidad = $opIngress->PCCU_Id;
                            $comisiones = -1 * $opIngress->PCCU_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos = -1 * $opEgress->PCCU_Ganancia;
                        }
                    }
                    // Pines
                    else  if ($value->gccp->GCCP_Id == 12) {
                        //recargas
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=0 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin
                                        and GCCS_Status = 1 
                                        and GCCS_Usuario=:id',
                                'params' => array(
                                    ':id' => "A-" . $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        //retiros
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin
                                        and GCCS_Status = 1 
                                        and GCCS_Usuario=:id',
                                'params' => array(
                                    ':id' => "A-" . $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval($opIngress->GCCS_Monto);
                            $comisiones = -1 * $opIngress->GCCS_Monto * $per;
                            $cantidad = $opIngress->GCCS_Id;
                        }
                        if (isset($opEgress)) {
                            $egresos = floatval($opEgress->GCCS_Monto);
                            $comisiones += $opEgress->GCCS_Monto * $pero;
                            $cantidad += $opEgress->GCCS_Id;
                        }
                    }
                    // Directas
                    else  if ($value->gccp->GCCP_Id == 10) {
                    }
                    // Maquinitas
                    else  if ($value->gccp->GCCP_Id == 2) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=7 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=8 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Carreras
                    else  if ($value->gccp->GCCP_Id == 3) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=9 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=10 
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Sorteos triples
                    else  if ($value->gccp->GCCP_Id == 8) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=5 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=6
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Live SPORTS
                    else  if ($value->gccp->GCCP_Id == 5) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=15 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=16
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Horses Center
                    else  if ($value->gccp->GCCP_Id == 4) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=11 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=12
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                    // Live Games
                    else  if ($value->gccp->GCCP_Id == 6) {
                        $opIngress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=13 
                                        and GCCA_Id=:id
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //debitos apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        $opEgress = Gccs::model()->find(
                            array(
                                'select' => 'count(GCCS_Id) as GCCS_Id, SUM(GCCS_Monto) as GCCS_Monto',
                                'condition' => 'GCUA_Id=14
                                        and GCCA_Id=:id 
                                        and GCCS_Status = 1 
                                        and GCCS_Currency=:currency 
                                        and GCCS_Fecha between :inicio and :fin', //credito apuestas deportivas
                                'params' => array(
                                    ':id' => $this->GCCA_Id,
                                    ':currency' => $value->GCCE_Currency,
                                    ':inicio' => $inicio,
                                    ':fin' => $fin,
                                )
                            )
                        );
                        if (isset($opIngress)) {
                            $ingresos = floatval(-1 * $opIngress->GCCS_Monto);
                            $comisiones =  $opIngress->GCCS_Monto * $per;
                        }
                        if (isset($opEgress)) {
                            $egresos =  -1 * $opEgress->GCCS_Monto;
                        }
                    }
                
                    if ($ingresos + $comisiones + $egresos  != 0 || $save)
                        $array[$value->GCCE_Currency] = array(
                            "Ingresos" => $ingresos,
                            "Egresos" => $egresos,
                            "Comisiones" => 0,
                            "Productos" => array()
                        );


                    if ($ingresos + $comisiones + $egresos  != 0 || $save)
                        $array[$value->GCCE_Currency]["Productos"][$value->gccp->GCCP_Id] = array(
                            "id" => $value->gccp->GCCP_Id,
                            "Producto" => "<i class=' " . $value->gccp->GCCP_Icon . "'></i> " . $value->gccp->GCCP_Nombre,
                            "icon" => $value->gccp->GCCP_Icon,
                            "link" => $value->gccp->GCCP_Link,
                            "Ingresos" => $ingresos,
                            "Egresos" => $egresos,
                            "Cantidad" => $cantidad,
                            "Utilidad" => $ingresos + $comisiones + $egresos,
                            "PorcentajeIn" => $per,
                            "PorcentajeOut" => $pero,
                            "Comisiones" => 0,
                            "Participacion" => 0,
                            "Gastos" => 0,
                            ':inicio' => $inicio,
                            ':fin' => $fin,
                        );

                    if (isset($array[$value->GCCE_Currency]["Productos"]))
                        ksort($array[$value->GCCE_Currency]["Productos"], SORT_STRING);

                    // }
                }
               

                //1. Guardo por Producto y monedas
                //2. Guardo por Moneda
                // if($save){

                $gccoAnt = Gcco::model()->find(
                    "GCCA_Id = :gcca_id and 
                            GCCD_Id = :gccd_id and 
                          
                            GCCP_Id = :gccp_id and
                            GCCO_Currency = :currency and 
                            GCCO_Fecha < :ini 
                            order by GCCO_Fecha desc",
                    array(
                        ':gcca_id' => $this->GCCA_Id,
                        ':gccd_id' => $this->GCCD_Id,
                        ':gccp_id' => $value->GCCP_Id,
                        ':currency' => $value->GCCE_Currency,
                        ':ini' => $inicio
                    )
                );

                if ($gccoAnt === null) {
                    $gccoAnt = new Gcco();
                    $gccoAnt->GCCO_UtilAcumulada = 0;
                    $gccoAnt->GCCO_SaldoF = 0;
                }
                $gcco = new Gcco;
                // $gcco->setIsNewRecord(true);
                $gcco->GCCO_Currency = $value->GCCE_Currency;
                $gcco->GCCP_Id = $value->GCCP_Id;
                $gcco->GCCA_Id = $this->GCCA_Id;
                $gcco->GCCD_Id = $this->GCCD_Id;
                $gcco->GCCE_Id = $value->GCCE_Id;
                $gcco->GCCO_Fecha = $inicio;
                $gcco->GCCO_SaldoAnt = $gccoAnt->GCCO_SaldoF;
                
                $gcco->GCCO_VentaBrutaP = $ingresos;
                $gcco->GCCO_PremioP = $egresos;
                $gcco->GCCO_ComisionP = 0;
                
                $gcco->GCCO_Gbanca = 0; //Las agencias no tienen Gastos

                $e = explode("-", $value->GCCE_ControlEsquema);
                /*
                * $e[0] = [0 => null, 1 => porcentaje de ventas, 2 => recompensa]
                * $e[1] lleva porcentaje de utilidad {0/1}
                * $e[2] lleva porcentaje de perdida  {0/1} *agencias no llevan perdida
                * $e[3] tipo de periodo              {diario = 0, semanal = 1, mensual = 2}
                */

                $gcco->GCCO_Descripcion = 'Cierre de Agencia Agrupadora';

                if ($e[0] == '1') {
                    $gcco->GCCO_Descripcion = 'Cierre de Agencia con comisiones';
                    $gcco->GCCO_ComisionP = $comisiones;
                    if ($ingresos + $comisiones + $egresos  != 0) {
                        $array[$value->GCCE_Currency]["Productos"][$value->GCCP_Id]["Comisiones"] = $comisiones;
                        $array[$value->GCCE_Currency]["Comisiones"] = $comisiones;
                    }
                }

                $gcco->GCCO_Utilidad =  $gcco->GCCO_VentaBrutaP + $gcco->GCCO_PremioP + $gcco->GCCO_ComisionP;

                if ($e[1] == '1' && $e[2] == '0') { //Agencia con participacion
                    $gcco->GCCO_UtilAcumulada = $gccoAnt->GCCO_UtilAcumulada + $gcco->GCCO_Utilidad;                    
                    $gcco->GCCO_Descripcion = 'Cierre de Agencia Participacion';


                    $diaS = date('w', strtotime($inicio));
                    $diaM = date('d', strtotime($inicio));

                    //Si va diario, poco probable pero disponible
                    if ($e[3] == 0) {
                        if ($gcco->GCCO_UtilAcumulada > 0)  //solo si la utilidad es positiva
                            $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;

                        $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion Diaria';
                        $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                    } else
                        // si va semanal y es domingo o si va mensual y es fin de mes
                        if (($e[3] == 1 && $diaS == 0) || ($e[3] == 2 && date('t', strtotime($inicio)) == $diaM)) { // si es fin de periodo

                            if ($gcco->GCCO_UtilAcumulada > 0)  //solo si la utilidad es positiva
                                $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;

                            $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion final de periodo';
                            $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                        } else {
                            $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion dia normal';
                            $gcco->GCCO_Participacion = 0;
                        }
                }
                if ($e[1] == '1' && $e[2] == '1') { //Agencia con participacion y Perdidas
                    $gcco->GCCO_UtilAcumulada = $gccoAnt->GCCO_UtilAcumulada + $gcco->GCCO_Utilidad;                   
                    $gcco->GCCO_Descripcion = 'Cierre de Agencia Participacion y Perdidas';

                    $diaS = date('w', strtotime($inicio));
                    $diaM = date('d', strtotime($inicio));

                    //Si va diario, poco probable pero disponible
                    if ($e[3] == 0) {
                        $gcco->GCCO_Descripcion = 'Cierre de Agencia Utilidades y Perdidas Diario';

                        if ($gcco->GCCO_UtilAcumulada > 0) {  //solo si la utilidad es positiva
                            $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;
                            $gcco->GCCO_Descripcion = 'Cierre de Agencia Utilidades y Perdidas Diario, Participacion en Utilidades';
                        } else if ($gcco->GCCO_UtilAcumulada < 0) {
                            $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajePerdida / 100;
                            $gcco->GCCO_Descripcion = 'Cierre de Agencia Utilidades y Perdidas Diario, Participacion en Perdidas';
                        } else {
                            $gcco->GCCO_Participacion = 0;
                        }

                        $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                    } else
                        // si va semanal y es domingo o si va mensual y es fin de mes
                        if (($e[3] == 1 && $diaS == 0) || ($e[3] == 2 && date('t', strtotime($inicio)) == $diaM)) { // si es fin de periodo
                            $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion y Perdidas Final de Periodo';

                            if ($gcco->GCCO_UtilAcumulada > 0) {  //solo si la utilidad es positiva
                                $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;
                                $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion y Perdidas Final de Periodo, Participacion en Utilidad';
                            } else if ($gcco->GCCO_UtilAcumulada < 0) {
                                $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajePerdida / 100;
                                $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion y Perdidas Final de Periodo, Participacion en Perdidas';
                            } else {
                                $gcco->GCCO_Participacion = 0;
                            }


                            $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                        } else {
                            $gcco->GCCO_Participacion = 0;
                            $gcco->GCCO_Descripcion = 'Cierre de Agencia con Participacion y Perdidas dia normal ';
                        }
                }
                if ($e[0] == '2') { //recompensa   
                                     
                    $gcco->GCCO_UtilAcumulada = $gccoAnt->GCCO_UtilAcumulada + $gcco->GCCO_Utilidad;
                    $gcco->GCCO_PartAcumulada = $gccoAnt->GCCO_PartAcumulada + $gcco->GCCO_VentaBrutaP;

                    if ($e[3] == '0') { //diario
                        if ($gcco->GCCO_UtilAcumulada > 0) { //solo si la utilidad es positiva
                            $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;
                           
                        } else {
                            $gcco->GCCO_Participacion = 0;
                                                       
                            $gcco->GCCO_ComisionP =-1 * $gcco->GCCO_PartAcumulada * $value->GCCE_PorcentajeVentasP / 100;
                            $gcco->GCCO_Utilidad = $gcco->GCCO_VentaBrutaP + $gcco->GCCO_PremioP + $gcco->GCCO_ComisionP;
                            if ($ingresos + $gcco->GCCO_ComisionP + $egresos  != 0) {
                                $array[$value->GCCE_Currency]["Productos"][$value->GCCP_Id]["Comisiones"] +=  $gcco->GCCO_ComisionP;
                                $array[$value->GCCE_Currency]["Comisiones"] += $array[$value->GCCE_Currency]["Productos"][$value->GCCP_Id]["Comisiones"];
                            }
                            
                        }
                        $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                        $gcco->GCCO_PartAcumulada = 0; // reinicio los acumulados
                        $gcco->GCCO_Descripcion = 'Cierre de Cuenta con Participacion/Recompensa final de periodo diario';

                    } else  if (($e[3] == '1' && $diaS == 0) || ($e[3] == ' 2' && date('t', strtotime($inicio)) == $diaM)) { // si es fin de periodo
                        if ($gcco->GCCO_UtilAcumulada > 0) { //solo si la utilidad es positiva
                            $gcco->GCCO_Participacion = -1 * $gcco->GCCO_UtilAcumulada * $value->GCCE_PorcentajeUtilidad / 100;                       
                        } else {
                            $gcco->GCCO_Participacion = 0;
                                                    
                            $gcco->GCCO_ComisionP = -1 * $gcco->GCCO_PartAcumulada * $value->GCCE_PorcentajeVentasP / 100;
                            $gcco->GCCO_Utilidad = $gcco->GCCO_VentaBrutaP + $gcco->GCCO_PremioP + $gcco->GCCO_ComisionP;
                            if ($ingresos + $gcco->GCCO_ComisionP + $egresos  != 0) {
                                $array[$value->GCCE_Currency]["Productos"][$value->GCCP_Id]["Comisiones"] +=  $gcco->GCCO_ComisionP;
                                $array[$value->GCCE_Currency]["Comisiones"] += $array[$value->GCCE_Currency]["Productos"][$value->GCCP_Id]["Comisiones"];
                            }
                        }
                        $gcco->GCCO_Descripcion = 'Cierre de Cuenta con Participacion/Recompensa final de periodo sem/men ';
                        $gcco->GCCO_UtilAcumulada = 0; // reinicio los acumulados
                        $gcco->GCCO_PartAcumulada = 0; // reinicio los acumulados
                    } else {
                        $gcco->GCCO_Participacion = 0;
                        $gcco->GCCO_Descripcion = 'Cierre de Grupo con Participacion/Recompensa dia normal';
                    }
                }

                $gcco->GCCO_SaldoF = $gcco->GCCO_SaldoAnt + $gcco->GCCO_Utilidad + $gcco->GCCO_Participacion;
               
                $gccs = $value->getPea($inicio, $fin);
                if ($gccs !== null) {
                    $gcco->GCCO_SaldoF = $gcco->GCCO_SaldoF - $gccs->Pagos - $gccs->Envios;
                    $gcco->GCCO_Descripcion = $gcco->GCCO_Descripcion . ' Encontre: P:' . $gccs->Pagos . ' E:' . $gccs->Envios;
                }

                if ($ingresos + $comisiones + $egresos  != 0 || $save) {
                    $array[$value->GCCE_Currency]["Productos"][$value->gccp->GCCP_Id]["Participacion"] = $gcco->GCCO_Participacion;
                    $array[$value->GCCE_Currency]["Productos"][$value->gccp->GCCP_Id]["Gastos"] = $gcco->GCCO_Gbanca;
                    // $array[$value->GCCE_Currency]["Productos"][$value->gccp->GCCP_Id]["Note"] = $gcco->attributes;
                }
                try {

                    if ($save &&  $this->GCCD_Id!=193 && ($gcco->GCCO_SaldoF != 0 ||  $gcco->GCCO_SaldoAnt != 0 || $gcco->GCCO_VentaBrutaP != 0 || $gcco->GCCO_PremioP != 0 || $gcco->GCCO_ComisionP != 0))
                        if (!$gcco->save()) {
                            Yii::app()->crugemailer->enviarNotificacion(
                                $to=array('oclean66@gmail.com'),
                                $subject="Agencia: " . $gcco->GCCA_Id,
                                $data=array(
                                    "titulo"=>"Error Generando cierre",
                                    "detalles"=>"fail 2819",
                                    "info"=>  CHtml::errorSummary($gcco)
                                )
                            );                           
                        }else{
                            if($gcco->GCCO_Participacion != 0){
                                // $control = Gccs::model()->find('GCCS_Control  =:control',array(':control'=>"GCCO:".$gcco->GCCO_Id));
                                
                                    $creditos = new Gccs;
                                    $creditos->GCCS_Fecha = date("Y-m-d H:i",strtotime($gcco->GCCO_Fecha.'+4 hours'));
                                    $creditos->GCCS_Monto = -1 * $gcco->GCCO_Participacion;
                                    $creditos->GCCS_Descripcion = 'Creditos automaticos de participacion, creado el '.date("d-m-Y h:iA", strtotime('-4 hours')).' VEN';
                                    $creditos->GCCD_Id = $gcco->GCCD_Id;
                                    $creditos->GCCA_Id = $gcco->GCCA_Id;
                                    $creditos->GCUA_Id = 20;
                                    $creditos->GCUI_Id = 4;
                                    $creditos->GCCS_Usuario = 2;
                                    $creditos->GCCS_Control = "GCCO:".$gcco->GCCO_Id;
                                    $creditos->GCCS_Status = 1;
                                    $creditos->GCCS_FechaRef = date("Y-m-d");
                                    $creditos->GCCS_Currency = $gcco->GCCO_Currency;
                                    // $creditos->save();
                                
                            }
                        }
                } catch (Exception $exc) {
                   
                    Yii::app()->crugemailer->enviarNotificacion(
                        $to=array('oclean66@gmail.com'),
                        $subject="Catch Agencia: " . $gcco->GCCA_Id,
                        $data=array(
                            "titulo"=>"Error Generando cierre",
                            "detalles"=>$exc->getTraceAsString(),
                            "info"=>  $gcco->attributes
                        )
                    );

                }
                // }
            }
        }

        //Debo cargar los creditos por participacion a las agencias y grupos...


        ksort($array, SORT_STRING);
        return $array;
    }
    public function getCuadre($inicio = null, $fin = null)
    {

        if (isset($inicio) && isset($fin)) {
            $inicio = date("Y-m-d", strtotime($inicio));
            $fin = date("Y-m-d", strtotime($fin));
        } else {
            $inicio = date("Y-m-d", strtotime('-4 hours'));
            $fin = date("Y-m-d", strtotime('-4 hours'));
        }

        $inicio = date("Y-m-d H:i", strtotime($inicio . ' +4 hours'));
        // $fin = date("Y-m-d H:i", strtotime($fin.' +28 hours'));
        $fin = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i", strtotime($fin . ' +28 hours')) . '-1 second'));

        //Recargas y retiros agroupados por bancos y efectivo, 

        $currencies = Pcdi::model()->findAll();

        foreach ($currencies as $value) {
            // $array[]=$value->attributes;
            $array[$value->PCDI_Cod] = array(
                "Ingresos" => 0,
                "Egresos" => 0,
                "Comisiones" => 0,
                "Total" => 0,
                "Usuarios" => array(),
                "Productos" => array(),
                "Bancos" => array(),
                "Cuentas" => array(),
                "Dias" => array()
            );
            $array[1] = array(
                "Ingresos" => 0,
                "Egresos" => 0,
                "Comisiones" => 0,
                "Total" => 0,
                "Usuarios" => array(),
                "Productos" => array(),
                "Bancos" => array(),
                "Cuentas" => array(),
                "Dias" => array()
            );
        }
        $tipo = Gcui::model()->findAll();
        foreach ($tipo as $value) {
            $cate = Gcua::model()->findAll();
            foreach ($cate as $cval) {
                $operaciones = Gccs::model()->findAll(
                    'GCCS_Usuario in ("A-' . $this->GCCA_Id . '") and 
                        GCUA_Id=:gcua and
                        GCCS_Status = 1 and 
                        GCCS_Fecha between :inicio and :fin and 
                        GCUI_Id=:gcui order by GCCS_Id',
                    array(
                        ':inicio' => $inicio,
                        ':fin' => $fin,
                        ':gcui' => $value->GCUI_Id,
                        ':gcua' => $cval->GCUA_Id
                    )
                );
                foreach ($operaciones as $oper) {
                    // if($oper->gccd->GCCU_Id==2){
                    $array[$oper->GCCS_Currency]["Ingresos"] +=  $oper->GCCS_Monto;
                    $array[$oper->GCCS_Currency]["Total"] +=  1;
                    // $array[$oper->GCCS_Currency]['Usuarios'][]=array(
                    //     'Nombre'=>"Eimy",
                    //     "Total"=>32,
                    //     "Ingresos"=>15000,
                    //     "Egresos"=>80000
                    // );

                    //por cuenta
                    if (isset($array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id . "-" . (isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")])) {

                        if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id . "-" . (isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")]['Ingresos'] += $oper->GCCS_Monto;
                        if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id . "-" . (isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")]['Egresos'] += $oper->GCCS_Monto;

                        $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id . "-" . (isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")]['Total'] += 1;
                        // $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id."-".(isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")]['Detalles'][]=$oper->attributes;

                    } else {

                        $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id . "-" . (isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")] = array(
                            'Nombre' => $oper->gccd->GCCD_Nombre . " - " . (isset($oper->GCCA_Id) ? $oper->gcca->GCCA_Nombre : ""),
                            'gcca_id' => $oper->GCCA_Id,
                            'gccd_id' => $oper->GCCD_Id,
                            'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                            'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                            'Total' => 1,
                            'Detalles' => array()
                        );
                        // $array[$oper->GCCS_Currency]['Cuentas'][$oper->GCCD_Id."-".(isset($oper->GCCA_Id) ? $oper->GCCA_Id : "NA")]['Detalles'][]=$oper->attributes;
                    }

                    //por Dias
                    if (isset($array[$oper->GCCS_Currency]['Dias'][date('Y-m-d', strtotime($oper->GCCS_Fecha . ' -4 hours'))])) {

                        if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Dias'][date('Y-m-d', strtotime($oper->GCCS_Fecha . ' -4 hours'))]['Ingresos'] += $oper->GCCS_Monto;
                        if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Dias'][date('Y-m-d', strtotime($oper->GCCS_Fecha . ' -4 hours'))]['Egresos'] += $oper->GCCS_Monto;
                        $array[$oper->GCCS_Currency]['Dias'][date('Y-m-d', strtotime($oper->GCCS_Fecha . ' -4 hours'))]['Total'] += 1;
                    } else {

                        $array[$oper->GCCS_Currency]['Dias'][date('Y-m-d', strtotime($oper->GCCS_Fecha . ' -4 hours'))] = array(
                            'Fecha' => date('d/m', strtotime($oper->GCCS_Fecha . ' -4 hours')),

                            'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                            'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                            'Total' => 1,

                        );
                    }


                    //por tipos
                    if (isset($array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id])) {

                        $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]['Ingresos'] += $oper->GCCS_Monto;
                        $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]['Total'] += 1;

                        if (isset($array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id])) {

                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id]['Ingresos'] += $oper->GCCS_Monto;
                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id]['Total'] += 1;

                            if (isset($oper->GCUT_IdDestino) && $oper->GCUT_IdDestino != "") {

                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino] = array(
                                        'Nombre' => $oper->gcutIdDestino->gCUD->GCUD_Nombre . " " . $oper->gcutIdDestino->GCUT_Numero,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else if (isset($oper->GCUT_IdOrigen) && $oper->GCUT_IdOrigen != "") {

                                if (isset($array[$oper->GCCS_Currency]['Bancos']['ORIGEN'])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN'] = array(
                                        'Nombre' => "Sin Origen",
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id] = array(
                                        'Nombre' => $value->GCUI_Nombre,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            }
                        } else {
                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id] = array(
                                'Nombre' => $cval->GCUA_Nombre,
                                "Id" => $cval->GCUA_Id,
                                'Ingresos' => $oper->GCCS_Monto,
                                'Egresos' => 0,
                                'Total' => 1,
                                'Destinos' => array(),
                                'Origen' => array()
                            );
                            if (isset($oper->GCUT_IdDestino)) {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino] = array(
                                        'Nombre' => $oper->gcutIdDestino->gCUD->GCUD_Nombre . " " . $oper->gcutIdDestino->GCUT_Numero,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else if (isset($oper->GCUT_IdOrigen)) {

                                if (isset($array[$oper->GCCS_Currency]['Bancos']['ORIGEN'])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN'] = array(
                                        'Nombre' => "Sin Origen",
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id] = array(
                                        'Nombre' => $value->GCUI_Nombre,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            }
                        }
                    } else {
                        $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id] = array(
                            'Nombre' => $value->GCUI_Nombre,
                            "Ingresos" => $oper->GCCS_Monto,
                            "Egresos" => 0,
                            "Comisiones" => 0,
                            "Total" => 1,
                            'Operaciones' => array()
                        );


                        if (isset($array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id])) {

                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id]['Ingresos'] += $oper->GCCS_Monto;
                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id]['Total'] += 1;

                            if (isset($oper->GCUT_IdDestino)) {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino] = array(
                                        'Nombre' => $oper->gcutIdDestino->gCUD->GCUD_Nombre . " " . $oper->gcutIdDestino->GCUT_Numero,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else if (isset($oper->GCUT_IdOrigen)) {

                                if (isset($array[$oper->GCCS_Currency]['Bancos']['ORIGEN'])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN'] = array(
                                        'Nombre' => "Sin Origen",
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id] = array(
                                        'Nombre' => $value->GCUI_Nombre,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            }
                        } else {
                            $array[$oper->GCCS_Currency]["Productos"][$value->GCUI_Id]["Operaciones"][$cval->GCUA_Id] = array(
                                'Nombre' => $cval->GCUA_Nombre,
                                "Id" => $cval->GCUA_Id,
                                'Ingresos' => $oper->GCCS_Monto,
                                "Egresos" => 0,
                                'Total' => 1,
                                'Destinos' => array(),
                                'Origen' => array()
                            );
                            if (isset($oper->GCUT_IdDestino)) {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino] = array(
                                        'Nombre' => $oper->gcutIdDestino->gCUD->GCUD_Nombre . " " . $oper->gcutIdDestino->GCUT_Numero,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$oper->GCUT_IdDestino]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else if (isset($oper->GCUT_IdOrigen)) {

                                if (isset($array[$oper->GCCS_Currency]['Bancos']['ORIGEN'])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN'] = array(
                                        'Nombre' => "Sin Origen",
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos']['ORIGEN']['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            } else {
                                if (isset($array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id])) {
                                    if ($oper->GCCS_Monto > 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Ingresos'] += $oper->GCCS_Monto;
                                    if ($oper->GCCS_Monto < 0) $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Egresos'] += $oper->GCCS_Monto;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Total'] += 1;
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                } else {

                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id] = array(
                                        'Nombre' => $value->GCUI_Nombre,
                                        'Ingresos' => $oper->GCCS_Monto > 0 ? $oper->GCCS_Monto : 0,
                                        'Egresos' => $oper->GCCS_Monto < 0 ? $oper->GCCS_Monto : 0,
                                        'Total' => 1,
                                        'Detalles' => array()
                                    );
                                    $array[$oper->GCCS_Currency]['Bancos'][$value->GCUI_Id]['Detalles'][$oper->GCCS_Id] = array(
                                        'GCCS_Id' => $oper->GCCS_Id,
                                        'GCCS_Control' => $oper->GCCS_Control,
                                        'GCCS_Monto' => $oper->GCCS_Monto,
                                        'GCCS_Fecha' => $oper->GCCS_Fecha,
                                        'GCCA_Id' => $oper->gcua->GCUA_Nombre . "<br/>" . $oper->cuenta,
                                        'GCCS_Usuario' => $oper->fullUsername,
                                    );
                                }
                            }
                        }
                    }

                    // }
                }
            }
        }
        return $array;
    }


    public function addToBalance($amount, $fuente, $tipo, $description, $currency, $status = 1, $control, $fechaRef, $destino = null)
    {
        $op = new Gccs;
        $op->GCCS_Fecha = date("Y-m-d H:i");
        $op->GCCS_Monto = $fuente == 1 ? -1 * $amount : $amount;
        $op->GCCS_Descripcion = $description;
        $op->GCCD_Id = $this->GCCD_Id;
        $op->GCCA_Id = $this->GCCA_Id;
        $op->GCUA_Id = $fuente;
        $op->GCUI_Id = $tipo;
        $op->GCCS_Usuario = 2;
        $op->GCCS_Control = $control;
        $op->GCCS_FechaRef = $fechaRef;
        $op->GCCS_Status = $status;
        $op->GCCS_Currency = $currency;
        if ($fuente == 0) $op->GCUT_IdDestino = $destino;
        if ($fuente == 1) $op->GCUT_IdOrigen = $destino;

        // Yii::app()->crugemailer->enviarNotificacion(
        //     $to=array('soporte.kingdeportes@gmail.com'),
        //     $subject="getCustomerToken",
        //     $data=array(
        //         "titulo"=>"Nueva getCustomerToken",
        //         "detalles"=>"getCustomerToken",
        //         "info"=>  $op->attributes
        //     )
        // );


        if ($op->save()) {
            return $op->attributes;
        } else {
            $erros = $op->getErrors();

            return $erros;
        }
        // $op->
    }

    //Gcue - Gcuo Version 4
    public function setControles()
    {
        //  echo "nice";

        Gcue::model()->deleteAll('GCCA_Id =:gcca and GCCD_Id =:gccd', array(':gcca' => $this->GCCA_Id, ':gccd' => $this->GCCD_Id));
        Gcuo::model()->deleteAll('GCCA_Id =:gcca and GCCD_Id =:gccd', array(':gcca' => $this->GCCA_Id, ':gccd' => $this->GCCD_Id));

        Yii::app()->db->createCommand(
            "INSERT INTO gcue 
            select null,GCUE_Index,GCUE_Min,GCUE_Max,GCUE_Value," . $this->GCCA_Id . "," . $this->GCCD_Id .
                "  from gcue where GCCA_Id is null and GCCD_Id=" . $this->GCCD_Id
        )->execute();



        Yii::app()->db->createCommand(
            "INSERT INTO gcuo 
        select null,GCUO_N,GCUO_Multiplicador,GCUO_Premio," . $this->GCCA_Id . "," . $this->GCCD_Id .
                "  from gcuo where GCCA_Id is null and GCCD_Id=" . $this->GCCD_Id
        )->execute();
    }

    public function getVersion()
    {
        $version = Pcdd::model()->find('GCCA_Id=' . $this->GCCA_Id . ' and GCCD_Id=' . $this->GCCD_Id);
        $icon = '';
        $label = !isset($version->PCDD_Version) ? "No registrado" : $version->PCDD_Version;
        if ($label != Yii::app()->params['cliente']['version']) {
            $icon = CHtml::link('<i class="fa fa-cloud-upload "></i> ' . $label, '#update' . $this->GCCA_Id, array(
                'class' => "btn btn-small btn-success",
                'id' => 'update' . $this->GCCA_Id,
                'rel' => "tooltip", 'title' => "", 'data-original-title' => "Actualizar a " . Yii::app()->params['cliente']['version'],
                'name' => 'update',
                'onClick' => CHtml::ajax(
                    array(
                        'type' => 'GET',
                        'url' => array("gcca/upgrate", 'gcca_id' => $this->GCCA_Id, 'gccd_id' => $this->GCCD_Id),
                        'success' => "function( data ){
                
                $('#update" . $this->GCCA_Id . "').parent().html(data);
                $('#update" . $this->GCCA_Id . "').removeClass('btn btn-small btn-success');
            }"
                    )
                )
            ));
        } else {
            $icon = $version->PCDD_Version;
        }

        return $icon;
    }


    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('GCCD_Id,GCCA_Tv,PCDI_Id, GCCA_Cod, GCCA_Nombre, GCCA_Step', 'required'),
            // array('GCCA_Cod','unique', 'message' => "Este codigo ya existe"),
            // array('GCCA_Email','unique', 'message' => "Este email ya existe"),
            array('GCCD_Id, GCCA_status,PCDI_Id, GCCA_Step', 'numerical', 'integerOnly' => true),
            array('GCCA_Cod, GCCA_Nombre, GCCA_RIF,GCCA_Tv, GCCA_Type, GCCA_Email,GCCA_Fullname, GCCA_Phone', 'length', 'max' => 45),
            array('GCCA_Address', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('GCCA_Id, GCCA_Cod, GCCA_Step, GCCA_Email,GCCA_Nombre, GCCD_Id, GCCA_Address, GCCA_status, GCCA_RIF, GCCA_Fullname, GCCA_Phone, PCDI_Id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'gccd' => array(self::BELONGS_TO, 'Gccd', 'GCCD_Id'),
            'gcces' => array(self::HAS_MANY, 'Gcce', 'GCCA_Id'),
            'gccis' => array(self::HAS_MANY, 'Gcci', 'GCCA_Id'),
            'gccns' => array(self::HAS_MANY, 'Gccn', 'GCCA_Id'),
            'gccns1' => array(self::HAS_MANY, 'Gccn', 'GCCD_Id'),
            'gccs' => array(self::HAS_MANY, 'Gccs', 'GCCA_Id'),
            'gcuts' => array(self::HAS_MANY, 'Gcut', 'GCCD_Id'),
            'gcuts1' => array(self::HAS_MANY, 'Gcut', 'GCCA_Id'),
            'pcdi' => array(self::BELONGS_TO, 'Pcdi', 'PCDI_Id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'GCCA_Id' => 'ID',
            'GCCA_Cod' => "Codigo",
            'GCCA_Nombre' => 'Nombre',
            'GCCA_Date' => 'Registrado',
            'GCCA_Email' => 'Email',
            'GCCA_Fullname' => 'Nombre Completo',
            'GCCA_Country' => 'Pais',
            'GCCA_Type' => 'Tipo de Cuenta',
            'GCCA_Tv' => 'Tipo de Venta',
            'GCCD_Id' => 'Grupo',
            'GCCA_Address' => 'Direccion',
            'GCCA_status' => 'Estado',
            'GCCA_RIF' => 'Rif',
            'GCCA_Fullname' => 'Responsable',
            'GCCA_Phone' => 'Telefono',
            'PCDI_Id' => 'Moneda Activa',
            'GCCA_Promo' => 'Promocion',
            'GCCA_Step'=>'Verificacion'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->with = array('gccns');
       

        $criteria->compare('GCCN_Login', $this->GCCA_Nombre, true);
        $criteria->compare('GCCA_Nombre', $this->GCCA_Nombre, true);
        $criteria->compare('t.GCCA_Id', $this->GCCA_Id, true);
        $criteria->compare('GCCA_Cod', $this->GCCA_Cod, true);
        $criteria->compare('t.GCCD_Id', $this->GCCD_Id);
        $criteria->compare('GCCA_Address', $this->GCCA_Address, true);
        $criteria->compare('GCCA_status', $this->GCCA_status);
        $criteria->compare('GCCA_Email', $this->GCCA_Email, true);
        $criteria->compare('GCCA_RIF', $this->GCCA_RIF, true);
        $criteria->compare('GCCA_Fullname', $this->GCCA_Fullname, true);
        $criteria->compare('GCCA_Phone', $this->GCCA_Phone, true);
        $criteria->compare('PCDI_Id', $this->PCDI_Id);
        $criteria->compare('GCCA_Type', $this->GCCA_Type);
        $criteria->compare('GCCA_Tv', $this->GCCA_Tv);
        // $criteria->compare('GCCA_updated', $this->updated);
        if (!Yii::app()->user->isSuperAdmin)
            $criteria->addInCondition('t.GCCD_Id', Gccd::model()->arrayHijos(Yii::app()->user->grupo));

        // if($this->GCCA_status=='')
        // $criteria->addCondition('GCCA_status != 3');

        $criteria->addSearchCondition('GCCA_Cod', $this->GCCA_Nombre, true, 'OR');
        $criteria->addSearchCondition('t.GCCA_Id', $this->GCCA_Nombre, true, 'OR');
        if ($this->PCDI_Id == 1)
            $criteria->addCondition('PCDI_Id is null', 'OR');

            $criteria->together = true;

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 100),
            'sort' => array(
                'defaultOrder' => 'GCCA_Date desc',
            ),
        ));
    }

   
}
