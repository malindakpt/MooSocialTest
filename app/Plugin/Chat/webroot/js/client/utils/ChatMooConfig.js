/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatConstants = require('../constants/ChatConstants');

module.exports = {
    isFirsTimeUsing:function(){
        return window.mooConfig.mooChat.settings.first_time_using;
    },
    isTurnOffForFirstTimeUsing:function(){
        if(!window.hasOwnProperty('mooConfig')){
            return false;
        }
        if(window.mooConfig.mooChat.load_bar_in_offline_mode_for_all_first_time_users == ChatConstants.YES_STRING && window.mooConfig.mooChat.settings.first_time_using == true){
            return true;
        }
        return false;
    },
    isHideOfflineUser: function () {
        return (window.mooConfig.mooChat.hide_offline == "1") ? true : false;
    },
    isOpennedChatboxWhenANewMesasgeArrives: function(){
        return (window.mooConfig.mooChat.open_chatbox_when_a_new_message_arrives == "1") ? true : false;
    },
    isDisabled: function () {
        if(!window.hasOwnProperty('mooConfig')){
            return true;
        }
        if (window.mooConfig.hasOwnProperty('mooChat')) {
            if (!window.mooConfig.mooChat.hasOwnProperty('token')) {
                return true;
            }
            if (window.mooConfig.mooChat.server_url == "") {
                return true;
            }
            if(this.isAllowedChat()){
                return (window.mooConfig.mooChat.disable == "1") ? true : false;
            }else{
                return true;
            }

        } else {
            return true;
        }
    },
    isAllowedChat:function(){
        return window.mooConfig.mooChat.permissions.isAllowedChat;
    },
    isAllowedSendPicture:function(){
        return window.mooConfig.mooChat.permissions.isAllowedSendPicture;
    },
    isAllowedSendFiles:function(){
        return window.mooConfig.mooChat.permissions.isAllowedSendFiles;
    },
    isAllowedEmotion:function(){
        return window.mooConfig.mooChat.permissions.isAllowedEmotion;
    },
    isAllowedChatGroup:function(){
        return window.mooConfig.mooChat.permissions.isAllowedChatGroup;
    },
    isChatSoundGlobalEnable:function(){
        return (parseInt(window.mooConfig.mooChat.chat_beep_on_arrival_of_all_messages) == ChatConstants.SOUND_ENABLE)?true:false;
    },
    isAllowedSendToNonFriend:function(){
        return window.mooConfig.mooChat.send_message_to_non_friend === "1";
    },
    isTurnOnNotification:function(){
        return  window.mooConfig.mooChat.chat_turn_on_notification === "1";
    },
    isMobile:function(){
        if(window.hasOwnProperty("mooConfig")){
            if(window.mooConfig.hasOwnProperty("mooChat")){
                return window.mooConfig.mooChat.isMobile;
            }
        }
        return false;
    },
    getUploadFileLimitOnSite:function(){
      return window.mooConfig.sizeLimit;
    },
    getLanguage:function(){
        return window.mooConfig.language;
    },
    getLanguage2Letter:function(){
        return window.mooConfig.language_2letter;
    },
    getSiteUrl: function () {
        return mooConfig.url.base;
    },
    getChatBlockSettingURL:function(){
        return mooConfig.url.base + "/chats/settings/blocking";
    },
    getChatHistoryURL:function(){
        
        return mooConfig.url.base + "/chat/messages";
    },
    getChatFullConversationURL:function(){
        return mooConfig.url.base + "/chat/messages";
    },
    getChatToken: function () {
        return window.mooConfig.mooChat.token;
    },
    getChatStatus: function () {
        return window.mooConfig.mooChat.settings.status;
    },
    getServerUrl: function () {
        return window.mooConfig.mooChat.server_url;
    },
    getChatSoundState: function () { 
        return (window.mooConfig.mooChat.settings.sound) ? ChatConstants.SOUND_ENABLE : ChatConstants.SOUND_DISABLE;
    },
    getHideGroupState: function () {
        return (window.mooConfig.mooChat.settings.hide_group) ? ChatConstants.HIDE_GROUP_ENABLE : ChatConstants.HIDE_GROUP_DISABLE;
    },
    getRoomIsOpen:function(){
        var rooms = window.mooConfig.mooChat.settings.room_is_opened;
        if(rooms == ""){
            return {isCreated:[]};
        }
        return JSON.parse(rooms);
    },
    getMoreMessageLimit:function(){
        return parseInt(window.mooConfig.mooChat.number_of_messages_fetched_when_load_earlier_messeges);
    },
    getFirstTimeMessagesLimit:function(){
        var i = parseInt(window.mooConfig.mooChat.number_of_messages_fetched_window_first_time);
        if (i > 9){
            return parseInt(window.mooConfig.mooChat.number_of_messages_fetched_window_first_time);
        }
        return 10;
    },
    setChatSoundState: function (state) {
        window.mooConfig.mooChat.settings.sound = (state == ChatConstants.SOUND_ENABLE) ? true : false;
    },
    setFirsTimeUsing:function(state){
        window.mooConfig.mooChat.load_bar_in_offline_mode_for_all_first_time_users = ChatConstants.NO_STRING ;
    },
    setChatStatus: function (status) {
        window.mooConfig.mooChat.settings.status = status;
    },
    setIsMobile: function (isMoblie) {
        if(window.hasOwnProperty("mooConfig")){
            if(window.mooConfig.hasOwnProperty("mooChat")){
                window.mooConfig.mooChat.isMobile = isMoblie;
            }
        }

    }
};
