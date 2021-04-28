<?php

/**
 * This is the model class for table "gccs".
 *
 * The followings are the available columns in table 'gccs':
 * @property integer $GCCS_Id
 * @property string $GCCS_Fecha
 * @property float $GCCS_Monto
 * @property string $GCCS_Descripcion
 * @property integer $GCCD_Id
 * @property integer $GCCA_Id
 * @property integer $GCUA_Id
 * @property integer $GCUI_Id
 * @property string $GCCS_Usuario
 * @property integer $GCUT_IdOrigen
 * @property integer $GCUT_IdDestino
 * @property string $GCCS_FechaRef
 * @property string $GCCS_Control
 * @property string $GCCS_Status
 *
 * The followings are the available model relations:
 * @property Gcca $gCCA
 * @property Gccd $gCCD
 * @property Gcua $gCUA
 * @property Gcui $gCUI
 * @property Gcut $gCUTIdOrigen
 * @property Gcut $gCUTIdDestino
 */
class Gccs extends CActiveRecord {

    public $Pagos, $Envios, $Ajustes;
    public $gcca_search;
    public $user;
    public $excludeType;
    public $range;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Gccs the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function getRellenarGcca()
    {
        $respuesta=array();
        $cuentas = Gcca::model()->findAll(' GCCD_Id in (' . implode(",", Gccd::model()->arrayHijos(Yii::app()->user->grupo)) . ') order by GCCA_Nombre');
        foreach ($cuentas as $value) {
            // if($value->activated)
            $respuesta[$value->GCCA_Id]=$value->concatened;
        }
        // $cuentas = CHtml::listData(, 'GCCA_Id', 'concatened');
        return $respuesta;

    }


    public function total($records){
        $total=0;

        foreach($records as $record)
        if($record->GCCS_Status!=4)
            $total+=$record->GCCS_Monto;

        return $total;
    }
    public  function getFullUsername()
    {
        if(strpos($this->GCCS_Usuario,"A-") !== false ){
            $operador =  Gcca::model()->find("GCCA_Id=:id",array(":id"=>str_replace("A-","",$this->GCCS_Usuario)));
            $nameOperador = "<i class='fa fa-user'></i> (".$this->GCCS_Usuario.") ".$operador->concatened;
            // $nameOperador = CHtml::link($nameOperador,array('/gcca/view', 'gccd_id' => $operador->GCCD_Id,  'gcca_id' => $operador->GCCA_Id), array('target'=>'_blank'));
            
        }else{
            $operador = Yii::app()->user->um->loadUserById($this->GCCS_Usuario,true);
            $nombre = Yii::app()->user->um->getFieldValue($this->GCCS_Usuario,"nombre");
            $nameOperador = "<div rel='tooltip' data-placement='right' style='display:inline' data-original-title='".$nombre."'><i class='fa fa-user'></i>  (".$this->GCCS_Usuario.") ".$operador->username."</div>";
            // $nameOperador = CHtml::link($nameOperador,array('/gccs/detalle', 'user' => $this->GCCS_Usuario), array('target'=>'_blank'));

        }
       return  $nameOperador;
    }

