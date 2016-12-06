<?php
	$uid = $this->Auth->user('id');
?>
<div class="content_center">
	<?php if ($check_see_status):?>
		<h2><?php if (isset($title)) echo $title; else echo __('Comments');?> (<span id="comment_count"><?php if (isset($comment_count)) echo $comment_count; else echo $subject[key($subject)]['comment_count']?></span>)</h2>
		<?php if (Configure::read('core.comment_sort_style') == COMMENT_RECENT): ?>
                    <?php if ($check_post_status || !$uid):?>
                        <?php echo $this->element( 'comment_form', array_merge($params,array( 'target_id' => $subject[key($subject)]['id'], 'type' => $subject[key($subject)]['moo_type']) )); ?>
                    <?php else:?>
                        <div><?php if(isset($text_post_error)) echo $text_post_error;?></div>
                    <?php endif;?>
                    <ul class="list6 comment_wrapper comment_list" id="comments">
                        <?php echo $this->element('comments',array('data'=>$data));?>
                    </ul>
                <?php elseif(Configure::read('core.comment_sort_style') == COMMENT_CHRONOLOGICAL): ?>
                    <ul class="list6 comment_wrapper comment_list" id="comments">
                        <?php echo $this->element('comments_chrono',array('data'=>$data));?>
                    </ul>
                    <?php if ($check_post_status || !$uid):?>
                        <?php echo $this->element( 'comment_form', array_merge($params,array( 'target_id' => $subject[key($subject)]['id'], 'type' => $subject[key($subject)]['moo_type']) )); ?>
                    <?php else:?>
                        <div><?php if(isset($text_post_error)) echo $text_post_error;?></div>
                    <?php endif;?>
                <?php endif; ?>
                
                
		
	<?php else:?>
		<?php if(isset($text_private_error)) echo $text_private_error; else echo __('This is private item');?>
	<?php endif;?>
</div>