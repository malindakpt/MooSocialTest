<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class SubscriptionPackagePlan extends SubscriptionAppModel
{
    public $belongsTo = array('SubscriptionPackage');
    public $validate = array(
        'price' => array(
            'rule' => array('decimal', 2)
        ),
        'plan_duration' => array(
            'rule' => array('comparison', '>', 0),
            'message'  => 'Duration must be a valid integer greater than 0'
        ),
        'expiration_reminder' => array(
            'rule' => array('comparison', '>', 0),
            'message' => 'Expiration reminder must be a valid integer greater than 0'
        ),
        'trial_price' => array(
            'rule' => array('decimal', 2)
        ),
        'order' => array(
            'rule' => array('comparison', '>', 0),
            'message'  => 'Plan\'s order must be a valid integer greater than 0'
        ),
    );
}
