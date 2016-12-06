<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class ReportsController extends AppController 
{
	public function ajax_create( $type = null, $target_id = null )
	{
		$target_id = intval($target_id);
		$this->_checkPermission();
		$this->set( 'type', $type );
		$this->set( 'target_id', $target_id );
	}
		
	public function ajax_save()
	{
		$this->_checkPermission();
		$uid = $this->Auth->user('id');
		
		if ( !empty( $this->request->data ) )
		{
			$this->autoRender = false;
			$uid = $this->Auth->user('id');
			
			$this->request->data['user_id'] = $uid;
			$this->Report->set( $this->request->data );
			$this->_validateData( $this->Report );
			
			$count = $this->Report->find( 'count', array( 'conditions' => array( 'type' => $this->request->data['type'],
																				 'target_id' => $this->request->data['target_id'],
																				 'user_id' => $uid )
										 ) 	);
			if ( $count > 0 )
			{
				$response['result'] = 0;
                $response['message'] = __('Duplicated report');
                echo json_encode($response);
				return;
			}
			
			$item = MooCore::getInstance()->getItemByType($this->request->data['type'],$this->request->data['target_id']);
			
			if ( $this->Report->save() ) // successfully saved	
			{
				$this->loadModel('AdminNotification');	
				if(!empty($uid))
                {
				$this->AdminNotification->save( array( 'user_id' => $uid,
													   'message' => $this->request->data['reason'],
													   'text' => __('reported a %s', key($item)),
													   'url' => $item[key($item)]['moo_href'],
											) );
                }
                $response['result'] = 1;
                $response['message'] = __('Thank you! Your report has been submitted');
                echo json_encode($response);
			}
		}
	}
}

