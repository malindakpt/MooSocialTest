<?php
App::uses('AppHelper', 'View/Helper');
class GroupHelper extends AppHelper {
	
	public function isMember($group,$uid)
	{
		$model = MooCore::getInstance()->getModel('Group_Group_User');
		$is_member = $model->isMember($uid, $group['Group']['id']);
		
		return $is_member;
	}
	
	public function getEnable()
	{
		return Configure::read('Group.group_enabled');
	}
	
	public function checkPostStatus($group, $uid) {
            if (!$uid)
                return false;

            $cuser = MooCore::getInstance()->getViewer();
            if($cuser['Role']['is_admin'])
                return true;
            $my_status = $this->isMember($group, $uid);
            if (($my_status)){
                return true;
            }

            return false;
        }

        public function checkSeeActivity($group,$uid)
	{
		$is_member = $this->isMember($group, $uid);
		
		if ($group['Group']['type'] == PRIVACY_PRIVATE) {
			$cuser = MooCore::getInstance()->getViewer();

       		if (!$cuser['Role']['is_admin'] && !$is_member)
				return false;
		}
		return true;
	}

    public function isPublicFeedIcon($group)
    {
        if ($group['Group']['type'] == PRIVACY_PRIVATE)
            return false;
        return true;
    }

    public function checkPrivacyFeedHome($group)
    {
        $uid = MooCore::getInstance()->getViewer(true);

        if (!$uid)
            return false;

        return $this->isMember($group,$uid);
    }
    
    public function getItemSitemMap($name,$limit,$offset)
    {
    	if (!MooCore::getInstance()->checkPermission(null, 'group_view'))
    		return null;
    
    	$groupModel = MooCore::getInstance()->getModel("Group.Group");
    	$groups = $groupModel->find('all',array(
    		'conditions' => array('Group.type'=>PRIVACY_PUBLIC),
    		'limit' => $limit,
    		'offset' => $offset
    	));
    	
    	$urls = array();
    	foreach ($groups as $group)
    	{
    		$urls[] = FULL_BASE_URL.$group['Group']['moo_href'];
    	}
    		
    	return $urls;
    }
	
	public function getAdminList($group)
	{
		$groupUserModel = MooCore::getInstance()->getModel('Group_Group_User');
       	$group_admins = $groupUserModel->getUsersList($group['Group']['id'], GROUP_USER_ADMIN);
       	
    	return $group_admins;
	}
        
        public function getImage($item, $options) {
            $request = Router::getRequest();
            $view = MooCore::getInstance()->getMooView();
            $prefix = '';
            if (isset($options['prefix'])) {
                $prefix = $options['prefix'] . '_';
            }

            if ($item[key($item)]['photo']) {
                $url = FULL_BASE_URL . $request->webroot . 'uploads/groups/photo/' . $item[key($item)]['id'] . '/' . $prefix . $item[key($item)]['photo'];
            } else {
                $url = FULL_BASE_URL . $this->assetUrl('Group.noimage/group.png', $options + array('pathPrefix' => Configure::read('App.imageBaseUrl')));
            }

            return $url;
        }

}
