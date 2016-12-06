
<style>
    #themeModal .modal-body{
        padding:15px;
    }
</style>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initOnUserView();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooUser'), 'object' => array('$', 'mooUser'))); ?>
mooUser.initOnUserView();
<?php $this->Html->scriptEnd(); ?> 
<?php endif; ?>


<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
	<?php if ( $canView ): ?>
	<div id="browse" class="menu block-body">
		<ul class="list2 menu_top_list">
			<li class="current">
				<a class="no-ajax" href="<?php echo $this->Moo->getProfileUrl( $user['User'] )?>"><i class="material-icons">person</i> <?php echo __('Profile')?></a>
			</li>
			<li>
				<a data-url="<?php echo $this->request->base?>/users/ajax_info/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">info</i> <?php echo __('Info')?></a>
			</li>
			<li>
				<a data-url="<?php echo $this->request->base?>/users/profile_user_friends/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">people</i> <?php echo __('Friends')?>
				<span class="badge_counter"><?php echo $user['User']['friend_count']?></span></a>
			</li>
			<?php if (Configure::read("core.enable_follow") && $user['User']['id'] == $uid): ?>
				<?php
				$followModel = MooCore::getInstance()->getModel("UserFollow");
				?>
				<li>
					<a id="profile_follow" data-url="<?php echo $this->request->base?>/follows/user_follows" rel="profile-content" href="#"><i class="material-icons follow_icon">group</i>  <?php echo __('Following')?>
						<span class="badge_counter"><?php echo $followModel->find('count',array('conditions'=>array('UserFollow.user_id'=>$uid)));?></span></a>
				</li>
		          <?php endif; ?>
                        <?php if($cuser && ($uid == $user['User']['id'] || $cuser['Role']['is_admin'])): ?>
                                <?php
				$blockModel = MooCore::getInstance()->getModel("UserBlock");
				?>
                        <li>
				<a data-url="<?php echo $this->request->base?>/users/profile_user_blocks/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">block</i> <?php echo __('Blocked Members')?>
				<span class="badge_counter"><?php echo $blockModel->find('count',array('conditions'=>array('UserBlock.user_id'=>$user['User']['id'])));?></span></a>
			</li>
                        <?php endif; ?>
			<?php if (Configure::read('Photo.photo_enabled')): ?>
			<li>
				<a data-url="<?php echo $this->request->base?>/photos/profile_user_photo/<?php echo $user['User']['id']?>" rel="profile-content" id="user_photos" href="#"><i class="material-icons">collections</i> <?php echo __('Albums')?>
				<span class="badge_counter"><?php echo $albums_count?></span></a>
			</li>		
			<?php endif; ?>
			<?php if (Configure::read('Blog.blog_enabled')): ?>
			<li>
			    <a data-url="<?php echo $this->request->base?>/blogs/profile_user_blog/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">library_books</i> <?php echo __('Blogs')?>
				<span class="badge_counter"><?php echo $user['User']['blog_count']?></span></a>
			</li>
			<?php endif; ?>
                        <?php if (Configure::read('Topic.topic_enabled')): ?>
			<li>
			    <a data-url="<?php echo $this->request->base?>/topics/profile_user_topic/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">comment</i> <?php echo __('Topics')?>
				<span class="badge_counter"><?php echo $user['User']['topic_count']?></span></a>
			</li>		
			<?php endif; ?>
                        <?php if (Configure::read('Video.video_enabled')): ?>
			<li><a data-url="<?php echo $this->request->base?>/videos/profile_user_video/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">videocam</i> <?php echo __('Videos')?>
				<span class="badge_counter"><?php echo $user['User']['video_count']?></span></a>
			</li>	
			<?php endif; ?>
                        
                        <?php if (Configure::read('Group.group_enabled')): ?>
			<li><a data-url="<?php echo $this->request->base?>/groups/profile_user_group/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">group_work</i> <?php echo __('Groups')?>
				<span class="badge_counter"><?php echo $user['User']['group_count']?></span></a>
			</li>	
			<?php endif; ?>
                        
                        <?php if (Configure::read('Event.event_enabled')): ?>
			<li><a data-url="<?php echo $this->request->base?>/events/profile_user_event/<?php echo $user['User']['id']?>" rel="profile-content" href="#"><i class="material-icons">event</i> <?php echo __('Events')?>
				<span class="badge_counter"><?php echo $user['User']['event_count']?></span></a>
			</li>	
			<?php endif; ?>
                        
			<?php
				$this->getEventManager()->dispatch(new CakeEvent('profile.afterRenderMenu', $this)); 
			?>
			<?php
            if ( $this->elementExists('menu/user') )
                echo $this->element('menu/user');
            ?>
		</ul>
	</div>

	<?php if ($user['User']['friend_count']): ?>
	<div class="box2 box-friend" >
		<h3><?php echo __('Friends')?> (<?php echo $user['User']['friend_count']?>)</h3>
		<div class="box_content">
		    <?php echo $this->element( 'blocks/users_block', array( 'users' => $friends ) ); ?>
		</div>
	</div>
	<?php endif; ?>
	
	<?php if ( !empty( $mutual_friends ) ): ?>
	<div class="box2 mutual-friend">
		<h3>
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "friends",
                                            "action" => "ajax_show_mutual",
                                            "plugin" => false,
                                            $user['User']['id']
                                            
                                        )),
             'title' => __('Mutual Friends'),
             'innerHtml'=> __('Mutual Friends'),
     ));
 ?>
                    </h3>
		<div class="box_content">
		    <?php echo $this->element( 'blocks/users_block', array( 'users' => $mutual_friends ) ); ?>
		</div>
	</div>
	<?php endif; ?>
    <?php endif; ?>

	<?php if ( $canView ): ?>
	    
		 <?php echo $this->element('Video.blocks/videos_block'); ?>
	
		<?php echo $this->element('Blog.blocks/blogs_block'); ?>
	
		<?php echo $this->element('Group.blocks/group_block'); ?>
		
	<?php endif; ?>
		
	<div class="box2">
            <div class="box_content">
		<ul class="list6 list6sm">
			<?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && !$user['User']['featured'] ): ?>
			<li><a href="<?php echo $this->request->base?>/admin/users/feature/<?php echo $user['User']['id']?>"><?php echo __('Feature User')?></a></li>
			<?php endif; ?>
			<?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && $user['User']['featured'] ): ?>
			<li><a href="<?php echo $this->request->base?>/admin/users/unfeature/<?php echo $user['User']['id']?>"><?php echo __('Unfeature User')?></a></li>
			<?php endif; ?>
			<?php if ( !empty($cuser['role_id']) && $cuser['Role']['is_admin'] && !$user['Role']['is_admin'] ): ?>
			<li><a href="<?php echo $this->request->base?>/admin/users/edit/<?php echo $user['User']['id']?>"><?php echo __('Edit User')?></a></li>
			<?php endif; ?>
			<li>
                            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "reports",
                                            "action" => "ajax_create",
                                            "plugin" => false,
                                            'user',
                                            $user['User']['id']
                                        )),
             'title' => __('Report User'),
             'innerHtml'=> __('Report User'),
     ));
 ?>
                          </li>
			<?php if ( !empty($uid) && $areFriends ): ?>
            <li><?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "friends",
                                            "action" => "ajax_remove",
                                            "plugin" => false,
                                            $user['User']['id']
                                            
                                        )),
             'title' => __('Unfriend'),
             'innerHtml'=> __('Unfriend'),
     ));
 ?></li>
            <?php endif; ?>		
            <?php if ( !empty($uid) && ($uid != $user['User']['id'] ) && $user['User']['role_id'] != 1): ?>
            <li><?php
                if(!$is_viewer_block){
                    $this->MooPopup->tag(array(
                        'href'=>$this->Html->url(array("controller" => "user_blocks",
                                            "action" => "ajax_add",
                                            "plugin" => false,
                                             $user['User']['id']
                                            
                                        )),
                            'title' => __('Block'),
                            'innerHtml'=> __('Block'),
                         ));
                }else{
                    $this->MooPopup->tag(array(
                        'href'=>$this->Html->url(array("controller" => "user_blocks",
                                            "action" => "ajax_remove",
                                            "plugin" => false,
                                            $user['User']['id']
                                            
                                        )),
                            'title' => __('Unblock'),
                            'innerHtml'=> __('Unblock'),
                         ));
                }
 ?></li>
            <?php endif; ?>	
		</ul>	
            </div>
	</div>	
