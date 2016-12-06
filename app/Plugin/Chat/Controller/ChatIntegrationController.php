<?php

class ChatIntegrationController extends ChatAppController
{
    public function beforeFilter(){
        parent::beforeFilter();
        $this->loadModel('Chat.ChatRoom');
        $this->loadModel('Chat.ChatMessage');
        $this->loadModel('Chat.ChatStatusMessages');
        $this->loadModel('Chat.ChatRoomsMember');
        $this->loadModel('User');
    }
    private function _getLatestMessageId($uId,$page=0,$limit=RESULTS_LIMIT){
        $roomIdS = $this->ChatRoomsMember->find("all",array(
            'fields' => array('ChatRoomsMember.room_id'),
            'conditions'=>array("ChatRoomsMember.user_id"=>$uId),
        ));

        $messageIdS = $this->ChatRoom->find("all",array(
            'fields' => array('ChatRoom.latest_mesasge_id'),
            'conditions'=>array("ChatRoom.id"=>Hash::extract($roomIdS,'{n}.ChatRoomsMember.room_id')),
            'page'=>$page,
            'order' => array('ChatRoom.latest_mesasge_id DESC'),
            'limit' => $limit,
        ));
        return Hash::extract($messageIdS,'{n}.ChatRoom.latest_mesasge_id');
    }
    public function show() {
        $this->_checkPermission();

        $condRoomIds = array();
        $viewerId = $this->Auth->user('id');

        /*
        $messageIdS = $this->ChatStatusMessages->find("all",array(
            'fields' => array('MAX(ChatStatusMessages.message_id) AS message_id'),
            'conditions'=>array("ChatStatusMessages.user_id"=>$viewerId),
            'group'=>'ChatStatusMessages.room_id',
            'limit' => 50,
        ));
        $condMessageIds = Hash::extract($messageIdS,'{n}.{n}.message_id');
        */
        $condMessageIds = $this->_getLatestMessageId($viewerId,0,50);
        $messageStatusS = $this->ChatStatusMessages->find("all",array(
            'fields' => array(' ChatStatusMessages.message_id,ChatStatusMessages.unseen'),
            'conditions'=>array("ChatStatusMessages.message_id"=>$condMessageIds,"ChatStatusMessages.user_id"=>$viewerId),
        ));
        $messageStatusS = Hash::combine($messageStatusS,'{n}.ChatStatusMessages.message_id','{n}.ChatStatusMessages.unseen');

        $messages = $this->ChatMessage->find("all",array(
            'conditions'=>array("ChatMessage.id"=>$condMessageIds),
            'order'=> array('ChatMessage.id DESC'),
        ));
        $condRoomIds = Hash::extract($messages,'{n}.ChatMessage.room_id');
        $rooms = $this->ChatRoom->find("all",array(
            'conditions'=>array("ChatRoom.id"=>$condRoomIds)
        ));

        $users = $this->User->find('all', array(
            'conditions' => array('User.id' => Hash::extract($rooms,'{n}.ChatRoomsMember.{n}.user_id'))
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        //var_dump($messages,$rooms);die();
        $this->set("messages",$messages);
        $this->set("status",$messageStatusS);
        $this->set("rooms",Hash::combine($rooms,'{n}.ChatRoom.id','{n}'));
        $this->set("users",$users);
        $this->set('viewerId',$viewerId);
    }
    public function ajax_browse() {
        $this->_checkPermission();


        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;






        $this->loadModel('Chat.ChatRoom');
        $this->loadModel('Chat.ChatMessage');
        $this->loadModel('Chat.ChatStatusMessages');
        $this->loadModel('User');
        $condRoomIds = array();
        $viewerId = $this->Auth->user('id');

        /*
        $messageIdS = $this->ChatStatusMessages->find("all",array(
            'fields' => array('MAX(ChatStatusMessages.message_id) AS message_id'),
            'conditions'=>array("ChatStatusMessages.user_id"=>$viewerId),
            'group'=>'ChatStatusMessages.room_id',
            'page' => $page,
            'limit' => RESULTS_LIMIT,
        ));
        $condMessageIds = Hash::extract($messageIdS,'{n}.{n}.message_id');
        */
        $condMessageIds = $this->_getLatestMessageId($viewerId,$page);
        $messageStatusS = $this->ChatStatusMessages->find("all",array(
            'fields' => array(' ChatStatusMessages.message_id,ChatStatusMessages.unseen'),
            'conditions'=>array("ChatStatusMessages.message_id"=>$condMessageIds,"ChatStatusMessages.user_id"=>$viewerId),
        ));
        $messageStatusS = Hash::combine($messageStatusS,'{n}.ChatStatusMessages.message_id','{n}.ChatStatusMessages.unseen');

        $messages = $this->ChatMessage->find("all",array(
            'conditions'=>array("ChatMessage.id"=>$condMessageIds),
            'order'=> array('ChatMessage.id DESC'),
        ));
        $condRoomIds = Hash::extract($messages,'{n}.ChatMessage.room_id');
        $rooms = $this->ChatRoom->find("all",array(
            'conditions'=>array("ChatRoom.id"=>$condRoomIds)
        ));

        $users = $this->User->find('all', array(
            'conditions' => array('User.id' => Hash::extract($rooms,'{n}.ChatRoomsMember.{n}.user_id'))
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        //var_dump($rooms);die();
        $this->set("messages",$messages);
        $this->set("status",$messageStatusS);
        $this->set("rooms",Hash::combine($rooms,'{n}.ChatRoom.id','{n}'));
        $this->set("users",$users);
        $this->set('viewerId',$viewerId);
        $this->set('more_url', '/conversations/ajax_browse/page:' . ( $page + 1 ) ) ;

        if ( $page > 1 )
            $this->render('Chat.Elements/messages_list');
    }
    public function mark_all_read(){
        $viewerId = $this->Auth->user('id');
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."chat_status_messages SET unseen = 0 WHERE user_id =$viewerId");
        $chatCounter = $this->ChatMessage->query("SELECT COUNT(*) AS count FROM (SELECT room_id FROM ".$this->ChatMessage->tablePrefix."chat_status_messages WHERE user_id = $viewerId and unseen=1 group by room_id) AS A");
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."users SET chat_count = ".$chatCounter[0][0]["count"]." WHERE id=$viewerId");
        $this->redirect($this->referer());
    }
    public function mark_read(){
        $this->autoRender = false;
        $id = isset($this->request->data['id']) ? $this->request->data['id'] : 0;
        $status = isset($this->request->data['status']) ? $this->request->data['status'] : 0;
        $viewerId = $this->Auth->user('id');
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."chat_status_messages SET unseen = $status WHERE message_id =$id and user_id=$viewerId");
        $chatCounter = $this->ChatMessage->query("SELECT COUNT(*) AS count FROM (SELECT room_id FROM ".$this->ChatMessage->tablePrefix."chat_status_messages WHERE user_id = $viewerId and unseen=1 group by room_id) AS A");
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."users SET chat_count = ".$chatCounter[0][0]["count"]." WHERE id=$viewerId");
        echo json_encode(array('success' => true, 'status' => $status));
    }
    public function ajax_send($recipient = null)
    {
        $this->_checkPermission( array( 'confirm' => true ) );
        $uid = $this->Auth->user('id');

        if ( !empty($recipient) )
        {
            $this->_checkPermission( array('user_block' => $recipient) );
            $this->loadModel( 'User' );
            $this->loadModel('Friend');

            $to = $this->User->findById($recipient);
            $this->_checkExistence( $to );
            $allow_send_message_to_non_friend = Configure::read('core.send_message_to_non_friend');
            if ($allow_send_message_to_non_friend) {
                if(empty($to['User']['receive_message_from_non_friend'])){
                    $areFriend = $this->Friend->areFriends($uid, $to['User']['id']);
                    if(!$areFriend)
                        $this->set('notAllow', 1);
                }
            }
            else {
                $areFriend = $this->Friend->areFriends($uid, $to['User']['id']);
                if(!$areFriend)
                    $this->set('notAllow', 1);
            }
            $this->set('to', $to);
        }
    }

}