    public function getCuenta(){
        if(isset($this->GCCA_Id)){
            $cuenta = CHtml::link($this->gcca->concatened,array('/gcca/view','gcca_id' => $this->GCCA_Id, 'gccd_id' => $this->GCCD_Id),array('target'=>'_blank'));
        }else{
            $cuenta = CHtml::link($this->gccd->concatened,array('/gccd/view', 'id' => $this->GCCD_Id),array('target'=>'_blank'));
        }
        return $cuenta;
    }

    


    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'gccs';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('GCCS_Fecha, GCCS_Monto, GCCD_Id, GCUA_Id, GCUI_Id, GCCS_Usuario,GCCS_Status', 'required'),
            array('GCCD_Id, GCCA_Id, GCUA_Id, GCUI_Id, GCUT_IdOrigen, GCCS_Status, GCUT_IdDestino', 'numerical', 'integerOnly' => true),
            array('GCCS_Monto', 'numerical'),
            array('GCCS_Usuario, GCCS_Control,GCCS_Currency', 'length', 'max' => 45),
            array('GCCS_Descripcion, GCCS_FechaRef', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('GCCS_Id, GCCS_Fecha, gcca_search, GCCS_Monto, GCCS_Descripcion, GCCD_Id, GCCS_Status, GCCA_Id, GCUA_Id, GCUI_Id, GCCS_Usuario, GCUT_IdOrigen, GCUT_IdDestino, GCCS_FechaRef, GCCS_Control', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'gcca' => array(self::BELONGS_TO, 'Gcca', 'GCCA_Id'),
            'gccd' => array(self::BELONGS_TO, 'Gccd', 'GCCD_Id'),
            'gcua' => array(self::BELONGS_TO, 'Gcua', 'GCUA_Id'),
            'gcui' => array(self::BELONGS_TO, 'Gcui', 'GCUI_Id'),
            'gcutIdOrigen' => array(self::BELONGS_TO, 'Gcut', 'GCUT_IdOrigen'),
            'gcutIdDestino' => array(self::BELONGS_TO, 'Gcut', 'GCUT_IdDestino'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'GCCS_Id' => 'ID',
            'GCCS_Fecha' => 'Fecha',
            'GCCS_Monto' => 'Monto',
            'GCCS_Currency'=>'Moneda',
            'GCCS_Descripcion' => 'Descripcion',
            'GCCD_Id' => 'Grupo',
            'GCCA_Id' => 'Cuenta Web',
            'GCUA_Id' => 'Tipo',
            'GCUI_Id' => 'Forma de pago',
            'GCCS_Usuario' => 'Usuario',
            'GCUT_IdOrigen' => 'Cuenta Bancaria Origen',
            'GCUT_IdDestino' => 'Cuenta Bancaria',
            'GCCS_FechaRef' => 'Fecha Referencia',
            'GCCS_Control' => 'Referencia',
            'GCCS_Status'=>"Estado"
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        $criteria->with = array('gcca','gccd');
        // $criteria->with = array('gccd');
        // $criteria->with = array('gcca');

        $criteria->compare('GCCS_Id', $this->GCCS_Id);
        $criteria->compare('GCCS_Fecha', $this->GCCS_Fecha, true);
        $criteria->compare('GCCS_Monto', $this->GCCS_Monto);
        $criteria->compare('GCCS_Descripcion', $this->GCCS_Descripcion, true);
        // $criteria->compare('gccd.GCCD_Id', $this->GCCD_Id);
        $criteria->compare('gcca.GCCD_Id', $this->GCCD_Id);
        $criteria->compare('gcca.GCCA_Id', $this->GCCA_Id);
        // $criteria->compare('GCCA_Id', $this->GCCA_Id);
        $criteria->compare('GCUA_Id', $this->GCUA_Id);
        $criteria->compare('GCUI_Id', $this->GCUI_Id);
        $criteria->compare('GCCS_Usuario', $this->GCCS_Usuario);
        $criteria->compare('GCCS_Currency', $this->GCCS_Currency);
        $criteria->compare('GCUT_IdOrigen', $this->GCUT_IdOrigen);
        $criteria->compare('GCUT_IdDestino', $this->GCUT_IdDestino);
        $criteria->compare('GCCS_FechaRef', $this->GCCS_FechaRef, true);
        $criteria->compare('GCCS_Control', $this->GCCS_Control, true);
        $criteria->compare('GCCS_Status', $this->GCCS_Status);
        $criteria->compare('gcca.GCCA_Nombre', $this->gcca_search, true);

        // if(isset($this->excludeType) ){
            // $criteria->addNotInCondition('GCUA_Id',$this->excludeType);

        // }

         if(!Yii::app()->user->isSuperAdmin)
            $criteria->addInCondition('gccd.GCCD_Id',Gccd::model()->arrayHijos( Yii::app()->user->grupo) );
         
        
        if (isset($_GET['date1']) && isset($_GET['date2']) && !isset($this->range) && $this->GCCS_Control=="") {
            $criteria->addBetweenCondition('GCCS_Fecha', date("Y-m-d H:i", strtotime($_GET['date1'].'+4 hours')), date("Y-m-d H:i:s", strtotime( date('Y-m-d H:i', strtotime($_GET['date2'] . ' + 28 hours')).'-1 second' ) ) );
        }else
        if (isset($_GET['fecha1']) && isset($_GET['fecha2']) && !isset($this->range) && $this->GCCS_Control=="") {
            $criteria->addBetweenCondition('GCCS_Fecha', date("Y-m-d H:i", strtotime($_GET['fecha1'].'+4 hours')), date('Y-m-d H:i:s', strtotime( date('Y-m-d H:i',strtotime($_GET['fecha2'] . ' + 28 hours')).'-1 second' )));
        }
        if(isset($_GET['gcca_id']) && $_GET['gcca_id']!='grupo'){
            $criteria->addCondition('gcca.GCCA_Id ='.$_GET['gcca_id']);
            $criteria->addSearchCondition('GCCS_Usuario','A-'.$_GET['gcca_id'], true, 'or');
        }
        if(isset($_GET['gccd_id'])){
            $criteria->addCondition('t.GCCD_Id ='.$_GET['gccd_id']);
        }
        if(isset($_GET['gcca_id']) && $_GET['gcca_id']=='grupo'){
            $criteria->addCondition('gcca.GCCA_Id is null');
        }
        // GCCS_Status desc,
        $sort = array(
            'defaultOrder' => 'GCCS_Id desc',
            'attributes'=>array(
                'gcca_search'=>array(
                    'asc'=>'gcca.GCCA_Nombre',
                    'desc'=>'gcca.GCCA_Nombre DESC',
                ),
                '*',
            ),
        );


        return new CActiveDataProvider($this, array(
            'criteria' => $criteria, 
            'sort' => $sort,
             'pagination'=>
             array(
                 'pageSize'=>500
                 )
        ));
    }
  

}
