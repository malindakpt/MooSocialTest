<?php

Cache::config('blog', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('blog')));

if (Configure::read('Blog.blog_enabled')) {
    App::uses('BlogListener', 'Blog.Lib');
    CakeEventManager::instance()->attach(new BlogListener());
    MooSeo::getInstance()->addSitemapEntity("Blog", array(
    	'blog'	
    ));
}