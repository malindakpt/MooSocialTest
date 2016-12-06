<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

class SubscribesController extends SubscriptionAppController 
{
    public $components = array('Paginator');
    
    public function __construct($request = null, $response = null) 
    {
        parent::__construct($request, $response);
        $this->loadModel('Role');
        $this->loadModel('User');
        $this->loadModel('Subscription.Subscribe');
        $this->loadModel('Subscription.SubscriptionPackage');
        $this->loadModel('Subscription.SubscriptionPackagePlan');
        $this->loadModel('Subscription.SubscriptionCompare');        
        $this->loadModel('Subscription.SubscriptionRefund');          
        $this->loadModel('PaymentGateway.Gateway');        
        $this->loadModel('Subscription.SubscriptionTransaction');
        
        $this->url = '/admin/subscription/subscribes/';
        $this->furl = '/subscription/subscribes/';
        $this->url_create = $this->url.'create/';
        $this->url_delete = $this->url.'delete/';
        $this->set('url', $this->url);
        $this->set('furl', $this->furl);
        $this->set('url_create', $this->url_create);
        $this->set('url_delete', $this->url_delete);
    }
    
    public function beforeFilter()
	{
		parent::beforeFilter();

        if((int)$this->Auth->user('id') < 1 &&
           $this->params['action'] != 'preview')          
        {
            $this->redirect( '/pages/no-permission');
        }
        
        $cuser = $this->_getUser();
        if($this->params['prefix'] != 'admin' && isset($cuser['Role']) && $cuser['Role']['is_super'])
		{
			$this->redirect('/');
		}
		if(isset($this->params['prefix']) && $this->params['prefix'] == 'admin')
		{
			$this->_checkPermission(array('super_admin' => 1));
		}
		else 
		{
			 $helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
			 
			 if (!$helper->checkEnableSubscription())
			 	$this->redirect( '/pages/no-permission');
		}
	}
    
    public function admin_index($id = null)
    {
        $this->Paginator->settings = array(
            'limit' => 20,
            'order' => array(
                'Subscribe.created' => 'DESC'
            )
        );
        $cond = array();
        if ( !empty( $this->request->data['plan_id'] ) )
        {
        	$cond['Subscribe.plan_id'] = $this->request->data['plan_id'];
        	$this->set('plan_id',$this->request->data['plan_id']);
        }
        
    	if ( !empty( $this->request->data['name'] ) )
        {
        	$cond['User.name LIKE'] = '%'.$this->request->data['name'].'%';
        	$this->set('name',$this->request->data['name']);
        }
        
        if ( !empty( $this->request->data['start_date'] ) )
        {
        	$cond['Subscribe.created >'] = $this->request->data['start_date'];
        	$this->set('start_date',$this->request->data['start_date']);
        }
        
    	if ( !empty( $this->request->data['end_date'] ) )
        {
        	$cond['Subscribe.created <'] = $this->request->data['end_date'];
        	$this->set('end_date',$this->request->data['end_date']);
        }
        
    	if ( !empty( $this->request->data['status'] ) )
        {
        	$cond['Subscribe.status'] = $this->request->data['status'];
        	$this->set('status',$this->request->data['status']);
        }
        
        $subscribes = $this->Paginator->paginate('Subscribe',$cond);
        $this->set('subscribes', $subscribes);
        
        $plans = $this->SubscriptionPackagePlan->find('all');
        $this->set('plans',$plans);
    }
    
    public function admin_detail($id = null) {
        if (!$id) {
            $this->redirect($this->referer());
        }

        if (!$this->Subscribe->isIdExist($id)) {
            $this->set('notice', __( 'This subscription does not exist'));
        } else {
            $subscribeDetail = $this->Subscribe->findById($id);

            //current user group
            $group = $this->Role->findById($subscribeDetail['SubscriptionPackage']['role_id']);

            $this->set('subscribeDetail', $subscribeDetail['Subscribe']);
            $this->set('subscribe',$subscribeDetail);
            $this->set('package', $subscribeDetail['SubscriptionPackage']);
            $this->set('plan', $subscribeDetail['SubscriptionPackagePlan']);
            $this->set('user', $subscribeDetail['User']);
            $this->set('group', $group['Role']);
            
            $transactions = $this->SubscriptionTransaction->find('all',array(
            	'conditions' => array(
            		'SubscriptionTransaction.subscribes_id' => $subscribeDetail['Subscribe']['id'],
            		'admin' => 0
            	),
            	'order' => array('SubscriptionTransaction.created DESC'),
            ));
            
            $this->set('transactions',$transactions);
        }        
    }
    
