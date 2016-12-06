<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class ProfileField extends AppModel {
	    
    public $order = 'ProfileField.weight asc';

	public $hasMany = array( 'ProfileFieldValue' => array( 
												'className' => 'ProfileFieldValue',						
												'dependent'=> true
											)
							); 

	public $validate = array(	
							'name' => 	array( 	 
								'rule' => 'notBlank',
								'message' => 'Name is required'
							),
							'type' => 	array( 	 
								'rule' => 'notBlank',
								'message' => 'Type is required'
							)
	);
	
	// get custom fields for registration page
	public function getRegistrationFields( $exclude_heading = false )
	{
		$cond = array( 'ProfileField.active' => 1, 
		               'ProfileField.registration' => 1
                     );
                     
        if ( $exclude_heading )
            $cond['ProfileField.type <> ?'] = 'heading';
            
		$custom_fields = $this->find( 'all', array( 'conditions' => $cond ) );
									
		return 	$custom_fields;
	}

}