<?php
    $this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
    $this->Html->addCrumb(__d('chat','Chat Permission'), array('controller' => 'chat_permissions', 'action' => 'admin_index'));
    $this->startIfEmpty('sidebar-menu');
    echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
    $this->end();
?>
<?php echo$this->Moo->renderMenu('Chat',__d('chat','Permission'));?>

<div class="row">
    <div class="col-md-12" style="padding-top: 5px;">
        <div class="note note-info">
            <p>
                <?php echo __d("chat","This plugin supports  settings are applied on a per member level .  You can go to %s to do it",$this->Html->link(__d("chat","User Roles"),'/admin/roles')); ?>
            </p>
            <p><?php echo __d("chat","Notice that \"Guest\" Role is not allowed to see or use chat messages."); ?> </p>
        </div>
    </div>
</div>