    public function admin_cancel()
    {
    	$id = $this->request->data['id'];
    	$subscribe = $this->Subscribe->findById($id);
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$result = array('status'=>0);
    	
    	if (!$subscribe || !$helper->canCancel($subscribe))
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$result_respone = $helper->onCancel($subscribe);
    	
    	if (!$result_respone)
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$this->Session->setFlash(__('Subscription canceled'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
    	
    	$plan = $subscribe['SubscriptionPackagePlan'];
		$package = $subscribe['SubscriptionPackage'];		
    	//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $request = Router::getRequest();
        $params = array(
        	'subscription_title' => $subscribe['SubscriptionPackage']['name'],
        	'subscription_description' => $subscribe['SubscriptionPackage']['description'],        	        	
        	'link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/users/member_login',
        	'plan_title' => $plan['title'],
			'plan_description' => $helper->getPlanDescription($plan, $subscribe['Subscribe']['currency_code'])
        );
        $mailComponent->send(array('User'=>$subscribe['User']),'subscription_cancel',$params);
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    }
    
	public function admin_refunded()
    {
    	$id = $this->request->data['id'];
    	$subscribe = $this->Subscribe->findById($id);
    	$result = array('status'=>0);
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	
    	if (!$subscribe || !$helper->canRefunded($subscribe))
    	{
    		echo json_encode($result);
            exit;
    	}
    	$refundModel = MooCore::getInstance()->getModel('Subscription.SubscriptionRefund');
    	$refund = $refundModel->find('first',array(
    			'conditions'=>array(
    				'SubscriptionRefund.subscribe_id' => $subscribe['Subscribe']['id'],
    				'SubscriptionRefund.status' => 'initial',
    			)
    	));

    	$result_respone = $helper->doRefund($subscribe,$refund);
    	
    	if (!$result_respone)
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$this->Session->setFlash(__('Subscription refunded'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
    	
    	$plan = $subscribe['SubscriptionPackagePlan'];
		$package = $subscribe['SubscriptionPackage'];
    	
    	//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $request = Router::getRequest();
        $params = array(
        	'subscription_title' => $subscribe['SubscriptionPackage']['name'],
        	'subscription_description' => $subscribe['SubscriptionPackage']['description'],        	        	
        	'link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/users/member_login',
        	'plan_title' => $plan['title'],
			'plan_description' => $helper->getPlanDescription($plan, $subscribe['Subscribe']['currency_code'])
        );
        $mailComponent->send(array('User'=>$subscribe['User']),'subscription_cancel',$params);
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    }
    
    public function admin_active()
    {
    	$id = $this->request->data['id'];
    	$subscribe = $this->Subscribe->findById($id);
    	$result = array('status'=>0);
    	
    	if (!$subscribe)
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$helper->onSuccessful($subscribe,array(),0,false,true);
    	
    	$this->Session->setFlash(__('This Subscribe has been successfully active'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    }
    
	public function admin_inactive()
    {
    	$id = $this->request->data['id'];
    	$subscribe = $this->Subscribe->findById($id);
    	$result = array('status'=>0);
    	
    	if (!$subscribe)
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$result_respone = $helper->onInActive($subscribe);
    	
    	$this->Session->setFlash(__('Subscription inactivated'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    }
    
    public function admin_expired()
    {
    	$id = $this->request->data['id'];
    	$subscribe = $this->Subscribe->findById($id);
    	$result = array('status'=>0);
    	
    	if (!$subscribe)
    	{
    		echo json_encode($result);
            exit;
    	}
    	
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$helper->onExpire($subscribe,true);
    	
    	$this->Session->setFlash(__('Subscription exprired'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    }
    
    //////////////////////////////
    // subscription transaction//
    //////////////////////////////
    public function index()
    {
    	$this->clearSession();
        $this->compareData();
        
        if ($this->request->is('post')) 
        {
        	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
        	$plan = $this->SubscriptionPackagePlan->findById($this->request->data['plan_id']);
        	$currency = Configure::read('Config.currency');
        	if ($helper->isFreePlan($plan))
        	{
        		//cancel all subscription
				$subscribe = $this->Subscribe->find('first', array(
					'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1, 'Subscribe.status' => 'active'),
					'limit' => 1
				));
	         	$helper->inActiveAll($this->Auth->user('id'),$subscribe);		
        		
        		$currency = Configure::read('Config.currency');
        		$data = array('user_id' => $this->Auth->user('id'),
                         	'plan_id' => $this->request->data['plan_id'],
             		  		'package_id' => $plan['SubscriptionPackagePlan']['subscription_package_id'],
                          	'gateway_id' => 0,
                          	'status' => 'initial',
             		  		'currency_code' => $currency['Currency']['currency_code']);
        		
        		$this->Subscribe->save($data);
        		$item = $this->Subscribe->read();
        		$helper->onSuccessful($item);
        		
        		$this->redirect('/');	 
        	}
        	else 
        	{
        		$this->Session->write('plan_id',$this->request->data['plan_id']);
        		$this->redirect($this->furl.'gateway');
        	}
        }
    }
    
    public function success()
    {
    	 $subscribe_id = $this->Session->read('subscribe_id');
    	 if ($subscribe_id)
    	 {
    	 	$subscribe = $this->Subscribe->findById($subscribe_id);
    	 	if ($subscribe && $subscribe['Subscribe']['status'] == 'initial')
    	 	{
    	 		$this->Subscribe->id = $subscribe_id;
    	 		$this->Subscribe->save(array('status'=>'process'));
    	 	}
    	 }
    }
    
    public function cancel()
    {
    	 $subscribe_id = $this->Session->read('subscribe_id');
    	 if ($subscribe_id)
    	 {
    	 	$subscribe = $this->Subscribe->findById($subscribe_id);
    	 	if ($subscribe && $subscribe['Subscribe']['status'] == 'initial')
    	 	{
    	 		$this->Subscribe->id = $subscribe_id;
    	 		$this->Subscribe->save(array('status'=>'inactive'));
    	 	}
    	 }
    }
    
    public function gateway()
    {
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	//current package
		$subscribe_active = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.active'=>1,'Subscribe.user_id' => $this->Auth->user('id')),
			'limit' => 1,
			'order' => 'Subscribe.id DESC'
		));
		$this->set('subscribe_active',$subscribe_active);
    	
    	$this->Session->delete('gateway_id');
    	
    	if (!$this->Session->read('plan_id'))
    	{
    		$this->Session->setFlash(__( 'Please select a plan'), 'default', array('class' => 'Metronic-alerts alert alert-danger fade in','subscription' ));
    		$this->redirect($this->furl);
    		return;
    	}
    	$plan_id = $this->Session->read('plan_id');
    	$plan = $this->SubscriptionPackagePlan->findById($this->Session->read('plan_id'));
    	if (!$plan)
    	{
			$this->Session->setFlash(__( 'This plan does not exist'), 'default', array('class' => 'Metronic-alerts alert alert-danger fade in','subscription' ));
			$this->redirect($this->referer());
			return;    		
    	}

		$gateways = $this->Gateway->find('all', array('conditions' => array('enabled' => "1")));
    	$currency = Configure::read('Config.currency');
        if($this->request->is('post'))
        {
         	 $gateway_id = $this->request->data['gateway_id'];
         	 $gateway = $this->Gateway->findById($gateway_id);
         	 if (!$gateway)
         	 {
         	 	$this->Session->setFlash(__( 'Please select a gateway'), 'default', array('class' => 'Metronic-alerts alert alert-danger fade in','subscription'  ));
	    		$this->redirect($this->furl.'gateway');
	    		return;
         	 }
         	 //cancel all subscription
			 $subscribe = $this->Subscribe->find('first', array(
				'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1, 'Subscribe.status' => 'active'),
				'limit' => 1
			 ));
         	 $helper->inActiveAll($this->Auth->user('id'),$subscribe);
         	 
        	 //save subscribes
             $data = array('user_id' => $this->Auth->user('id'),
                          'plan_id' => $plan_id,
             			  'package_id' => $plan['SubscriptionPackagePlan']['subscription_package_id'],
                          'gateway_id' => $gateway_id,
                          'status' => 'initial',
             			  'currency_code' => $currency['Currency']['currency_code']);
             $this->Subscribe->clear();
             $this->Subscribe->save($data);
             $subscribe_id = $this->Subscribe->getLastInsertId();
             $this->Session->write('subscribe_id',$subscribe_id);
             
             $item = $this->Subscribe->read();
             if ($helper->isFreePlan($item))
             {
             	$helper->onSuccessful($item);
             	$this->redirect('/');
             	return;
             }
         	 
         	 $plugin = $gateway['Gateway']['plugin'];
		     $helperGateway = MooCore::getInstance()->getHelper($plugin.'_'.$plugin);

		     $this->redirect($helperGateway->getUrlProcess().'/Subscription_Subscribe/'.$subscribe_id);
		     return;
        } 
        $this->set('currency_code',$currency['Currency']['currency_code']);
        $this->set('gateways',$gateways);
        $this->set('plan',$plan);
    }

    public function upgrade()
    {
    	$subscribe = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1),
			'limit' => 1
		));
		
		if (!$subscribe)
		{
			$this->redirect($this->furl);
		}
    	
    	$this->clearSession();
        $this->compareData();
        
    	if ($this->request->is('post')) 
        {
        	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
        	$plan = $this->SubscriptionPackagePlan->findById($this->request->data['plan_id']);
        	$currency = Configure::read('Config.currency');
        	if ($helper->isFreePlan($plan))
        	{
        		//cancel all subscription
				$subscribe = $this->Subscribe->find('first', array(
					'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1, 'Subscribe.status' => 'active'),
					'limit' => 1
				));
	         	$helper->inActiveAll($this->Auth->user('id'),$subscribe);		
        		
        		$currency = Configure::read('Config.currency');
        		$data = array('user_id' => $this->Auth->user('id'),
                         	'plan_id' => $this->request->data['plan_id'],
             		  		'package_id' => $plan['SubscriptionPackagePlan']['subscription_package_id'],
                          	'gateway_id' => 0,
                          	'status' => 'initial',
             		  		'currency_code' => $currency['Currency']['currency_code']);
        		
        		$this->Subscribe->save($data);
        		$item = $this->Subscribe->read();
        		$helper->onSuccessful($item);
        		
        		$this->redirect('/');	 
        	}
        	else 
        	{
	        	$this->Session->write('plan_id',$this->request->data['plan_id']);
	        	$this->redirect($this->furl.'gateway');
        	}
        }
    }
    
    public function compare()
    {
        $this->compareData();
    }
    
    private function compareData()
    {
        //current package
		$subscribe = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.user_id' => $this->Auth->user('id')),
			'limit' => 1,
			'order' => 'Subscribe.id DESC'
		));
				
		$this->set('subscribe',$subscribe);
		$currency = Configure::read('Config.currency');		
		
		$viewer = MooCore::getInstance()->getViewer();
		$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
		if ($viewer['User']['has_active_subscription'])
		{
			list($columns,$compares) = $helper->getPackageSelect(2,$subscribe);	
		}
		else
		{
			list($columns,$compares) = $helper->getPackageSelect(1,$subscribe);
		}
        
        $this->set('columns', $columns);
        $this->set('compares', $compares);
        $this->set('currency', $currency);
    }
    
    private function clearSession()
    {
    	$this->Session->delete('subscribe_id');
        $this->Session->delete('plan_id');
    }
    
    public function cancel_recurring()
    {
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$subscribe = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1, 'Subscribe.status' => 'active'),
			'limit' => 1
		));
		
		$result = array('status'=>0);
		
		if (!$subscribe || !$helper->canCancel($subscribe))
		{
			echo json_encode($result);
            exit;
		}
		
		$result_api = $helper->onCancel($subscribe);
		if (!$result_api)
		{
			echo json_encode($result);
            exit;
		}
		
		$this->Session->setFlash(__('Your subscription has been canceled'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		
		//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $params = array(
        	'subscription_title' => $subscribe['SubscriptionPackage']['name'],
        	'subscription_description' => $subscribe['SubscriptionPackage']['description'],
        	'sender_title' => $subscribe['User']['name'],
        	'sender_link' => $http.'://'.$_SERVER['SERVER_NAME'].$subscribe['User']['moo_href'],
        	'reason' => $this->request->data['text_reason'],
        	'plan_title' => $subscribe['SubscriptionPackagePlan']['title'],
			'plan_description' => $helper->getPlanDescription($subscribe['SubscriptionPackagePlan'], $subscribe['Subscribe']['currency_code'])
        );
        $mailComponent->send(Configure::read('core.site_email'),'subscription_cancel_admin',$params);
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    } 
    
    public function request_refund()
    {
    	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
    	$subscribe = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.user_id' => $this->Auth->user('id'), 'Subscribe.active' => 1, 'Subscribe.status' => 'active'),
			'limit' => 1
		));
		
