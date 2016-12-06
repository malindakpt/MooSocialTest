<?php
App::uses('NoteAppModel', 'Note.Model');
/**
 * Note Model
 *
 */
class Note extends NoteAppModel {
    public $validate = array(
        'title' => array(
            'rule' => 'notEmpty'
        ),
        'body' => array(
            'rule' => 'notEmpty'
        )
    );

    public $belongsTo = array( 'User'  => array('counterCache' => true ));
    public $mooFields = array('title','href','plugin','type');
    public function getHref($row)
    {
        if(isset($row['id']))
        {
            $request = Router::getRequest();
            return $request->base.'/notes/view/'.$row['id'];
        }
        return '';
    }

    public function search($keyword)
    {
        $cond = array( 'MATCH(Note.title, Note.body) AGAINST(? IN BOOLEAN MODE)' => urldecode($keyword));
        $notes = $this->find( 'all', array( 'conditions' => $cond, 'limit' => RESULTS_LIMIT, 'page' => 1 ) );
        return $notes;
    }
}