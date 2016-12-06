<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooUser"], function($,mooUser) {
        mooUser.initEditProfilePicture();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooUser'), 'object' => array('$', 'mooUser'))); ?>
mooUser.initEditProfilePicture();
<?php $this->Html->scriptEnd(); ?> 
<?php endif; ?>

<?php $this->setCurrentStyle(4); ?>

<div class="title-modal">
    <?php echo __('Profile Picture')?>
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
    <div id="avatar_wrapper" style="vertical-align: top;margin: 0 10px 10px 0">
        <?php echo $this->Moo->getImage(array('User' => $cuser), array("id" => "av-img2", 'prefix' => '600'))?>
    </div>

    <div class="Metronic-alerts alert alert-warning fade in"><?php echo __("Optimal size 200x200px"); ?></div>
    <div id="select-0" class="ava-upload"></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-action save-avatar"><span aria-hidden="true"><?php echo __('Save Thumbnail')?></span></button>
</div>
