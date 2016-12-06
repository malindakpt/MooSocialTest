


<?php
$subject = isset($data['subject']) ? $data['subject'] : MooCore::getInstance()->getSubject();
$historyModel = MooCore::getInstance()->getModel('CommentHistory');
if ( !empty( $data['comments'] ) ):
	foreach ($data['comments'] as $comment):
?>
	<li id="itemcomment_<?php echo $comment['Comment']['id']?>" style="position: relative">
		<?php 
                
		// delete link available for commenter, site admin and item author (except convesation)
		if ( ( $this->request->controller != Inflector::pluralize(APP_CONVERSATION) ) && ((!empty($subject) && $subject[key($subject)]['user_id'] == $uid) ||  $comment['Comment']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $data['admins'] ) && in_array( $uid, $data['admins'] ) ) ) ):
		?>		
			<div class="dropdown edit-post-icon comment-option">
			<a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
				<i class="material-icons">more_vert</i>
			</a>
			<ul class="dropdown-menu">
                                <?php if ($comment['Comment']['user_id'] == $uid || $cuser['Role']['is_admin'] ):?>
				<li><a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-photo-comment="0" class="editItemComment"><?php echo __('Edit Comment'); ?>
                                    </a>	
				</li>
                                <?php endif;?>
				<li>
                                    <?php $isTheaterMode = (!empty($blockCommentId) && $blockCommentId == 'theaterComments')? 1 : 0; ?>
                                    <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-photo-comment="<?php echo $isTheaterMode; ?>" class="removeItemComment" >
                                        <?php echo __('Delete Comment'); ?></a>
				</li>
				
				
			</ul>
		</div>
		<?php endif; ?>
		    
		<?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']), array('prefix' => '100_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
		<div class="comment hasDelLink">
			<div class="comment_message">
				<?php echo $this->Moo->getName($comment['User'])?>
				<span id="item_feed_comment_text_<?php echo $comment['Comment']['id']?>">
					<?php echo $this->viewMore( h($comment['Comment']['message']),null, null, null, true, array('no_replace_ssl' => 1))?>
					<?php if ($comment['Comment']['thumbnail']):?>
						<div class="comment_thumb">
							<a data-dismiss="modal" href="<?php echo $this->Moo->getImageUrl($comment,array());?>">
								<?php echo $this->Moo->getImage($comment,array('prefix'=>'200'));?>
							</a>
						</div>
					<?php endif;?>
				</span>
			</div>
			<div class="feed-time date">
				<?php echo $this->Moo->getTime( $comment['Comment']['created'], Configure::read('core.date_format'), $utz )?>
			<?php
                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "histories",
                                                                  "action" => "ajax_show",
                                                                  "plugin" => false,
                                                                  'comment',
                                                                  $comment['Comment']['id'],
                                                              )),
                                   'title' => __('Show edit history'),
                                   'innerHtml'=> $historyModel->getText('comment',$comment['Comment']['id']),
                                'id' => 'history_item_comment_'.$comment['Comment']['id'],
                                'class'=>'edit-btn',
                                'style' => empty($comment['Comment']['edited']) ? 'display:none' : '',
								'data-dismiss'=>'modal'
                           ));
                       ?>	
        <span>                    
				<?php if (empty($comment_type)): ?> 
	            <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="comment" data-status="1" id="comment_l_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $data['comment_likes'][$comment['Comment']['id']] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
	            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "likes",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'comment',
                                            $comment['Comment']['id'],
                                        )),
             'title' => __('People Who Like This'),
             'innerHtml'=> '<span id="comment_like_' . $comment['Comment']['id'] . '">' . $comment['Comment']['like_count'] . '</span>',
          'data-dismiss' => 'modal'
     ));
 ?>
    </span>
    <span>                
	                <?php if(empty($hide_dislike)): ?>
	            <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="comment" data-status="0" id="comment_d_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && isset( $data['comment_likes'][$comment['Comment']['id']] ) && $data['comment_likes'][$comment['Comment']['id']] == 0 ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>
	            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "likes",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'comment',
                                            $comment['Comment']['id'],1
                                        )),
             'title' => __('People Who Dislike This'),
             'innerHtml'=> '<span id="comment_dislike_' . $comment['Comment']['id'] . '">' .  $comment['Comment']['dislike_count'] . '</span>',
          'data-dismiss' => 'modal'
     ));
 ?>
                     <?php endif; ?>
	            <?php endif; ?> 
              </span>
            </div>
            
		</div>
	</li>
<?php
	endforeach;
endif;
?>

<?php if ($data['bIsCommentloadMore'] > 0): ?>

        <?php if (empty($blockCommentId)): ?>
            <?php $this->Html->viewMore($data['more_comments'],'comments') ?>
        <?php else: ?>
            <?php $this->Html->viewMore($data['more_comments'].'/id_content:'.$blockCommentId,$blockCommentId) ?>
        <?php endif; ?>

<?php endif; ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooComment"], function($,mooComment) {
        mooComment.initOnCommentListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooComment'), 'object' => array('$', 'mooComment'))); ?>
mooComment.initOnCommentListing();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>