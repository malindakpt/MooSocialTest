<?php
App::uses('AppHelper', 'View/Helper');
class BlogHelper extends AppHelper {
	public function checkPostStatus($blog,$uid)
	{
		if (!$uid)
			return false;		
		$friendModel = MooCore::getInstance()->getModel('Friend');
		if ($uid == $blog['Blog']['user_id'])
			return true;
			
		if ($blog['Blog']['privacy'] == PRIVACY_EVERYONE)
		{
			return true;
		}
		
		if ($blog['Blog']['privacy'] == PRIVACY_FRIENDS)
		{
			$areFriends = $friendModel->areFriends( $uid, $blog['Blog']['user_id'] );
			if ($areFriends)
				return true;
		}
		
		
		return false;
	}
	
	public function getEnable()
	{
		return Configure::read('Blog.blog_enabled');
	}
	
	public function checkSeeComment($blog,$uid)
	{
		if ($blog['Blog']['privacy'] == PRIVACY_EVERYONE)
		{
			return true;
		}
		
		return $this->checkPostStatus($blog,$uid);
	}
	
	public function getTagUnionsBlog($blogids)
	{
		return "SELECT i.id, i.title, i.body, i.like_count, i.created, 'Blog_Blog' as moo_type, i.privacy, i.user_id
						 FROM " . Configure::read('core.prefix') . "blogs i
						 WHERE i.id IN (" . implode(',', $blogids) . ")";
	}
	
	public function getItemSitemMap($name,$limit,$offset)
	{
		if (!MooCore::getInstance()->checkPermission(null, 'blog_view'))
			return null;
		
		$blogModel = MooCore::getInstance()->getModel("Blog.Blog");
		$blogs = $blogModel->find('all',array(
				'conditions' => array('Blog.privacy'=>PRIVACY_PUBLIC),
				'limit' => $limit,
				'offset' => $offset
		));
		 
		$urls = array();
		foreach ($blogs as $blog)
		{
			$urls[] = FULL_BASE_URL.$blog['Blog']['moo_href'];
		}
		 
		return $urls;
	}
	
	public function getImage($item, $options) {
            $request = Router::getRequest();
            $view = MooCore::getInstance()->getMooView();
            $prefix = '';
            if (isset($options['prefix'])) {
                $prefix = $options['prefix'] . '_';
            }

            if ($item[key($item)]['thumbnail']) {
                $url = FULL_BASE_URL . $request->webroot . 'uploads/blogs/thumbnail/' . $item[key($item)]['id'] . '/' . $prefix . $item[key($item)]['thumbnail'];
            } else {
                $url = FULL_BASE_URL . $this->assetUrl('Blog.noimage/blog.png', $options + array('pathPrefix' => Configure::read('App.imageBaseUrl')));
            }

            return $url;
        }

}
