
<?php
$activitiesParams = array(
    'request_base'=>((!empty($this->request->base))? $this->request->base : ''),
    );

$this->addPhraseJs(array(
    'confirm'=>__('Confirm'),
    'remove_tags'=>__('Remove Tags'),
    'remove_tags_contents'=>__('You wont be tagged in this post anymore. It may appear in other places like New Feed or search.'),
    'ok'=>__('Ok'),
    'cancel'=>__('Cancel'),
    'please_confirm'=>__('Please Confirm'),
    'please_confirm_remove_this_activity'=>__('Are you sure you want to remove this activity?'),
));
?>

<?php if($this->request->is('ajax')): ?>
<script>
    require(["jquery","mooActivities", "mooEmoji"], function($, mooActivities, mooEmoji) {
        var activitiesParams = '<?php echo json_encode($activitiesParams,true); ?>';
        mooActivities.init(activitiesParams);
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false,'requires'=>array('jquery', 'mooActivities', 'mooEmoji'),'object'=>array('$', 'mooActivities', 'mooEmoji'))); ?>
    var activitiesParams = '<?php echo json_encode($activitiesParams,true); ?>';
    mooActivities.init(activitiesParams);
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<style>
#list-content li {
	position: relative;
}
</style>
<?php if (!empty($activities)): ?>
<?php
$historyModel = MooCore::getInstance()->getModel('CommentHistory');

foreach ($activities as $index => $activity):
    $check_privacy_type = true; // Event, Group ....
	$admins_current = (isset($admins) ? array_merge($admins,array($activity['Activity']['user_id'])) : array($activity['Activity']['user_id']));
	$item_type = $activity['Activity']['item_type'];
	if ($activity['Activity']['plugin'])
	{
		$options = array('plugin'=>$activity['Activity']['plugin']);
	}
	else
	{
		$options = array();
	}

	if ($item_type)
	{
		list($plugin, $name) = mooPluginSplit($item_type);
		$object = MooCore::getInstance()->getItemByType($item_type,$activity['Activity']['item_id']);

	}
	else
	{
		$plugin = '';
		$name ='';
		$object = null;
	}

