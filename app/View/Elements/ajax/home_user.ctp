<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooBehavior"], function($, mooBehavior) {
        mooBehavior.initMoreResults();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooBehavior'), 'object' => array('$', 'mooBehavior'))); ?>
mooBehavior.initMoreResults();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>

<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>

<style>
#list-content li {
    position: relative;
}
</style>
<div class="content_center_home">
    <a href="<?php echo $this->request->base?>/home/index/tab:invite-friends" class="topButton button button-action"><?php echo __('Invite Friends')?></a>
    <h1><?php echo __('Friends')?></h1>
    <ul class="users_list" id="list-content">
        <?php echo $this->element( 'lists/users_list' ); ?>
    </ul> 
</div>