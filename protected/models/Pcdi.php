<?php

/**
 * This is the model class for table "pcdi".
 *
 * The followings are the available columns in table 'pcdi':
 * @property integer $PCDI_Id
 * @property string $PCDI_Nombre
 * @property string $PCDI_Cod
 */
class Pcdi extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Pcdi the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function getConcatened()
	{
		return $this->PCDI_Cod." - ".$this->PCDI_Nombre;
	}
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pcdi';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			
			array('PCDI_Id', 'numerical', 'integerOnly'=>true),
			array('PCDI_Nombre, PCDI_Cod', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('PCDI_Id, PCDI_Nombre,PCDI_Cod', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'PCDI_Id' => 'Id',
			'PCDI_Nombre' => 'Nombre de la moneda',
			'PCDI_Cod'=>"Codigo Corto"
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

		$criteria=new CDbCriteria;

		$criteria->compare('PCDI_Id',$this->PCDI_Id);
		$criteria->compare('PCDI_Nombre',$this->PCDI_Nombre,true);
		$criteria->compare('PCDI_Cod',$this->PCDI_Cod,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}