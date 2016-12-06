<?php
//echo $this->Html->css(array('jquery-ui', 'footable.core.min'), null, array('inline' => false));
//echo $this->Html->script(array('jquery-ui', 'footable'), array('inline' => false));

$this->Html->addCrumb(__('System Admin'));
$this->Html->addCrumb(__('Profile Fields Manager'), array('controller' => 'profile_fields', 'action' => 'admin_index'));

$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array("cmenu" => "profile_fields"));
$this->end();
?>
<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(document).on('loaded.bs.modal', function (e) {
Metronic.init();
});
$(document).on('hidden.bs.modal', function (e) {
$(e.target).removeData('bs.modal');
});
jQuery(document).ready(function(){
    var fixHelperModified = function(e, tr) {         
        var $originals = tr.children();         
        var $helper = tr.clone();         
        $helper.children().each(function(index){           
            jQuery(this).width($originals.eq(index).width())        
        });         
            return $helper;     
    }; 
    jQuery( "#sample_1" ).sortable( {
        items: "tr:not(.tbl_head)",
        handle: ".reorder",
         helper: fixHelperModified,
        update: function(event, ui) {
            var list = jQuery('#sample_1').sortable('toArray');
            jQuery.post('<?php echo $this->request->base?>/admin/profile_fields/ajax_reorder', { fields: list });
        }
    });
})
<?php $this->Html->scriptEnd(); ?>

    <div class="portlet-body">
        <div class="table-toolbar">
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group">
                        <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "profile_fields",
                                            "action" => "admin_ajax_create",
                                            "plugin" => false,
                                           
                                        )),
             'title' => __('Add New Field'),
             'innerHtml'=> __('Add New Field'),
          'class' => 'btn btn-gray'
     ));
 ?>
                        
                    </div>

                </div>
                <div class="col-md-6">

                </div>
            </div>
            <div class="row">
                <div class="col-md-12" style="padding-top: 5px;">
                    <div class="note note-info hide">

                        <p>
                            <?php echo __('You can enable Spam Challenge to force user to answer a challenge question in order to register.');?> <br/>
                            <?php echo __('To enable this feature, click System Settings -> Security -> Enable Spam Challenge');?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped table-bordered table-hover" id="sample_1">
            <thead>
            <tr class="tbl_head">
                <th width="50px"><?php echo  __('ID');?></th>
                <th width="250px"><?php echo  __('Name');?></th>
                <th width="50px"><?php echo  __('Type');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Required');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Registration');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Searchable');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Profile');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Active');?></th>
                <th width="50px" data-hide="phone"><?php echo  __('Actions');?></th>
            </tr>
            </thead>
            <tbody>

            <?php $count = 0;
            foreach ($fields as $field): ?>
                <tr class="gradeX <?php (++$count % 2 ? "odd" : "even") ?>" id="<?php echo $field['ProfileField']['id']?>">
                    <td width="50px"><?php echo $field['ProfileField']['id']?></td>
                    <td width="300px" class="reorder"><?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "profile_fields",
                                            "action" => "admin_ajax_create",
                                            "plugin" => false,
                                            $field['ProfileField']['id']
                                            
                                        )),
             'title' => $field['ProfileField']['name'],
             'innerHtml'=> $field['ProfileField']['name'],
     ));
 ?></td>
                    <td width="50px" class="reorder"><?php echo $field['ProfileField']['type']?></td>
                    <td width="50px" class="reorder"><?php echo ($field['ProfileField']['required']) ? __('Yes') : __('No')?></td>
                    <td width="50px" class="reorder"><?php echo ($field['ProfileField']['registration']) ? __('Yes') : __('No')?></td>
                    <td width="50px" class="reorder"><?php echo ($field['ProfileField']['searchable']) ? __('Yes') : __('No')?></td>
                    <td width="50px" class="reorder"><?php echo ($field['ProfileField']['profile']) ? __('Yes') : __('No')?></td>
                    <td width="50px" class="reorder"><?php echo ($field['ProfileField']['active']) ? __('Yes') : __('No')?></td>
                    <td width="50px"><a href="javascript:void(0)" onclick="mooConfirm('<?php echo addslashes(__('Are you sure you want to delete this field? All the items within it will also be deleted. This cannot be undone!'));?>', '<?php echo $this->request->base?>/admin/profile_fields/delete/<?php echo $field['ProfileField']['id']?>')"><i class="icon-trash icon-small"></i></a></td>

                </tr>
            <?php endforeach ?>

            </tbody>
        </table>


    </div>




