<?php 
class ChatsController extends ChatAppController{
    public $components = array('Paginator');
    public $paginate = array(
        'limit' => 50,
        //'conditions' => 'ChatRoom.id=1'
    );
    public function admin_index()
    {
    }
    public function index()
    {
    }
    public function sendPicture(){

        if($this->_isAllowedSendPicture()){
            $allowedExtensions = MooCore::getInstance()->_getPhotoAllowedExtension();
            $maxFileSize = MooCore::getInstance()->_getMaxFileSize();
            App::import('Vendor', 'qqFileUploader');
            $uploader = new qqFileUploader($allowedExtensions, $maxFileSize,'image');
            $path = 'uploads' . DS . 'chat'.DS.'room-'.$this->request->data['roomId'];
            $this->_prepareDir($path);
            $result = $uploader->handleUpload($path);
            if (!empty($result['success'])) {
                $this->set(array(
                    'result' => $result,
                    'error_code'=>0,
                    '_serialize' => array('result','error_code')
                ));
            }else{
                $this->set(array(
                    'result' => $result,
                    'error_code'=>1,
                    '_serialize' => array('result','error_code')
                ));
            }

        }else{
            $this->_echoAccessDenied();
        }

    }

    public function sendFiles(){
        if($this->_isAllowedSendFiles()){
            $allowedExtensions = MooCore::getInstance()->_getFileAllowedExtension();
            $maxFileSize = MooCore::getInstance()->_getMaxFileSize();
            App::import('Vendor', 'qqFileUploader');
            $uploader = new qqFileUploader($allowedExtensions, $maxFileSize,'file');
            $path = 'uploads' . DS . 'chat'.DS.'room-'.$this->request->data['roomId'];
            $this->_prepareDir($path);
            $result = $uploader->handleUpload($path);
            if (!empty($result['success'])) {
                $this->set(array(
                    'result' => $result,
                    'error_code'=>0,
                    '_serialize' => array('result','error_code')
                ));
            }else{
                $this->set(array(
                    'result' => $result,
                    'error_code'=>1,
                    '_serialize' => array('result','error_code')
                ));
            }

        }else{
            $this->_echoAccessDenied();
        }
    }
    public function saveUserSettings(){
        $this->loadModel('Chat.ChatUsersSetting');
        $settings = $this->ChatUsersSetting->find('first',array(
            'conditions' => array('ChatUsersSetting.user_id' => $this->Auth->user('id'))
        ));
        if (empty($settings)){
            $settings = array(
                'ChatUsersSetting'=>array(
                    'user_id'=>$this->Auth->user('id'),
                )
            );
        }
       
        if(isset($this->request->data['status'])){
            $settings['ChatUsersSetting']['status'] = $this->request->data['status'];
        }
        if(isset($this->request->data['sound'])){
            $settings['ChatUsersSetting']['sound'] = ($this->request->data['sound']==1)?true:false;
        }
        
        if(isset($this->request->data['hide_group'])){
            $settings['ChatUsersSetting']['hide_group'] = ($this->request->data['hide_group']==1)?true:false;
        }

        if(isset($this->request->data['first_time_using'])){
            $settings['ChatUsersSetting']['first_time_using'] = ($this->request->data['first_time_using']==1)?false:true;
        }
        if(isset($this->request->data['room_is_opened'])){
            $settings['ChatUsersSetting']['room_is_opened'] = $this->request->data['room_is_opened'];
        }
        $this->ChatUsersSetting->save($settings);
        $this->set(array(
            'result' => $this->request->data,
            'error_code'=>0,
            '_serialize' => array('result','error_code')
        ));
    }
    private function _rooms(){
        $this->loadModel('Chat.ChatRoom');
        $this->loadModel('User');
        $condRoomIds = array();
        $viewerId = $this->Auth->user('id');
        $rooms = $this->ChatRoom->ChatRoomsMember->find("all",array(
            'fields' => array('ChatRoomsMember.room_id'),
            'conditions'=>array("ChatRoomsMember.user_id"=>$viewerId),
        ));

        $condRoomIds = Hash::extract($rooms,'{n}.ChatRoomsMember.room_id');
        $rooms = $this->ChatRoom->find("all",array(
            'conditions'=>array("ChatRoom.id"=>$condRoomIds)
        ));
        $order = $this->ChatMessage->find("all",array(
            'fields' => array('ChatMessage.room_id','MAX(ChatMessage.created) AS created'),
            'conditions'=>array("ChatMessage.room_id"=>$condRoomIds),
            'group'=>'ChatMessage.room_id',
            'order'=>'created DESC'
        ));

        return array(Hash::combine($rooms,'{n}.ChatRoom.id','{n}'),Hash::extract($order,'{n}.ChatMessage.room_id'));


    }
    public function messages($roomId = 0){
        $viewerId = $this->Auth->user('id');

        $this->helpers[] = 'Chat.Message';
        $this->loadModel('Chat.ChatMessage');
        $this->loadModel('User');


        $this->paginate["conditions"] =   array(
            'ChatMessage.room_id' => $roomId,
            'ChatStatusMessages.user_id' => $viewerId,
            'ChatStatusMessages.delete' => 0,
        );
        $this->paginate["order"] = array('ChatMessage.created'=>'DESC');
        $this->paginate['joins'] = array(
            array('table' => 'chat_status_messages',
                'alias' => 'ChatStatusMessages',
                'type' => 'LEFT',
                'conditions' => array(
                    'ChatMessage.id = ChatStatusMessages.message_id',
                )
            )
        );
        $this->Paginator->settings =$this->paginate;
        $data = $this->Paginator->paginate(
            'ChatMessage'
        );
        // get users have chatted
        $userIds = Hash::extract($data, '{n}.ChatMessage.sender_id');
        $actions = Hash::extract($data, '{n}.ChatMessage[type=system].content');

        // get user in room
        $this->loadModel('Chat.ChatRoomsMember');
        $usersInRoom = $this->ChatRoomsMember->find("all",array(
            'fields' => array('ChatRoomsMember.user_id'),
            'conditions' => array('ChatRoomsMember.room_id' => $roomId)
        ));
        $userIds = array_merge($userIds,   Hash::extract($usersInRoom,"{n}.ChatRoomsMember.user_id"));
        if(empty($viewerId)){
            // Fix for moo 2.4.1
            $this->Flash = $this->Components->load('Flash');
            $this->Flash->set(__d('chat','You need to login first'));
            $data = array();

        }else{
            if($this->_getUserRoleId() != ROLE_ADMIN && !in_array($viewerId,Hash::extract($usersInRoom,"{n}.ChatRoomsMember.user_id")) && $roomId != 0){
                // Fix for moo 2.4.1
                $this->Flash = $this->Components->load('Flash');
                $this->Flash->set(__d('chat','You are not in this room'));
                $data = array();
            }
        }

        // Here is list of user for all room
        list($rooms,$order) = $this->_rooms();
        if($roomId == 0 && !empty($order)){
            // Fix for setting default room when going to /chat/messages
            $this->redirect(
                array('controller' => 'chats', 'action' => 'messages',$order[0])
            );
        }
        $userIdsInAllRoom = Hash::extract($rooms, '{n}.ChatRoomsMember.{n}.user_id');
        $userIds = array_merge($userIds,$userIdsInAllRoom);
        // Now get users information
        $users = $this->User->find('all', array(

            'conditions' => array('User.id' => $userIds)
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        // Mark All Read
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."chat_status_messages SET unseen = 0 WHERE room_id=$roomId AND user_id =$viewerId");
        $chatCounter = $this->ChatMessage->query("SELECT COUNT(*) AS count FROM (SELECT room_id FROM ".$this->ChatMessage->tablePrefix."chat_status_messages WHERE user_id = $viewerId and unseen=1 group by room_id) AS A");
        $this->ChatMessage->query("UPDATE ".$this->ChatMessage->tablePrefix."users SET chat_count = ".$chatCounter[0][0]["count"]." WHERE id=$viewerId");
        // Assign here 
        $this->set("rooms",$rooms);
        $this->set('data',$data);
        $this->set('users', $users);
        $this->set('roomId',$roomId);
        $this->set('order',$order);
        $this->set('viewerId',$viewerId);

    }
    public function embed(){
        $link = $this->request->data['link'];
        if (filter_var($link, FILTER_VALIDATE_URL) === FALSE) {
            $result = array();
        }else{
            $file_headers = @get_headers($link);
            if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                $result = array();
            }else{
                include(dirname(__FILE__).DS."..".DS."Lib".DS."Embed-1.x".DS."src".DS."autoloader.php");
                $info = Embed\Embed::create($link);
                //$info = Embed\Embed::create('http://vnexpress.net/tin-tuc/thoi-su/mui-hoi-thoi-tan-cong-nhieu-noi-o-sai-gon-3458328.html');
                //$info = Embed\Embed::create('http://img.f29.vnecdn.net/2016/08/29/quoc-lo-50-JPG-5437-1472440103.jpg');
                //$info = Embed\Embed::create('https://www.youtube.com/watch?v=Xb5VrR8hT2k');
                $result['title'] = $info->title;
                $result['description'] = $info->description;
                $result['type'] =$info->type;
                $result['image'] =$info->image;
                $result['images'] =$info->images;
                $result['code'] =$info->code;
                $result['url'] =$info->url;
                if(!empty($result['images'])){
                    $result['image'] = $result['images'][0];
                }
                if(empty($result['description'])){
                    $result['description'] = $result['title'];
                }
            }

        }

        $this->set(array(
            'result' => $result,
            '_serialize' => array('result')
        ));
    }
    public function videoCalling(){
        $this->layout = 'Chat.video';
    }
    public function videoCalling1(){
        $this->layout = 'Chat.video1';
    }

}