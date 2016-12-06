<?php

Cache::config('event', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('event')));

if (Configure::read('Event.event_enabled')) {	
    App::uses('EventListener', 'Event.Lib');
    CakeEventManager::instance()->attach(new EventListener());
    MooSeo::getInstance()->addSitemapEntity("Event", array(
    	'event'
    ));
}