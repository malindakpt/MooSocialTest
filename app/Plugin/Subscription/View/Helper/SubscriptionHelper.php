<?php
App::uses('AppHelper', 'View/Helper');
class SubscriptionHelper extends AppHelper {	
	protected $_enable = null;
	protected $_active_users = array();
	public function getParamsPayment($item)
	{		
		$subscribe = $item['Subscribe'];
		$plan = $item['SubscriptionPackagePlan'];
		$package = $item['SubscriptionPackage'];
		
		$url = Router::url('/',true);
		$first_amount = 0;
		switch ($plan['type']) 
		{
			case SUBSCRIPTION_ONE_TIME:
			case SUBSCRIPTION_RECURRING:
				$first_amount = $plan['price'];
			break;
			case SUBSCRIPTION_TRIAL_RECURRING:
			case SUBSCRIPTION_TRIAL_ONE_TIME:
				$first_amount = $plan['trial_price'];
			break;
			
		}
		$params = array(			
			'cancel_url' => $url.'subscription/subscribes/cancel',
			'return_url' => $url.'subscription/subscribes/success',
			'currency' => $subscribe['currency_code'],
			'description' => $package['name'].' - '.$this->getPlanDescription($plan, $subscribe['currency_code']),
			'type' => 'Subscription_Subscribe',
			'id' => $subscribe['id'],
			'is_recurring' => $this->isRecurring($item),
			'amount' => $plan['price'],
			'first_amount' => $first_amount,
			'end_date' => $this->calculateEndDate($plan),
			'trial_duration' => $plan['trial_duration'],
			'trial_duration_type' => $plan['trial_duration_type'],
			'total_amount' => $this->totalAmount($plan)
		); 
		return $params;
	}
	
	public function totalAmount($plan)
	{
		$total = 0;
		switch ($plan['type']) {
			case SUBSCRIPTION_ONE_TIME: 
				$total = $plan['price'];
				break;
			case SUBSCRIPTION_RECURRING:
				$cycle = $plan['billing_cycle'];
				$end = $this->getTotalTimeByType($plan['billing_cycle_type'],$plan['plan_type'], $plan['plan_duration']);				
				if ($cycle && $end)
				{
					$total = floor($end/$cycle)*$plan['price'];
				}			
				break;
			case SUBSCRIPTION_TRIAL_RECURRING:
				$cycle = $plan['billing_cycle'];
				$end = $this->getTotalTimeByType($plan['billing_cycle_type'],$plan['plan_type'], $plan['plan_duration']);				
				$total = $plan['trial_price'];
				if ($cycle && $end)
				{
					$total += floor($end/$cycle)*$plan['price'];					
				}				
				
				if ($plan['plan_type'] == 'forever')
				{
					$total = 0;
				}
				break;
			case SUBSCRIPTION_TRIAL_ONE_TIME:
				$total = $plan['trial_price'] + $plan['price'];
				break;
		}
		return $total;
	}
	
