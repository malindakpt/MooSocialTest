<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class SubscriptionPackage extends SubscriptionAppModel
{
    public $validate = array(   
        'name' =>   array(   
            'notEmpty' => array(
                'rule'     => 'notBlank',
                'message'  => 'Name is required'
            ),
        ), 
        'role_id' => array(
            'rule' => array('validateRole', 'role_id'),
            'message' => 'Select user role'
        ),
        'ordering' => array(
            'rule' => array('comparison', '>', 0),
            'message'  => 'Order must be a valid integer greater than 0'
        ),
    );
    public $belongsTo = 'Role';
    public $hasMany = array("SubscriptionPackagePlan"=> array(
        'conditions' => array('SubscriptionPackagePlan.deleted <> ' => 1),
        'dependent' => true,
    	'order' => 'SubscriptionPackagePlan.order ASC,SubscriptionPackagePlan.id ASC',
    ));//PackagePricingPlan
    public $recursive = 2;
    function validateRole($id)
    {
        return $this->Role->hasAny(array('id' => $id));
    }
    
    public function generateOrdering()
    {
        $result = $this->find('first', array(
            'fields' => array('ordering'),
            'order' => 'ordering DESC'
        ));
        if($result != null)
        {
            return (int)$result['SubscriptionPackage']['ordering'] + 1;
        }
        return 1;
    }
}