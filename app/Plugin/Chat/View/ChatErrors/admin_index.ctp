<?php
    $this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
    $this->Html->addCrumb(__d('chat','Chat Error'), array('controller' => 'chat_errors', 'action' => 'admin_index'));
    $this->startIfEmpty('sidebar-menu');
    echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
    $this->end();
?>
<?php echo$this->Moo->renderMenu('Chat',__d('chat','Error'));?>

<div class="row">
    <div class="col-md-12" style="padding-top: 5px;">
        <div class="note note-info">
           <?php

                $filename = __DIR__.DS."..".DS."..".DS."webroot".DS."js".DS."server".DS."log".DS."error-all.log";
                if (file_exists($filename)) {
                   ?>
                    You can download <a href="./chat_errors/download"  >Error file </a> for checking to  keep your chat server running smoothly.
                    <?php
                } else {
                    echo "The file $filename does not exist";
                }
           ?>
        </div>
    </div>
</div>