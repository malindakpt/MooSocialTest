/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

var ChatFriendActionCreators = require('../actions/ChatFriendActionCreators');
var ChatGroupActionCreators = require('../actions/ChatGroupActionCreators');
var ChatRoomActionCreators = require('../actions/ChatRoomActionCreators');
var ChatMessageActionCreators = require('../actions/ChatMessageActionCreators');
var ChatUserActionCreators = require('../actions/ChatUserActionCreators');
var ChatViewerActionCreators = require('../actions/ChatViewerActionCreators');
var ChatPoupActionCreators = require('../actions/ChatPoupActionCreators');
// Mobi
var ChatStateMobileActionCreators = require('../actions/ChatStateMobileActionCreators');
// End Mobi
var ChatMooUtils = require('../utils/ChatMooUtils');
var CHAT_CONSTANTS = require('../constants/ChatConstants');
var __ = require('../utils/ChatMooI18n').i18next;
var _mySocket = null;

var _createChatGroupWindowForUsersCallback = function(){};
function userIsLoggedCallback(uId) {
    ChatViewerActionCreators.userIsLoggedCallback(uId);
}
function getMyFriendsCallBack(friends) {
    ChatFriendActionCreators.receiveAll(friends);
    _mySocket.emit("getMyFriendsOnline");
}
function getMyFriendsHaveIdsCallBack(friends) {
    ChatFriendActionCreators.add(friends);
    _mySocket.emit("getMyFriendsOnline");
}
function getMyFriendsOnlineCallBack(friends) {
    ChatFriendActionCreators.setOnline(friends);
}
function friendIsLogged(uId) {
    ChatFriendActionCreators.setOnline([uId]);
}
function friendIsLogout(uId) {
    ChatFriendActionCreators.setOffline([uId]);
}
function createChatWindowByUserCallback(data) {
    if(ChatMooUtils.isMobile()){
        _createChatGroupWindowForUsersCallback(data);
        _createChatGroupWindowForUsersCallback=function(data){};
    }
    ChatRoomActionCreators.createForAUserByUserCallback(data);

}
function createChatWindowBySystemCallback(data) {
    ChatRoomActionCreators.createForAUserBySystemCallback(data);
}
function markMessagesIsSeenInRoomsCallback(data) {
    ChatRoomActionCreators.markMessagesIsSeenInRoomsCallback(data);
}
function getRoomMessagesCallback(data) {
    ChatMessageActionCreators.getRoomMessagesCallback(data);
}
function getRoomMessagesMoreCallback(data){

    ChatMessageActionCreators.getRoomMessagesMoreCallback(data);
}
function newMessage(data) {
    if(module.exports.getChatStatus() == CHAT_CONSTANTS.USER_OFFLINE){

    }else{
        ChatMessageActionCreators.newMessage(data);
    }

}