	public function getTotalTimeByType($type,$duration_type, $duration)
	{
		$result = 0;
        switch($type)
        {
        	case 'day':
        		switch ($duration_type) {
        			case 'day':
        				$result = $duration;
        				break;
        			case 'week':
		                $result = $duration * 7;
		                break;
		            case 'month':
		                $result = $duration * 30;
		                break;
		            case 'year':
		                $result = $duration * 365;
		                break;
        		}
        		
        		break;
            case 'week':
        		switch ($duration_type) {
        			case 'week':
        				$result = $duration;
        				break;        		
		            case 'month':
		                $result = $duration * 4;
		                break;
		            case 'year':
		                $result = floor($duration * (365 / 7));
		                break;
        		}
                break;
            case 'month':
        		switch ($duration_type) {
        			case 'month':
        				$result = $duration;
        				break;        		
		            case 'year':
		                $result = $duration * 12;
		                break;
        		}
                break;
            case 'year':
        		switch ($duration_type) {
		            case 'year':
		                $result = $duration;
		                break;
        		}
                break;
        }
        
        return $result;
	}
	
	
	public function getSubscribeActive($cuser,$is_active = true)
	{
		if (!$cuser)
			return true;

		if (!$this->checkEnableSubscription())
		{
			return true;
		}
		if (isset($cuser['Role']) &&  $cuser['Role']['is_super'] == 1)
        {
           	return true;
        }
        
		if (isset($this->_active_users[$cuser['id']]))
		{
			$active = $this->_active_users[$cuser['id']];
		}
		else
		{			
			$active = Cache::read('subscription_active_'.$cuser['id'], 'subscription');
			if (!$active) 
			{
				$subscribeModel = MooCore::getInstance()->getModel('Subscription_Subscribe');
				$active = $subscribeModel->find('first',array(
					'conditions' => array(
						'Subscribe.user_id' => $cuser['id'], 
						'Subscribe.status' => array('active','cancel','process'),
					),
					'order'=>'Subscribe.id DESC'
				));
			}
			Cache::write('subscription_active_'.$cuser['id'],$active, 'subscription');
		}
		$this->_active_users[$cuser['id']] = $active;
		if ($is_active)
		{
			if ($active)
			{
				if (in_array($active['Subscribe']['status'],array('active','cancel')))
				{
					return $active;
				}
				else
				{
					return false;
				}
			}
		}
		
		return $active;
	}
	
