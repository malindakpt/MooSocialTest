<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
	<div class="box2 filter_block">
            <h3 class="visible-xs visible-sm"><?php echo __( 'Browse')?></h3>
            <div class="box_content">
            <?php echo $this->element('sidebar/menu'); ?>
            <?php echo $this->element('lists/categories_list') ?>
            <?php echo $this->element('sidebar/search'); ?>
            </div>
	</div>	
<?php $this->end(); ?>
<div class="bar-content">
<div class="content_center">
   
    <div class="mo_breadcrumb">
        <h1><?php echo __( 'Groups')?></h1>
        <?php if (!empty($uid)): ?>
	<a href="<?php echo $this->request->base?>/groups/create" class="button button-action topButton button-mobi-top"><?php echo __( 'Create New Group')?></a>
	<?php endif; ?>
    </div>
	<ul class="list6" id="list-content">
            <?php 
            if ( !empty( $this->request->named['category_id'] )  || !empty($cat_id) ){

                if (empty($cat_id)){
                    $cat_id = $this->request->named['category_id'];
                }

                echo $this->element( 'lists/groups_list', array( 'more_url' => '/groups/browse/category/' . $cat_id . '/page:2' ) );
            }
            else{
                echo $this->element( 'lists/groups_list', array( 'more_url' => '/groups/browse/all/page:2' ) );
            }
            ?>
	</ul>
</div>
</div>
