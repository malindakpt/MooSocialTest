<?php
class NotesController extends NoteAppController {
    /**
     * Scaffold
     *
     * @var mixed
     */
    public $scaffold;
    public function admin_index(){

    }

    public function myTopics23() {

    }

    public function admin_infos(){
        // get plugin info
        $xmlPath = sprintf(PLUGIN_INFO_PATH, 'Note');
        if(file_exists($xmlPath))
        {
            $content = file_get_contents($xmlPath);
            $info = new SimpleXMLElement($content);
            $this->set('info', $info);
        }
        else
        {
            $this->set('info', null);
        }
    }

    public function index() {
        $this->set('notes', $this->Note->find('all'));
    }

    public function view($id = null) {
        if (!$id) {
            throw new NotFoundException(__d('Note', 'Invalid note'));
        }
        $note = $this->Note->findById($id);
        if (!$note) {
            throw new NotFoundException(__d('Note', 'Invalid note'));
        }
        $this->set('note', $note);

        $this->likesComments($id);
    }

    private function likesComments($id) {
        //comment
        $this->Comment = ClassRegistry::init('Comment');
        $comments = $this->Comment->getComments($id, 'Note_Note');
        $comment_count = $this->Comment->getCommentsCount($id, 'Note_Note');
        $page = 1;

        $data['bIsCommentloadMore'] = $comment_count - $page * RESULTS_LIMIT;
        $data['comments'] = $comments;
        $this->set('data', $data);
        $this->set('comment_count', $comment_count);

        //like
        $this->Like = ClassRegistry::init('Like');
        $likes = $this->Like->getLikes($id, 'Note_Note');
        $dislikes = $this->Like->getDisLikes($id, 'Note_Note');

        $this->set('likes', $likes);
        $this->set('dislikes', $dislikes);
    }

    public function add() {
        if ($this->request->is('post')) {
            $this->Note->create();
            $this->request->data['Note']['user_id'] = $this->Session->read('uid');
            if ($this->Note->save($this->request->data)) {
                $this->Session->setFlash(__d('Note', 'Your note has been saved.'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__d('Note', 'Unable to add your note.'));
        }
    }

    public function edit($id = null) {
        if (!$id) {
            throw new NotFoundException(__d('Note', 'Invalid note'));
        }
        $note = $this->Note->findById($id);
        if (!$note) {
            throw new NotFoundException(__d('Note', 'Invalid note'));
        }
        if ($this->request->is(array('post', 'put'))) {
            $this->Note->id = $id;
            $this->request->data['Note']['user_id'] = $this->Session->read('uid');
            if ($this->Note->save($this->request->data)) {
                $this->Session->setFlash(__d('Note', 'Your note has been updated.'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__d('Note', 'Unable to update your note.'));
        }
        if (!$this->request->data) {
            $this->request->data = $note;
        }
    }

    public function delete($id) {
        if (!$id) {
            throw new NotFoundException(__d('Note', 'Invalid note'));
        }
        if ($this->Note->delete($id)) {
            $this->Session->setFlash(
                __('The note with id: %s has been deleted.', h($id))
            );
            return $this->redirect(array('action' => 'index'));
        }
    }

    public function myNotes(){
        $notes = $this->Note->find('all', array(
            'conditions' => array('uri' => $this->request->uri),
            'limit' => 1,
            'order' => array('Note.id' => 'DESC')
        ));
        return $notes;
    }

    public function ajax_add(){

        if ($this->request->is('post')) {
            $this->Note->create();
            $this->request->data['Note']['user_id'] = $this->Session->read('uid');
            if ($this->Note->save($this->request->data)) {
                $note = $this->Note->findById($this->Note->getLastInsertId());
                echo json_encode(array('result' => $note));
                exit;
            }
            echo json_encode(array('error' => _d("Note", "Something went wrong! Please try again.")));
            exit;
        }
    }
}