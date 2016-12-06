<?php 
	$helperSubscription = MooCore::getInstance()->getHelper('Subscription_Subscription');
	$currency = Configure::read('Config.currency');
?>
<div class="bar-content  full_content p_m_10">
    <div class="content_center">
        <h1><?php echo __( 'Select Payment Gateway')?></h1>
        <?php echo $this->Session->flash('subscription');?>      
			<div>
        		<?php echo __('Your selected plan');?>: <b><?php echo $plan['SubscriptionPackage']['name'].' - '. $helperSubscription->getPlanDescription($plan['SubscriptionPackagePlan'],$currency['Currency']['currency_code']) ?></b>
        	</div> 
            <?php foreach($gateways as $gateway):
                $gateway = $gateway['Gateway'];
                $helper = MooCore::getInstance()->getHelper($gateway['plugin'].'_'.$gateway['plugin']);
                $is_recurring = $helperSubscription->isRecurring($plan);
				$is_trial = $helperSubscription->isTrial($plan);
                if ($helper->checkSupportCurrency($currency_code) && (!$is_trial || ($is_trial && $helper->supportTrial()) ) && (!$is_recurring || ($is_recurring && $helper->supportRecurring()) )):
            ?>
		            <form onSubmit="return submitGateway();" id="formGateway" method="post" action="<?php echo $this->request->base.$furl;?>gateway/">            
		            	<?php echo $this->Form->hidden('gateway_id', array('id' => 'gateway_id','value'=>$gateway['id'])); ?>
		                <h2><?php echo $gateway['name'];?></h2>
		                <p><?php echo $gateway['description'];?></p>
		                <input type="submit" class="btn btn-action btnGateway" value="<?php echo __( 'Pay with').' '.$gateway['name'];?>" />
		                <br/><br/>
		            </form>
            	<?php endif;?>
            <?php endforeach;?>
        
        <div id="formPayment"></div>
    </div>
</div>
<script>
function submitGateway()
{
	<?php if ($subscribe_active) :?>
		if(!confirm('<?php echo addslashes(__('Are you sure you would like to change your membership? If you click "Pay" button then your current membership will be inactived and you can not be undone.'));?>'))
			return false;
	<?php endif;?>
	return true;
}
</script>