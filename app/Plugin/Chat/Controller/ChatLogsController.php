<?php

class ChatLogsController extends ChatAppController
{
    public $components = array('Paginator');
    public $paginate = array(
        'limit' => 20,
        //'conditions' => 'ChatRoom.id=1'
    );
    
    public function admin_index()
    {
        $this->loadModel('Chat.ChatRoom');
        $this->loadModel('User');

        if(!isset($this->request->data['keyword'])){
            if(($this->Session->read('Chat.AdminLogs.Keyword') != null)){
                $keyword = $this->Session->read('Chat.AdminLogs.Keyword');
            }else{
                $keyword = '';
            }
        }else{
            $keyword = $this->request->data['keyword'];

        }

        if ($keyword != '') {
            $this->Session->write('Chat.AdminLogs.Keyword', $keyword);
            $filter = $this->User->find('all', array(
                    'fields' => array('User.id'),
                    'conditions' => array('OR'=>array(
                        "User.name LIKE" => "%$keyword%",
                        "User.email LIKE" => "%$keyword%",
                    ))
                )
            );
            if(!empty($filter)){

                $rooms = $this->ChatRoom->ChatRoomsMember->find("all",array(
                    'fields' => array('ChatRoomsMember.room_id'),
                    'conditions'=>array("ChatRoomsMember.user_id"=>Hash::extract($filter,'{n}.User.id')),
                ));

                $condRoomIds = Hash::extract($rooms,'{n}.ChatRoomsMember.room_id');
                if(!empty($condRoomIds)){
                    $this->paginate['conditions'] = array('ChatRoom.id'=>$condRoomIds);
                }
            }else{
                $this->Flash->adminMessages(__d('chat','No Results'));
                $this->set('data',array());
                return;
            }
        }else{
            $this->Session->delete('Chat.AdminLogs.Keyword');
        }
        $this->Paginator->settings = $this->paginate;
        $data = $this->Paginator->paginate(
            'ChatRoom'
        );
        $userIds = Hash::extract($data, '{n}.ChatRoomsMember.{n}.user_id');
        $users = $this->User->find('all', array(
            'fields' => array('User.id', 'User.name', 'User.email'),
            'conditions' => array('User.id' => $userIds)
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');

        $this->set('data', $data);
        $this->set('users', $users);
        $this->set('keyword',$keyword);
        $this->set('title_for_layout', __d('chat','Chats Logs'));

    }

    public function admin_delete()
    {
        $this->loadModel('Chat.ChatRoom');
        $this->_checkPermission(array('super_admin' => 1));

        if (!isset($_POST['reports'])) {
            $reports = $this->ChatRoom->findAllById($_POST['reports']);

            foreach ($reports as $report) {

                $this->ChatRoom->delete($report['ChatRoom']['id']);
            }

            $this->Flash->adminMessages(__d('chat','Rooms have been deleted'));
        }

        $this->redirect(array(
            'plugin' => 'chat',
            'controller' => 'chat_logs',
            'action' => 'admin_index'
        ));
    }
    public function admin_messages($roomId){
        if(!isset($roomId)){
            $this->redirect(array(
                'plugin' => 'chat',
                'controller' => 'chat_logs',
                'action' => 'admin_index'
            ));
        }
        $this->helpers[] = 'Chat.Message';
        $this->loadModel('Chat.ChatMessage');
        $this->loadModel('User');
        
        $this->paginate["conditions"] =   array('ChatMessage.room_id' => $roomId);
        $this->paginate["order"] = array('ChatMessage.created'=>'DESC');
        $this->Paginator->settings = $this->paginate;
        $data = $this->Paginator->paginate(
            'ChatMessage'
        );
        // get users have chatted
        $userIds = Hash::extract($data, '{n}.ChatMessage.sender_id');
        $actions = Hash::extract($data, '{n}.ChatMessage[type=system].content');
        // Get users are added
        /*
        if(count($actions) > 0){
            foreach($actions as $action){
                $action = json_decode($action);
                if(is_object($action)){
                    if(property_exists($action,'action')){
                        switch ($action->action){
                            case "added":
                                $userIds = array_merge($userIds, $action->usersId);
                                break;
                            default:
                        }
                    }
                }


            }
        }
        */
        // get user in room
        $this->loadModel('Chat.ChatRoomsMember');
        $usersInRoom = $this->ChatRoomsMember->find("all",array(
            'fields' => array('ChatRoomsMember.user_id'),
            'conditions' => array('ChatRoomsMember.room_id' => $roomId)
        ));

        $userIds = array_merge($userIds,   Hash::extract($usersInRoom,"{n}.ChatRoomsMember.user_id"));
        $users = $this->User->find('all', array(
            'conditions' => array('User.id' => $userIds)
        )); 
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $this->set('data',$data);
        $this->set('users', $users);
        $this->set('roomId',$roomId);
    }
}