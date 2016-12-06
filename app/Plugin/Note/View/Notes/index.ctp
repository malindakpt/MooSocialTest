<!-- File: /app/Plugin/Note/View/Notes/index.ctp -->
<!-- In case you want to create multiple themed views,  ex you want to  only
apply this view for default template , you can copy it to the folder: /app/View/Plugin/Note/Notes/index.ctp -->
<div class="col-md-s20">
    <div class="bar-content">
        <div class="content_center">
            <div class="mo_breadcrumb">
                <h1><?=__d('Note', 'Note posts'); ?></h1>
                <?php
                echo $this->Html->link(
                    __d('Note', 'Add Note'), array(
                    'controller' => 'notes',
                    'action' => 'add',
                ), array('class' => 'button button-action topButton button-mobi-top')
                );
                ?>
            </div>
            <table class="table table-bordered">
                <tr>
                    <th class="col-md-sl1"><?=__d('Note', 'Id'); ?></th>
                    <th><?=__d('Note', 'Title'); ?></th>
                    <th class="col-md-sl2"><?=__d('Note', 'Created'); ?></th>
                    <th class="col-md-sl1"></th>
                </tr>
                <!-- Here is where we loop through our $posts array, printing out post info -->
                <?php foreach ($notes as $note): ?>
                    <tr>
                        <td><?php echo $note['Note']['id']; ?></td>
                        <td>
                            <?php echo $this->Html->link($note['Note']['title'], array(
                                'controller' => 'notes',
                                'action' => 'view',
                                $note['Note']['id']));
                            ?>
                        </td>
                        <td><?php echo $note['Note']['created']; ?></td>
                        <td>
                            <?php echo $this->Html->link(__d('Notes', 'Edit'), array(
                                'controller' => 'notes',
                                'action' => 'edit',
                                $note['Note']['id']
                            ));?> |
                            <?php echo $this->Html->link(__d('Notes', 'Delete'), array(
                                'controller' => 'notes',
                                'action' => 'delete',
                                $note['Note']['id']),
                                array(
                                    'confirm' => __d('Notes', 'Are you sure?')
                                ));?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php unset($note); ?>
            </table>
        </div>
    </div>
</div>