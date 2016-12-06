<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooComment"], function($, mooComment) {
        mooComment.initOnAjaxLoadActivityCommentEdit();
    });
</script>
<?php else: ?>
<?php endif; ?>

<div id="activity_comment_edit_<?php echo $activity_comment['ActivityComment']['id']?>">
	<textarea id="message_activity_comment_edit_<?php echo $activity_comment['ActivityComment']['id']?>" name="message" ><?php echo $activity_comment['ActivityComment']['comment']?></textarea>
	<input type="hidden" value="<?php echo $activity_comment['ActivityComment']['thumbnail'];?>" name="comment_attach" id="activity_comment_attach_id_<?php echo $activity_comment['ActivityComment']['id']?>">
	<div <?php if ($activity_comment['ActivityComment']['thumbnail']) echo "style='display:none;'";?> id="activity_comment_attach_<?php echo $activity_comment['ActivityComment']['id'];?>"></div>
	<div id="activity_comment_preview_attach_<?php echo $activity_comment['ActivityComment']['id'];?>">
		<?php
			if ($activity_comment['ActivityComment']['thumbnail']): 
		?>
			<span style="background-image:url(<?php echo $this->Moo->getImageUrl($activity_comment);?>)"><a class="removePhotoComment" data-type="activity" data-id="<?php echo $activity_comment['ActivityComment']['id']?>" href="javascript:void(0);"><i class="material-icons thumb-review-delete">clear</i></span></a>
		<?php endif;?>
	</div>
	<div class="edit-post-action">
		<a class="button button-action cancelEditActivityComment" href="javascript:void(0);" data-id="<?php echo $activity_comment['ActivityComment']['id'];?>"><?php echo __('Cancel');?></a> 
                <a class="btn btn-action confirmEditActivityComment" href="javascript:void(0);" data-id="<?php echo $activity_comment['ActivityComment']['id'];?>"><?php echo __('Done Editing');?></a>
	</div>
</div>