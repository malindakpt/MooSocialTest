<?php
if (!empty($messages)) {
    foreach ($messages as $message):

        $members = Hash::extract($rooms[$message["ChatMessage"]["room_id"]], "ChatRoomsMember.{n}[user_id!=$viewerId].user_id");

        $name = array();
        $imgs = array();
        $limit = 0;
        foreach ($members as $member) {
            if (isset($users[$member]["name"])) {
                array_push($name, $users[$member]["name"]);
                if ($limit < 4) {
                    array_push($imgs, '<img class="moochat_userscontentavatarimage" src="' . $this->Moo->getImageUrl(array("User" => $users[$member]), array('prefix' => '50_square')) . '" prefix="50_square" alt="root"> ');
                }
            } else {
                array_push($name, __d('chat', "Account Deleted"));
            }
            $limit++;


        }

        ?>
        <li <?php if ($status[$message["ChatMessage"]['id']]) echo 'class="unread"'; ?>>
            <a href="<?php echo $this->request->base ?>/chat/messages/<?php echo $message["ChatMessage"]['room_id'] ?>">
                <div <?php if (count($imgs) > 1): ?>class="mooGroup"<?php endif; ?> >
                            <span
                                class="moochat_userscontentavatar <?php if (count($imgs) == 2): ?>two_member<?php elseif (count($imgs) == 3): ?>three_member<?php endif; ?>">
                            <?php
                            foreach ($imgs as $img) {
                                echo $img;
                            }
                            ?>
                            </span>
                </div>
            </a>
            <div class="comment">
                <a href="<?php echo $this->request->base ?>/chat/messages/<?php echo $message["ChatMessage"]['room_id'] ?>"><b><?php echo h($this->Text->truncate(implode(", ",$name ), 100, array('exact' => false)))?></b></a>
                <div
                    class="comment_message"><?php echo $this->Text->truncate($this->Message->export($message["ChatMessage"], $users, true), 85, array('exact' => false))?></div>
                <span class="date">
				<?php echo $this->Time->niceShort($message["ChatMessage"]['created']) ?>
			</span>

                <a style="<?php if (!$status[$message["ChatMessage"]['id']]) echo 'display:none;' ?>"
                   href="javascript:void(0)" data-id="<?php echo $message["ChatMessage"]['id'] ?>"
                   data-status="0" class="markMsgStaus tip mark_section mark_read"
                   title="<?php echo __('Mark as Read') ?>">
                    <i class="material-icons">check_circle</i>
                </a>
                <a style="<?php if ($status[$message["ChatMessage"]['id']]) echo 'display:none;' ?>"
                   href="javascript:void(0)" data-id="<?php echo $message["ChatMessage"]['id'] ?>"
                   data-status="1" class="markMsgStaus tip mark_section mark_unread"
                   title="<?php echo __('Mark as unRead') ?>">
                    <i class="material-icons">check_circle</i>
                </a>

            </div>
        </li>
        <?php
    endforeach;
} else
    echo '<div align="center" style="margin-top:10px">' . __('No more results found') . '</div>';
?>

<?php if (count($messages) >= RESULTS_LIMIT): ?>

    <?php $this->Html->viewMore($more_url); ?>
<?php endif; ?>

<?php if ($this->request->is('ajax')): ?>
    <script type="text/javascript">
        require(["jquery", "mooBehavior", "mooGlobal"], function ($, mooBehavior, mooGlobal) {
            mooBehavior.initMoreResults();
            mooGlobal.initMsgList();
        });
    </script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires' => array('jquery', 'mooBehavior', 'mooGlobal'), 'object' => array('$', 'mooBehavior', 'mooGlobal'))); ?>
    mooBehavior.initMoreResults();
    mooGlobal.initMsgList();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>