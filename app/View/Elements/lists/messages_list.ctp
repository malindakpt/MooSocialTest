

<?php 
if ( !empty($conversations) )
{   
	foreach ($conversations as $conversation): ?>
	<li <?php if ($conversation['ConversationUser']['unread']) echo 'class="unread"';?>>
            <a href="<?php echo $this->request->base?>/conversations/view/<?php echo $conversation['Conversation']['id']?>"><?php echo $this->Moo->getImage(array('User' => $conversation['Conversation']['LastPoster']), array('prefix' => '100_square', 'class' => 'img_wrapper2', 'width' => 45))?></a>
		<div class="comment">
			<a href="<?php echo $this->request->base?>/conversations/view/<?php echo $conversation['Conversation']['id']?>"><b><?php echo h($conversation['Conversation']['subject'])?></b></a>
			<div class="comment_message"><?php echo h($this->Text->truncate($conversation['Conversation']['message'], 85, array('exact' => false)))?></div>
			<span class="date">
				<?php echo __n('%s message', '%s messages', $conversation['Conversation']['message_count'], $conversation['Conversation']['message_count'])?> .
				<?php echo __('Participants')?>:
				<?php 
				$i = 1;
                $count = count( $conversation['Conversation']['ConversationUser'] );
				foreach ( $conversation['Conversation']['ConversationUser'] as $user ):
				    echo $this->Moo->getName( $user['User'], false );
                    $remaining = $count - $i;
                    
                    if ( $i == $count )
                        break; 
                    elseif ( $i >= 3 && ( $remaining > 0  ) )
                    {
                        printf(__(' and %s others'), $remaining);
                        break;
                    }
                    else
                        echo ', ';
                    
                    $i++;
                endforeach; 
                ?>
			</span>
                        
                    <a style="<?php if (!$conversation['ConversationUser']['unread']) echo 'display:none;' ?>" href="javascript:void(0)" data-id="<?php echo $conversation['Conversation']['id']?>" data-status="0" class="markMsgStaus tip mark_section mark_read" title="<?php echo __( 'Mark as Read')?>">
                        <i class="material-icons">check_circle</i>
                    </a>
                    <a style="<?php if ($conversation['ConversationUser']['unread']) echo 'display:none;' ?>" href="javascript:void(0)" data-id="<?php echo $conversation['Conversation']['id']?>" data-status="1" class="markMsgStaus tip mark_section mark_unread" title="<?php echo __( 'Mark as unRead')?>">
                        <i class="material-icons">check_circle</i>
                    </a>
                        
		</div>
	</li>
<?php 
	endforeach;
}
else
	echo '<div align="center" style="margin-top:10px">' . __('No more results found') . '</div>';
?>

<?php if (count($conversations) >= RESULTS_LIMIT): ?>

    <?php $this->Html->viewMore($more_url); ?>
<?php endif; ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooBehavior", "mooGlobal"], function($, mooBehavior, mooGlobal) {
        mooBehavior.initMoreResults();
        mooGlobal.initMsgList();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooBehavior', 'mooGlobal'), 'object' => array('$', 'mooBehavior', 'mooGlobal'))); ?>
mooBehavior.initMoreResults();
mooGlobal.initMsgList();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>