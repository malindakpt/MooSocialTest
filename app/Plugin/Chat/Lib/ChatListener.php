<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('CakeEventListener', 'Event');

class ChatListener implements CakeEventListener
{

    public function implementedEvents()
    {
        return array(
            'MooView.afterLoadMooCore' => 'afterLoadMooCore',
            'MooView.beforeRenderRequreJsConfig' => 'beforeRenderRequreJsConfig',
            'Auth.afterIdentify' => 'Auth_afterIdentify',
            'AppController.doBeforeFilter' => 'AppController_doBeforeFilter',
            'MooView.beforeMooConfigJSRender' => 'mooView_beforeMooConfigJSRender',
            'Controller.User.afterLogout' => 'doControllerUserAfterLogout',
            'NotificationsController.refresh'=>'notificationRefresh',
            'AppController.doViewerProcess'=>'doViewerProcess',
            'MooPoupHelper.tag'=>'tagPopuHelperIntegration',
            'Model.Friend.afterSave'=>'afterSaveFriendModel',
            'Model.Friend.beforeDelete'=>'beforeDeleteFriendModel',
            'Model.UserBlock.afterSave'=>'afterSaveUserBlockModel',
            'Model.UserBlock.beforeDelete'=>'beforeDeleteUserBlockModel',
        );
    }

    public function afterLoadMooCore($event)
    {

        
        $v = $event->subject();
        if($this->isMobile()){ // For dev it will has ! , the correct is not !
            $css = 'Chat.chat-mobile.css';
        }else{
            $css = 'Chat.chat.css';
        }
        $v->Helpers->Html->css(array(
            $css,
        ),
            array('block' => 'css')
        );

        //$v->append('footer', '<div id="app"></div>');

    }

    public function beforeRenderRequreJsConfig($event)
    {
        $v = $event->subject();
        if (Configure::read('debug') == 0){
            $min="min.";
        }else{
            $min="";
        }
        if($this->isMobile()){ // For dev it will has ! , the correct is not !
            $chatJS = $v->request->base . "/chat/js/client/mooChat-mobile";
        }else{
            $chatJS = $v->request->base . "/chat/js/client/mooChat";
        }
        $v->Helpers->MooRequirejs->addPath(array(
            "mooChat" => $chatJS,
            "chat" => $v->Helpers->MooRequirejs->assetUrl('/chat'),
            "webChat"=>$v->Helpers->MooRequirejs->assetUrlJS("Chat.js/webChat.{$min}js")
        ));

        $v->Helpers->MooRequirejs->addShim(array(
            "mooAjax" => array("deps" => array("mooChat")),
        ));


    }

    public function Auth_afterIdentify($event)
    {
        $user = $event->data['user'];
        $sub = $event->subject();
        $chatModel = MooCore::getInstance()->getModel('Chat.ChatToken');
        $chatToken = uniqid('chat_');
        if ($sub->_Collection->getController()->isAllowedPermissions("chat_allow_chat")) {
            $sub->Session->write('chatTokens', $chatToken);
            $chatModel->create();
            $chatModel->save(array(
                'user_id' => $user['id'],
                'session_id' => $sub->Session->id(),
                'token' => $chatToken,
            ));
        }


    }

    public function AppController_doBeforeFilter($event)
    {
        $appController = $event->subject();
        $appController->Auth->authenticate["Chat.Chat"] = array();
        $appController->loadModel('Chat.ChatUsersSetting');
        $settings = $appController->ChatUsersSetting->find('first', array(
            'conditions' => array('ChatUsersSetting.user_id' => $appController->Auth->user('id'))
        ));
        if (empty($settings)) {
            $settings = array(
                'ChatUsersSetting' => array(
                    'user_id' => $appController->Auth->user('id'),
                )
            );
            $appController->ChatUsersSetting->save($settings);
            $settings = $appController->ChatUsersSetting->find('first', array(
                'conditions' => array('ChatUsersSetting.user_id' => $appController->Auth->user('id'))
            ));
        }
        $appController->set('ChatUserSettings', $settings);
        $appController->set('ChatUserPermissions', array(
            'isAllowedChat'=>$appController->isAllowedPermissions( 'chat_allow_chat'),
            'isAllowedSendPicture'=>$appController->isAllowedPermissions( array('chat_allow_chat','chat_allow_send_picture') ),
            'isAllowedSendFiles'=>$appController->isAllowedPermissions( array('chat_allow_chat','chat_allow_send_files') ),
            'isAllowedEmotion'=>$appController->isAllowedPermissions( array('chat_allow_chat','chat_allow_user_emotion') ),
            'isAllowedChatGroup'=>$appController->isAllowedPermissions( array('chat_allow_chat','chat_allow_chat_group') ),
        ));
    }