		$result = array('status'=>0);
		
		if (!$helper->canRefunded($subscribe))
		{
			echo json_encode($result);
            exit;
		}
		if (!isset($this->request->data['account']))
			$this->request->data['account'] = '';
		
		$data = array(
			'subscribe_id' => $subscribe['Subscribe']['id'],
			'plan_id' => $subscribe['Subscribe']['plan_id'],
			'user_id' => $this->Auth->user('id'),
			'account' => $this->request->data['account'],
			'reason' => $this->request->data['reason'],
			'transaction_id' => $subscribe['Subscribe']['transaction_id'],
		);
		$this->SubscriptionRefund->clear();
		$this->SubscriptionRefund->save($data);
		
		$this->Subscribe->id = $subscribe['Subscribe']['id'];
		$this->Subscribe->save(array('is_request_refund'=>1));
		
		$this->Session->setFlash(__('Your request for refund has been sent and is pending for approval'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));
		
		//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $params = array(
        	'subscription_title' => $subscribe['SubscriptionPackage']['name'],
        	'subscription_description' => $subscribe['SubscriptionPackage']['description'],
        	'sender_title' => $subscribe['User']['name'],
        	'sender_link' => $http.'://'.$_SERVER['SERVER_NAME'].$subscribe['User']['moo_href'],
        	'reason' => $this->request->data['reason'],
        	'plan_title' => $subscribe['SubscriptionPackagePlan']['title'],
			'plan_description' => $helper->getPlanDescription($subscribe['SubscriptionPackagePlan'], $subscribe['Subscribe']['currency_code'])
        );
        $mailComponent->send(Configure::read('core.site_email'),'subscription_refund_admin',$params);
		
		$result['status'] = 1;
		echo json_encode($result);
		exit;
    } 
    
	public function preview($type = null){
		//current package
		$subscribe = $this->Subscribe->find('first', array(
			'conditions' => array('Subscribe.user_id' => $this->Auth->user('id')),
			'limit' => 1,
			'order' => 'Subscribe.id DESC'
		));
        $currency = Configure::read('Config.currency');
        $helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
        list($columns,$compares) = $helper->getPackageSelect(1,$subscribe);
        $this->set('type',$type);      
        $this->set('columns', $columns);
        $this->set('compares', $compares);
        $this->set('currency', $currency);
    }
    
}
