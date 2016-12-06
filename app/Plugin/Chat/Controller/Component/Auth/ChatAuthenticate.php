<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class ChatAuthenticate extends BaseAuthenticate {
    public function authenticate(CakeRequest $request, CakeResponse $response) {
        return false;
    }
    public function logout($user) {

        $chatModel = MooCore::getInstance()->getModel('Chat.ChatToken');
        $chatModel->deleteAll(array('ChatToken.session_id'=>CakeSession::id()),false);
    }
}