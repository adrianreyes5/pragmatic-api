<?php

/**
 * This is the model class for table "gcce".
 *
 * The followings are the available columns in table 'gcce':
 * @property integer $GCCE_Id
 * @property integer $GCCA_Id
 * @property integer $GCCD_Id
 * @property integer $GCCE_PorcentajeVentasD
 * @property integer $GCCE_PorcentajeVentasP
 * @property string $GCCE_ControlEsquema
 * @property integer $GCCE_PorcentajeUtilidad
 * @property integer $GCCE_PorcentajePerdida
 * @property string $GCCE_Fecha
 * @property integer $GCCE_ApMinDirecta
 * @property integer $GCCE_ApMinParley
 * @property integer $GCCE_ApMaxDirecta
 * @property integer $GCCE_ApMaxParley
 * @property integer $GCCE_PreMaxDirecta
 * @property integer $GCCE_PreMaxParley
 * @property integer $GCCE_MaxMulti
 * @property integer $GCCE_MaxJugada
 * @property integer $GCCE_MinJugada
 * @property integer $GCCE_RepetidosMonto
 * @property integer $GCCE_CupoDeuda
 * @property string $GCCE_Repetidos
 * @property string $GCCE_Label
 * @property string $GCCE_Currency

 *
 * The followings are the available model relations:
 * @property Gcca $gCCA
 * @property Gccd $gCCD
 * @property Gcco[] $gccos
 * @property GccoAngeles[] $gccoAngeles
 */