    public function mooView_beforeMooConfigJSRender($event)
    {
        $v = $event->subject();

        $chat_setting = array(
            'disable' => Configure::read('Chat.chat_disable'),
            'server_url' => Configure::read('Chat.chat_chat_server_url'),
            'hide_offline' => Configure::read('Chat.chat_hide_offline_users_in_who_online'),
            'open_chatbox_when_a_new_message_arrives' => Configure::read('Chat.chat_open_chatbox_when_a_new_message_arrives'),
            'chat_beep_on_arrival_of_all_messages' => Configure::read('Chat.chat_beep_on_arrival_of_all_messages'),
            'hidden_in_mobile' => Configure::read('Chat.chat_hidden_in_mobile'),
            'load_bar_in_offline_mode_for_all_first_time_users' => Configure::read('Chat.chat_load_bar_in_offline_mode_for_all_first_time_users'),
            'number_of_messages_fetched_when_load_earlier_messeges' => Configure::read('Chat.chat_number_of_messages_fetched_when_load_earlier_messeges'),
            'number_of_messages_fetched_window_first_time'=>Configure::read('Chat.number_of_messages_fetched_window_first_time'),
            'send_message_to_non_friend'=> Configure::read('core.send_message_to_non_friend'),
            'chat_turn_on_notification'=> Configure::read('Chat.chat_turn_on_notification'),
        );

        if (Configure::read('Chat.chat_hidden_in_mobile') == '1') {
            if ($this->isMobile()) {
                $chat_setting["disable"] = "1";
            }
        }
        if (!is_null($v->Helpers->Session->read('chatTokens'))) {
            $chat_setting['token'] = $v->Helpers->Session->read('chatTokens');
            $settings = $v->get("ChatUserSettings");
            if (isset($settings["ChatUsersSetting"])) {
                unset($settings["ChatUsersSetting"]['id'], $settings["ChatUsersSetting"]["user_id"]);
                $chat_setting['settings'] = $settings["ChatUsersSetting"];
            }
            $chat_setting['permissions'] = $v->get("ChatUserPermissions");
            // Fixing for 2.4.1
            $config = $event->data['mooConfig'];
            if(!isset($config['language'])){
                $config['language'] = Configure::read('Config.language');

            }
            if(!isset($config['language_2letter'])){
                App::uses('MooLangISOConvert','Lib');
                $config['language_2letter'] = MooLangISOConvert::getInstance()->lang_iso639_2t_to_1(Configure::read('Config.language'));

            }

            // Fixing for sharing action

            if(isset($v->params['controller']) && isset($v->params['action'])){
                if($v->params['controller'] == 'share' && $v->params['action'] == 'ajax_share'){
                    $chat_setting["disable"] = "1";
                }
            }
            $event->result['mooConfig'] = $config + array("mooChat" => $chat_setting);

        }


    }

