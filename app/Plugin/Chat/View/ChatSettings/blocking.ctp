<?php


$this->addPhraseJs(array(
    'chat_unblock'=>__d("chat", "Unblock"),
));
?>


<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires' => array('webChat'), 'object' => array('webChat'))); ?>
webChat.initOnBlockingPage();
<?php $this->Html->scriptEnd(); ?>









<?php $this->end(); ?>

<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <div class="post_body">
            <div class="mo_breadcrumb">
                <h1><?php echo __d("chat", "Block Settings") ?></h1>

            </div>

            <div class="post_content">
                <div class="row">

                    <div class="col-md-12">
                        <p><?php echo __d("chat", "Once you block someone, that person can no longer  start a conversation with you"); ?></p>
                        <div id="blocking-userlist">
                            <?php foreach ($data as $user): ?>
                                <div class="row">
                                    <?php echo $user["User"]["name"]; ?>

                                    &nbsp;

                                    <a href="#" class="chat-unblock-action" data-id="<?php echo $user["User"]["id"]; ?>"
                                       style="display:none"> 
                                        <?php echo __d("chat", "Unblock") ?>
                                    </a>

                                </div>
                            <?php endforeach; ?>

                        </div>

                    </div>
                </div>

            </div>


        </div>
    </div>
</div>



