<?php 
class ChatReportsController extends ChatAppController{
    
    public function admin_index()
    {
        //$this->loadModel('Chat.ChatReport');

        if(!isset($this->request->data['keyword'])){
            if(($this->Session->read('Chat.AdminReports.Keyword') != null)){
                $keyword = $this->Session->read('Chat.AdminReports.Keyword');
            }else{
                $keyword = '';
            }
        }else{
            $keyword = $this->request->data['keyword'];
            $this->Session->write('Chat.AdminReports.Keyword', $keyword);
        }
        $data = $this->paginate(
            'ChatReport',array('ChatReport.reason LIKE' => "%$keyword%")
        );

        $this->set('data', $data);
        $this->set('title_for_layout', __d('chat','Chats Manager'));
        $this->set('keyword',$keyword);
    }
    public function admin_delete()
    {
        $this->loadModel('Chat.ChatReport');
        $this->_checkPermission(array('super_admin' => 1));

        if ( !empty( $_POST['reports'] ) )
        {
            $reports = $this->ChatReport->findAllById($_POST['reports']);

            foreach ( $reports as $report ){

                $this->ChatReport->delete( $report['ChatReport']['id'] );
           }

            $this->Flash->adminMessages(__d('chat','Reports have been deleted'));
        }

        $this->redirect( array(
            'plugin' => 'chat',
            'controller' => 'chat_reports',
            'action' => 'admin_index'
        ) );
    }
}