<?php echo $this->Html->script(array('jquery.mp.min'), array('inline' => false));?>
<?php echo $this->Html->css(array('jquery.mp'), null, array('inline' => false));?>
<?php  $this->Html->scriptStart(array('inline' => false));   ?>
    jQuery(document).ready(function(){
        $("#comments li").hover(
            function () {
                $(this).contents('.cross-icon').show();
            },
            function () {
                $(this).contents('.cross-icon').hide();
            }
        );
    });

    function removeItemComment(id)
    {
        $.fn.SimpleModal({
            btn_ok: '<?php echo addslashes(__('OK'))?>',
            btn_cancel: '<?php echo addslashes(__('Cancel'))?>',
            callback: function(){
                $.post('<?php echo $this->request->base?>/comments/ajax_remove', {id: id}, function() {
                    $('#itemcomment_'+id).fadeOut('normal', function() {
                        $('#itemcomment_'+id).remove();
                        $('#comment_count').html( parseInt($('#comment_count').html()) - 1 );
                    });
                });
            },
            title: '<?php echo addslashes(__('Please Confirm'))?>',
            contents: "<?php echo addslashes(__('Are you sure you want to remove this comment?'))?>",
            model: 'confirm', hideFooter: false, closeButton: false
        }).showModal();
    }
    function removeActivityComment(id)
    {
        $.fn.SimpleModal({
            btn_ok: '<?php echo  addslashes(__('OK')) ?>',
            callback: function() {
                $.post('<?php echo  $this->request->base ?>/activities/ajax_removeComment', {id: id}, function() {
                    $('#comment_' + id).fadeOut('normal', function() {
                        $('#comment_' + id).remove();
                    });
                });
            },
            title: '<?php echo  addslashes(__('Please Confirm')) ?>',
            contents: "<?php echo  addslashes(__('Are you sure you want to remove this comment?')) ?>",
            model: 'confirm', hideFooter: false, closeButton: false
        }).showModal();
    }
<?php $this->Html->scriptEnd();  ?>

