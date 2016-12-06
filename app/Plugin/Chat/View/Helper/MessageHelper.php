<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

App::uses('Helper', 'View');


class MessageHelper extends Helper {
    public function export($message,$users=array(),$useNoteContentHtml= false,$isAllowedToAddLinkExtra=true,$isApi = false){


        if (isset($message["type"])){
            switch ($message["type"]) {
                case "system":

                    $action = json_decode($message["content"]);
                    if(is_object($action)){
                        if(property_exists($action,'action')){
                            switch ($action->action){
                                case "added":
                                    $name = array();
                                    foreach( $action->usersId as $uId){
                                        if(isset($users[$uId]["name"])){
                                            array_push($name, $users[$uId]["name"]);
                                        }else{
                                            array_push($name, __d('chat',"Account Deleted"));
                                        }

                                    }
                                    return __d("chat","added ").implode(",",$name );
                                    break;
                                case "left_the_conversation":
                                    return __d("chat","left the conversation");
                                    break;
                                default:
                            }
                        }
                    }
                    break;
                case "text":
                    return ($useNoteContentHtml)?$message["note_content_html"]:$message["content"];
                case "image":
                case "file":
                    return __d("chat","send a file") . " <a target=\"_blank\" href='".$this->webroot("uploads/chat/room-{$message["room_id"]}/".$message["note_content_html"])."'>{$message["note_content_html"]}</a>";
                case "link":
                    $action = json_decode($message["content"]);
                    $extInfor = "";
                    if($isApi){
                        return $action->message;
                    }
                    if(empty($action->dataURL)){
                        return '<a target="_blank" href="'.$action->message.'">'.$action->message.'</a>';
                    }else{
                        if($isAllowedToAddLinkExtra){
                            $extInfor = '<a target="_blank" href="'.$action->dataURL->url.'"><div class="mooText mooText_linkparse"><span class="img_link" style="background-image: url(&quot;'.$action->dataURL->image.'&quot;);"></span><div class="linkcontent"><div style="font-weight: bold;">'.$action->dataURL->title.'</div><div class="link_description">'.$action->dataURL->title.'</div></div></div></a>';
                        }
                        return '<a target="_blank" href="'.$action->dataURL->url.'">'.$action->dataURL->url.'</a>'.$extInfor;
                    }
                default:
            }
        }else{
            return "";
        }
    }
}
