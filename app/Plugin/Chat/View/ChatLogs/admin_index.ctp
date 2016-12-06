<?php
echo $this->Html->css(array('jquery-ui', 'footable.core.min'), null, array('inline' => false));
echo $this->Html->script(array('jquery-ui', 'footable'), array('inline' => false));
$this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
$this->Html->addCrumb(__d('chat','Chat Logs'), array('controller' => 'chat_logs', 'action' => 'admin_index'));
$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
$this->end();
?>
<?php echo $this->Moo->renderMenu('Chat', __d('chat','Logs')); ?>

<div class="portlet-body">
    <div class="table-toolbar">
        <div class="row">
            <div class="col-md-6">
                <div class="btn-group">

                </div>
            </div>
            <div class="col-md-6">
                <div id="sample_1_filter" class="dataTables_filter"><label>
                        <form method="post" action="<?php echo $this->request->base ?>/admin/chat/chat_logs">
                            <?php echo $this->Form->text('keyword', array('class' => 'form-control input-medium input-inline', 'value' => $keyword, 'placeholder' => __d("chat",'Search by username or email'))); ?>
                            <?php echo $this->Form->submit('', array('style' => 'display:none')); ?>
                        </form>
                    </label></div>
            </div>
        </div>
    </div>
    <form method="post" action="<?php echo $this->request->base ?>/admin/chat/chat_logs/delete" id="deleteForm">
        <table class="table table-striped table-bordered table-hover" id="sample_1">
            <thead>
            <tr>
                <th><?php echo $this->Paginator->sort('id', __d("chat",'ID')); ?></th>
                <th><?php echo $this->Paginator->sort('reason', __d("chat",'Room')); ?></th>
                <th data-hide="phone"><?php echo $this->Paginator->sort('created', __d("chat",'Created')); ?></th>

            </tr>
            </thead>
            <tbody>

            <?php $count = 0;

            foreach ($data as $room): ?>
                <tr class="gradeX <?php (++$count % 2 ? "odd" : "even") ?>">
                    <td><?php echo $room['ChatRoom']['id'] ?></td>
                    <td>
                        <?php
                        $memberIds = Hash::extract($room['ChatRoomsMember'], '{n}.user_id');
                        $name = array();
                        foreach ($memberIds as $id) {
                            array_push($name, $users[$id]["name"]);
                        }
                        echo $this->Html->link(
                            implode(",", $name),
                            array(
                                'controller' => 'ChatLogs',
                                'action' => 'admin_messages',
                                'full_base' => true,
                                $room['ChatRoom']['id']
                            )
                        );
                        ?>
                    </td>
                    <td><?php echo $this->Time->niceShort($room['ChatRoom']['created']) ?></td>
                </tr>
            <?php endforeach ?>

            </tbody>
        </table>
    </form>
    <div class="pagination pull-right">
        <?php echo $this->Paginator->prev('« ' . __('Previous'), null, null, array('class' => 'disabled')); ?>
        <?php echo $this->Paginator->numbers(); ?>
        <?php echo $this->Paginator->next(__('Next') . ' »', null, null, array('class' => 'disabled')); ?>
    </div>
</div>
