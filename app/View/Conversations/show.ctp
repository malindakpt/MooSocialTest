<span class="arr-notify"></span><ul class="initSlimScroll">
<?php if (empty($conversations)): ?>
    <li class="notify_no_content"><?php echo __('No more results found')?></li>
<?php else: ?>
    <?php foreach ($conversations as $conversation): ?>
    <li <?php echo $conversation['ConversationUser']['unread'] ? 'class="unread"' : ''?>>
    <a href="<?php echo $this->request->base?>/conversations/view/<?php echo $conversation['Conversation']['id']?>">
            <?php echo $this->Moo->getImage(array('User' => $conversation['Conversation']['LastPoster']),array( "width" => "45px", "class" => "img_wrapper2", 'prefix' => '50_square'))?>
            <div class="notification_content">
            <span><b><?php echo h($conversation['Conversation']['subject'])?></b></span>
            <div><?php echo h($this->Text->truncate($conversation['Conversation']['message'], 85, array('exact' => false)))?></div>
            <span class="date">
                    <?php echo __n("%s message", "%s messages", $conversation['Conversation']['message_count'],$conversation['Conversation']['message_count'])?>&nbsp;
                    <?php echo  __('Participants') . ':' ?>
                    <?php
                    $i = 1;
                    $count = count($conversation['Conversation']['ConversationUser']);
                    foreach ($conversation['Conversation']['ConversationUser'] as $user):
                        echo $this->Moo->getNameWithoutUrl($user['User'], true);
                        $remaining = $count - $i;

                        if ($i == $count)
                            break;
                        elseif ($i >= 3 && ( $remaining > 0 )) {
                            echo ' and ' .$remaining .' others';
                            break;
                        } else
                            echo ', ';
                        $i++;
                    endforeach;
                    ?>
        </span>
            </div></a></li>
    <?php endforeach; ?>
<?php endif; ?>
    
</ul><li class="more-notify"><a id="messages" href="<?php echo $this->request->base?>/home/index/tab:messages"><?php echo __('View All Message')?></a></li>