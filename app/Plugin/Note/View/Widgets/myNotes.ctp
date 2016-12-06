<?php $notes = $this->requestAction(array('plugin' => 'Note', 'controller' => 'notes', 'action' => 'myNotes'), array('uri' => $this->here));?>
    <div class="box2 search-friend">
        <h3><?=__d('Note', 'My Note');?></h3>
        <div class="box_content">
            <div id="newNote">
                <?php if($notes != null): ?>
                    <?=$notes[0]['Note']['body'];?>
                <?php else:?>
                    <?=__d('Note', 'No notes found');?>
                <?php endif;?>
            </div>
        </div>
        <h3><?=__d('Note', 'Add Note');?></h3>
        <div class="box_content">
            <?=$this->Form->create('Note');?>
            <?=$this->Form->input('uri', array(
                'value' => $this->here,
                'label' => false,
                'div' => false,
                'type' => 'hidden'
            ));?>
            <ul class="list6">
                <li>
                    <label><?=__d('Note', 'Title');?></label>
                    <?=$this->Form->input('title', array(
                        'label' => false,
                        'div' => false
                    ));?>
                </li>
                <li>
                    <label><?=__d('Note', 'Body');?></label>
                    <?=$this->Form->input('body', array(
                        'rows' => '3',
                        'label' => false,
                        'div' => false,
                        'style' => 'overflow: hidden;width: 100%;'
                    ));?>
                </li>
            </ul>
            <?=$this->Form->end(array(
                'label' => __d('Note', 'Add'),
                'div' => false,
                'class' => 'btn btn-action',
                'id' => 'btnAddNote'
            ));?>
            <div class="clear"></div>
        </div>
    </div>
<?php $this->Html->scriptStart(array('inline' => false)); ?>
    //<![CDATA[
    $(document).ready(function()
    {
    $(window).load(function(){
    $("#NoteIndexForm")[0].reset();
    })

    $("#NoteIndexForm").submit(function(e){
    e.preventDefault();
    $.post('/notes/ajax_add/', $("#NoteIndexForm").serialize(), function(data){
    data = jQuery.parseJSON(data);
    if(data.result)
    {
    $("#newNote").empty().append(data.result.Note.body);
    }
    else
    {
    $("#newNote").empty().append(data.error);
    }
    $("#NoteIndexForm")[0].reset();
    });
    })
    });
    //]]>
<?php $this->Html->scriptEnd(); ?>