    public function doControllerUserAfterLogout($event)
    {
        $v = $event->subject();
        $v->CakeSession->delete("chatTokens");
    }
    private function isMobile(){
        //return true;
        return MooCore::getInstance()->isMobile(null);
    }
    public function notificationRefresh($event){

        if(Configure::read('Chat.chat_turn_on_notification')==1){
            $v = $event->subject();
            $data = $event->data['data'];
            $user = $event->data['user'];
            if(isset($user['chat_count'])){
                $data['conversation_count']=$user['chat_count'];
                $event->result['data'] =$data;
            }

        }

    }
    public function doViewerProcess($event){

        if(Configure::read('Chat.chat_turn_on_notification')==1){
            $v = $event->subject();
            $user = $event->data['cuser'];
            if(isset($user['chat_count'])){
                $user['conversation_user_count']=$user['chat_count'];
                $event->result['cuser'] =$user;
            }

        }

    }
    public function tagPopuHelperIntegration($event){
        if(Configure::read('Chat.chat_turn_on_notification')==1){
            $v = $event->subject();
            $params = $event->data['params'];

            if(isset($params['title']) && $params['title'] == __('Send New Message')){


                $link = Router::url(array("controller" => "conversations",
                    "action" => "ajax_send",
                    "plugin" => false
                ),false);

                $profileId = str_replace($link."/","",$params["href"]);

                if(ctype_digit($profileId)){
                    $replace = array(
                        'id'=>$params['id'],

                        'class'=>$params['class'],
                        'title'=>$params['title'],
                        'innerHtml'=>$params['innerHtml'],
                        'style'=>$params['style'],

                    );
                    $search  = array('#id', '#class','#title','#innerHtml','#style');
                    $a = "<a id='#id' href='javascript:require([\"mooChat\"],function(chat){chat.openChatWithOneUser($profileId)});'  class='#class' title='#title' style='#style' >#innerHtml</a>";
                    $a = str_replace($search,$replace,$a);
                    $event->result['a'] =$a;

                    if(!Configure::read('core.send_message_to_non_friend')){
                        $friendModel = MooCore::getInstance()->getModel('Friend');
                        $viewer_id = MooCore::getInstance()->getViewer(true);

                        if (!$friendModel->areFriends($viewer_id, $profileId)){
                            $event->result['a'] = '';
                        }

                    }
                }

            }

        }
    }
    public function afterSaveFriendModel($event){
        $v = $event->subject();

        if(isset($v->data["Friend"]["user_id"])){
            $chatCachedModel = MooCore::getInstance()->getModel("Chat.ChatCachedQueryUserStat");
            $record = $chatCachedModel->findByUserId($v->data["Friend"]["user_id"]);

            if($record){
                $chatCachedModel->read(null,$record['ChatCachedQueryUserStat']['id']);
            }
            $chatCachedModel->save(array(
                    'user_id'=>$v->data["Friend"]["user_id"],
                    'new_friend'=>true,
                ));
        }
    }
    public function beforeDeleteFriendModel($event){
        $v = $event->subject();


            $chatCachedModel = MooCore::getInstance()->getModel("Chat.ChatCachedQueryUserStat");
            $record = $chatCachedModel->findByUserId($v->field('user_id'));

            if($record){
                $chatCachedModel->read(null,$record['ChatCachedQueryUserStat']['id']);
            }
            $chatCachedModel->save(array(
                'user_id'=>$v->field('user_id'),
                'new_friend'=>true,
            ));
    }
    public function afterSaveUserBlockModel($event){
        $v = $event->subject();

         if(isset($v->data['UserBlock']['user_id'])){
             $chatCachedModel = MooCore::getInstance()->getModel("Chat.ChatCachedQueryUserStat");
             $record = $chatCachedModel->findByUserId($v->data['UserBlock']['user_id']);

             if($record){
                 $chatCachedModel->read(null,$record['ChatCachedQueryUserStat']['id']);
             }
             $chatCachedModel->save(array(
                 'user_id'=>$v->data['UserBlock']['user_id'],
                 'new_block'=>true,
             ));
             $rId = $this->_blockUser($v->data['UserBlock']['user_id'],$v->data['UserBlock']['object_id']);

         }

    }
    public function beforeDeleteUserBlockModel($event){
        $v = $event->subject();
        $chatCachedModel = MooCore::getInstance()->getModel("Chat.ChatCachedQueryUserStat");
        $record = $chatCachedModel->findByUserId($v->field('user_id'));

        if($record){
            $chatCachedModel->read(null,$record['ChatCachedQueryUserStat']['id']);
        }
        $chatCachedModel->save(array(
            'user_id'=>$v->field('user_id'),
            'new_block'=>true,
        ));
        $rId = $this->_unblockuser($v->field('user_id'),$v->field('object_id'));

    }
    public function _parseRoomCode($userIds = array()){
        sort($userIds);
        return implode(".",$userIds);
    }
    public function _blockUser($ownerId,$blockerId){
        $roomModel = MooCore::getInstance()->getModel("Chat.ChatRoom");
        $code = $this->_parseRoomCode(array($ownerId,$blockerId));
        $room = $roomModel->findByCode($code);

        if($room){
            if($room["ChatRoom"]["first_blocked"] == 0 && $room["ChatRoom"]["second_blocked"] !=$ownerId){
                $room["ChatRoom"]["first_blocked"] = $ownerId;
            }elseif($room["ChatRoom"]["second_blocked"] == 0 && $room["ChatRoom"]["first_blocked"] !=$ownerId){
                $room["ChatRoom"]["second_blocked"] = $ownerId;
            }
            $roomModel->save($room["ChatRoom"]);
        }else{
            $roomModel->save(
                array(
                 'code'=>$code,
                 'name'=>$code,
                 'first_blocked'=> $ownerId
                )
            );
            $roomMemberModel = MooCore::getInstance()->getModel("Chat.ChatRoomsMember");
            $roomMemberModel->saveMany(array(
                array("room_id"=>$roomModel->id,"user_id"=>$ownerId,"joined"=>date("Y-m-d H:i:s")),
                array("room_id"=>$roomModel->id,"user_id"=>$blockerId,"joined"=>date("Y-m-d H:i:s")),
            ));

        }
    }
    public function _unblockuser($ownerId,$blockerId){
        $roomModel = MooCore::getInstance()->getModel("Chat.ChatRoom");
        $code = $this->_parseRoomCode(array($ownerId,$blockerId));
        $room = $roomModel->findByCode($code);
        if($room){
            if( $room["ChatRoom"]["second_blocked"] != $ownerId){
                $room["ChatRoom"]["first_blocked"] = 0;
            }elseif($room["ChatRoom"]["first_blocked"] !=$ownerId){
                $room["ChatRoom"]["second_blocked"] = 0;
            }
            $roomModel->save($room["ChatRoom"]);

        }
    }
}
