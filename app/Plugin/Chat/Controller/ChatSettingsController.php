<?php

class ChatSettingsController extends ChatAppController
{
    public $components = array('QuickSettings');

    public function admin_index()
    {
        $this->QuickSettings->run($this, array("Chat"));
    }
    private function _renderBlockingManager($json=false){
        $viewerId = $this->Auth->user('id');
        $this->loadmodel('Chat.ChatRoom');
        $this->loadmodel('Chat.ChatRoomsMember');
        $this->loadmodel('User');
        // Find room is blocked by viewer
        $rawsRoomId = $this->ChatRoom->find("all",
            array(
                'fields' => array('ChatRoom.id'),
                'conditions' => array("OR" => array(
                    'ChatRoom.first_blocked' => $viewerId,
                    'ChatRoom.second_blocked' => $viewerId,
                ))

            )
        );
        $roomsId = Hash::extract($rawsRoomId, "{n}.ChatRoom.id");

        // Get all block users
        $rawsUserId = $this->ChatRoomsMember->find("all",
            array(
                'fields' => array('ChatRoomsMember.user_id'),
                'conditions' => array(
                    'ChatRoomsMember.room_id' => $roomsId,
                    'ChatRoomsMember.user_id !=' => $viewerId,
                )

            )
        );
        $usersId = Hash::extract($rawsUserId,"{n}.ChatRoomsMember.user_id");
        $data = $this->User->find("all",
            array(
                'fields' => array('User.id','User.name'),
                'conditions' => array(
                    'User.id' => $usersId,
                )

            )
        );
        if($json){
            return $data;
        }else{
            $this->set("data",$data);
        }

    }
    private function checkIsLogged(){
        if ($this->Auth->user('id') == null) {
            $this->Flash->set(__d('chat', 'You need to login first'));
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'member_login'
            ));
            return false;
        }
        return true;
    }
    public function blocking()
    {

        if( $this->checkIsLogged()){
            $this->_renderBlockingManager();
        }else{
            $this->set("data",array());
        }

    }
    public function unblock($id){
        if( $this->checkIsLogged()){
            $viewerId = $this->Auth->user('id');
            $this->loadmodel('Chat.ChatRoom');
            $roomCode = array($viewerId,$id);
            asort($roomCode);
            $code = implode(".",$roomCode);

            $rawData = $this->ChatRoom->find("first",array(
                'conditions' => array(
                    'ChatRoom.code' => $code,
                )

            )
            );
            if($rawData){
               
                if($rawData['ChatRoom']["first_blocked"] == $viewerId){
                    $rawData['ChatRoom']["first_blocked"] = 0;
                }
                if($rawData['ChatRoom']["second_blocked"] == $viewerId){
                    $rawData['ChatRoom']["second_blocked"] = 0;
                }
                $this->ChatRoom->id = $rawData['ChatRoom']["id"];
                $this->ChatRoom->save($rawData['ChatRoom']);
            }
            $this->set("users",$this->_renderBlockingManager(true));
        }else{
            $this->set("users",array());
        }

        $this->set('_serialize', array('users'));
    }
}