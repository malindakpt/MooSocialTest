<?php 
App::uses('AppController', 'Controller');
class ChatAppController extends AppController{
    
    public function beforeFilter() {
        parent::beforeFilter();
    }
    protected function _isAllowedChat(){
        return $this->isAllowedPermissions( 'chat_allow_chat');
    }
    protected function _isAllowedSendPicture(){
        return $this->isAllowedPermissions( array('chat_allow_chat','chat_allow_send_picture') );
    }
    protected function _isAllowedSendFiles(){
        return $this->isAllowedPermissions( array('chat_allow_chat','chat_allow_send_files') );
    }
    protected function _isAllowedEmotion(){
        return $this->isAllowedPermissions( array('chat_allow_chat','chat_allow_user_emotion') );
    }
    protected function _isAllowedChatGroup(){
        return $this->isAllowedPermissions( array('chat_allow_chat','chat_allow_chat_group') );
    }
    protected function _prepareDir($path) {
        $path = WWW_ROOT . $path;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            file_put_contents($path . DS . 'index.html', '');
        }
    }
    protected function _echoAccessDenied(){
        $this->set(array(
            'result' => __d('chat','Access denied'),
            'error_code'=>('access_denied'),
            '_serialize' => array('result','error_code')
        ));
    }
}