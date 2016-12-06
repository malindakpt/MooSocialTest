<?php if($this->request->is('ajax')): ?>
<script>
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initRespondRequest();
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false,'requires'=>array('jquery','mooUser'),'object'=>array('$','mooUser'))); ?>
    mooUser.initRespondRequest();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php
if(empty($title)) $title = "Featured Members";
if(empty($num_item_show)) $num_item_show = 10;

$friends = $this->requestAction(
    "users/friends/num_item_show:$num_item_show/user_id:$uid"
);
?>

<div class="profile-header">


    <div id="cover">
        <?php if ( !empty( $user['User']['cover'] ) ): ?>
        <img id="cover_img_display" width="100%" src="<?php echo $this->request->webroot?>uploads/covers/<?php echo $user['User']['cover']?>" />
        <?php else: ?>
        <img id="cover_img_display" width="100%" src="<?php echo $this->request->webroot?>img/cover.jpg" />
        <?php endif; ?>
        <?php if ( !empty( $cover_album_id ) ): ?>
            <a href="<?php echo $this->request->base?>/albums/view/<?php echo $cover_album_id?>"></a>
        <?php endif; ?>

        <?php if ( $uid == $user['User']['id'] ): ?>
            <div id="cover_upload">
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "users",
                                            "action" => "ajax_cover",
                                            "plugin" => false,

                                        )),
             'title' => __('Edit Cover Picture'),
             'innerHtml'=> __('Edit Cover Picture'),
          'data-backdrop' => 'static',
     ));
 ?>

            </div>
        <?php endif; ?>
    </div>
    <div id="avatar">
            <?php if ( !empty( $profile_album_id ) ): ?>
                <a href="<?php echo $this->request->base?>/albums/view/<?php echo $profile_album_id?>">
                    <?php echo $this->Moo->getItemPhoto(array('User' => $user['User']), array('prefix' => '200_square'), array("id" => "av-img"))?>
                </a>
            <?php else: ?>
                <?php echo $this->Moo->getItemPhoto(array('User' => $user['User']), array('prefix' => '200_square'), array("id" => "av-img"))?>
            <?php endif; ?>

            <?php if ( $uid == $user['User']['id'] ): ?>
                <div id="avatar_upload" >
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "users",
                                            "action" => "ajax_avatar",
                                            "plugin" => false,
                                        )),
             'title' => __('Edit Profile Picture'),
             'innerHtml'=> __('Edit Profile Picture'),
             'data-backdrop' => 'static'
     ));
 ?>

                </div>
            <?php endif; ?>
        <?php if ( !empty($is_online)): ?>
                <span class="online-stt">
                </span>
        <?php endif; ?>
    </div>
    <div class="section-menu"><?php $this->Html->rating($uid,'profile'); ?>
        <div class="profile-action">
            <?php $this->getEventManager()->dispatch(new CakeEvent('View.Elements.User.headerProfile.beforeRenderSectionMenu', $this)); ?>

            <?php if ($user['User']['id'] != $uid && !empty($uid)): ?>


