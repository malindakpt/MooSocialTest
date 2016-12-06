<?php
$items = array();

if (!empty($conversations)) {
    foreach ($conversations as $message) {
        $members = Hash::extract($rooms[$message["ChatMessage"]["room_id"]],"ChatRoomsMember.{n}[user_id!=$viewerId].user_id");
        $name = array();
        $imgs = array();
        $limit = 0;
        foreach ($members as $member) {
            if(isset($users[$member]["name"])){
                array_push($name, $users[$member]["name"]);
                if($limit == 0){
                    $imgs["photo"] = $this->Moo->getImageUrl(array("User"=>$users[$member]), array('prefix' => '50_square'));
                }else{
                    $imgs["photo_".$limit] = $this->Moo->getImageUrl(array("User"=>$users[$member]), array('prefix' => '50_square'));
                }
            }else{
                array_push($name, __d('chat',"Account Deleted"));
            }
            $limit++;


        }

        $tmp = array(
            'id' => $message["ChatMessage"]['id'],
            'from' => null,
            'to' => null,
            'created_time' => $this->Moo->getTime($message["ChatMessage"]['created'], Configure::read('core.date_format'), $utz),
            'updated_time' => $message["ChatMessage"]['created'],
            'subject' => implode(",",$name ),
            //'message' => $this->Text->truncate($this->Message->export($message["ChatMessage"], $users, true,false), 300, array('exact' => false)),
            'message' => $this->Message->export($message["ChatMessage"], $users, false,false,true),
            'link' => FULL_BASE_URL . $this->request->base .'/chat/messages/'.$message["ChatMessage"]['room_id'],
            'unread' => !($status[$message["ChatMessage"]['id']] == 0),
            'object' => $imgs
        );
// Hacking for ios and androi app

        $tmp["object"]["more_info"] = $tmp["created_time"];
        $items[] = $tmp;
    }
}

if(empty($items) ) {
    $items = array('Error' => 'No record found');
}
echo json_encode($items);