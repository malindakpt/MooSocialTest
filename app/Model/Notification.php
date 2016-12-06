<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class Notification extends AppModel {	

	public $belongsTo = array( 'User'  => array( 'counterCache' => true,
												 'counterScope' => array( 'Notification.read' => 0 )		
							) );
	
	public $validate = array( 'user_id' => array( 'rule' => 'notBlank'),
							  'sender_id' => array( 'rule' => 'notBlank'),
							  'action' => array( 'rule' => 'notBlank' ),
							  'url' => array( 'rule' => 'notBlank' )
						 );
							  
	public $order = 'Notification.id desc';
	
	public $limit = RESULTS_LIMIT;
	
	/*
	 * Record a notification
	 * @params Array $params
	 */
	public function record($params = array()) {
            if (empty($params['recipients'])){
                return;
            }

            if (empty($params['params'])){
                $params['params'] = '';
            }

            $data = array();
            if (!is_array($params['recipients'])){
                $params['recipients'] = array($params['recipients']);
            }

            foreach ($params['recipients'] as $recipient_id) { // save notification
                $unread = $this->getUnreadNotification($recipient_id, $params['sender_id'], $params['url'], $params['action']);

                if (!$unread){
                    $data[] = array('user_id' => $recipient_id,
                        'sender_id' => $params['sender_id'],
                        'action' => $params['action'],
                        'url' => $params['url'],
                        'params' => $params['params'],
                        'plugin' => isset($params['plugin']) ? $params['plugin'] : '' 
                    );
                }
            }

            if (!empty($data)){
                $this->saveAll($data);
            }
        }

        public function getRecentNotifications()
	{
		$this->bindModel(
			array('belongsTo' => array(
					'Sender' => array(
						'className' => 'User',
						'foreignKey' => 'sender_id'
					)
				)
			)
		);
		
		$notifications = $this->find( 'all', array( 'conditions' => array( 'User.notification_email' => 1,
																		   'Notification.read' => 0,
																  		   'DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= Notification.created'
									) ) );
		
		return $notifications;
	}

    public function getUnreadNotification( $uid, $sender_id , $url, $action)
    {
        $noti = $this->find( 'count', array( 'conditions' => array( 'Notification.user_id' => $uid, 
                                                                    'Notification.sender_id' => $sender_id,
                                                                    'Notification.url' => $url,
                                                                    'Notification.action' => $action,
                                                                    'Notification.read' => 0
                            ) ) );
        return $noti;
    }

}
