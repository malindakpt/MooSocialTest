<?php

Cache::config('menu', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('menu')));

App::uses('MenuListener', 'Menu.Lib');
CakeEventManager::instance()->attach(new MenuListener());
