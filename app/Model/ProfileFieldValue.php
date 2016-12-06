<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class ProfileFieldValue extends AppModel 
{	
	public $belongsTo = array( 'ProfileField' );
	
	public function getValues( $uid, $profile_fields_only = false, $show_heading = false )
	{
		$cond = array( 'ProfileField.active' => 1 );
        
        if ( $profile_fields_only )
            $cond['ProfileField.profile'] = 1;
        
        if ( $show_heading )
            $cond['OR'] = array( 'ProfileFieldValue.user_id' => $uid, 'ProfileField.type' => 'heading' );
        else
            $cond['ProfileFieldValue.user_id'] = $uid;

		$vals = $this->find( 'all', array( 'conditions' => $cond, 'order' => 'ProfileField.weight' ) );
							
		return $vals;
	}
}
