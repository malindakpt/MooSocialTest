<?php

Cache::config('photo', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('photo')));

if (Configure::read('Photo.photo_enabled')) {
    App::uses('PhotoListener', 'Photo.Lib');
    CakeEventManager::instance()->attach(new PhotoListener());
    
    MooSeo::getInstance()->addSitemapEntity("Photo", array(
    	'album'
    ));
}