class Gcce extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Gcce the static model class
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
		return 'gcce';
	}

	public function getEsquema()
	{
		$esquema = explode("-", $this->GCCE_ControlEsquema);
		$respuesta = "";

		if ($esquema[0] . "-" . $esquema[1] . "-" . $esquema[2] == "0-0-0") {
			$respuesta = "Agrupador ";
		}
		if ($esquema[0] == 1) {
			$respuesta .= "%Ventas  ";
		}
		if ($esquema[1] == 1) {
			$respuesta .= "%Utilidad  ";
		}
		if ($esquema[1] == 2) {
			$respuesta .= "%Utilidad o Gastos ";
		}
		if ($esquema[1] == 3) {
			$respuesta .= "%Utilidad de Cuenta ";
		}
		
		if ($esquema[2] == 1) {
			$respuesta .= "%Perdidas  ";
		}
		if ($esquema[0] == 2) {
			$respuesta .= " o Recompensa  ";
		}

		return $respuesta;
	}
	public function getPeriodo()
	{
		$esquema = explode("-", $this->GCCE_ControlEsquema);
		$respuesta = "";

		if ($esquema[3] == 0) {
			$respuesta .= "Diario";
		}
		if ($esquema[3] == 1) {
			$respuesta .= "Semanal";
		}
		if ($esquema[3] == 2) {
			$respuesta .= "Mensual";
		}
		return $respuesta;
	}

	public function getPea($inicio, $fin)
	{
		// Busco pago y envio de la fecha
		$criteria = new CDbCriteria();
		$p = -1;
		$e = -1;

		if ($this->GCCP_Id == 11) {
			$p = 30; //credito parley-king
			$e = 31; //debito parley-king	
			if (isset($this->gCCA)) {
				if ($this->gCCA->GCCA_Tv == 'pos') {
					$p = '30,0';
					$e = '31,1';
				}
			} else {
				$p = '30,0';
				$e = '31,1';
			}
		} else if ($this->GCCP_Id == 12) {

			$p = 32; //credito recargas-king
			$e = 33; //debito recargas-king

		} else if ($this->GCCP_Id == 1) {
			$p = 0;
			$e = 1;
		}
		$criteria->select = "SUM(
		    CASE
		    WHEN (GCUA_Id in (" . $p . ") )
		    THEN GCCS_Monto
		    ELSE 0
		    END) as Pagos,
		    SUM(
		    CASE
		    WHEN (GCUA_Id in (" . $e . ") )
		    THEN GCCS_Monto
		    ELSE 0
			END) as Envios";

		if (isset($this->GCCA_Id))
			$criteria->condition = 'GCCA_Id = ' . $this->GCCA_Id . ' and GCCD_Id = ' .  $this->GCCD_Id . ' and GCCS_Status =1 and 
			GCCS_Currency = "' . $this->GCCE_Currency . '" and GCCS_Fecha  
			BETWEEN "' . $inicio . '"  AND "' . $fin . '"';
		else if ($this->gCCD->GCCU_Id == 2 )
			$criteria->condition = 'GCCD_Id = ' .  $this->GCCD_Id . ' and GCCS_Status =1 and 
			GCCS_Currency = "' . $this->GCCE_Currency . '" and GCCS_Fecha  
			BETWEEN "' . $inicio . '"  AND "' . $fin . '"';
		else
			$criteria->condition = 'GCCA_Id is null and GCCD_Id = ' .  $this->GCCD_Id . ' and GCCS_Status =1 and 
			GCCS_Currency = "' . $this->GCCE_Currency . '" and GCCS_Fecha  
			BETWEEN "' . $inicio . '"  AND "' . $fin . '"';


		return Gccs::model()->find($criteria);
	}
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array(
				'GCCA_Id, GCCD_Id,  GCCE_ApMinDirecta, GCCE_ApMinParley, 
				GCCE_ApMaxDirecta, GCCE_ApMaxParley, GCCE_PreMaxDirecta, GCCE_PreMaxParley, GCCE_MaxMulti, 
				GCCE_MaxJugada, GCCE_MinJugada, GCCE_Enabled, GCCE_RepetidosMonto, GCCE_Repetidos, GCCE_CupoDeuda',
				'numerical',
				'integerOnly' => true
			),
			array(
				'
				GCCE_Label,
				GCCE_Currency,
				GCCE_ControlEsquema, 
				GCCE_PorcentajeVentasP, 
				GCCE_Repetidos,
				GCCE_Enabled,
				GCCP_Id',
				'required'
			),

			array('GCCE_ControlEsquema', 'length', 'max' => 45),
			array('GCCE_Currency', 'length', 'max' => 10),
			array('GCCE_Repetidos, GCCE_PorcentajeVentasD, GCCE_PorcentajeVentasP, GCCE_PorcentajeUtilidad, GCCE_PorcentajePerdida', 'length', 'max' => 10),
			array('GCCE_Label', 'length', 'max' => 160),
			array('GCCE_Fecha', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array(
				'
				GCCE_Id, GCCA_Id, GCCD_Id, GCCE_PorcentajeVentasD, GCCE_PorcentajeVentasP,GCCE_Currency,
				GCCE_ControlEsquema, GCCE_PorcentajeUtilidad, GCCE_PorcentajePerdida, GCCE_Fecha, 
				GCCE_ApMinDirecta, GCCE_ApMinParley, GCCE_ApMaxDirecta, GCCE_ApMaxParley, GCCE_PreMaxDirecta, 
				GCCE_PreMaxParley, GCCE_MaxMulti, GCCE_MaxJugada, GCCE_MinJugada, GCCE_RepetidosMonto, GCCE_CupoDeuda, 
				GCCE_Repetidos, GCCE_Label, GCCE_Enabled, GCCP_Id',
				'safe',
				'on' => 'search'
			),
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
			'gCCA' => array(self::BELONGS_TO, 'Gcca', 'GCCA_Id'),
			'gCCD' => array(self::BELONGS_TO, 'Gccd', 'GCCD_Id'),
			'gccos' => array(self::HAS_MANY, 'Gcco', 'GCCE_Id'),
			'gccp' => array(self::BELONGS_TO, 'Gccp', 'GCCP_Id'),
			'gccoAngeles' => array(self::HAS_MANY, 'GccoAngeles', 'GCCE_Id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$base = array(
			'11' => array( //parley
				'GCCE_PorcentajeVentasD' => 'GCCE_PorcentajeVentasD', //sin traduccion disponible se descarta
				'GCCE_ApMinDirecta' => 'Apuesta Min Directa',
				'GCCE_ApMaxDirecta' => 'Apuesta Max Directa',
				'GCCE_PreMaxDirecta' => 'Premio Max Directa',

				'GCCE_PorcentajeVentasP' => '% Ventas',
				'GCCE_PorcentajeUtilidad' => '% Utilidad',
				'GCCE_PorcentajePerdida' => '% Perdidas',
				'GCCE_ApMinParley' => 'Apuesta Minima',
				'GCCE_ApMaxParley' => 'Apuesta Maxima',
				'GCCE_PreMaxParley' => 'Premio Maximo',
				'GCCE_MaxMulti' => 'Max Multiplo',
				'GCCE_MinJugada' => 'Jugadas Minimas',
				'GCCE_MaxJugada' => 'Jugadas Maximas',
				'GCCE_CupoDeuda' => 'Cupo Venta Diaria',

				'GCCE_RepetidosMonto' => 'Tope Tickets Repetidos',
				'GCCE_Repetidos' => 'Tickets Repetidos',

			),
			'13' => array( //sorteos

				'GCCE_PorcentajeVentasD' => 'GCCE_PorcentajeVentasD',
				'GCCE_ApMinDirecta' => 'GCCE_ApMinDirecta',
				'GCCE_MaxMulti' => 'GCCE_MaxMulti',
				'GCCE_ApMaxDirecta' => 'GCCE_ApMaxDirecta',
				'GCCE_PreMaxDirecta' => 'GCCE_PreMaxDirecta',

				'GCCE_PorcentajeVentasP' => '% Ventas',

				'GCCE_PorcentajeUtilidad' => '% Utilidad',
				'GCCE_PorcentajePerdida' => '% Perdidas',


				'GCCE_ApMinParley' => 'Apuesta Min',
				'GCCE_ApMaxParley' => 'Apuesta Max',
				'GCCE_PreMaxParley' => 'GCCE_PreMaxParley',

				'GCCE_MaxJugada' => 'Sorteos Max',
				'GCCE_MinJugada' => 'Sorteos Min',

				'GCCE_RepetidosMonto' => 'Tope',
				'GCCE_Repetidos' => 'GCCE_Repetidos',

				'GCCE_CupoDeuda' => 'Cupo Venta Diaria',

			),
			'12' => array( //recargas

				'GCCE_PorcentajeVentasD' => 'Porcentaje Retiros',
				'GCCE_PorcentajeVentasP' => 'Porcentaje Recargas',

				'GCCE_PorcentajeUtilidad' => 'GCCE_PorcentajeUtilidad',
				'GCCE_PorcentajePerdida' => 'GCCE_PorcentajePerdida',

				'GCCE_ApMinDirecta' => 'GCCE_ApMinDirecta',
				'GCCE_ApMaxDirecta' => 'GCCE_ApMaxDirecta',
				'GCCE_PreMaxDirecta' => 'GCCE_PreMaxDirecta',

				'GCCE_ApMinParley' => 'GCCE_ApMinParley',
				'GCCE_ApMaxParley' => 'GCCE_ApMaxParley',
				'GCCE_PreMaxParley' => 'GCCE_PreMaxParley',

				'GCCE_MinJugada' => 'GCCE_MinJugada',
				'GCCE_MaxJugada' => 'GCCE_MaxJugada',
				'GCCE_MaxMulti' => 'GCCE_MaxMulti',

				'GCCE_RepetidosMonto' => 'GCCE_RepetidosMonto',
				'GCCE_CupoDeuda' => 'GCCE_CupoDeuda',
				'GCCE_Repetidos' => 'GCCE_Repetidos',


			),
		);

		return array(
			'GCCE_Id' => 'ID',
			'GCCA_Id' => CrugeTranslator::t('app', 'Cuenta Web'),
			'GCCD_Id' => CrugeTranslator::t('app', 'Operador'),
			'GCCP_Id' => CrugeTranslator::t('app', 'Producto'),
			'GCCE_Fecha' => "Actualizado al",
			'GCCE_ControlEsquema' => 'Esquema',
			'GCCE_Enabled' => "Estado",
			'GCCE_PorcentajeVentasD' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PorcentajeVentasD'] : 'GCCE_PorcentajeVentasD',
			'GCCE_PorcentajeVentasP' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PorcentajeVentasP'] : 'GCCE_PorcentajeVentasP',
			'GCCE_PorcentajeUtilidad' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PorcentajeUtilidad'] : 'GCCE_PorcentajeUtilidad',
			'GCCE_PorcentajePerdida' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PorcentajePerdida'] : 'GCCE_PorcentajePerdida',
			'GCCE_ApMinDirecta' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_ApMinDirecta'] : 'GCCE_ApMinDirecta',
			'GCCE_ApMinParley' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_ApMinParley'] : 'GCCE_ApMinParley',
			'GCCE_ApMaxDirecta' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_ApMaxDirecta'] : 'GCCE_ApMaxDirecta',
			'GCCE_ApMaxParley' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_ApMaxParley'] : 'GCCE_ApMaxParley',
			'GCCE_PreMaxDirecta' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PreMaxDirecta'] : 'GCCE_PreMaxDirecta',
			'GCCE_PreMaxParley' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_PreMaxParley'] : 'GCCE_PreMaxParley',
			'GCCE_MaxMulti' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_MaxMulti'] : 'GCCE_MaxMulti',
			'GCCE_MaxJugada' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_MaxJugada'] : 'GCCE_MaxJugada',
			'GCCE_MinJugada' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_MinJugada'] : 'GCCE_MinJugada',
			'GCCE_RepetidosMonto' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_RepetidosMonto'] : 'GCCE_RepetidosMonto',
			'GCCE_CupoDeuda' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_CupoDeuda'] : 'GCCE_CupoDeuda',
			'GCCE_Repetidos' => isset($base[$this->GCCP_Id]) ? $base[$this->GCCP_Id]['GCCE_Repetidos'] : 'GCCE_Repetidos',
			'GCCE_Label' => 'Nota',
			'GCCE_Currency' => "Moneda",

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

		$criteria->compare('GCCE_Id', $this->GCCE_Id);
		$criteria->compare('GCCA_Id', $this->GCCA_Id);
		$criteria->compare('GCCD_Id', $this->GCCD_Id);
		$criteria->compare('GCCP_Id', $this->GCCP_Id);
		$criteria->compare('GCCE_PorcentajeVentasD', $this->GCCE_PorcentajeVentasD);
		$criteria->compare('GCCE_PorcentajeVentasP', $this->GCCE_PorcentajeVentasP);
		$criteria->compare('GCCE_ControlEsquema', $this->GCCE_ControlEsquema, true);
		$criteria->compare('GCCE_PorcentajeUtilidad', $this->GCCE_PorcentajeUtilidad);
		$criteria->compare('GCCE_PorcentajePerdida', $this->GCCE_PorcentajePerdida);
		$criteria->compare('GCCE_Fecha', $this->GCCE_Fecha, true);
		$criteria->compare('GCCE_ApMinDirecta', $this->GCCE_ApMinDirecta);
		$criteria->compare('GCCE_ApMinParley', $this->GCCE_ApMinParley);
		$criteria->compare('GCCE_ApMaxDirecta', $this->GCCE_ApMaxDirecta);
		$criteria->compare('GCCE_ApMaxParley', $this->GCCE_ApMaxParley);
		$criteria->compare('GCCE_PreMaxDirecta', $this->GCCE_PreMaxDirecta);
		$criteria->compare('GCCE_PreMaxParley', $this->GCCE_PreMaxParley);
		$criteria->compare('GCCE_MaxMulti', $this->GCCE_MaxMulti);
		$criteria->compare('GCCE_MaxJugada', $this->GCCE_MaxJugada);
		$criteria->compare('GCCE_MinJugada', $this->GCCE_MinJugada);
		$criteria->compare('GCCE_Enabled', $this->GCCE_Enabled);
		// $criteria->compare('GCCE_MachoMin',$this->GCCE_MachoMin);
		// $criteria->compare('GCCE_HembraMax',$this->GCCE_HembraMax);
		// $criteria->compare('GCCE_HembraMin',$this->GCCE_HembraMin);
		// $criteria->compare('GCCE_EmpateMax',$this->GCCE_EmpateMax);
		$criteria->compare('GCCE_RepetidosMonto', $this->GCCE_RepetidosMonto);
		$criteria->compare('GCCE_CupoDeuda', $this->GCCE_CupoDeuda);
		$criteria->compare('GCCE_Repetidos', $this->GCCE_Repetidos, true);
		$criteria->compare('GCCE_Label', $this->GCCE_Label, true);
		$criteria->compare('GCCE_Currency', $this->GCCE_Currency, true);

		if (!Yii::app()->user->isSuperAdmin)
			$criteria->addInCondition('GCCD_Id', Gccd::model()->arrayHijos(Yii::app()->user->getState('grupo')));

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort' => array(
				'defaultOrder' => 'GCCE_Fecha desc, GCCP_Id desc',
				'attributes' => array(
					'gcca_search' => array(
						'asc' => 'gcca.GCCA_Nombre',
						'desc' => 'gcca.GCCA_Nombre DESC',
					),
					'*',
				),
			),
			'pagination' =>
			array(
				'pageSize' => 500
			)
		));
	}



}
