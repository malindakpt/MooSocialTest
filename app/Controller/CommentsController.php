<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class CommentsController extends AppController 
{
		
        
	public function browse( $type = null, $target_id = null )
	{

		$target_id = intval($target_id);
		$uid = $this->Auth->user('id');
		$page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
		$id_content = (!empty($this->request->named['id_content'])) ? $this->request->named['id_content'] : '';

		$item = MooCore::getInstance()->getItemByType($type,$target_id);
        $model = key($item);
		$this->_checkExistence( $item );
		
		$admins = array();

		// topic author cannot delete comments
		if ( $type != 'Blog_Blog' && $type != APP_PAGE)
			$admins[] = $item[$model]['user_id'];
		
		// if it belongs to a group then the group admins can delete
		if ( !empty( $item[$model]['group_id'] ) )
		{
			$this->loadModel('Group.GroupUser');				
			if ( $item['Group']['type'] == PRIVACY_PRIVATE )
			{
				$cuser = $this->_getUser();		
				$is_member = $this->GroupUser->isMember( $cuser['id'], $item[$model]['group_id'] );
				
				if ( !$cuser['Role']['is_admin'] && !$is_member )
					return;
			}
			
			$group_admins = $this->GroupUser->getUsersList( $item[$model]['group_id'], GROUP_USER_ADMIN );
			$admins = array_merge( $admins, $group_admins );
		}

        $data = array();

        $data['admins'] = $admins;

		$comments = $this->Comment->getComments( $target_id, $type, $page );
		$data['comments'] = $comments;
		// get comment likes
		if ( !empty( $uid ) )
		{
			$this->loadModel( 'Like' );			
										
			$comment_likes = $this->Like->getCommentLikes( $comments, $uid );
            $data['comment_likes'] = $comment_likes;
		}
        $comment_count = $this->Comment->getCommentsCount( $target_id, $type );

        $data['bIsCommentloadMore'] = $comment_count - $page * RESULTS_LIMIT;
		if ($id_content)
        {
        	$this->set('blockCommentId',$id_content);        	
        }
        $data['more_comments'] = '/comments/browse/' . h($type) . '/' . intval($target_id) . '/page:' . ($page + 1);
        $this->set('data',$data);
		if ($type == 'conversation')
		{
			$this->set('comment_type',$type);
		}
                
                if (Configure::read('core.comment_sort_style') == COMMENT_CHRONOLOGICAL){
                    $this->render('/Elements/comments_chrono');
                }else{
                    $this->render('/Elements/comments');
                }
        

	}

	public function ajax_share()
	{
		$this->_checkPermission( array( 'confirm' => true ) );	
		$uid = $this->Auth->user('id');

		$this->request->data['user_id'] = $uid;

		if ($this->Comment->save($this->request->data))
		{
			$comment = $this->Comment->read();
			$this->set('comment', $comment);			
			
			switch ($this->request->data['type'])
			{
				case APP_CONVERSATION:
					$this->loadModel('Conversation');
					$this->loadModel('ConversationUser');
					
					// update unread var for participants, update modified field, message count field for convo, add noti and send email
					$this->Conversation->id = $this->request->data['target_id'];
					$conversation = $this->Conversation->read();

					$this->Conversation->save( array( 'lastposter_id' => $uid,
													  //'modified' => date("Y-m-d H:i:s"), 
													  'message_count' => $conversation['Conversation']['message_count'] + 1
											)	 );
											
					$participants = $this->ConversationUser->find( 'list', array( 'conditions' => array( 'conversation_id' => $this->request->data['target_id'],
																									     'ConversationUser.user_id <> '.$uid
																									),
																				  'fields' => array( 'ConversationUser.user_id' ),
																				  'group' => 'ConversationUser.user_id'
																)	);

					foreach ($participants as $key => $value)
					{
						$this->ConversationUser->id = $key;
						$this->ConversationUser->save( array( 'unread' => 1 ) );						
					}										
					
				break;
												
				default:			
					list($plugin, $model) = mooPluginSplit($this->request->data['type']);
					if ($plugin){
                                            $this->loadModel( $plugin.'.'.$model );
                                        }
					else{
                                            $this->loadModel( $model );
                                        }
					
                                        $cakeEvent = new CakeEvent('Controller.Comment.afterComment', $this, array('data' => $this->request->data));
                                        $this->getEventManager()->dispatch($cakeEvent);
					
                                        // MOOSOCIAL-2893
                                        $this->loadModel('Activity');
                                        $activity = $this->Activity->find('first', array('conditions' => array(
                                            'Activity.item_type' => $this->request->data['type'],
                                            'Activity.item_id' => $this->request->data['target_id']
                                        )));
                                        
                                        if (!empty($activity)){
                                            $this->Activity->id = $activity['Activity']['id'];
                                            $this->Activity->save( array( 'modified' => date('Y-m-d H:i:s') ) );
                                        }
                                        
					if ( $this->request->data['type'] != APP_PAGE ){
                                            $data = $this->request->data;
                                            $data['comment'] = $comment;
                                            
                                            $this->_sendNotifications( $data );
                                            
                                        }
			}
		}

		if ( !empty( $this->request->data['activity'] ) )
			$this->set('activity', true);
	}

	private function _sendNotifications($data) {
            $uid = $this->Auth->user('id');
			$this->loadModel("User");
            $cuser = $this->_getUser();
            $this->loadModel("UserBlock");            

            $this->loadModel('Notification');

           	list($plugin, $model) = mooPluginSplit($data['type']);
			if ($plugin)
				$this->loadModel( $plugin.'.'.$model );
			else
				$this->loadModel( $model );

            $obj = $this->$model->findById($data['target_id']);
            $block_users = $this->UserBlock->getBlockedUsers($obj['User']['id']);

            // group topic / video
            if (!empty($obj[$model]['group_id']))
                $url = '/groups/view/' . $obj[$model]['group_id'] . '/' . strtolower($model) . '_id:' . $data['target_id'];
            else
            {
                $url = $obj[key($obj)]['moo_url'];
            }

            // send notifications to anyone who commented on this item within a day
            $users = $this->Comment->find('list', array('conditions' => array_merge($this->Comment->addBlockCondition(), array('Comment.target_id' => $data['target_id'],
                    'Comment.type' => $data['type'],
                    'Comment.user_id <> ' . $uid . ' AND Comment.user_id <> ' . $obj['User']['id'],
                    'DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= Comment.created'
                )),
                'fields' => array('Comment.user_id'),
                'group' => 'Comment.user_id'
                    ));

            if ($data['type'] == 'Photo_Photo') {
                $action = 'photo_comment';
                $params = serialize(array('actor' => $cuser, 'owner' => $obj['User']));
                $url .= '#content';
            } else {
                $action = 'item_comment';
                $params = h($obj[$model]['moo_title']);
            }

            if (!empty($users)) {
				foreach ($users as $user_id) {
					if (!in_array($user_id, $block_users))
					{
						if ($this->User->checkSettingNotification($user_id,'comment_of_comment')) {
							$this->Notification->record(array('recipients' => $user_id,
								'sender_id' => $uid,
								'action' => $action,
								'url' => $url,
								'params' => $params
							));
						}
					}
				}
            }

            
            $content = strip_tags($data['message']);

            // insert into activity feed
            $this->loadModel('Activity');

            if ($data['type'] == 'Photo_Photo') { // update item comment activity
                // check privacy of album and group of this photo, if it's not for everyone then do not show it at all
                $update_activity = false;
                switch ($obj['Photo']['type']) {
                    case 'Group_Group':
                        $this->loadModel('Group.Group');
                        $group = $this->Group->findById($obj['Photo']['target_id']);

                        if ($group['Group']['type'] != PRIVACY_PRIVATE)
                            $update_activity = true;

                        break;

                    case 'Photo_Album':
                        $this->loadModel('Photo.Album');
                        $album = $this->Album->findById($obj['Photo']['target_id']);

                        if (isset($album['Album']['privacy']) && $album['Album']['privacy'] == PRIVACY_EVERYONE)
                            $update_activity = true;

                        break;
                }


                if ($update_activity) {
                    $activity = $this->Activity->find('first', array(
                        'conditions' => array(
                            'Activity.item_type' => $data['type'],
                            'Activity.item_id' => $data['target_id'],
                            'Activity.params' => 'no-comments',
                            'Activity.type' => 'user'
                        )));
					$comment = $data['comment'];
                    if (!empty($activity)) { // update the latest one
                        $this->Activity->id = $activity['Activity']['id'];
                        $this->Activity->save(array('user_id' => $uid,
                            'content' => $content,
                        	'items' => $comment['Comment']['id'],
                        ));
                    } else // insert new      
                        $this->Activity->save(array('type' => 'user',
                            'action' => 'comment_add_'.strtolower($model),
                            'user_id' => $uid,
                            'content' => $content,
                            'item_type' => $data['type'],
                            'item_id' => $data['target_id'],
                            'query' => 1,
                            'params' => 'no-comments',
                        	'plugin' => $plugin,
                        	'items' => $comment['Comment']['id'],
                        ));
                }
            }
            else { // update item activity
                $activity = $this->Activity->getItemActivity($data['type'], $data['target_id']);

                if (!empty($activity)) {
                    $this->Activity->id = $activity['Activity']['id'];
                    $this->Activity->save(array('modified' => date("Y-m-d H:i:s")));
                }
            }
            

			if ($this->User->checkSettingNotification($obj['User']['id'],'comment_item')) {
				$notificationStopModel = MooCore::getInstance()->getModel('NotificationStop');
				if (!$notificationStopModel->isNotificationStop($data['target_id'], $data['type'], $obj['User']['id'])) {
					// send notification to author
					if ($uid != $obj['User']['id']) {
						if ($data['type'] == APP_PHOTO)
							$action = 'own_photo_comment';

						$this->Notification->record(array('recipients' => $obj['User']['id'],
							'sender_id' => $uid,
							'action' => $action,
							'url' => $url,
							'params' => $params
						));
					}
				}
			}
			
			$ids = array($obj['User']['id']);
			
			/*if (key($obj) == 'Photo')
			{
				if ($obj['Photo']['album_type'])
	        	{
	        		$target = MooCore::getInstance()->getItemByType($obj['Photo']['album_type'],$obj['Photo']['album_type_id']);        				
	        		if ($target)
	        		{
	        			$ids[] = $target['User']['id'];
	        		}
	        	}
	        	
				if (strtolower($obj['Photo']['type']) != 'photo_album' && $obj['Photo']['target_id'])
	        	{
	        		$target = MooCore::getInstance()->getItemByType($obj['Photo']['type'],$obj['Photo']['target_id']);
	        		if ($target)
	        		{
	        			$ids[] = $target['User']['id'];
	        		}
	        	}
			}*/

            //notification for user mention
			$this->_sendNotificationToMentionUser($data['message'],$url,'mention_user_comment',array(),$ids);

    }

    public function ajax_remove()
	{
		$this->autoRender = false;		
		$this->_checkPermission( array( 'confirm' => true ) );
		
		$comment = $this->Comment->findById( $this->request->data['id'] );
		$this->_checkExistence( $comment );		
		$item = MooCore::getInstance()->getItemByType($comment['Comment']['type'],$comment['Comment']['target_id']);
		$this->_checkExistence( $item );
		$model = key($item);	
		$this->$model = MooCore::getInstance()->getModel($comment['Comment']['type']);
		
		$admins = array( $comment['Comment']['user_id'] );
		
		$admins[] = $item[$model]['user_id'];
		
		// if it belongs to a group then the group admins can delete
		if ( !empty( $item[$model]['group_id'] ) )
		{
			$this->loadModel('Group.GroupUser');
			
			$group_admins = $this->GroupUser->getUsersList( $item[$model]['group_id'], GROUP_USER_ADMIN );
			$admins = array_merge( $admins, $group_admins );
		}
		
		$this->_checkPermission( array( 'admins' => $admins ) );
		$this->Comment->delete( $this->request->data['id'] );
		
		// descrease comment count
		if ( method_exists($this->$model,'decreaseCounter'))
			$this->$model->updateCounter( $comment['Comment']['target_id'] );
		
		//after delete comment
        $this->getEventManager()->dispatch(new CakeEvent('Controller.Comment.afterDelete', $this));
		
		// delete activity
		$this->loadModel('Activity');
		$this->Activity->deleteAll( array( 'action' => 'comment_add', 'Activity.item_type' => $comment['Comment']['type'], 'Activity.item_id' => $comment['Comment']['target_id'] ), true, true );
		
		$activity = $this->Activity->find('first',array(
			'conditions' => array('Activity.item_type' => $comment['Comment']['type'],'Activity.item_id' => $comment['Comment']['target_id'])
		));
		
		if ($activity && count($activity))
		{
			$comment_last = $this->Comment->find('first',array(
				'conditions' => array('Comment.type' => $comment['Comment']['type'],'Comment.target_id' => $comment['Comment']['target_id']),
				'order' => array('Comment.id DESC'),
			));
			
			$this->Activity->id = $activity['Activity']['id'];
			if (count($comment_last))
			{
				$this->Activity->save( array( 'modified' => $comment_last['Comment']['created'] ) );
			}
			else
			{
				$this->Activity->save( array( 'modified' => $activity['Activity']['created'] ) );
			}
		}
	}
	
	public function ajax_loadCommentEdit($id)
	{
            $this->loadModel('Comment');
            $comment = $this->Comment->findById($id);
            $this->_checkExistence( $comment );

            $item = MooCore::getInstance()->getItemByType($comment['Comment']['type'],$comment['Comment']['target_id']);
            $this->_checkExistence( $item );
            $model = key($item);	
            $this->$model = MooCore::getInstance()->getModel($comment['Comment']['type']);

            $admins = array( $comment['Comment']['user_id'] );

            $admins[] = isset($item[$model]['user_id']) ? $item[$model]['user_id'] : 0;

            $this->_checkPermission( array( 'admins' => $admins ) );
            $this->set('isPhotoComment', $this->request->data['isPhotoComment']);
            $this->set('comment', $comment);
	}
	
	public function ajax_editComment($id)
	{
		$this->loadModel('Comment');
		$comment = $this->Comment->findById($id);
		$this->_checkExistence( $comment );
                
		$item = MooCore::getInstance()->getItemByType($comment['Comment']['type'],$comment['Comment']['target_id']);
		$this->_checkExistence( $item );
		$model = key($item);
		$this->$model = MooCore::getInstance()->getModel($comment['Comment']['type']);

		$admins = array( $comment['Comment']['user_id'] );

		if (isset($item[$model]['user_id']))
			$admins[] = $item[$model]['user_id'];
		
		$this->_checkPermission( array( 'admins' => $admins ) );

		$previous_users = $this->_getUserIdInMention($comment['Comment']['message']);
		$previous_users = is_array($previous_users)?$previous_users: array();
		$new_users = $this->_getUserIdInMention($this->request->data['message']);
		$new_users = is_array($new_users)? $new_users: array();
		$new_add_users = array_diff($new_users,$previous_users);

		$this->loadModel('CommentHistory');
		
		$this->Comment->id = $comment['Comment']['id'];
		
		$uid = $this->Auth->user('id');
		
		$photo = 0;
		if (trim($this->request->data['comment_attach']) == '')
		{
			if (trim($comment['Comment']['thumbnail']) != '')
			{
				$photo = 3; //Remove
			}
		}
		else
		{
			if (trim($comment['Comment']['thumbnail']) == '')
			{
				$photo = 1; // Add new
			}
			elseif (trim($comment['Comment']['thumbnail']) != $this->request->data['comment_attach'])
			{
				$photo = 2; //Replace
			}
		}
		
		if ($photo)
		{
			$this->Comment->save(array('edited'=>true,'modified'=>false,'thumbnail'=>$this->request->data['comment_attach'],'message'=>$this->request->data['message']));
		}
		else 
		{
			$this->Comment->save(array('edited'=>true,'modified'=>false,'message'=>$this->request->data['message']));
		}

        if(!empty($new_add_users)){
            list($plugin, $model) = mooPluginSplit($comment['Comment']['type']);
            if ($plugin)
                $this->loadModel( $plugin.'.'.$model );
            else
                $this->loadModel( $model );

            $obj = $this->$model->findById($comment['Comment']['target_id']);

            // group topic / video
            if (!empty($obj[$model]['group_id']))
                $url = '/groups/view/' . $obj[$model]['group_id'] . '/' . strtolower($model) . '_id:' . $comment['Comment']['target_id'];
            else
            {
                $url = $obj[key($obj)]['moo_url'];
            }
            
            $ids = array($obj['User']['id']);
        	/*if (key($obj) == 'Photo')
			{
				if ($obj['Photo']['album_type'])
	        	{
	        		$target = MooCore::getInstance()->getItemByType($obj['Photo']['album_type'],$obj['Photo']['album_type_id']);        				
	        		if ($target)
	        		{
	        			$ids[] = $target['User']['id'];
	        		}
	        	}
			}
			
        	if (strtolower($obj['Photo']['type']) != 'photo_album' && $obj['Photo']['target_id'])
        	{
        		$target = MooCore::getInstance()->getItemByType($obj['Photo']['type'],$obj['Photo']['target_id']);
        		if ($target)
        		{
        			$ids[] = $target['User']['id'];
        		}
        	}*/
            
            $this->_sendNotificationToMentionUser($this->request->data['message'],$url,'mention_user_comment',$new_add_users,$ids);
        }
        
		if (!$comment['Comment']['edited'])
		{			
			$this->CommentHistory->save(array(
				'user_id' => $comment['Comment']['user_id'],
				'type' => 'Comment',
				'content' =>  $comment['Comment']['message'],
				'target_id' => $comment['Comment']['id'],
				'created' => $comment['Comment']['created'],
				'photo' => $comment['Comment']['thumbnail'] != '' ? 1 : 0, 
			));
		}

		$this->CommentHistory->clear();
		$this->CommentHistory->save(array(
			'user_id' => $uid,
			'type' => 'Comment',
			'target_id' => $comment['Comment']['id'],
			'content' => $this->request->data['message'],
			'photo' => $photo
		));		
		
		$comment = $this->Comment->read();	
		if ($uid != $comment['Comment']['user_id'])
		{
			$this->set('other_user',$this->Auth->user());
		}
		$this->set('comment', $comment);
		
		$this->loadModel('Activity');
		$activity = $this->Activity->find('first', array(
			'conditions' => array(
				'Activity.action' => 'comment_add_photo',
				'Activity.items' => $id,
				'Activity.params' => 'no-comments',
				'Activity.type' => 'user'
		)));
                        
		if (!empty($activity)) { // update the latest one
			$this->Activity->id = $activity['Activity']['id'];
			$this->Activity->save(array(
				'content' => $this->request->data['message']
			));
		}
	}

}
