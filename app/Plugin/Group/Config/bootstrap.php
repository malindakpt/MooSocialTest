<?php

Cache::config('group', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('group')));

if (Configure::read('Group.group_enabled')) {
    App::uses('GroupListener', 'Group.Lib');
    CakeEventManager::instance()->attach(new GroupListener());
    MooSeo::getInstance()->addSitemapEntity("Group", array(
    	'group'
    ));
}