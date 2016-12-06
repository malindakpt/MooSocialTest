<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>
    <script>
function respondRequest(id, status)
{
	jQuery.post('<?php echo $this->request->base?>/groups/ajax_respond', {id: id, status: status}, function(data){
		jQuery('#request_'+id).html(data);
	});
    var request_count = parseInt(jQuery("#join-request").attr("data-request"));
    request_count = request_count - 1;
    if(request_count == 0)
        jQuery("#join-request").parent().remove();
    else if(request_count == 1)
        jQuery("#join-request").html(request_count+' <?php echo addslashes(__('join request'));?>');
    else
        jQuery("#join-request").html(request_count+' <?php echo addslashes(__('join requests'));?>');
    jQuery("#join-request").attr("data-request",request_count);
}

</script>

<?php if (empty($requests)): echo '<div align="center">' . __( 'No join requests') . '</div>';
else: ?>
<div class="title-modal">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo __('Close');?></span></button>
    <h4 class="modal-title" id="myModalLabel"><?php echo __('Join Requests');?></h4>
</div>
<div class="modal-body">
<ul class="list6 comment_wrapper" style="margin-top:0">
<?php foreach ($requests as $request): ?>
	<li id="request_<?php echo $request['GroupUser']['id']?>">
		<div style="float:right">
		    <a href="javascript:void(0)" onclick="respondRequest(<?php echo $request['GroupUser']['id']?>, 1)" class="button button-action"><?php echo __( 'Accept')?></a>
		    <a href="javascript:void(0)" onclick="respondRequest(<?php echo $request['GroupUser']['id']?>, 0)" class="button button-caution"><?php echo __( 'Delete')?></a>
		</div>
		<?php echo $this->Moo->getItemPhoto(array('User' => $request['User']), array( 'prefix' => '100_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
		<div class="comment">
			<?php echo $this->Moo->getName($request['User'])?><br />
			<span class="date"><?php echo $this->Moo->getTime( $request['GroupUser']['created'], Configure::read('core.date_format'), $utz )?></span>
		</div>
	</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>