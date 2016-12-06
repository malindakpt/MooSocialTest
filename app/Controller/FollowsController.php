<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class FollowsController extends AppController
{
    public function ajax_update_follow()
    {
        $this->_checkPermission(array('confirm' => true));
        $uid = MooCore::getInstance()->getViewer(true);
        $user_id = $this->request->data['user_id'];

        $this->loadModel("UserFollow");

        $follow = $this->UserFollow->checkFollow($uid,$user_id);

        if ($follow)
        {
            $this->UserFollow->deleteAll(array('UserFollow.user_id' => $uid, 'UserFollow.user_follow_id' => $user_id));
        }
        else
        {
            $this->UserFollow->save(array(
                'user_id' => $uid,
                'user_follow_id' => $user_id
            ));
        }
        die();
    }

    public function user_follows()
    {
        $this->_checkPermission(array('confirm' => true));
        $uid = MooCore::getInstance()->getViewer(true);

        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
        $this->loadModel("UserFollow");
        $users = $this->UserFollow->find('all',array(
            'conditions'=>array('UserFollow.user_id'=>$uid),
            'order' => 'User.name asc',
            'limit' => RESULTS_LIMIT,
            'page' => $page
        ));

        $count_user = $this->UserFollow->find('count',array('conditions'=>array('UserFollow.user_id'=>$uid)));
        $is_view_more = (($page - 1) * RESULTS_LIMIT  + count($users)) < $count_user;
        $this->set('page',$page);
        if ($is_view_more)
            $this->set('url_more', '/follows/user_follows/page:' . ( $page + 1 ) ) ;
        $this->set('users',$users);
    }

    public function ajax_remove($id = null)
    {
        $id = intval($id);
        $this->_checkPermission( array( 'confirm' => true ) );
        $uid = $this->Auth->user('id');

        // check if users are not follow
        $this->loadModel("UserFollow");
        if ( !$this->UserFollow->checkFollow( $uid, $id ) )
        {
            $this->autoRender = false;
            echo __('You are not a follow of this user');
            return;
        }

        // nothing? display the form
        $this->loadModel( 'User' );
        $user = $this->User->findById($id);
        $this->set('user', $user);
    }

    public function ajax_removeRequest()
    {
        $this->_checkPermission( array( 'confirm' => true ) );
        $uid = $this->Auth->user('id');
        $user_id = $this->request->data['user_id'];

        $this->loadModel("UserFollow");

        $this->UserFollow->deleteAll(array('UserFollow.user_id' => $uid, 'UserFollow.user_follow_id' => $user_id));
        echo json_encode(array('status'=>1));
        die();
    }
}

