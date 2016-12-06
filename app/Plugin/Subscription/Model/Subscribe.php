<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('SubscriptionAppModel','Subscription.Model');
class Subscribe extends SubscriptionAppModel 
{
	public $mooFields = array('plugin','type');
	
    public $validate = array(   
        'name' =>   array(   
            'notEmpty' => array(
                'rule'     => 'notBlank',
                'message'  => 'Name is required'
            ),
        ),     
        'role_id' => array(
            'rule' => array('comparison', '>', 0),
            'message'  => 'Please select user role'
        ),
        'price' => array(
            'rule' => array('comparison', '>=', 0),
            'message'  => 'Price is not valid'
        ),
        'recurring_price' => array(
            'rule' => array('comparison', '>=', 0),
            'message'  => 'Recurring price is not valid'
        ),
        'recurring' => array(
            'rule' => array('comparison', '>', 0),
            'message'  => 'Recurring must be a valid interger greater than 0'
        )
    );
    public $belongsTo = array(
        'SubscriptionPackage' => array(
            'foreignKey' => 'package_id'
        ),
        'Gateway' => array(
        	'className'=> 'PaymentGateway.Gateway',
            'foreignKey' => 'gateway_id'
        ),
        'SubscriptionTransaction' => array(
            'foreignKey' => 'transaction_id'
        ),
        'SubscriptionPackagePlan' => array(
            'foreignKey' => 'plan_id'
        ),'User');


	public function isIdExist($id)
    {
        return $this->hasAny(array('id' => (int)$id));
    }
    
    public function isBelongToPackage($package_id)
    {
        return $this->hasAny(array('package_id' => (int)$package_id));
    }
    public function isBelongToPlan($plan_id)
    {
        return $this->hasAny(array('plan_id' => (int)$plan_id));
    }
    
	public function afterSave($created, $options = array())
    {
    	Cache::clearGroup('subscription');
    	parent::afterSave($created, $options);
    }
}
