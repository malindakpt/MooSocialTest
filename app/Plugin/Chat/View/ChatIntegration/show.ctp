<span class="arr-notify"></span><ul class="initSlimScroll">
<?php if (empty($messages)): ?>
    <li class="notify_no_content"><?php echo __('No more results found')?></li>
<?php else: ?>
    <?php foreach ($messages as $message):
        $members = Hash::extract($rooms[$message["ChatMessage"]["room_id"]],"ChatRoomsMember.{n}[user_id!=$viewerId].user_id");

        $name = array();
        $imgs = array();
        $limit = 0;
        foreach ($members as $member) {
            if(isset($users[$member]["name"])){
                array_push($name, $users[$member]["name"]);
                if($limit < 4){
                    array_push($imgs,'<img class="moochat_userscontentavatarimage" src="'.$this->Moo->getImageUrl(array("User"=>$users[$member]), array('prefix' => '50_square')).'" prefix="50_square" alt="root"> ');
                }
            }else{
                array_push($name, __d('chat',"Account Deleted"));
            }
            $limit++;


        }


    ?>
    <li <?php echo $status[$message["ChatMessage"]['id']] ? 'class="unread"' : ''?>>
    <a href="<?php echo $this->request->base?>/chat/messages/<?php echo $message["ChatMessage"]['room_id']?>">
        <div <?php if(count($imgs) > 1): ?>class="mooGroup"<?php endif; ?>  >
            <span class="moochat_userscontentavatar <?php if(count($imgs) == 2): ?>two_member<?php elseif(count($imgs) == 3): ?>three_member<?php endif; ?>">
            <?php
            foreach($imgs as $img){
                echo $img;
            }
            ?>
            </span>
        </div>
        <div class="notification_content">
            <span><b><?php echo h($this->Text->truncate(implode(", ",$name ), 100, array('exact' => false)))?></b></span>
            <div><?php echo $this->Text->truncate($this->Message->export($message["ChatMessage"], $users, true,false), 300, array('exact' => false))?></div>
            <span class="date">
                      <?php echo $this->Time->niceShort($message["ChatMessage"]['created']) ?>
        </span>
            </div></a></li>
    <?php endforeach; ?>
<?php endif; ?>
    
</ul><li class="more-notify"><a id="messages" href="<?php echo $this->request->base?>/home/index/tab:messages"><?php echo __('View All Message')?></a></li>