<?php
    echo $this->Html->script(array('Chat.mooChat-admin.js'), array('inline' => false));
    $this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
    $this->Html->addCrumb(__d('chat','Chat Monitor'), array('controller' => 'chat_monitor', 'action' => 'admin_index'));
    $this->startIfEmpty('sidebar-menu');
    echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
    $this->end();
?>
<?php echo$this->Moo->renderMenu('Chat',__d('chat','Monitor'));?>
<div id="chatGeneral"></div>
<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(document).ready(function(){
window.mooChat().renderMonitor();
});
<?php $this->Html->scriptEnd(); ?>

