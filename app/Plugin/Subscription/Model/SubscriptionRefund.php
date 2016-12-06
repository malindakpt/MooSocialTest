<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class SubscriptionRefund extends SubscriptionAppModel 
{
    public $belongsTo = array('User',
    	'Subscribe' => array(
    		'className'=> 'Subscription.Subscribe', 
			'foreignKey' => 'subscribe_id'			
        	),
        'SubscriptionTransaction' => array(
        	'className'=> 'Subscription.SubscriptionTransaction',
			'foreignKey' => 'transaction_id'
        	),
        'SubscriptionPackagePlan' => array(
        	'className'=> 'Subscription.SubscriptionPackagePlan',
			'foreignKey' => 'plan_id'
        	)
    	);
    	
    	public $order = 'SubscriptionRefund.id desc';
    
}