<?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "conversations",
                                            "action" => "ajax_send",
                                            "plugin" => false,
                                            $user['User']['id']
                                        )),
             'title' => __('Send New Message'),
             'innerHtml'=> '<i class="visible-xs visible-sm material-icons">chat</i><i class="hidden-xs hidden-sm">' . __('Send Message') . '</i>',
          'class'=>'topButton button button-action'
     ));
 ?>


            <?php if ( !empty($request_sent) ): ?>
            <a id="userCancelFriend" href="<?php echo $this->request->base?>/friends/ajax_cancel/<?php echo $user['User']['id']?>" class="topButton button button-action" title="<?php __('Cancel a friend request');?>">
                <i class="visible-xs visible-sm material-icons">clear</i><i class="hidden-xs hidden-sm"><?php echo __('Cancel Request')?></i>
            </a>
            <?php endif; ?>

            <?php if ( !empty($respond) ): ?>
            <div class="dropdown" style="float:right" >
                <a id="respond" data-target="#" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false" class="topButton button button-action" title="<?php __('Respond to Friend Request');?>">
                    <i class="visible-xs visible-sm material-icons">person_add</i><i class="hidden-xs hidden-sm"><?php echo __('Respond to Friend Request')?></i>
                </a>

                <ul class="dropdown-menu" role="menu" aria-labelledby="respond">
                    <li><a data-id="<?php echo  $request_id; ?>" data-status="1" class="respondRequest" href="javascript:void(0)"><?php echo  __('Accept'); ?></a></li>
                    <li><a data-id="<?php echo  $request_id; ?>" data-status="0" class="respondRequest" href="javascript:void(0)"><?php echo  __('Delete'); ?></a></li>
                </ul>
            </div>

            <?php endif; ?>

            <?php if ( !empty($uid) && !$areFriends && empty($request_sent) && empty($respond) ): ?>
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "friends",
                                            "action" => "ajax_add",
                                            "plugin" => false,
                                            $user['User']['id']
                                        )),
             'title' => sprintf( __('Send %s a friend request'), h($user['User']['name']) ),
             'innerHtml'=> '<i class="visible-xs visible-sm material-icons">person_add</i><i class="hidden-xs hidden-sm">' . __('Add as Friend') .'</i>',
          'id' => 'addFriend_'. $user['User']['id'],
          'class' => 'topButton button button-action'
     ));
 ?>
            <?php endif; ?>

        <?php endif;?>

        <?php if ($uid && Configure::read("core.enable_follow") && $uid != $user['User']['id']): ?>
            <?php
            $followModel = MooCore::getInstance()->getModel("UserFollow");
            $follow = $followModel->checkFollow($uid,$user['User']['id']);
            ?>
            <?php if (!$follow): ?>
                <a href="javascript:void(0);" class="button button-action user_action_follow" data-uid="<?php echo $user['User']['id']; ?>" data-follow="0" >
                    <i class="visible-xs visible-sm material-icons">rss_feed</i><i class="hidden-xs hidden-sm">
                        <?php echo __('Follow')?></i></a>
            <?php else : ?>
                <a href="javascript:void(0);" class="button button-action user_action_follow" data-uid="<?php echo $user['User']['id']; ?>" data-follow="1" >
                    <i class="visible-xs visible-sm material-icons"">check</i><i class="hidden-xs hidden-sm">
                        <?php echo __('Unfollow')?></i></a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($user['User']['id'] == $uid): ?>
            <a href="<?php echo $this->request->base?>/users/profile" class="button button-action" >
            <i class="visible-xs visible-sm material-icons">mode_edit</i><i class="hidden-xs hidden-sm">
                <?php echo __('Edit Profile')?></i></a>
        <?php endif; ?>
         </div>


    </div>


    <div class="profile-info-section">
        <h1><?php echo h($this->Text->truncate($user['User']['name'], 30, array('exact' => false)))?></h1>
    </div>

    <?php if ( $canView ): ?>
            <ul class="list3 profile_info">
                    <?php if ( !empty( $user['User']['gender'] ) ): ?>
                        <li style="background:none;padding:0"><span class="date"><?php echo __('Gender')?>:</span> <?php $this->Moo->getGenderTxt($user['User']['gender']); ?></li>
                    <?php endif; ?>
                <?php if ( !empty( $user['User']['birthday'] ) && $user['User']['birthday'] != '0000-00-00'): ?>
                        <li><span class="date"><?php echo __('Born on')?>:</span> <?php echo $this->Time->event_format($user['User']['birthday'], '%B %d')?></li>
                    <?php endif; ?>
                     <?php foreach ($fields as $field):
                         if (!in_array($field['ProfileField']['type'],array('heading','textfield','list','multilist')))
                         {
                             $options = array();
                             if ($field['ProfileField']['plugin'])
                             {
                                 $options = array('plugin' => $field['ProfileField']['plugin']);
                             }

                             echo $this->element('profile_field/' . $field['ProfileField']['type'].'_profile', array('field' => $field,'user'=>$user),$options);
                             continue;
                         }
                           if ( !empty( $field['ProfileFieldValue']['value'] ) && $field['ProfileField']['type'] != 'heading' ) :
                        ?>
                                <li><span class="date"><?php echo $field['ProfileField']['name']?>: </span>
                                        <?php echo $this->element( 'misc/custom_field_value', array( 'field' => $field ) ); ?>
                                </li>
                        <?php endif;
                    endforeach;
                        ?>
                </ul>
        <?php endif; ?>
    <div class="profile_info">
        <?php echo $this->Html->rating($user['User']['id'],'users'); ?>
    </div>
</div>