	public function getPlanDescription($plan,$currency)
    {
        $info = '';
        switch ($plan['type']) {
        	case SUBSCRIPTION_ONE_TIME:
        		if ($plan['price'] > 0)
        		{
        			$info = $plan['price'].' '.$currency.' '.__('for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		else
        		{
        			$info = __('free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		break;
        	case SUBSCRIPTION_RECURRING:
        		if ($plan['price'] > 0)
        		{
        			$info = $plan['price'].' '.$currency.' '.__('for each').' '.$this->getTextDuration($plan['billing_cycle'], $plan['billing_cycle_type']).' '.__('in').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		else 
        		{
        			$info = __('free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		break;
        	case SUBSCRIPTION_TRIAL_RECURRING:
        		if ($plan['trial_price'] == 0 && $plan['price'] == 0)
        		{
        			$info = __('free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		else
        		{
	        		if ($plan['trial_price'] > 0)
	        		{
	        			$info = $plan['trial_price'].' '.$currency.' '.__('for first').' '.$this->getTextDuration($plan['trial_duration'], $plan['trial_duration_type']);
	        		}
	        		else
	        		{
	        			$info = __('free for first').' '.$this->getTextDuration($plan['trial_duration'], $plan['trial_duration_type']);
	        		}
	        		
	        		if ($plan['price'] > 0)
	        		{
	        			$info.= ' '.__('then').' '.$plan['price'].' '.$currency.' '.__('for each').' '.$this->getTextDuration($plan['billing_cycle'], $plan['billing_cycle_type']).' '.__('in').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
	        		}
	        		else
	        		{
	        			$info.= ' '.__('then free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
	        		}
        		}
        		break;
        	case SUBSCRIPTION_TRIAL_ONE_TIME:
        		if ($plan['trial_price'] == 0 && $plan['price'] == 0)
        		{
        			$info = __('free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
        		}
        		else
        		{
        			if ($plan['trial_price'] > 0)
	        		{
	        			$info = $plan['trial_price'].' '.$currency.' '.__('for first').' '.$this->getTextDuration($plan['trial_duration'], $plan['trial_duration_type']);
	        		}
	        		else
	        		{
	        			$info = __('free for first').' '.$this->getTextDuration($plan['trial_duration'], $plan['trial_duration_type']);
	        		}
	        		
	        		if ($plan['price'] > 0)
	        		{
	        			$info .= ' '.__('then ').$plan['price'].' '.$currency.' '.__('for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
	        		}
	        		else
	        		{
	        			$info .= __('free for').' '.$this->getTextDuration($plan['plan_duration'], $plan['plan_type']);
	        		}
        		}
        		
        }
        
        return $info;
    }
    
    public function getTextDuration($num,$type)
    {
    	switch ($type) {
    		case 'forever': return __('forever');
    			break;
    		case 'day': return $num.' '.__('day(s)');
    			break;
    		case 'week': return $num.' '.__('week(s)');
    			break;
    		case 'month': return $num.' '.__('month(s)');
    			break;
    		case 'year': return $num.' '.__('year(s)');
    			break;
    	}
    }
	
	public function checkEnableSubscription()
	{
		if ($this->_enable !== null)
		{
			return $this->_enable;	
		}
		
		if (!Configure::read('Subscription.enable_subscription_packages'))
		{
			$this->_enable = false;
			return false;
		}
			
		$gateway = MooCore::getInstance()->getModel('PaymentGateway.Gateway');
		$subscriptionPackagePlan = MooCore::getInstance()->getModel('Subscription.SubscriptionPackagePlan');
			
        if (!$gateway->hasAny(array('enabled' => 1))){
        	$this->_enable = false;
            return false;
        }
        
        if (!$subscriptionPackagePlan->hasAny(array('deleted' => 0,'enable_plan' => 1))){
        	$this->_enable = false;
            return false;
        }
        $this->_enable = true;
        return true;
	}
	
	public function isFreePlan($plan)
	{		
		if($plan['SubscriptionPackagePlan']['price'] == 0 &&  $plan['SubscriptionPackagePlan']['trial_price'] == 0)
        {
            return true;
        }
        return false;
	}
	
	public function onFailure($item,$data)
	{
		$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
		$transactionModel = MooCore::getInstance()->getModel('Subscription.SubscriptionTransaction');
		
		$data = array('user_id' => $item['Subscribe']['user_id'],
                          'subscribes_id' => $item['Subscribe']['id'],
						  'package_id' => $item['Subscribe']['package_id'],
						  'plan_id' => $item['Subscribe']['plan_id'],
						  'status' => 'failed',						  
						  'callback_params' => json_encode($data)
				);
             
        $transactionModel->save($data);
        
        $subscribeModel->id = $item['Subscribe']['id'];
		$subscribeModel->save(array('status'=>'pending','transaction_id'=>$transactionModel->id));
        
        $plan = $item['SubscriptionPackagePlan'];
		$package = $item['SubscriptionPackage'];
        
        //Send email
        $ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $request = Router::getRequest();
        $params = array(
        	'subscription_title' => $package['name'],
        	'subscription_description' => $package['description'],
        	'link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/subscription/subscribes',
        	'plan_title' => $plan['title'],
			'plan_description' => $this->getPlanDescription($plan, $item['Subscribe']['currency_code'])
        );
        $mailComponent->send($item['User']['email'],'subscription_pending',$params);
	}
	
	public function onExpire($item, $expire = false)
	{
		$plan = $item['SubscriptionPackagePlan'];
		$package = $item['SubscriptionPackage'];
		
		$transaction = $item['SubscriptionTransaction'];
		if ($transaction)
		{
			$gateway = $item['Gateway']['plugin'];
			$helper = MooCore::getInstance()->getHelper($gateway.'_'.$gateway);
			
			if (!$expire)
			{
				if (($this->isRecurring($item) && $item['Subscribe']['status'] == 'active') || $item['SubscriptionPackagePlan']['type'] == SUBSCRIPTION_TRIAL_ONE_TIME)
				{
					if ($item['Subscribe']['end_date'])
					{
						$time_end = strtotime($item['Subscribe']['end_date']);
					}
					$time_end = time() + 1000;
					if (method_exists($helper, 'expire') && $time_end > time() )
					{
						$result = $helper->expire('Subscription_Subscribe',$item['Subscribe']['id'],json_decode($transaction['callback_params'],true));
						if ($result)
						{
							return;
						}
					}
				}
			}
			
			if (method_exists($helper, 'cancelExpire'))
			{
				$helper->cancelExpire(json_decode($transaction['callback_params'],true));
			}
		}
		$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
		
		$subscribeModel->id = $item['Subscribe']['id'];
		$subscribeModel->save(array('status'=>'expired','active'=>0,'transaction_id'=>0));
		
		//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $request = Router::getRequest();
        $params = array(
        	'subscription_title' => $package['name'],
        	'subscription_description' => $package['description'],
        	'expire_time' => $item['Subscribe']['expiration_date'],
        	'link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/subscription/subscribes',
        	'plan_title' => $plan['title'],
			'plan_description' => $this->getPlanDescription($plan, $item['Subscribe']['currency_code'])
        );
        $mailComponent->send($item['User']['email'],'subscription_expire',$params);
		
	}
	
	public function onSuccessful($item,$data = array(),$price = 0,$recurring = false,$admin = 0)
	{
		$plan = $item['SubscriptionPackagePlan'];
		$package = $item['SubscriptionPackage'];
		$subscribe = $item['Subscribe'];
		$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
		$userModel = MooCore::getInstance()->getModel('Core.User');
		$transactionModel = MooCore::getInstance()->getModel('Subscription.SubscriptionTransaction');
		$expire = $this->getTimeExpire($plan, $subscribe);	
		
		//Update subscribe
		$subscribeModel->id = $subscribe['id'];
		$data_sub = array(
			'status' => 'active',
			'is_trial'=> 0,
			'active' => 1,
			'expiration_date' => $expire,			
			'pay_date' => date('Y-m-d H:i:s'),
			'is_warning_email_sent' => 0,
		);		
		
		if ($plan['expiration_reminder'])
		{
			$data_sub['reminder_date'] = $this->calculateReminderDate($plan['expiration_reminder_type'], $plan['expiration_reminder'],$expire);	
		}

		$data_sub['end_date'] = $this->calculateEndDate($plan);

		//Insert tranaction
		$data = array('user_id' => $item['Subscribe']['user_id'],
                          'subscribes_id' => $item['Subscribe']['id'],
						  'package_id' => $item['Subscribe']['package_id'],
						  'plan_id' => $item['Subscribe']['plan_id'],
						  'status' => 'completed',					
						  'amount' => $price,
						  'currency' => $subscribe['currency_code'],	  
						  'callback_params' => json_encode($data),
						  'gateway_id' => $item['Subscribe']['gateway_id'],
						  'admin' => $admin
				);
        $transactionModel->clear();
        $transactionModel->save($data);
        
        $data_sub['transaction_id'] = $transactionModel->id;
        $subscribeModel->save($data_sub);
        
        //Update user role
        $userModel->id = $subscribe['user_id'];
        $userModel->save(
        	array(
        		'role_id' => $package['role_id'],
        		'has_active_subscription' => 1
        	)
        );
        
        //Send email
        $ssl_mode = Configure::read('core.ssl_mode');
	    $http = (!empty($ssl_mode)) ? 'https' :  'http';
	    $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
	    $request = Router::getRequest();
	    $params = array(
        	'subscription_title' => $package['name'],
        	'subscription_description' => $package['description'],
        	'login_link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/users/member_login',
	    	'plan_title' => $plan['title'],
	    	'plan_description' => $this->getPlanDescription($plan, $item['Subscribe']['currency_code'])
        );
        if (!$recurring)
        {	        	        
	        $mailComponent->send($item['User']['email'],'subscription_activated',$params);
        }
        else
        {
        	$mailComponent->send($item['User']['email'],'subscription_recurrence',$params);
        }
	}
	
	public function calculateEndDate($plan)
	{
		$end_date = '';
		if ($plan['trial_duration'])
		{
			$end_date = $this->calculateTime($plan['trial_duration_type'], $plan['trial_duration']);
		}
		if ($plan['plan_type'] != 'forever')
		{
			$end_date = $this->calculateTime($plan['plan_type'], $plan['plan_duration'],$end_date);
			return date('Y-m-d H:i:s',strtotime("-1 hours",strtotime($end_date)));
		}
		return '';
	}
	
	public function calculateTime($type,$duration,$time = '')
	{
		if ($time == '')
			$time = date("Y-m-d H:i:s");
			
		$result = '';
        switch($type)
        {
        	case 'day':
        		$result = strtotime("+$duration Day",strtotime($time));
        		break;
            case 'week':
                $result = strtotime("+$duration Week",strtotime($time));
                break;
            case 'month':
                $result = strtotime("+$duration Month",strtotime($time));
                break;
            case 'year':
                $result = strtotime("+$duration Year",strtotime($time));
                break;
        }
		
		return date('Y-m-d H:i:s', $result);
	}
	
	public function getTimeExpire($plan,$subscribe)
	{
		$expire = '';
		switch ($plan['type']) {
			case SUBSCRIPTION_ONE_TIME: 
				$expire = $this->calculateExpirationDate($plan['plan_type'], $plan['plan_duration']);
				break;
			case SUBSCRIPTION_RECURRING:
				$expire = $this->calculateExpirationDate($plan['billing_cycle_type'], $plan['billing_cycle']);
				break;
			case SUBSCRIPTION_TRIAL_RECURRING:
				if ($subscribe['is_trial'])
					$expire = $this->calculateExpirationDate($plan['trial_duration_type'], $plan['trial_duration']);
				else
					$expire = $this->calculateExpirationDate($plan['billing_cycle_type'], $plan['billing_cycle']);
				break;
			case SUBSCRIPTION_TRIAL_ONE_TIME:
				if ($subscribe['is_trial'])
					$expire = $this->calculateExpirationDate($plan['trial_duration_type'], $plan['trial_duration']);
				else
					$expire = $this->calculateExpirationDate($plan['plan_type'], $plan['plan_duration']);
				break;
		}
		
		return $expire;
	}
	
	public function calculateReminderDate($type,$duration,$expire)
	{
		if (!$expire)
			return '';
			
		$reminder_date = '';
        switch($type)
        {
        	case 'day':
        		$reminder_date = strtotime("-$duration Day",strtotime($expire));
        		break;
            case 'week':
                $reminder_date = strtotime("-$duration Week",strtotime($expire));
                break;
            case 'month':
                $reminder_date = strtotime("-$duration Month",strtotime($expire));
                break;
            case 'year':
                $reminder_date = strtotime("-$duration Year",strtotime($expire));
                break;
        }
        return $reminder_date != '' ? date('Y-m-d H:i:s', $reminder_date) : '';
	}

	public function calculateExpirationDate($type, $duration)
    {
        $expiration_date = '';
        switch($type)
        {
        	case 'day':
        		$expiration_date = strtotime("+$duration Day");
        		break;
            case 'week':
                $expiration_date = strtotime("+$duration Week");
                break;
            case 'month':
                $expiration_date = strtotime("+$duration Month");
                break;
            case 'year':
                $expiration_date = strtotime("+$duration Year");
                break;
            case 'forever':
                $expiration_date = '';
        }
        return $expiration_date != '' ? date('Y-m-d H:i:s', $expiration_date) : '';
    }
    
    public function onCancel($item)
    {
    	$transactionModel = MooCore::getInstance()->getModel('Subscription.SubscriptionTransaction');
		$transaction = $item['SubscriptionTransaction'];
		
		if ($transaction)
		{
	    	$gateway = $item['Gateway']['plugin'];
			$helper_gateway = MooCore::getInstance()->getHelper($gateway.'_'.$gateway);	
			if ($helper_gateway && method_exists($helper_gateway, 'cancelRecurring'))
			{
				$result_cancel = $helper_gateway->cancelRecurring(json_decode($transaction['callback_params'],true));			
				if (!$result_cancel)
				{
					return false;
				}
			}
		}
		$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
		$subscribeModel->id = $item['Subscribe']['id'];
		$subscribeModel->save(array('status'=>'cancel','transaction_id'=>0));
		
		return true;
    }
    
    public function inActiveAll($user_id,$item = null)
    {
   		$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');		
		
    	$conditions = array(
			'Subscribe.active' => 1,
    		'Subscribe.user_id' => $user_id
  		);
  		
  		if ($item)
  		{
  			$conditions['Subscribe.plan_id'] = $item['Subscribe']['plan_id'];
  		}
		
    	$list = $subscribeModel->find('all',array(
			'conditions' => $conditions
		));
		
		foreach ($list as $item)
		{
			$this->onInActive($item);
		}
    }
    
    public function onInActive($item)
    {
    	$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
		
		$subscribeModel->id = $item['Subscribe']['id'];
		$subscribeModel->save(array('status'=>'inactive','active'=>0,'transaction_id'=>0));
				
		$transaction = $item['SubscriptionTransaction'];
		
		if ($transaction)
		{
			$gateway = $item['Gateway']['plugin'];
			$helper = MooCore::getInstance()->getHelper($gateway.'_'.$gateway);
			
			if ($helper && method_exists($helper, 'cancel'))
			{
				$helper->cancel(json_decode($transaction['callback_params'],true));
			}
		}
    }
    
    public function doRefund($item,$refund = null)
    {
    	$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
    	
		$transaction = $item['SubscriptionTransaction'];
		
		if ($transaction)
		{
			$gateway = $item['Gateway']['plugin'];
			$helper = MooCore::getInstance()->getHelper($gateway.'_'.$gateway);
			$refundModel = MooCore::getInstance()->getModel('Subscription.SubscriptionRefund');			
			
			if ($helper && method_exists($helper, 'refund'))
			{
				$refund_accept = $helper->refund(json_decode($transaction['callback_params'],true)); //1 waiting callback. 2 no waiting			
				if (!$refund_accept)
				{
					return false;
				}
				if ($refund_accept == 1)
				{
					$refundModel->id = $refund['SubscriptionRefund']['id'];
					$refundModel->save(array('status'=>'process'));
				}
				else 
				{
					$this->onRefund($item,$refund);
				}
			}
			else
			{
				$this->onRefund($item,$refund);
			}
			
			return true;
		}
		
		return false;
    }
    
    public function onRefundFailed($item,$refund = null)
    {
    	$refundModel = MooCore::getInstance()->getModel('Subscription.SubscriptionRefund');	
    	if (!$refund)
    	{
    		$refund = $refundModel->find('first',array(
    			'conditions'=>array(
    				'SubscriptionRefund.subscribe_id' => $item['Subscribe']['id'],
    				'SubscriptionRefund.status' => 'process',
    			)
    		));
    		
    		if (!$refund)
    			return;
    	}
    	
    	$refundModel->id = $refund['SubscriptionRefund']['id'];
		$refundModel->save(array('status'=>'failed'));
    }
    
    public function onRefund($item,$refund = null)
    {
    	$refundModel = MooCore::getInstance()->getModel('Subscription.SubscriptionRefund');	
    	if (!$refund)
    	{
    		$refund = $refundModel->find('first',array(
    			'conditions'=>array(
    				'SubscriptionRefund.subscribe_id' => $item['Subscribe']['id'],
    				'SubscriptionRefund.status' => 'process',
    			)
    		));
    		
    		/*if (!$refund)
    			return;*/
    	}
    	
    	if ($refund)
    	{
	    	$refundModel->id = $refund['SubscriptionRefund']['id'];
			$refundModel->save(array('status'=>'completed'));
    	}
    	
    	$subscribeModel = MooCore::getInstance()->getModel('Subscription.Subscribe');
    	
		$transaction = $item['SubscriptionTransaction'];
		
		if ($transaction)
		{
			$gateway = $item['Gateway']['plugin'];
			$helper = MooCore::getInstance()->getHelper($gateway.'_'.$gateway);
			
			if ($helper && method_exists($helper, 'cancel'))
			{
				$helper->cancel(json_decode($transaction['callback_params'],true));
			}
		}

		$subscribeModel->id = $item['Subscribe']['id'];
		$subscribeModel->save(array('status'=>'refunded','active'=>0,'transaction_id'=>0));
		
		$transactionModel = MooCore::getInstance()->getModel('Subscription.SubscriptionTransaction');
		$transactionModel->clear();
		$data = array('user_id' => $item['Subscribe']['user_id'],
                  'subscribes_id' => $item['Subscribe']['id'],
				  'package_id' => $item['Subscribe']['package_id'],
				  'plan_id' => $item['Subscribe']['plan_id'],
				  'status' => 'completed',					
				  'amount' => $transaction['amount'],
				  'currency' => $item['Subscribe']['currency_code'],	  
				  'gateway_id' => $item['Subscribe']['gateway_id'],
				  'type' => 'pay'
		);
		$transactionModel->save($data);
		
		$plan = $item['SubscriptionPackagePlan'];
		$package = $item['SubscriptionPackage'];
		$subscribe = $item['Subscribe'];
		
		//Send email
		$ssl_mode = Configure::read('core.ssl_mode');
        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        $mailComponent = MooCore::getInstance()->getComponent('Mail.MooMail');
        $request = Router::getRequest();
        $params = array(
        	'subscription_title' => $item['SubscriptionPackage']['name'],
        	'subscription_description' => $item['SubscriptionPackage']['description'],        	        	
        	'link' => $http.'://'.$_SERVER['SERVER_NAME'].$request->base.'/users/member_login',
        	'plan_title' => $plan['title'],
			'plan_description' => $this->getPlanDescription($plan, $item['Subscribe']['currency_code'])
        );
        $mailComponent->send(array('User'=>$item['User']),'subscription_refund_accept',$params);
    }
    
    public function canActive($item)
    {
    	if ($item['Subscribe']['status'] == 'active' || $item['Subscribe']['status'] == 'cancel')
    	{
    		return false;
    	}

		return true;
    }
    
    public function canCancel($item)
    {
    	if ($item['Subscribe']['status'] != 'active')
    	{
    		return false;
    	}
    	
    	if ($item['Subscribe']['is_request_refund'])
    	{
    		return false;
    	}
		$transaction = $item['SubscriptionTransaction'];
		
		if (!$this->isRecurring($item))
		{
			return false;
		}
		
		if (!$transaction)
		{
			return false;
		}
		$params = json_decode($transaction['callback_params'],true);
		if (!count($params))
		{
			return false;
		}
		
		return true;
    }
    
    public function canRefunded($item)
    {
    	if ($item['Subscribe']['status'] != 'active' && $item['Subscribe']['status'] != 'cancel')
    	{
    		return false;
    	}
    	
    	if ($item['Subscribe']['is_request_refund'])
    	{
    		return false;
    	}
    	
    	$transaction = $item['SubscriptionTransaction'];

		if (!$transaction)
		{
			return false;
		}
		
		return  $transaction['amount'] > 0;
    }
    
    public function getTextStatus($item)
    {
    	switch ($item['Subscribe']['status']) {
    		case 'initial': return __('Initial');
    		case 'active': return __('Active');
    		case 'pending': return __('Pending');
    		case 'reversed': return __('Reversed');
    		case 'expired': return __('Expired');
    		case 'refunded': return __('Refunded');
    		case 'failed': return __('Failed');
    		case 'free': return __('Free');
    		case 'cancel': return __('Cancel');
    		case 'process' : return __('Process');
    		case 'inactive' : return __('Inactive');
    	}
    }
    
    public function getListStatus($type)
    {
    	switch ($type) {
    		case 'Subscribe':
    			return array(
    				'initial' => __('Initial'),
    				'active' => __('Active'),
    				'pending' => __('Pending'),
    				'expired' => __('Expired'),
    				'refunded' => __('Refunded'),
    				'failed' => __('Failed'),
    				'cancel' => __('Cancel Recurring'),
    				'process' => __('Process'),
    				'inactive' => __('Inactive'),
    			);    		
    		break;
    		case 'SubscriptionTransaction':
    			return array(				    				
    				'completed' => __('Paid'),
    				'failed' => __('Failed'),
    				'pending' => __('Pending'),
    			);    		
    		break;
    		case 'SubscriptionRefund':
    			return array(
    				'initial' => __('Waiting'),
    				'denied' => __('Denied'),
    				'completed' => __('Refuned'),
    				'process' => __('Process'),
    				'failed' => __('Failed')
    			);    		
    		break;
    		
    	} 
    }
    
    public function getTextStatusRefund($item)
    {
    	switch ($item['SubscriptionRefund']['status']) {
    		case 'initial': return __('Waiting');
    		case 'denied': return __('Denied');
    		case 'completed': return __('Refunded');
    		case 'process': return __('Process');    
    		case 'failed': return __('Failed');        		
    	}
    }
    
    public function getTextStatusTransaction($item)
    {
    	switch ($item['SubscriptionTransaction']['status']) {
    		case 'initial': return __('Initial');
    		case 'pending': return __('Pending');
    		case 'expired': return __('Expired');    		
    		case 'refunded': return __('Refunded');
    		case 'failed': return __('Failed');
    		case 'cancel': return __('Cancel');
    		case 'inactive': return __('Inactive');
    		case 'completed': return __('Paid');
    	}
    }
    public function getTextTypeTransaction($item)
    {
    	switch ($item['SubscriptionTransaction']['type']) {
    		case 'receive': return __('Receive');
    		case 'pay': return __('Pay');
    	}
    }
    
    public function isRecurring($item)
    {
    	return (in_array($item['SubscriptionPackagePlan']['type'], array(SUBSCRIPTION_RECURRING,SUBSCRIPTION_TRIAL_RECURRING)) ? true : false);
    }

	public function isTrial($item)
	{
		return (in_array($item['SubscriptionPackagePlan']['type'], array(SUBSCRIPTION_TRIAL_RECURRING,SUBSCRIPTION_TRIAL_ONE_TIME)) ? true : false);
	}
    
    public function getPackageSelect($type = '1',$subscribe = null) // 1: signup - 2: update
    {
    	$subscriptionPackageModel = MooCore::getInstance()->getModel('Subscription.SubscriptionPackage');
    	$subscriptionPackagePlanModel = MooCore::getInstance()->getModel('Subscription.SubscriptionPackagePlan');
    	$conditions = array(
    			'SubscriptionPackagePlan.deleted <> ' => 1,
	    		'SubscriptionPackagePlan.show_at LIKE ' => '%'.$type.'%',
	    		'SubscriptionPackagePlan.enable_plan' => 1    			
    	);
    	if ($subscribe && $subscribe['Subscribe'] && $subscribe['Subscribe']['active'])
    	{
    		$conditions['SubscriptionPackagePlan.id <> '] = $subscribe['Subscribe']['plan_id'];
    	}
    	
    	$plans = $subscriptionPackagePlanModel->find('all',array(
    		'conditions' =>  $conditions
    	));
    	
    	if (!$plans)
    	{
    		return array(null,null);
    	}
    	
    	$subscriptionCompareModel = MooCore::getInstance()->getModel('Subscription.SubscriptionCompare');
    	 
    	
    	$old = $subscriptionPackageModel->hasMany;
    	$subscriptionPackageModel->hasMany['SubscriptionPackagePlan']['conditions'] = $conditions;
    	$columns = $subscriptionPackageModel->find('all',array(
    		'conditions' => array(
    			'SubscriptionPackage.deleted <> ' => 1	
    		),
    		'order' => array('SubscriptionPackage.ordering ASC')
    	));    	
    	$subscriptionPackageModel->hasMany = $old;
    	
        $compares = $subscriptionCompareModel->find('all');
        if($compares != null)
        {
            foreach($compares as $k => $v)
            {
                $compares[$k]['SubscriptionCompare']['compare_value'] = json_decode($v['SubscriptionCompare']['compare_value'], true);
            }
        }
        
        return array($columns,$compares);
    }
}
