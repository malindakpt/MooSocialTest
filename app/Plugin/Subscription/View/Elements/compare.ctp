<?php 
	$helper = MooCore::getInstance()->getHelper('Subscription_Subscription');
	$currency = Configure::read('Config.currency');
	$active = false;
?>
<div>
	<h1><?php echo __('Please select a new package') ?></h1>
	<?php if ($subscribe):?>
		<div>
			<div class="current_pack_title"><?php echo __('Current Package');?></div>
			<div class="current_pack_name"><?php echo $subscribe['SubscriptionPackage']['name'] ?></div>
			<div class="package_info">
			<div><span><?php echo __('Status').': </span>'.$helper->getTextStatus($subscribe);?></span></div>
			<?php if (in_array($subscribe['Subscribe']['status'],array('active','cancel'))):?>
				<?php $active = true;?>
				<div><?php echo __('Pay date').': '.$this->Time->format('m/d/Y H:i:s', $subscribe['Subscribe']['pay_date'],null, $utz )?></div>
				<?php if ($subscribe['Subscribe']['status'] == 'cancel' || ($subscribe['SubscriptionTransaction'] && $subscribe['SubscriptionTransaction']['admin']) || !$helper->isRecurring($subscribe)):?>
					<div><?php echo __('Expire date').': '.($subscribe['Subscribe']['expiration_date'] ? $this->Moo->getTime( $subscribe['Subscribe']['expiration_date'], Configure::read('core.date_format'), $utz ) : __('Forever'))?></div>
				<?php endif;?>
			<?php endif;?>
			</div>
		</div>
		<?php if ( $helper->canCancel($subscribe) || $helper->canRefunded($subscribe)):?>
			<div class="package-btn package_action">
				<?php if ($helper->canCancel($subscribe)):?>
					<a href="javascript:void(0);" onclick="cancelRecurring();"><?php echo __('Cancel this recurring');?></a>
					<div style="display:none;" id="content_cancel_recurring">
						<div class="bar-content full_content p_m_10">
							<div class="content_center">
								<div>
									<form>
										<div>
											<span><?php echo __('Are you sure to cancel your subscription? Your subscription will end on %s if canceled',$this->Moo->getTime( $subscribe['Subscribe']['expiration_date'], Configure::read('core.date_format'), $utz ));?></span><br>
											<b><?php echo __('Please provide reasons for canceling your subscription');?></b>												
										</div>
										<textarea style="width:100%" id="reason"></textarea>
										<div class="clear"></div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<script>
						function cancelRecurring()
						{
							$.fn.SimpleModal({
						        btn_ok: '<?php echo addslashes(__('OK'))?>',
						        btn_cancel: '<?php echo addslashes(__('Cancel'))?>',
						        title: '<?php echo addslashes(__('Cancel this recurring'))?>',
						        contents: $('#content_cancel_recurring').html(),
						        model: 'content'
						    }).addButton('<?php echo __('Ok');?>', "btn btn-action", function(e){								
								$('.simple-modal-footer .btn.btn-action').addClass('disabled');
								$('.simple-modal-footer .btn.btn-action').spin('small');
								var popup = this;
								$.post(mooConfig.url.base + "/subscription/subscribes/cancel_recurring", {text_reason:$('.simple-modal-body #reason').val()}, function(data){
									popup.hideModal();
									if (data.status)
									{
										window.location=window.location;
									}
									else
									{
										 $.fn.SimpleModal({
											btn_ok : '<?php echo addslashes(__('OK'))?>',
											model: 'modal',
											title: '<?php echo __('Warning');?>',
											contents: '<?php echo __('Error when canceling subscription');?>'
										}).showModal();
									}
								},'json');
							}).addButton('<?php echo __('Cancel');?>', "button button-action").showModal();
						}
					</script>
				<?php endif;?>
				<?php if ($helper->canRefunded($subscribe)):?>
					<a href="javascript:void(0);" onclick="requestRefund();"><?php echo __('Request a refund');?></a>
					<div style="display:none;" id="content_refund">
						<div class="bar-content full_content p_m_10">
							<div class="content_center">
								<div>
									<form>
										<div>
											<span><?php echo __('Are you sure to request a refund for your subscription?');?></span><br>
										</div>
										<div class="error-message hide" style=""><?php echo __('Account is required'); ?></div>
										<p><b><?php echo __('Account');?></b></p>
										<p>
											<input id="account" type="text">
										</p>
										<p><b><?php echo __('Please provide reasons for requesting a refund');?></b></p>
										<textarea style="width:100%" id="reason"></textarea>
										<div class="clear"></div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<script>
					function requestRefund()
					{
						$.fn.SimpleModal({
					        btn_ok: '<?php echo addslashes(__('OK'))?>',
					        btn_cancel: '<?php echo addslashes(__('Cancel'))?>',
					        title: '<?php echo addslashes(__('Request a refund'))?>',
					        contents: $('#content_refund').html(),
					        model: 'content'
					    }).addButton('<?php echo addslashes(__('Ok'));?>', "btn btn-action", function(e){																
							var popup = this;
							$('.simple-modal-body .error-message').addClass('hide');
							if ($('.simple-modal-body #account').val().trim() == '')
							{
								$('.simple-modal-body .error-message').removeClass('hide');
								return;
							}
							$('.simple-modal-footer .btn.btn-action').addClass('disabled');
							$('.simple-modal-footer .btn.btn-action').spin('small');
							$.post(mooConfig.url.base + "/subscription/subscribes/request_refund", {account:$('.simple-modal-body #account').val(),reason:$('.simple-modal-body #reason').val()}, function(data){
								popup.hideModal();
								if (data.status)
								{
									window.location=window.location;
								}
								else
								{
									 $.fn.SimpleModal({
										btn_ok : '<?php echo addslashes(__('OK'))?>',
										model: 'modal',
										title: '<?php echo addslashes(__('Warning'));?>',
										contents: '<?php echo addslashes(__('Error when canceling subscription'));?>'
									}).showModal();
								}
							},'json');
						}).addButton('<?php echo addslashes(__('Cancel'));?>', "button button-action").showModal();
					}
					</script>
				<?php endif;?>
			</div>
		<?php endif;?>
	<?php endif;?>
</div>
<?php 
	$element = (Configure::read('Subscription.select_theme_subscription_packages') ? 'Subscription.theme_compare' : 'Subscription.theme_default');
?>
<?php echo $this->element($element,array('type'=>'manage'));?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
	$('.button_select').click(function(){		
		package_id = $(this).attr('ref');
		$('#plan_id').val($('.package_manage_'+package_id).val());
		<?php if ($active):?>
		if ($(".package_manage_"+package_id + " option[value='"+$('.package_manage_'+package_id).val()+"']").attr('ref') > 0)
		{
			if(!confirm('<?php echo addslashes(__('Are you sure you would like to change your membership? If you click "Select" button then your current membership will be inactived and you can not be undone.'));?>'))
				return;
		}
		<?php endif;?>
		$('#plan_id').parent().submit();
	});
<?php $this->Html->scriptEnd(); ?>