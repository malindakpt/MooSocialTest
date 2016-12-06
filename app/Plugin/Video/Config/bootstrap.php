<?php

Cache::config('video', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('video')));

if (Configure::read('Video.video_enabled')) {
    App::uses('VideoListener', 'Video.Lib');
    CakeEventManager::instance()->attach(new VideoListener());
    
    MooSeo::getInstance()->addSitemapEntity("Video", array(
    	'video'
    ));
}