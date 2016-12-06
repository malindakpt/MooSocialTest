<?php

Cache::config('topic', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('topic')));

if (Configure::read('Topic.topic_enabled')) {
    App::uses('TopicListener', 'Topic.Lib');
    CakeEventManager::instance()->attach(new TopicListener());
    
    MooSeo::getInstance()->addSitemapEntity("Topic", array(
    	'topic'
    ));
}