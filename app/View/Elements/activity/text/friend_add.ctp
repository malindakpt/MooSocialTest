<?php

$ids = explode(',', $activity['Activity']['items']);
$userModel = MooCore::getInstance()->getModel('User');
$userModel->cacheQueries = false;
$users = $userModel->find('all', array('conditions' => array('User.id' => $ids)));
echo __('is now friends with') . ' ';

$friend_add1 = '%s';
$friend_add2 = __('%s and %s');
$friend_add3 = __('%s and %s');
$friend_add = '';

switch (count($users)) {
    case 1:
        $friend_add = sprintf($friend_add1, $this->Moo->getName($users[0]['User'], false));
        break;
    case 2:
        $friend_add = sprintf($friend_add2, $this->Moo->getName($users[0]['User'], false), $this->Moo->getName($users[1]['User'], false));
        break;
    case 3:
    default :
        $friend_add = sprintf($friend_add3, $this->Moo->getName($users[0]['User'], false), '<a data-toggle="modal" data-target="#themeModal" href="' . $this->Html->url(array('controller' => 'users', 'action' => 'ajax_friend_added', 'activity_id' => $activity['Activity']['id'])) . '">' . abs(count($users) - 1) . ' ' . __('others') . '</a>');
        break;
}

echo $friend_add;

?>