function getUsersCallback(data) {
    ChatUserActionCreators.add(data);
}
function getUsersByRoomIdsAtBootingCallback(data) {
    ChatUserActionCreators.add(data);
    var roomsIsOpened = ChatMooUtils.getRoomIsOpen();
    for(var i =0;i<roomsIsOpened.isCreated.length;i++){
        module.exports.sendRequestCreateChatWindowByRoomId(roomsIsOpened.isCreated[i],roomsIsOpened[roomsIsOpened.isCreated[i]].m,CHAT_CONSTANTS.NOT_FOCUSED_CHAT_WINDOW); // m min minimized
    }
}
function getRoomHasUnreadMessageCallback(rooms) {
    if (rooms.length != 0) {
        for (var i = 0; i < rooms.length; i++) {
            module.exports.sendRequestCreateChatWindowByRoomId(rooms[i].room_id);
        }
    }
}
function getMyGroupsCallBack(groups) {
    ChatGroupActionCreators.receiveAll(groups);
}
function failedConnect(rooms) {
    console.log('Failed to connect to server');
    setTimeout(function() { module.exports.openAlertModal(__.t("warning"),__.t("socket_is_not_connected"));
    }, 3000);


}
function setOnlineCallback() {
    module.exports.sendRequestForGetRoomHasUnreadMessage();
}
function deleteConversationCallback(rId) {
    module.exports.sendRequestGetRoomMessages(rId);
}
function reportMessageSpamCallback(data) {
    if (data.error == CHAT_CONSTANTS.ERROR.REPORT_ROOM_MESSAGE_SPAM_IS_EXIST) {
        module.exports.openAlertModal(__.t("report_chat_session"),__.t("we_marked_this_message_as_spam"));
    }
}
function leaveConversationCallback(rId){
    module.exports.destroyARoom(rId);
    module.exports.sendRequestGetMyGroups();
}
function addUsersToARoomCallback(data){
    ChatRoomActionCreators.addUsersToARoomCallback(data.roomId,data.users);
}
function blockMessagesCallback(rId){
    module.exports.refeshARoom(rId);
}
function unblockMessagesCallback(rId){
    module.exports.refeshARoom(rId);
}
function refeshStatusChatWindowByRoomIdCallback(data){
    ChatRoomActionCreators.refeshStatusARoom(data);
}
function startTypingCallback(data){
    ChatRoomActionCreators.startTyping(data);
}
function stopTypingCallback(data){
    ChatRoomActionCreators.stopTyping(data);
}
var searchFriendBackground = {on:false,callback:function(){}};
function searchFriendCallback(data){
    if(!searchFriendBackground.on){
        ChatFriendActionCreators.searchFriendCallback(data);
    }else{
        ChatFriendActionCreators.addByKeyword(data);
        searchFriendBackground.callback();
        searchFriendBackground.on = false;

    }

}
module.exports = {
    isEnableChat: function () {
        return ChatMooUtils.isEnableChat();
    },
    getChatToken: function () {
        return ChatMooUtils.getChatToken();
    },
    getChatStatus: function () {
        return ChatMooUtils.getChatStatus();
    },
    getServerUrl: function () {
        return ChatMooUtils.getServerUrl();
    },
    getSiteUrl: function () {
        return ChatMooUtils.getSiteUrl();
    },
    getAvatarLinkFromDataUser:function(data){
        return ChatMooUtils.getAvatarLinkFromDataUser(data);
    },
    getProfileLinkFromDataUser:function(data){
        return ChatMooUtils.getProfileLinkFromDataUser(data);
    },
    isHideOfflineUser: function () {
        return ChatMooUtils.isHideOfflineUser();
    },
    isOpennedChatboxWhenANewMesasgeArrives: function () {
        return ChatMooUtils.isOpennedChatboxWhenANewMesasgeArrives();
    },
    boot:function(isMoblie){
        isMoblie = typeof isMoblie !== 'undefined' ? isMoblie:false;
     
        ChatMooUtils.setIsMobile(isMoblie);
        console.log("setIsMobile",ChatMooUtils.isMobile());



        if (this.isEnableChat()) {

            this.initSocket(function(){
                
                if(ChatMooUtils.isTurnOffForFirstTimeUsing()){
                    ChatMooUtils.turnOffChat();
                }else{
                        var roomsIsOpened = ChatMooUtils.getRoomIsOpen();
                        if (roomsIsOpened.isCreated.length > 0){
                            if(ChatMooUtils.getChatStatus() == CHAT_CONSTANTS.USER_OFFLINE){

                            }else{
                                module.exports.sendReqpuestForUpdatingUserByRoomIdsAtBooting(roomsIsOpened.isCreated);
                            }

                        }
                }

            });
            return true;
        }
        return false;
    },
    initSocket: function (callback) {
        ChatMooUtils.setThisOfChatWebAPIUtils(this);
        _mySocket = require('socket.io-client')(this.getServerUrl(), {
            query: "chat_token=" + this.getChatToken() + "&chat_status=" + this.getChatStatus(),
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionAttempts: 10
        });
        _mySocket.on("connect",callback);
        //_mySocket.on("connect_error", failedConnect);
        _mySocket.on("reconnect_failed", failedConnect);
        _mySocket.on("disconnect", failedConnect);
        _mySocket.on("userIsLogged", userIsLoggedCallback);
        _mySocket.on("getMyFriendsCallBack", getMyFriendsCallBack);
        _mySocket.on("getMyFriendsHaveIdsCallBack", getMyFriendsHaveIdsCallBack);
        _mySocket.on("getMyFriendsOnlineCallBack", getMyFriendsOnlineCallBack);
        _mySocket.on("friendIsLogged", friendIsLogged);
        _mySocket.on("friendIsLogout", friendIsLogout);
        _mySocket.on("createChatWindowByUserCallback", createChatWindowByUserCallback);
        _mySocket.on("createChatWindowBySystemCallback", createChatWindowBySystemCallback);
        _mySocket.on("getRoomMessagesCallback", getRoomMessagesCallback);
        _mySocket.on("getRoomMessagesMoreCallback", getRoomMessagesMoreCallback);
        _mySocket.on("markMessagesIsSeenInRoomsCallback", markMessagesIsSeenInRoomsCallback);
        _mySocket.on("newMessage", newMessage);
        _mySocket.on("getUsersCallback", getUsersCallback);
        _mySocket.on("getUsersByRoomIdsAtBootingCallback", getUsersByRoomIdsAtBootingCallback);
        _mySocket.on("getRoomHasUnreadMessageCallback", getRoomHasUnreadMessageCallback);
        _mySocket.on("setOnlineCallback", setOnlineCallback);
        _mySocket.on("getMyGroupsCallBack", getMyGroupsCallBack);
        _mySocket.on("deleteConversationCallback", deleteConversationCallback);
        _mySocket.on("reportMessageSpamCallback", reportMessageSpamCallback);
        _mySocket.on("leaveConversationCallback", leaveConversationCallback);
        _mySocket.on("addUsersToARoomCallback", addUsersToARoomCallback);
        _mySocket.on("blockMessagesCallback", blockMessagesCallback);
        _mySocket.on("unblockMessagesCallback", unblockMessagesCallback);
        _mySocket.on("refeshStatusChatWindowByRoomIdCallback", refeshStatusChatWindowByRoomIdCallback);
        _mySocket.on("startTypingCallback", startTypingCallback);
        _mySocket.on("stopTypingCallback", stopTypingCallback);
        _mySocket.on("searchFriendCallback", searchFriendCallback);


    },

    createChatWindowForAUser: function (uId) {
        ChatRoomActionCreators.createForAUser(uId);
    },
    createChatWindowByRoomId: function (rId) {
        ChatRoomActionCreators.createByRoomId(rId);
    },
    createChatGroupWindowForUsers: function (uIds,callback) {
        if(ChatMooUtils.isMobile()){
            if(typeof callback === undefined){
                _createChatGroupWindowForUsersCallback=function(data){};
            }else{
                _createChatGroupWindowForUsersCallback = callback;
            }
        }

        _mySocket.emit("createChatWindowByUser", {friendIds:uIds, isAllowedSendToNonFriend:ChatMooUtils.isAllowedSendToNonFriend()});
    },
    addUsersToARoom: function (friendIds, roomId) {
        _mySocket.emit("addUsersToARoom", friendIds, roomId);
    },
    sendRequestCreateChatWindowByUser: function (uId) {
        _mySocket.emit("createChatWindowByUser", {friendIds:uId,isAllowedSendToNonFriend:ChatMooUtils.isAllowedSendToNonFriend()});
    },
    sendRequestRefeshStatusARoomByRoomId:function(rId){
        _mySocket.emit("refeshStatusChatWindowByRoomId", rId);
       
    },
    sendRequestGetRoomMessages: function(roomId,firstIdNewMessage){
       
        firstIdNewMessage = typeof firstIdNewMessage !== 'undefined' ? firstIdNewMessage : 0; firstIdNewMessage=0;
        _mySocket.emit("getRoomMessages", {roomId:roomId,limit:ChatMooUtils.getFirstTimeMessagesLimit(),firstIdNewMessage:firstIdNewMessage});
    },
    sendRequestGetRoomMessagesMore: function(rId,mIdStart,limit){

        _mySocket.emit("getRoomMessagesMore", {rId:rId,mIdStart:mIdStart,limit:limit});
    },
    sendRequestTextMessage: function(text, roomId, type){
        //text = ChatMooUtils.convertEmoji(text);
        _mySocket.emit("sendTextMessage", {
            text: text,
            roomId: roomId,
            type: type,
            timestamps: ChatMooUtils.unixTime()
        });
    },
    sendRequestCreateChatWindowByRoomId: function(roomId,isMinimized,isFocused){
        var minimized = (typeof isMinimized != 'undefined')? isMinimized:CHAT_CONSTANTS.WINDOW_MAXIMIZE ;
        var focus =  (typeof isFocused != 'undefined')?isFocused:CHAT_CONSTANTS.IS_FOCUSED_CHAT_WINDOW;
        if(ChatMooUtils.isMobile()){
            isMinimized = CHAT_CONSTANTS.WINDOW_MINIMIZE;
        }
        _mySocket.emit("createChatWindowByRoomId", {roomId:roomId,minimized:minimized,isFocused:focus});
    },
    sendRequestMarkMessagesIsSeenInRooms: function(messageIdsUnSeen,roomIsSeen){
        _mySocket.emit("markMessagesIsSeenInRooms", {messageIdsUnSeen:messageIdsUnSeen,roomIsSeen:roomIsSeen});
    },
    sendRequestForGetRoomHasUnreadMessage: function(){
        if(ChatMooUtils.getChatStatus() != CHAT_CONSTANTS.USER_OFFLINE){
            _mySocket.emit("getRoomHasUnreadMessage");
        }

    },
    sendRequestForUpdatingUsers: function(users){
        _mySocket.emit("getUsers", users);
    },
    sendReqpuestForUpdatingUserByRoomIdsAtBooting:function(rIds){
        _mySocket.emit("getUsersByRoomIdsAtBooting", rIds);
    },
    sendRequestGetMyFriends: function (ids) {
        ids = typeof ids !== 'undefined' ? ids:[];
        _mySocket.emit("getMyFriends",ids);
    },
    sendRequestTurnOffChat: function () {
        ChatMooUtils.setChatStatus(CHAT_CONSTANTS.USER_OFFLINE);
        _mySocket.emit("setOffline");
    },
    sendRequestTurnOnChat: function () {
        ChatMooUtils.setChatStatus(CHAT_CONSTANTS.USER_ONLINE);
        _mySocket.emit("setOnline");
    },
    sendRequestGetMyGroups: function () {
        _mySocket.emit("getMyGroups");
    },
    sendRequestDeleteConversation: function (rId) {
        ChatMessageActionCreators.deleteAllMesages(rId);
        _mySocket.emit("deleteConversation", rId);
    },
    sendRequestReportMesasgeSpam: function (data) {
        _mySocket.emit("reportMessageSpam", data);
    },
    sendRequestLeaveConversation: function (rId) {
        _mySocket.emit("leaveConversation", rId);
    },
    sendRequestBlockMessages:function(rId){
        _mySocket.emit("blockMessages", rId);
    },
    sendRequestUnblockMessages:function(rId){
        _mySocket.emit("unblockMessages", rId);
    },
    sendRequestStartTyping:function(rId){
        _mySocket.emit("startTyping", rId);
    },
    sendRequestStopTyping:function(rId){
        _mySocket.emit("stopTyping", rId);
    },
    sendRequestSearchName:function(name,callback){
        if(typeof callback !== 'undefined'){
            searchFriendBackground.on = true;
            searchFriendBackground.callback = callback;
        }
        _mySocket.emit("searchFriend", name);
    },
    /* Room Behavior */
    refeshARoom: function(roomId){
        ChatRoomActionCreators.refeshARoom(roomId);
    },
    activeARoom: function(roomId){
        ChatRoomActionCreators.activeARoom(roomId);
    },
    destroyARoom: function(roomId){
        ChatRoomActionCreators.destroyARoom(roomId);
    },
    destoryAllRoom: function(){
        ChatRoomActionCreators.destroyAllRoom();
    },
    minimizeARoom: function(roomId){
        ChatRoomActionCreators.minimizeARoom(roomId, true);
    },
    maximizeARoom: function(roomId, isMinimized){
        ChatRoomActionCreators.minimizeARoom(roomId, false);
    },
    caculateNewMessagesForAllRoom: function(){
        ChatRoomActionCreators.caculateNewMessages();
    },
    reRenderAllRooms: function(){
        ChatRoomActionCreators.reRenderAllRooms();
    },
    markMessagesIsLoadedForARoom:function(roomId){
       ChatRoomActionCreators.markMessagesIsLoaded(roomId);
    },
    /* End Room Behavior */
    findAFriendByName: function(name){
        ChatFriendActionCreators.filter(name);
    },
    /* POPUP behavior*/
    openReportModal: function(rId){
        ChatPoupActionCreators.openReportModal(rId);
    },
    closeReportModal: function(){
        ChatPoupActionCreators.closeReportModal();
    },
    openAlertModal: function(title,body){
        ChatPoupActionCreators.openAlertModal(title,body);
    },
    closeAlertModal: function(){
        ChatPoupActionCreators.closeAlertModal();
    },
    openAlertYesNoModal: function(title,body,noButton,yesButton,callback){
        ChatPoupActionCreators.openAlertYesNoModal(title,body,noButton,yesButton,callback);
    },
    closeAlertYesNoModal:function(){
        ChatPoupActionCreators.closeAlertYesNoModal();
    },
    /* END POPUP behavior*/
    /* Mobi */
    showMobiIconStatus:function(){
        ChatStateMobileActionCreators.showIconStatus();
    },
    showMobiFriendsWindow:function(){
        ChatStateMobileActionCreators.showFriendsWindow();
    },
    showMobiChatWindow:function(){
        ChatStateMobileActionCreators.showChatWindow();
    },
    updateConversationCounter:function(n){
        ChatMooUtils.updateConversationCounter(n);
    }
    /* End Mobi */
};