<?php
if ( (count( $comments )+ count($activity_comments)) > 0):
    //Item comments
    foreach ($comments as $comment):
        ?>
        <li id="itemcomment_<?php echo $comment['Comment']['id']?>" style="position: relative">
            <?php
            // delete link available for commenter, site admin and item author (except convesation)
            if ( ( $this->request->controller != Inflector::pluralize(APP_CONVERSATION) ) && ( $comment['Comment']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $data['admins'] ) && in_array( $uid, $data['admins'] ) ) ) ):
                ?>
                <div class="dropdown edit-post-icon comment-option">
                    <a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
                        <i class="material-icons">more_vert</i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($comment['Comment']['user_id'] == $uid || $cuser['Role']['is_admin'] ):?>
                            <li>
                                <a href="javascript:void(0)" data-id="<?php echo $comment['Comment']['id']?>" data-photo-comment="0" class="editItemComment">
                                    <?php echo __('Edit Comment'); ?>
                                </a>
                            </li>
                        <?php endif;?>

                        <li>
                            <a class="admin-or-owner-confirm-delete-item-comment removeItemComment" href="javascript:void(0)" data-photo-comment="0" data-id="<?php echo $comment['Comment']['id']?>"  >
                                <?php echo __('Delete Comment'); ?>
                            </a>
                        </li>


                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']), array('prefix' => '100_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
            <div class="comment hasDelLink">

                <div class="comment_message" id="item_feed_comment_text_<?php echo $comment['Comment']['id']?>">
                    <?php echo $this->Moo->getName($comment['User'])?>
                    <?php echo $this->viewMore( h($comment['Comment']['message']))?>
                    <div class="search-more" style="margin-top:0px;">
                        <a href="<?php echo $comment['Comment']['view_link']?>" class="button"><?php echo __('Go to comment')?></a>
                    </div>
                    <?php if ($comment['Comment']['thumbnail']):?>
                        <div class="comment_thumb">
                            <a data-dismiss="modal" href="<?php echo $this->Moo->getImageUrl($comment,array());?>">
                                <?php echo $this->Moo->getImage($comment,array('prefix'=>'200'));?>
                            </a>
                        </div>
                    <?php endif;?>
                </div>

			<span class="feed-time date">
				<?php echo $this->Moo->getTime( $comment['Comment']['created'], Configure::read('core.date_format'), $utz )?>
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "histories",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'core_activity_comment',
                                            $comment['Comment']['id']
                                        )),
             'title' => __('Show edit history'),
             'innerHtml'=> __('Edited'),
          'class'=>'edit-btn',
          'id' => 'history_activity_comment_' . $comment['Comment']['id'],
          'style' => empty($comment['Comment']['edited']) ? 'display:none;' : '',
          'data-dismiss'=>'modal'
     ));
 ?>
                            

            </span>

            </div>
        </li>
    <?php
    endforeach;
    //Activity comments
    foreach ($activity_comments as $comment):
        ?>
        <li id="comment_<?php echo $comment['ActivityComment']['id']?>" style="position: relative">
            <?php
            // delete link available for commenter, site admin and item author (except convesation)
            if ( ( $this->request->controller != Inflector::pluralize(APP_CONVERSATION) ) && ( $comment['ActivityComment']['user_id'] == $uid || ( $uid && $cuser['Role']['is_admin'] ) || ( !empty( $data['admins'] ) && in_array( $uid, $data['admins'] ) ) ) ):
                ?>
                <div class="dropdown edit-post-icon comment-option">
                    <a href="javascript:void(0)" data-toggle="dropdown" class="cross-icon">
                        <i class="material-icons">more_vert</i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ($comment['ActivityComment']['user_id'] == $uid || $cuser['Role']['is_admin'] ):?>
                            <li>
                                <a href="javascript:void(0)" data-activity-comment-id="<?php echo $comment['ActivityComment']['id']?>" class="editActivityComment">
                                    <?php echo __('Edit Comment'); ?>
                                </a>
                            </li>
                        <?php endif;?>

                        <li>
                            <a class=" removeActivityComment" data-activity-comment-id="<?php echo $comment['ActivityComment']['id'] ?>" href="javascript:void(0)"  >
                                <?php echo __('Delete Comment'); ?>
                            </a>
                        </li>


                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $this->Moo->getItemPhoto(array('User' => $comment['User']), array('prefix' => '100_square'), array('class' => 'img_wrapper2 user_avatar_large'))?>
            <div class="comment hasDelLink">

                <div class="comment_message" id="activity_feed_comment_text_<?php echo $comment['ActivityComment']['id']?>">
                    <?php echo $this->Moo->getName($comment['User'])?>
                    <?php echo $this->viewMore( h($comment['ActivityComment']['comment']))?>
                    <div class="search-more" style="margin-top:0px;">
                        <a href="<?php echo $this->request->base?>/users/view/<?php echo $comment['ActivityComment']['user_id']?>/activity_id:<?php echo $comment['ActivityComment']['activity_id']?>" class="button"><?php echo __('Go to comment')?></a>
                    </div>
                    <?php if ($comment['ActivityComment']['thumbnail']):?>
                        <div class="comment_thumb">
                            <a data-dismiss="modal" href="<?php echo $this->Moo->getImageUrl($comment,array());?>">
                                <?php echo $this->Moo->getImage($comment,array('prefix'=>'200'));?>
                            </a>
                        </div>
                    <?php endif;?>
                </div>

			<span class="feed-time date">
				<?php echo $this->Moo->getTime( $comment['ActivityComment']['created'], Configure::read('core.date_format'), $utz )?>
                <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "histories",
                                            "action" => "ajax_show",
                                            "plugin" => false,
                                            'core_activity_comment',
                                            $comment['ActivityComment']['id']
                                        )),
             'title' => __('Show edit history'),
             'innerHtml'=> __('Edited'),
          'id' => 'history_activity_comment_'. $comment['ActivityComment']['id'],
          'class' => 'edit-btn',
          'style' => empty($comment['ActivityComment']['edited']) ? 'display:none;' : '',
          'data-dismiss'=>'modal'
     ));
 ?>
                            

            </span>

            </div>
        </li>
    <?php
    endforeach;

else:
    echo '<div align="center">' . __( 'No more results found') . '</div>';
endif;
?>
<?php if (isset($more_url) && (count($comments)+count($activity_comments)) >= RESULTS_LIMIT): ?>
    <?php $this->Html->viewMore($more_url) ?>
<?php endif; ?>