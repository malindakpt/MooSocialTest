<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('CakeEventListener', 'Event');

class SubListener implements CakeEventListener
{
	private $pass_action = array('home_ajax_lang','home_ajax_theme','home_do_language','home_do_theme');
    public function implementedEvents()
    {
        return array(
            'AppController.doBeforeFilter' => 'doBeforeFilter',            
            'UserController.doAfterRegister' => 'doAfterRegister',
        	'Model.beforeDelete' => 'doAfterDelete'
        );
    }
    
    public function doAfterDelete($event)
    {
    	$model = $event->subject();
    	$type = ($model->plugin) ? $model->plugin.'_' : ''.get_class($model);
    	if ($type == 'User')
    	{
    		$subscribeModel = MooCore::getInstance()->getModel('Subscription_Subscribe');
    		$subscribes = $subscribeModel->find('all',array(
    			'conditions' => array(
    				'Subscribe.user_id' => $model->id
    			)
    		));
    		
    		foreach ($subscribes as $subscribe)
    		{
    			$subscribeModel->delete($subscribe['Subscribe']['id']);
    		}
    		
    		$transactionModel = MooCore::getInstance()->getModel('Subscription.SubscriptionTransaction');
    		$transactionModel->deleteAll(array('SubscriptionTransaction.user_id' => $model->id));
    		
    		$refundModel = MooCore::getInstance()->getModel('Subscription.SubscriptionRefund');
    		$refundModel->deleteAll(array('SubscriptionRefund.user_id' => $model->id)); 
    	}
    }

    public function doBeforeFilter($event)
    {
        $e = $event->subject();
        if (!$e->check_subscription)
        	return;
        
        $this->Subscribe = MooCore::getInstance()->getModel('Subscription_Subscribe');
        $helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
        $cuser = $e->_getUser();
        $url = ($e->params['plugin'] ? $e->params['plugin'].'_' : '');
        $url.= $e->params['controller'].'_'.$e->params['action'];

        //validSignupSubscription
        if(isset($cuser['Role']) && 
           $cuser['Role']['is_super'] != 1 && 
           $e->params['prefix'] != 'admin' && !in_array($url,$this->pass_action) &&
           $e->params['plugin'] != 'subscription' && 
           ($e->params['controller'] != 'users' || 
           ($e->params['controller'] == 'users' && $e->params['action'] != 'do_logout')) &&
           $helper->checkEnableSubscription())
        {
        	$subscribe = $helper->getSubscribeActive($cuser,false);
			
            if (!$subscribe)
            {
            	$e->redirect('/subscription/subscribes/');				            	
                return;
            }
            if ($subscribe['Subscribe']['status'] == 'process')
            {
            	$e->redirect('/subscription/subscribes/success');
            	return;
            }
        }
    }
    
    public function doAfterRegister($event)
    {
        //check redirect to gateway if package exist
        $helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
        if ($helper->checkEnableSubscription())
        {
	        $e = $event->subject();	        
	        $this->SubscriptionPackagePlan = MooCore::getInstance()->getModel('Subscription.SubscriptionPackagePlan');
	        if($this->SubscriptionPackagePlan->hasAny(array('show_at LIKE' => '%1%', 'enable_plan' => 1)) &&
	            isset($e->data['plan_id']) && (int)$e->data['plan_id'] > 0)
	        {
	        	$planModel = MooCore::getInstance()->getModel('Subscription.SubscriptionPackagePlan');
	        	$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
	        	$plan = $planModel->findById($e->data['plan_id']);
	        	if ($helper->isFreePlan($plan))
	        	{
	        		$currency = Configure::read('Config.currency');
	        		$data = array('user_id' => $e->Auth->user('id'),
                          'plan_id' => $e->data['plan_id'],
             			  'package_id' => $plan['SubscriptionPackagePlan']['subscription_package_id'],                          
                          'status' => 'initial',
	        			  'gateway_id' => 0,
             			  'currency_code' => $currency['Currency']['currency_code']);
	        		
	        		$subscribeModel->save($data);
	        		$item = $subscribeModel->read();
	        		$helper->onSuccessful($item);	        		 
	        	}
	        	else 
	        	{
		            $e->Session->write('plan_id', $e->data['plan_id']);
		            echo json_encode(array('redirect' => $e->request->base.'/subscription/subscribes/gateway/'));
	        	}
	        }
        }
    }
}