<?php

/**
 * This is the model class for table "cruge_session".
 *
 * The followings are the available columns in table 'cruge_session':
 * @property integer $idsession
 * @property integer $iduser
 * @property string $created
 * @property string $expire
 * @property integer $status
 * @property string $ipaddress
 * @property integer $usagecount
 * @property string $lastusage
 * @property string $logoutdate
 * @property string $ipaddressout
 */
class MyCrugeSession extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cruge_session';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('iduser', 'required'),
			array('status, usagecount', 'numerical', 'integerOnly'=>true),
			array('iduser, created, expire, lastusage, logoutdate', 'length', 'max'=>30),
			array('ipaddress, ipaddressout', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('idsession, iduser, created, expire, status, ipaddress, usagecount, lastusage, logoutdate, ipaddressout', 'safe', 'on'=>'search'),
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
			'idsession' => 'Idsession',
			'iduser' => 'Iduser',
			'created' => 'Created',
			'expire' => 'Expire',
			'status' => 'Status',
			'ipaddress' => 'Ipaddress',
			'usagecount' => 'Usagecount',
			'lastusage' => 'Lastusage',
			'logoutdate' => 'Logoutdate',
			'ipaddressout' => 'Ipaddressout',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('idsession',$this->idsession);
		$criteria->compare('iduser',$this->iduser);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('expire',$this->expire,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('ipaddress',$this->ipaddress,true);
		$criteria->compare('usagecount',$this->usagecount);
		$criteria->compare('lastusage',$this->lastusage,true);
		$criteria->compare('logoutdate',$this->logoutdate,true);
		$criteria->compare('ipaddressout',$this->ipaddressout,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return MyCrugeSession the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
