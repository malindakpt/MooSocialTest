<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class SubscriptionTransaction extends SubscriptionAppModel 
{
    public $belongsTo = array('User',
    	'Gateway' => array(
    		'className'=> 'PaymentGateway.Gateway',            
        	),
        'Subscribe' => array(
    		'className'=> 'Subscription.Subscribe', 
			'foreignKey' => 'subscribes_id'			
        	),
        'SubscriptionPackagePlan' => array(
        	'className'=> 'Subscription.SubscriptionPackagePlan',
			'foreignKey' => 'plan_id'
        	)
    	);
}
