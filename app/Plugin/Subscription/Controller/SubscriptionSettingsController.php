<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class SubscriptionSettingsController extends SubscriptionAppController 
{
    public $components = array('QuickSettings');
    public function __construct($request = null, $response = null) 
    {
        parent::__construct($request, $response);

        $this->url = '/admin/subscription/subscription_settings/';
        $this->set('url', $this->url);
    }
    
    public function beforeFilter()
	{
		parent::beforeFilter();
        
        $this->_checkPermission(array('super_admin' => 1));
	}
    
    public function admin_index($id = null)
    {
		if ($this->request->isPost())
		{
			$enable = $this->request->data['enable'];			
			$select_theme = $this->request->data['select'];
			
			$enable_setting = $this->Setting->findByName('enable_subscription_packages');
			$theme_setting = $this->Setting->findByName('select_theme_subscription_packages');
			
			$array = json_decode($enable_setting['Setting']['value_actual'],true);
			$array[0]['select'] = ($enable ? 1 : 0);
			$this->Setting->id = $enable_setting['Setting']['id'];
			$this->Setting->save(array('value_actual'=>json_encode($array)));
			
			$array = json_decode($theme_setting['Setting']['value_actual'],true);
			foreach ($array as $key=>$item)
				$array[$key]['select'] = 0;		
			$array[$select_theme]['select'] = 1;
			$this->Setting->id = $theme_setting['Setting']['id'];
			$this->Setting->save(array('value_actual'=>json_encode($array)));
			
			$this->Session->setFlash(__('Successfully updated'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in' ));
            $this->redirect( $this->url );
		}
    }
}
