<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('Widget','Controller/Widgets');

class popularEventWidget extends Widget {
    public function beforeRender(Controller $controller) {
    	$controller->loadModel('Event.Event');
    	$num_item_show = $this->params['num_item_show'];        
         $user_blocks = array();
            $cuser = $controller->_getUser();
            if($cuser){
                $user_blocks = $controller->getBlockedUsers($cuser['id']);  
            }
            
            if(empty($user_blocks)){
        $upcomming_events = Cache::read('popular_events.'.$num_item_show, 'event');
        
	    if(!$upcomming_events){
	        $upcomming_events = $controller->Event->getPopularEvents( $num_item_show,Configure::read('core.popular_interval'));
	        Cache::write('popular_events.'.$num_item_show,$upcomming_events, 'event');
	    }
        }else{
                $upcomming_events = $controller->Event->getPopularEvents( $num_item_show,Configure::read('core.popular_interval'));
            }
        $this->setData('popularEventWidget',$upcomming_events);
    }
}