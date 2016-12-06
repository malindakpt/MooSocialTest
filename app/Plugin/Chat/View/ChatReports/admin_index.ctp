<?php
    $this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
    $this->Html->addCrumb(__d('chat','Chat Report'), array('controller' => 'chat_reports', 'action' => 'admin_index'));
    $this->startIfEmpty('sidebar-menu');
    echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
    $this->end();
?>
<?php echo$this->Moo->renderMenu('Chat',__d('chat','Report'));?>

<div class="portlet-body">
    <div class="table-toolbar">
        <div class="row">
            <div class="col-md-6">
                <div class="btn-group">
                    <button class="btn btn-gray" id="sample_editable_1_new" onclick="confirmSubmitForm('<?php echo __d("chat",'Are you sure you want to delete these reports')?>', 'deleteForm')">
                        <?php echo  __d("chat",'Delete');?>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div id="sample_1_filter" class="dataTables_filter"><label>
                        <form method="post" action="<?php echo $this->request->base?>/admin/chat/chat_reports">
                            <?php echo $this->Form->text('keyword', array('class' => 'form-control input-medium input-inline','value' => $keyword, 'placeholder' => __d("chat",'Search by reason')));?>
                            <?php echo $this->Form->submit('', array( 'style' => 'display:none' ));?>
                        </form>
                    </label></div>
            </div>
        </div>
    </div>
    <form method="post" action="<?php echo $this->request->base?>/admin/chat/chat_reports/delete" id="deleteForm">
        <table class="table table-striped table-bordered table-hover" id="sample_1">
            <thead>
            <tr>
                <?php if ($cuser['Role']['is_super']): ?>

                    <th width="30"><input type="checkbox" onclick="toggleCheckboxes2(this)"></th>
                <?php endif; ?>
                <th><?php echo $this->Paginator->sort('id', __d("chat",'ID')); ?></th>
                <th><?php echo $this->Paginator->sort('reason', __d("chat",'Reason')); ?></th>
                <th><?php echo $this->Paginator->sort('room_id', __d("chat",'Room')); ?></th>
                <th data-hide="phone"><?php echo $this->Paginator->sort('User.name', __d("chat",'Reported By')); ?></th>
                <th data-hide="phone"><?php echo $this->Paginator->sort('created', __d("chat",'Date')); ?></th>

            </tr>
            </thead>
            <tbody>

            <?php $count = 0;
            foreach ($data as $report): ?>
                <tr class="gradeX <?php (++$count % 2 ? "odd" : "even") ?>">
                    <?php if ( $cuser['Role']['is_super'] ): ?>
                        <td><input type="checkbox" name="reports[]" value="<?php echo $report['ChatReport']['id']?>" class="check"></td>
                    <?php endif; ?>
                    <td><?php echo $report['ChatReport']['id']?></td>
                    <td><?php echo h($report['ChatReport']['reason']); ?></td>
                    <td>
                    <?php
                    echo $this->Html->link(
                        $report['ChatReport']['room_id'],
                        array(
                            'controller' => 'ChatLogs',
                            'action' => 'admin_messages',
                            'full_base' => true,
                            $report['ChatReport']['room_id']
                        )
                    );

                    ?>
                    </td>
                    <td><a href="<?php echo $this->request->base?>/admin/users/edit/<?php echo $report['User']['id']?>"><?php echo h($report['User']['name'])?></a></td>
                    <td><?php echo $this->Time->niceShort($report['ChatReport']['created'])?></td>
                </tr>
            <?php endforeach ?>

            </tbody>
        </table>
    </form>
    <div class="pagination pull-right">
        <?php echo $this->Paginator->prev('« '.__('Previous'), null, null, array('class' => 'disabled')); ?>
        <?php echo $this->Paginator->numbers(); ?>
        <?php echo $this->Paginator->next(__('Next').' »', null, null, array('class' => 'disabled')); ?>
    </div>
</div>