?>
<li id="activity_<?php echo $activity['Activity']['id']?>">
    <div class="feed_main_info">
	<?php
	// delete link available for activity poster, site admin and item admins
	if ( $activity['Activity']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $admins_current ) && in_array( $uid, $admins_current ) || (!empty($activity['UserTagging']) && in_array($uid, explode(',', $activity['UserTagging']['users_taggings']) ) ) || $this->MooPeople->isMentioned($uid, $activity['Activity']['id'])  ) ):
	?>
        <div class="dropdown edit-post-icon">
            <?php if (!empty($uid)): ?>
            <a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
               <i class="material-icons">more_vert</i>
            </a>
            <ul class="dropdown-menu">

                <?php
                $item_type =  empty($activity['Activity']['item_type']) ? 'activity' : $activity['Activity']['item_type'];
                ?>
                <?php if (($uid == $activity['Activity']['user_id']) || $this->MooPeople->isTagged($uid, $activity['Activity']['id'], 'activity'/*$item_type*/) || $this->MooPeople->isMentioned($uid, $activity['Activity']['id'])): ?>
                <li>
                    <?php
                        $item_id = !empty($activity['Activity']['item_id']) ? $activity['Activity']['item_id'] : $activity['Activity']['id'];
                        ?>

                    <?php if ( $activity['Activity']['params'] == 'item' && (isset($object[$name]['like_count']))): ?>
                    <?php
                        $title = $this->Moo->isNotificationStop($item_id, $item_type) ? __('Turn on notifications') : __('Stop Notifications');

                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "notifications",
                                                                  "action" => "stop",
                                                                  "plugin" => false,
                                                                $item_type,
                                                                  $item_id
                                                              )),
                                   'title' => $title,
                                   'innerHtml'=> $title,
                                    'id' => 'stop_notification_' . $item_type. $item_id
                           ));
                       ?>
                    <?php else: ?>
                    <?php
                        $title = $this->Moo->isNotificationStop($activity['Activity']['id'], 'activity') ? __('Turn on notifications') : __('Stop Notifications');

                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "notifications",
                                                                  "action" => "stop",
                                                                  "plugin" => false,
                                                                'activity',
                                                                  $activity['Activity']['id']
                                                              )),
                                   'title' => $title,
                                   'innerHtml'=> $title,
                                    'id' => 'stop_notification_' . 'activity'. $activity['Activity']['id']
                           ));
                       ?>
                    <?php endif; ?>




                </li>
                <?php endif; ?>

                <?php if(!empty($activity['UserTagging']['users_taggings']) && $activity['Activity']['user_id'] == $uid ): ?>
                <li>
                    <?php
                            $this->MooPopup->tag(array(
                                   'href'=>$this->Html->url(array("controller" => "friends",
                                                                  "action" => "tagged",
                                                                  "plugin" => false,
                                                                  $activity['Activity']['id']
                                                              )),
                                   'title' => __('Tag Friends'),
                                   'innerHtml'=> __('Tag Friends'),
                           ));
                       ?>
                </li>
                <?php endif; ?>

                <?php if (isset($activity['UserTagging']['users_taggings']) && in_array($uid, explode(',', $activity['UserTagging']['users_taggings']) ) || $this->MooPeople->isMentioned($uid, $activity['Activity']['id']) ): ?>
                <li>
                    <a class="removeTags" data-activity-id="<?php echo $activity['Activity']['id']; ?>" data-activity-item-type="activity" href="javascript:void(0)" ><?php echo __('Remove Tags'); ?></a>
                </li>
                <?php endif; ?>

                <?php if (($activity['Activity']['user_id'] == $uid || $cuser['Role']['is_admin']) &&  $activity['Activity']['action'] == 'wall_post'):?>
                <li>
                    <a class="editActivity" data-activity-id="<?php echo $activity['Activity']['id']?>" href="javascript:void(0)" >
                        <?php echo __('Edit Post'); ?>
                    </a>
                </li>
                <?php endif;?>

                <?php if (( (!empty($admins_current) && in_array($uid, $admins_current)) || $activity['Activity']['user_id'] == $uid || $cuser['Role']['is_admin'])): ?>
                <li>
                    <a class="removeActivity" data-activity-id="<?php echo $activity['Activity']['id']?>" href="javascript:void(0)" >
                        <?php echo __('Delete Post'); ?>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
            <?php endif; ?>
          </div>
	<?php endif; ?>
        <div class="activity_feed_image">
            <?php echo $this->Moo->getItemPhoto(array('User' => $activity['User']),array( 'prefix' => '50_square', 'tooltip' => true), array('class' => 'img_wrapper2 user_avatar_large'))?>
        </div>

        <div class="activity_feed_content">
            <div class="comment hasDelLink">
		<div class="activity_text">
			<?php echo $this->Moo->getName($activity['User'])?>
			<?php
				echo $this->element('activity/text/' . $activity['Activity']['action'], array('activity' => $activity,'object'=>$object),$options);
			?>
		</div>
                <div class="feed_time">
                <?php if ( $activity['Activity']['params'] != 'no-comments' ): ?>
                    <a href="<?php echo $this->request->base?>/users/view/<?php echo $activity['Activity']['user_id']?>/activity_id:<?php echo $activity['Activity']['id']?>" class="date"><?php echo $this->Moo->getTime( $activity['Activity']['created'], Configure::read('core.date_format'), $utz )?></a>
                 <?php else: ?>
                    <span class="date"><?php echo $this->Moo->getTime( $activity['Activity']['created'], Configure::read('core.date_format'), $utz )?></span>
                 <?php endif; ?>
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "histories",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'activity',
                                            $activity['Activity']['id']
                                        )),
             'title' => __('Show edit history'),
             'innerHtml'=> $historyModel->getText('activity',$activity['Activity']['id']),
          'style' => empty($activity['Activity']['edited']) ? 'display:none' : '',
          'id' => 'history_activity_'. $activity['Activity']['id'],
          'class' => 'edit-btn',
		  'data-dismiss'=>'modal'
     ));
 ?>

                 <?php if (!$activity['Activity']['target_id']):?>
                 	<?php
                 	 switch ($activity['Activity']['privacy']) {
                 	 	case '1':
                 	 		$text = __('Shared with: Everyone');
                 	 		$icon = 'public';
                 	 	break;
                 	 	case '2':
                 	 		$text = __('Shared with: Friend');
                 	 		$icon = 'people';
                 	 	break;
                 	 	case '3':
                 	 		$text = __('Shared with: Only Me');
                 	 		$icon = 'lock';
                 	 	break;
                 	 }
                 	?>
                    <?php if(!empty($uid) && ($activity['Activity']['user_id'] == $uid || $cuser['Role']['is_admin']) && $activity['Activity']['action'] == 'wall_post'): ?>

                        <span class="dropdown">
                            <a id="permission_<?php echo $activity['Activity']['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" class="tip" href="javascript:void(0);" original-title="<?php echo $text;?>"> <i class="material-icons"><?php echo $icon;?></i>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="permission_<?php echo $activity['Activity']['id'] ?>">
                                <li><a data-privacy="1" data-activity-id="<?php echo $activity['Activity']['id']; ?>" class="change-activity-privacy<?php if($activity['Activity']['privacy'] == 1) echo ' n52'; ?>" href="javascript:void(0)"><?php echo __('Everyone'); ?></a></li>
                                <li><a data-privacy="2" data-activity-id="<?php echo $activity['Activity']['id']; ?>" class="change-activity-privacy<?php if($activity['Activity']['privacy'] == 2) echo ' n52'; ?>" href="javascript:void(0)"><?php echo __('Friends Only'); ?></a></li>
                            </ul>
                        </span>
                    <?php else: ?>
                 	    <a class="tip" href="javascript:void(0);" original-title="<?php echo $text;?>"> <i class="material-icons"><?php echo $icon;?></i></a>
                    <?php endif; ?>
                 <?php elseif (strtolower($activity['Activity']['type']) == 'user'):?>
                   <?php
                   	$target = MooCore::getInstance()->getItemByType($activity['Activity']['type'],$activity['Activity']['target_id']);
                   ?>
                    <?php if ($activity['Activity']['privacy'] == PRIVACY_FRIENDS) :?>
                 	<a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: %s\'s friends of friends',$target['User']['moo_title']);?>"> <i class="material-icons">people</i></a>
                 	<?php else:?>
                 	<a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: Everyone');?>"> <i class="material-icons">public</i></a>
                 	<?php endif;?>
                 <?php else:?>
                 	<?php
                   	$target = MooCore::getInstance()->getItemByType($activity['Activity']['type'],$activity['Activity']['target_id']);
                   	list($plugin_target, $name_target) = mooPluginSplit($activity['Activity']['type']);
                   	$show_subject = MooCore::getInstance()->checkShowSubjectActivity($target);
	    			if ($show_subject):
                    ?>
                        <?php
                            $plugin_helper = MooCore::getInstance()->getHelper($plugin_target.'_'.$plugin_target);
                            $is_public = true;
                            if (method_exists($plugin_helper,'isPublicFeedIcon'))
                            {
                                $is_public = $plugin_helper->isPublicFeedIcon($target);
                            }

                            if (method_exists($plugin_helper,'checkPrivacyFeedHome'))
                            {
                                $check_privacy_type = $plugin_helper->checkPrivacyFeedHome($target);
                            }
                        ?>
                        <?php if ($is_public): ?>
                            <a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: Everyone');?>"> <i class="material-icons">public</i></a>
                        <?php else:?>
                            <a class="tip" href="javascript:void(0);" original-title="<?php echo __('Shared with: member of %s ',$target[$name_target]['moo_title']);?>"> <i class="material-icons">people</i></a>
                        <?php endif; ?>
					<?php endif;?>
                 <?php endif;?>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="activity_feed_content_text" id="activity_feed_content_text_<?php echo $activity['Activity']['id'];?>">
        <?php

			echo $this->element('activity/content/' . $activity['Activity']['action'], array('activity' => $activity,'object'=>$object),$options);
		?>
        </div>
    </div>
    <?php if($activity['Activity']['params'] != 'no-comments'): ?>

    <div class="feed_comment_info">
        <?php if ( (!($activity['Activity']['item_type'] == 'Topic_Topic' && isset($object['Topic']) && $object['Topic']['locked']) ) || (!empty($cuser) && $cuser['Role']['is_admin']) ): ?>
			<div class="date">
				<?php if ( $activity['Activity']['params'] == 'mobile' ) echo __('via mobile'); ?>
				<?php if ( !isset($is_member) || $is_member || $cuser['Role']['is_admin'] ): ?>
                    <?php if($check_privacy_type && ((isset($groupTypeItem) && $groupTypeItem['is_member']) || (!isset($groupTypeItem)) )) : ?>

                            <a <?php if (isset($is_search) && $is_search): ?> href="<?php echo $this->request->base?>/users/view/<?php echo $activity['Activity']['user_id']?>/activity_id:<?php echo $activity['Activity']['id']?>"<?php else:?>href="javascript:void(0)" class="showCommentForm"<?php endif;?> data-id="<?php echo $activity['Activity']['id']?>"><i class='material-icons'>comment</i>&nbsp;<?php echo __('Comment')?></a>

                            <?php if ( $activity['Activity']['params'] == 'item' && (isset($object[$name]['like_count']))): ?>
                                &nbsp;<a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['item_id']?>" data-type="<?php echo $item_type?>" data-status="1" id="<?php echo $item_type?>_l_<?php echo $activity['Activity']['item_id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $activity['Likes'][$uid] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
                                <?php
                                      $this->MooPopup->tag(array(
                                             'href'=>$this->Html->url(array("controller" => "likes",
                                                                            "action" => "ajax_show",
                                                                            "plugin" => false,
                                                                            $item_type,
                                                                            $activity['Activity']['item_id'],
                                                                        )),
                                             'title' => __('People Who Like This'),
                                             'innerHtml'=> '<span id="'. $item_type . '_like_' . $activity['Activity']['item_id'] . '">' . $object[$name]['like_count'] . '</span>',
                                          'data-dismiss' => 'modal'
                                     ));
                                ?>
                                <?php if(empty($hide_dislike)): ?>
                                    <a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['item_id']?>" data-type="<?php echo $item_type?>" data-status="0" id="<?php echo $item_type?>_d_<?php echo $activity['Activity']['item_id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && isset( $activity['Likes'][$uid] ) && $activity['Likes'][$uid] == 0 ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>

                                    <?php
                                    $this->MooPopup->tag(array(
                                             'href'=>$this->Html->url(array("controller" => "likes",
                                                                            "action" => "ajax_show",
                                                                            "plugin" => false,
                                                                            $item_type,
                                                                            $activity['Activity']['item_id'],1
                                                                        )),
                                             'title' => __('People Who Dislike This'),
                                             'innerHtml'=> '<span id="'.  $item_type . '_dislike_' . $activity['Activity']['item_id'] . '">' . $object[$name]['dislike_count'] . '</span>',
                                    ));
                                    ?>
                                <?php endif; ?>

                                <?php echo $this->element('share', array('activity' => $activity)); ?>

                            <?php else: ?>
	                            &nbsp;<a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['id']?>" data-type="activity" data-status="1" id="activity_l_<?php echo $activity['Activity']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $activity_likes['activity_likes'][$activity['Activity']['id']] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
	                            <?php
						          $this->MooPopup->tag(array(
						                 'href'=>$this->Html->url(array("controller" => "likes",
						                                                "action" => "ajax_show",
						                                                "plugin" => false,
						                                                'activity',
						                                                $activity['Activity']['id'],
						                                            )),
						                 'title' => __('People Who Like This'),
						                 'innerHtml'=> '<span id="activity_like_'. $activity['Activity']['id']. '">' . $activity['Activity']['like_count'] . '</span>',
						              'data-dismiss' => 'modal'
						         ));
						     ?>
						                <?php if(empty($hide_dislike)): ?>
						                            <a href="javascript:void(0)" data-id="<?php echo $activity['Activity']['id']?>" data-type="activity" data-status="0" id="activity_d_<?php echo $activity['Activity']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && isset( $activity_likes['activity_likes'][$activity['Activity']['id']] ) && $activity_likes['activity_likes'][$activity['Activity']['id']] == 0 ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>



						                                <?php
						          $this->MooPopup->tag(array(
						                 'href'=>$this->Html->url(array("controller" => "likes",
						                                                "action" => "ajax_show",
						                                                "plugin" => false,
						                                                'activity',
						                                                $activity['Activity']['id'],1
						                                            )),
						                 'title' => __('People Who Dislike This'),
						                 'innerHtml'=> '<span id="activity_dislike_' . $activity['Activity']['id'] . '">' .  $activity['Activity']['dislike_count'] . '</span>',
						         ));
						     ?>
						                <?php endif; ?>

	                                <?php echo $this->element('share', array('activity' => $activity)); ?>

                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
	        </div>
        <?php endif; ?>

    <?php if( (isset($groupTypeItem) && $groupTypeItem['is_member']) || (!isset($groupTypeItem)) ) : ?>


        <?php $showFormComment = true; ?>
        <ul class="activity_comments comment_list" id="comments_<?php echo $activity['Activity']['id']?>" <?php if (empty($activity['ActivityComment']) && empty($activity['PhotoComment']) && empty($activity['Activity']['like_count']) && empty($activity['ItemComment']) && ( $activity['Activity']['params'] != 'item' || empty($object[$name]['like_count']) ) ) {echo 'style="display:none"';$showFormComment = false;} ?>>

                        <?php if (isset($check_post_status) && $check_post_status): ?>
                        <?php
			// comment form
			if ($activity['Activity']['params'] != 'no-comments' && $check_privacy_type && ( (isset($is_member) && $is_member) || (!empty($cuser) && $cuser['Role']['is_admin']) || !($activity['Activity']['item_type'] == 'Topic_Topic' && isset($object['Topic']) && $object['Topic']['locked']))):
			?>
				<li id="newComment_<?php echo $activity['Activity']['id']?>">
					<?php echo $this->Moo->getItemPhoto(array('User' => $cuser), array( 'prefix' => '50_square','tooltip' => true), array('class' => 'user_avatar_small img_wrapper2'))?>
					<div class="comment">

						<?php echo $this->Form->textarea("commentForm_".$activity['Activity']['id'],array('class' => "commentBox showCommentBtn", 'data-id' => $activity['Activity']['id'], 'placeholder' => __('Write a comment...') ), true) ?>

                        <div class="clear"></div>
						<div style="display:block;" class="commentButton" id="commentButton_<?php echo $activity['Activity']['id']?>">
							<?php if ( !empty( $uid ) ): ?>
								<input type="hidden" id="comment_image_<?php echo $activity['Activity']['id'];?>" />
								<div id="comment_button_attach_<?php echo $activity['Activity']['id'];?>"></div>
								<a href="javascript:void(0)"  <?php if ( $activity['Activity']['params'] == 'item' && isset($object[$name]['comment_count'])): ?> class="btn btn-action  viewer-submit-item-comment" data-item-type="<?php echo $item_type?>" data-activity-item-id="<?php echo $activity['Activity']['item_id']?>" data-activity-id="<?php echo $activity['Activity']['id']?>" <?php else: ?> class="btn btn-action  viewer-submit-comment" data-activity-id="<?php echo $activity['Activity']['id']?>" <?php endif; ?>><?php echo __('Comment')?></a>

								<?php if($this->request->is('ajax')): ?>
                                                                <script type="text/javascript">
                                                                    require(["jquery","mooAttach"], function($,mooAttach) {
                                                                        mooAttach.registerAttachComment(<?php echo $activity['Activity']['id'];?>);
                                                                    });
                                                                </script>
								<?php else: ?>
                                                                <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true,'requires'=>array('jquery','mooAttach'), 'object' => array('$', 'mooAttach'))); ?>
                                                                mooAttach.registerAttachComment(<?php echo $activity['Activity']['id'];?>);
								<?php $this->Html->scriptEnd(); ?>
								<?php endif; ?>

							<?php else: ?>
							<?php echo __('Please login or register')?>
							<?php endif; ?>
						</div>
						<div id="comment_preview_image_<?php echo $activity['Activity']['id'];?>"></div>
					</div>
				</li>
                <?php if ($showFormComment && (!isset($profile_has_activity) || (isset($profile_has_activity) && !$profile_has_activity))): ?>
                    <?php if($this->request->is('ajax')): ?>
                        <script type="text/javascript">
                            require(["jquery","mooMention"], function($, mooMention) {
                                mooMention.init('commentForm_<?php echo $activity['Activity']['id']?>');
                            });
                        </script>
                    <?php else: ?>
                        <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooMention'), 'object' => array('$', 'mooMention'))); ?>
                            mooMention.init('commentForm_<?php echo $activity['Activity']['id']?>');
                        <?php $this->Html->scriptEnd(); ?>
                    <?php endif; ?>
                <?php endif;?>
			<?php
			endif;
                        // end comment form
			?>
                        <?php endif; ?>

                    <?php
			// item comments
			if ( !empty($activity['ItemComment']) ):

		    ?>
		    <?php
                            $ItemComment = array_chunk($activity['ItemComment'], LIMIT_DISPLAY_COMMENT);
                            $ItemComment = isset($ItemComment[0]) ? $ItemComment[0] : array();
				foreach ($ItemComment as $comment):
			?>
				<li id="itemcomment_<?php echo $comment['Comment']['id']?>"><?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']), array( 'prefix' => '50_square','tooltip' => true), array('class' => 'user_avatar_small img_wrapper2'))?>
				    <?php
		            // delete link available for activity poster, site admin and admins array
		            if ( $comment['Comment']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $admins_current ) && in_array( $uid, $admins_current ) ) ):
		            ?>
		            	<div class="dropdown edit-post-icon comment-option">
							<a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
								<i class="material-icons">more_vert</i>
							</a>
							<ul class="dropdown-menu">
								<?php if ($comment['Comment']['user_id'] == $uid || $cuser['Role']['is_admin']):?>
								<li>
									<a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-photo-comment="0" class="editItemComment">
										<?php echo __('Edit Comment'); ?>
									</a>
								</li>
								<?php endif; ?>

								<li>
									<a class="admin-or-owner-confirm-delete-item-comment removeItemComment" href="javascript:void(0)" data-photo-comment="0" data-id="<?php echo $comment['Comment']['id']?>" >
										<?php echo __('Delete Comment'); ?>
									</a>
								</li>


							</ul>
						</div>
		            <?php endif; ?>
					<div class="comment hasDelLink">
						<?php echo $this->Moo->getName($comment['User'])?>
						<span id="item_feed_comment_text_<?php echo $comment['Comment']['id']?>">
							<?php
                                echo $this->viewMore(h($comment['Comment']['message']),null,null,null,true,array('no_replace_ssl'=>1));
							?>

							<?php if ($comment['Comment']['thumbnail']):?>
							<div class="comment_thumb">
		                        <a href="<?php echo $this->Moo->getImageUrl($comment,array());?>">
				                     <?php echo $this->Moo->getImage($comment,array('prefix'=>'200'));?>
				                </a>
			                </div>
                        	<?php endif;?>
                        </span>

						<div class="feed-time date">
							<?php echo $this->Moo->getTime( $comment['Comment']['created'], Configure::read('core.date_format'), $utz )?>
			                <?php
                            $this->MooPopup->tag(array(
                                     'href'=>$this->Html->url(array("controller" => "histories",
                                                                    "action" => "ajax_show",
                                                                    "plugin" => false,
                                                                    'comment',
                                                                    $comment['Comment']['id']
                                                                )),
                                     'title' => __('Show edit history'),
                                     'innerHtml'=> $historyModel->getText('comment',$comment['Comment']['id']),
                                  'style' => empty($comment['Comment']['edited']) ? 'display:none;' : '',
                                  'id' => 'history_item_comment_'. $comment['Comment']['id'],
                                  'class' => 'edit-btn',
                                  'data-dismiss'=>'modal'
                            ));
                            ?>

							&nbsp;<a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="comment" data-status="1"  id="comment_l_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $activity_likes['item_comment_likes'][$comment['Comment']['id']] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
							<?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "likes",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'comment',
                                            $comment['Comment']['id'],
                                        )),
             'title' => __('People Who Like This'),
             'innerHtml'=> '<span id="comment_like_'.  $comment['Comment']['id'] . '">' . $comment['Comment']['like_count'] . '</span>',
          'data-dismiss' => 'modal'
     ));
 ?>
                            <?php if(empty($hide_dislike)): ?>
		                    <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="comment" data-status="0" id="comment_d_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && isset( $activity_likes['item_comment_likes'][$comment['Comment']['id']] ) && $activity_likes['item_comment_likes'][$comment['Comment']['id']] == 0 ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>



                            <?php
                            $this->MooPopup->tag(array(
                                     'href'=>$this->Html->url(array("controller" => "likes",
                                                                    "action" => "ajax_show",
                                                                    "plugin" => false,
                                                                    'comment',
                                                                    $comment['Comment']['id'],1
                                                                )),
                                     'title' => __('People Who Dislike This'),
                                     'innerHtml'=> '<span id="comment_dislike_' .  $comment['Comment']['id'] . '">' . $comment['Comment']['dislike_count'] . '</span>',
                            ));
                            ?>
                            <?php endif; ?>
						</div>
					</div>
				</li>



			<?php endforeach; ?>
                                <?php if ( isset($activity['ItemCommentCount']) && $activity['ItemCommentCount'] > LIMIT_DISPLAY_COMMENT ): ?>
                                <li><i class="material-icons">comment</i> <a href="<?php echo $object[$name]['moo_href'];?>"><?php echo __('View all comments')?></a></li>
                                <?php endif; ?>
                        <?php endif; ?>

			<?php
			// photo comments
            if(!empty($activity['PhotoComment'])):?>

                <?php
                foreach ($activity['PhotoComment'] as $key => $comment):
                    $class = '';
                    if ( count($activity['PhotoComment']) > 2 && $key > 1 )
                        $class = 'hidden';
                    ?>
                    <li id="itemcomment_<?php echo $comment['Comment']['id']?>" class="<?php echo $class?>"><?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']),array('class' => 'user_avatar_small', 'prefix' => '50_square','tooltip' => true), array('class' => 'user_avatar_small img_wrapper2'))?>
                        <?php
                        // delete link available for activity poster, site admin and admins array
                        if ( $comment['Comment']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $admins_current ) && in_array( $uid, $admins_current ) ) ):
                            ?>
                            <div class="dropdown edit-post-icon comment-option">
                                <a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
                                    <i class="material-icons">more_vert</i>
                                </a>
                                <ul class="dropdown-menu">
                                        <?php if ($comment['Comment']['user_id'] == $uid || $cuser['Role']['is_admin']):?>
                                        <li>
                                            <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-photo-comment="1" class="editItemComment">
                                                <?php echo __('Edit Comment'); ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                    <li>
                                        <a class="removeItemComment" href="javascript:void(0)" data-photo-comment="1" data-id="<?php echo $comment['Comment']['id']?>" >
                                            <?php echo __('Delete Comment'); ?>
                                        </a>
                                    </li>


                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="comment hasDelLink">
                            <?php echo $this->Moo->getName($comment['User'])?>
                            <span id="photo_feed_comment_text_<?php echo $comment['Comment']['id']?>">
							<?php
                                echo $this->viewMore(h($comment['Comment']['message']),null,null,null,true,array('no_replace_ssl'=>1));
                            ?>

                                <?php if ($comment['Comment']['thumbnail']):?>
                                    <div class="comment_thumb">
                                        <a href="<?php echo $this->Moo->getImageUrl($comment,array());?>">
                                            <?php echo $this->Moo->getImage($comment,array('prefix'=>'200'));?>
                                        </a>
                                    </div>
                                <?php endif;?>
                        </span>

                            <div class="feed-time date">
                                <?php echo $this->Moo->getTime( $comment['Comment']['created'], Configure::read('core.date_format'), $utz )?>
                                <?php
                                $this->MooPopup->tag(array(
                                        'href'=>$this->Html->url(array("controller" => "histories",
                                                    "action" => "ajax_show",
                                                    "plugin" => false,
                                                    'comment',
                                                    $comment['Comment']['id']
                                                )),
                                        'title' => __('Show edit history'),
                                        'innerHtml'=> $historyModel->getText('comment',$comment['Comment']['id']),
                                        'style' => empty($comment['Comment']['edited']) ? 'display:none;' : '',
                                        'id' => 'history_item_comment_'. $comment['Comment']['id'],
                                        'class' => 'edit-btn',
                                        'data-dismiss'=>'modal'
                                    ));
                                ?>

                                &nbsp;<a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="photo_comment" data-status="1" id="photo_comment_l_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $comment['Comment']['like_count'] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
                                <?php
                                $this->MooPopup->tag(array(
                                        'href'=>$this->Html->url(array("controller" => "likes",
                                                    "action" => "ajax_show",
                                                    "plugin" => false,
                                                    'comment',
                                                    $comment['Comment']['id'],
                                                )),
                                        'title' => __('People Who Like This'),
                                        'innerHtml'=> '<span id="photo_comment_like_'.  $comment['Comment']['id'] . '">' . $comment['Comment']['like_count'] . '</span>',
                                        'data-dismiss' => 'modal'
                                    ));
                                ?>
                                <?php if(empty($hide_dislike)): ?>
                                    <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-type="photo_comment" data-status="0" id="photo_comment_d_<?php echo $comment['Comment']['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $comment['Comment']['dislike_count'] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>



                                    <?php
                                    $this->MooPopup->tag(array(
                                            'href'=>$this->Html->url(array("controller" => "likes",
                                                        "action" => "ajax_show",
                                                        "plugin" => false,
                                                        'comment',
                                                        $comment['Comment']['id'],1
                                                    )),
                                            'title' => __('People Who Dislike This'),
                                            'innerHtml'=> '<span id="photo_comment_dislike_' .  $comment['Comment']['id'] . '">' . $comment['Comment']['dislike_count'] . '</span>',
                                        ));
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if ( count( $activity['PhotoComment'] ) > 2 ): ?>
                    <li id="all_comments_<?php echo $activity['Activity']['id']?>"><i class="material-icons">comment</i> <a href="javascript:void(0)" class="showAllComments" data-id="<?php echo $activity['Activity']['id']?>"><?php echo __('View all %s comments', count($activity['PhotoComment']))?></a></li>
                <?php endif; ?>
            <?php
			elseif (!empty($activity['ActivityComment'])):

		    ?>

		    <?php

				foreach ($activity['ActivityComment'] as $key => $comment):
					$class = '';
					if ( count($activity['ActivityComment']) > 2 && $key > 1 )
						$class = 'hidden';
			?>
				<li id="comment_<?php echo $comment['id']?>" class="<?php echo $class?>"><?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']),array('class' => 'user_avatar_small', 'prefix' => '50_square','tooltip' => true), array('class' => 'user_avatar_small img_wrapper2'))?>
					<?php

					// delete link available for activity poster, site admin and admins array
					if ( ($comment['user_id'] == $uid) || ($activity['Activity']['user_id'] == $uid) || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $admins_current ) && in_array( $uid, $admins_current ) ) ):
					?>
                                        <div class="dropdown edit-post-icon comment-option">
                                            <a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php if ($comment['user_id'] == $uid || $cuser['Role']['is_admin']):?>
                                                <li>
                                                    <a href="javascript:void(0)" class="editActivityComment" data-activity-comment-id="<?php echo $comment['id']?>" >
                                                        <?php echo __('Edit Comment'); ?>
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a class="removeActivityComment" data-activity-comment-id="<?php echo $comment['id']?>" href="javascript:void(0)"  >
                                                        <?php echo __('Delete Comment'); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
					<?php endif; ?>

					<div class="comment hasDelLink">
						<?php echo $this->Moo->getName($comment['User'])?>
						<span id="activity_feed_comment_text_<?php echo $comment['id']?>">
							<?php
								echo $this->viewMore(h($comment['comment']),null,null,null,true,array('no_replace_ssl'=>1));
							?>

							<?php if ($comment['thumbnail']):?>
							<div class="comment_thumb">
								<a href="<?php echo $this->Moo->getImageUrl(array('ActivityComment'=>$comment),array());?>">
				                	<?php echo $this->Moo->getImage(array('ActivityComment'=>$comment),array('prefix'=>'200'));?>
				                </a>
			                </div>
	                        <?php endif;?>
                        </span>

						<div class="feed-time date">
							<?php echo $this->Moo->getTime( $comment['created'], Configure::read('core.date_format'), $utz )?>
			                 	<?php
                                $this->MooPopup->tag(array(
                                         'href'=>$this->Html->url(array("controller" => "histories",
                                                                        "action" => "ajax_show",
                                                                        "plugin" => false,
                                                                        'core_activity_comment',
                                                                        $comment['id']
                                                                    )),
                                         'title' => __('Show edit history'),
                                         'innerHtml'=> $historyModel->getText('core_activity_comment',$comment['id']),
                                      'style' => empty($comment['edited']) ? 'display:none;' : '',
                                      'id' => 'history_activity_comment_'. $comment['id'],
                                      'class' => 'edit-btn',
                                      'data-dismiss'=>'modal'
                                ));
                                ?>

							&nbsp;<a href="javascript:void(0)" data-id="<?php echo $comment['id']?>" data-type="core_activity_comment" data-status="1" id="core_activity_comment_l_<?php echo $comment['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && !empty( $activity_likes['comment_likes'][$comment['id']] ) ): ?>active<?php endif; ?>"><i class="material-icons">thumb_up</i></a>
							<?php
                                  $this->MooPopup->tag(array(
                                         'href'=>$this->Html->url(array("controller" => "likes",
                                                                        "action" => "ajax_show",
                                                                        "plugin" => false,
                                                                        'core_activity_comment',
                                                                        $comment['id'],
                                                                    )),
                                         'title' => __('People Who Like This'),
                                         'innerHtml'=> '<span id="core_activity_comment_like_'. $comment['id'] . '">' . $comment['like_count'] . '</span>',
                                 ));
                            ?>
                            <?php if(empty($hide_dislike)): ?>
                                <a href="javascript:void(0)" data-id="<?php echo $comment['id']?>" data-type="core_activity_comment" data-status="0" id="core_activity_comment_d_<?php echo $comment['id']?>" class="comment-thumb likeActivity <?php if ( !empty( $uid ) && isset( $activity_likes['comment_likes'][$comment['id']] ) && $activity_likes['comment_likes'][$comment['id']] == 0 ): ?>active<?php endif; ?>"><i class="material-icons">thumb_down</i></a>
                                <?php
                                $this->MooPopup->tag(array(
                                         'href'=>$this->Html->url(array("controller" => "likes",
                                                                        "action" => "ajax_show",
                                                                        "plugin" => false,
                                                                        'core_activity_comment',
                                                                        $comment['id'],1
                                                                    )),
                                         'title' => __('People Who Dislike This'),
                                         'innerHtml'=> '<span id="core_activity_comment_dislike_'. $comment['id'] . '">' .  $comment['dislike_count'] . '</span>',
                                ));
                                ?>
                            <?php endif; ?>

		                </div>
					</div>
				</li>


			<?php
				endforeach;
                                ?>

                                <?php if ( count( $activity['ActivityComment'] ) > 2 ): ?>
		        <li id="all_comments_<?php echo $activity['Activity']['id']?>"><i class="material-icons">comment</i> <a href="javascript:void(0)" class="showAllComments" data-id="<?php echo $activity['Activity']['id']?>"><?php echo __('View all %s comments', count($activity['ActivityComment']))?></a></li>
		    <?php
		        endif; ?>

			<?php endif;
			?>


		</ul>
    <?php endif; ?>
    </div>
    <?php endif; ?>
</li>
<?php $this->getEventManager()->dispatch(new CakeEvent('element.activities.afterRenderOneFeed', $this,array('index'=>$index))); ?>
<?php
endforeach;
?>
<?php else: ?>
<div class="no-feed"><?php echo __('There are no new feeds to view at this time.')?></div>
<?php endif; ?>

<?php if (isset($bIsACtivityloadMore) && $bIsACtivityloadMore > 0 && count($activities)) :?>
    <?php $this->Html->viewMore($more_url) ?>
<?php endif; ?>