<?php $this->end(); ?>

<div class="profilePage ">
	<div id="profile-content">
		<?php 
		if ( !empty( $activity ) )
		{   
			echo '<ul class="list6 comment_wrapper" id="list-content">';
                        ?>
                        <?php if (isset($groupTypeItem['type'])): ?>
                            <script>
                                
                            </script>

                            <?php if($groupTypeItem['type'] == PRIVACY_RESTRICTED && !$groupTypeItem['is_member']): ?>
                            <div class="privacy_mess">
                                <div class="m_b_5"><?php echo __('This content is private'); ?></div>
                                <a href="javascript:void(0);" onclick="return requestJoinGroup(<?php echo $groupTypeItem['id']; ?>);" class="btn btn-action"><?php echo __('Join Group to access'); ?></a>
                            </div>
                            <?php elseif($groupTypeItem['type'] == PRIVACY_PRIVATE && !$groupTypeItem['is_member']): ?>
                                <div class="privacy_mess"><?php echo __('This is a private group. You must be invited by a group admin in order to join'); ?></div>

                            <?php else: ?>
                                <?php if (Configure::read('core.comment_sort_style') == COMMENT_RECENT): ?>
                                    <?php echo $this->element( 'activities', array( 'activities' => array( $activity ) ) ); ?>
                                <?php elseif(Configure::read('core.comment_sort_style') == COMMENT_CHRONOLOGICAL): ?>
                                    <?php echo $this->element( 'activities_chrono', array( 'activities' => array( $activity ) ) ); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php elseif(isset($eventTypeItem) && empty($eventTypeItem)): ?>
                            <div class="privacy_mess"><?php echo __('This is a private event.'); ?></div>
                        <?php else: ?>
                            <?php if (Configure::read('core.comment_sort_style') == COMMENT_RECENT): ?>
                                <?php echo $this->element( 'activities', array( 'activities' => array( $activity ) ) ); ?>
                            <?php elseif(Configure::read('core.comment_sort_style') == COMMENT_CHRONOLOGICAL): ?>
                                <?php echo $this->element( 'activities_chrono', array( 'activities' => array( $activity ) ) ); ?>
                            <?php endif; ?>
                        <?php endif; ?>
			<?php echo '</ul>';
		}
		else
		{		
			if ( $canView )
				echo $this->element('ajax/profile_detail');
			else
				printf( __('<div class="privacy_profile full_content p_m_10">%s only shares some information with everyone</div>'), $user['User']['name'] );
		}		
		?>
	</div>
</div> 