/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatAppDispatcher = require('../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../constants/ChatConstants');
var EventEmitter = require('events').EventEmitter;
var assign = require('object-assign');
var FriendStore = require('../stores/FriendStore');
var MessageStore = require('../stores/MessageStore');
var CounterUnseenMessageStore = require('../stores/CounterUnseenMessageStore');
var GroupStore = require('../stores/GroupStore');
var ViewerStore = require('../stores/ViewerStore');
var UserStore = require('../stores/UserStore');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var ChatMooUtils = require('../utils/ChatMooUtils');
var _ = require('lodash');

var Howl = require('howler').Howl;
var _sound = new Howl({
    src: [ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.mp3", ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.ogg", ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.aac"]
});

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';
var _rooms = {isCreated: [], lastestRoomIsActived: 0, lastestRoomIsCreated: 0};
var _missingRooms = [];
var _hasNewMessage = false;
var _roomIdHasNewMessage = 0;
var _roomMobiIsActive = 0;
var _friendsRoom = {};
function _hasRoom(id) {
    return _rooms.hasOwnProperty(id);
}
function _noteMissingRoom(id) {
    _missingRooms.push(id);
}
function _clearMissingUser() {
    _missingRooms = [];
}
function _isRoomExitsForAUser(uid) {
    var myFriends = FriendStore.get(uid);
    if (_rooms.isCreated.indexOf(myFriends.roomId) < 0) {
        return false;
    } else {
        return true;
    }
}
function _activeRoom(rId) {
    if (_rooms.isCreated.indexOf(rId) > -1) {
        _rooms.lastestRoomIsActived = rId;
        _rooms[rId].minimized = (!ChatMooUtils.isMobile())?ChatConstants.WINDOW_MAXIMIZE:ChatConstants.WINDOW_MINIMIZE;
        _rooms[rId].isFocused = ChatConstants.IS_FOCUSED_CHAT_WINDOW;
        _renderChatWindows();
        ChatMooUtils.noteRoomIsOpned(_rooms);
    } else {
        console.log("active room is not exits");
    }
}
var _destroyRoom = function (rId) {
    if (_hasRoom(rId)) {
        delete(_rooms[rId]);
        MessageStore.destroy(rId);
        if (_rooms.isCreated.indexOf(rId) > -1) {
            _rooms.isCreated.splice(_rooms.isCreated.indexOf(rId), 1);
            ChatMooUtils.noteRoomIsOpned(_rooms);
        }


    }
}
var _destroyAllRoom = function () {
    var tmp = _rooms.isCreated.slice(0);
    if (tmp.length > 0) {
        for (var i = 0; i < tmp.length; i++) {
            _destroyRoom(tmp[i]);
        }
    }
}
function _isChatWindowCreatedBySystem() {
    return (_rooms.lastestRoomIsActived == _rooms.lastestRoomIsCreated) ? false : true;
}
function _isGroup(members) {


    return ((members.length > 1) ? true : false);
}
var _notedRoomIsCreated = function (members, roomId) {
    if (typeof members == 'undefined') {
        return false;
    }
    if (!_isGroup(members)) {
        FriendStore.setRoomIdOfAUser(members[0], roomId);
    } else {
        // Todo add to group type
    }

}
var _hasMissingFriendInfo = function(members){
    if(_.isArray(members)){
        for(var i=0;i<members.length;i++){
            if(!FriendStore.has(members[i])){
                return true;
            }
        }
    }
    return false;
}
function _renderChatWindows() {
    // Rule to make sure the current window is always visible
    if(ChatMooUtils.isMobile()){

            if(_hasRoom(_roomMobiIsActive)){
                _rooms[_roomMobiIsActive].minimized = ChatConstants.WINDOW_MAXIMIZE;
            }

    }else{
        var left_base = ChatConstants.WINDOW_CHAT_MAXIMIZE_WIDTH;
        var left_base_minized = ChatConstants.WINDOW_CHAT_MINIMIZE_WIDTH;
        var left_room = ChatMooUtils.windowWidth() - ChatConstants.WINDOW_FIRST_CHAT_LEFT_MAGRIN_POSTION; // 467 ? 482
        var roomWillBeRender = [];
        if (_rooms.isCreated) {
            for (var i = 0; i < _rooms.isCreated.length; i++) {
                if (left_room > ChatConstants.WINDOW_MINIUM_LEFT_POSTION_ALLOWED_MAXIMIZE) {
                    // is if a last room - we must open the current room is clicked
                    roomWillBeRender.push(_rooms.isCreated[i]);
                    if (left_room - left_base < ChatConstants.WINDOW_MINIUM_LEFT_POSTION_ALLOWED_MAXIMIZE && _rooms.lastestRoomIsActived != 0 && roomWillBeRender.indexOf(_rooms.lastestRoomIsActived) < 0) {
                        _rooms.isCreated[_rooms.isCreated.indexOf(_rooms.lastestRoomIsActived)] = _rooms.isCreated[i];
                        _rooms.isCreated[i] = _rooms.lastestRoomIsActived;
                    }
                    if (_rooms[_rooms.isCreated[i]].minimized == ChatConstants.WINDOW_MAXIMIZE) {
                        left_room = left_room - left_base;
                    } else {
                        left_room = left_room - left_base_minized;
                    }
                } else {
                    _rooms[_rooms.isCreated[i]].minimized = ChatConstants.WINDOW_MINIMIZE;
                }

            }
        }
    }
    
    RoomStore.emitChange();
};
function _createChatWindow(uId) {

    if (!FriendStore.isRoomCreated(uId)) {
        ChatWebAPIUtils.sendRequestCreateChatWindowByUser(uId);
    } else {
        if (!_isRoomExitsForAUser(uId)) {
            ChatWebAPIUtils.sendRequestCreateChatWindowByUser(uId);
        } else {
            _activeRoom(FriendStore.getRoomIdOfAUser(uId));
        }

    }

}
function _createChatWindowByRoomId(rId) {

    if (!_hasRoom(rId)) {
        ChatWebAPIUtils.sendRequestCreateChatWindowByRoomId(rId);
    } else {
        _activeRoom(rId);
    }
}
function _minimizeChatWindow(rId, isMinimized) {
    if (_hasRoom(rId)) {
        if( _rooms[rId].minimized == ChatConstants.WINDOW_MINIMIZE && !isMinimized){
            _rooms[rId].isFocused = ChatConstants.IS_FOCUSED_CHAT_WINDOW;
        }
        _rooms[rId].minimized = isMinimized ? ChatConstants.WINDOW_MINIMIZE : ChatConstants.WINDOW_MAXIMIZE;

        if (!isMinimized) {
            _rooms.lastestRoomIsActived = rId;
        }

        ChatMooUtils.noteRoomIsOpned(_rooms);
        RoomStore.emitChange();
        CounterUnseenMessageStore.emitChange(rId);
    }
}
function _createChatWindowByUserCallback(data) {
    if (!_hasRoom(data.roomId)) {
        _rooms.lastestRoomIsActived = data.roomId;
        _createChatWindowBySystemCallback(data);
    }
}
function _createChatWindowBySystemCallback(data) {

    if (typeof data.roomId == 'undefined') {
        return false;
    } 
    // Fix for bug owner in members
    if(data.hasOwnProperty('members')){
        var index = data.members.indexOf(ViewerStore.get('id'));
        if (index > -1) {
            data.members.splice(index, 1);
        }
    }
    if (!_hasRoom(data.roomId)) {
        //_rooms.lastestRoomIsCreated = data.roomId;
        _rooms.isCreated.push(data.roomId);
        if(data.hasOwnProperty('minimized')){
            var minimized = data.minimized;
        }else{
            var minimized = (_isChatWindowCreatedBySystem() && !ChatWebAPIUtils.isOpennedChatboxWhenANewMesasgeArrives()) ? ChatConstants.WINDOW_MINIMIZE : ChatConstants.WINDOW_MAXIMIZE;

        }
        // Hacking for mobi - all chat window is created must be minimized
        
        if(ChatMooUtils.isMobile()){
            
            minimized = ChatConstants.WINDOW_MINIMIZE;
        }
        var isFocused = data.hasOwnProperty('isFocused')? data.isFocused : ChatConstants.IS_FOCUSED_CHAT_WINDOW;
        _rooms[data.roomId] = {
            id: data.roomId,
            first_blocked: data.first_blocked,
            second_blocked: data.second_blocked,
            messages: [],
            members: data.members,
            minimized: minimized,
            isFocused:isFocused,
            messagesIsLoaded:ChatConstants.WINDOW_MESSAGES_IS_UNLOADED,
            isMessageLoading: false,
            isGroup:data.is_group,
            hasJoined:data.has_joined
        };
        if(data.hasOwnProperty('newMessages')){

            CounterUnseenMessageStore.set(data.roomId,data.newMessages);
        }
        if(data.hasOwnProperty('firstIdNewMessage')){
            _rooms[data.roomId].firstIdNewMessage = data.firstIdNewMessage;
        }
        _notedRoomIsCreated(data.members, data.roomId);
        _renderChatWindows();
        // Hacking for adding friends
        if(_hasMissingFriendInfo(data.members)){
            ChatWebAPIUtils.sendRequestGetMyFriends();
        }
        if (!GroupStore.isGroupExists(data.roomId)) {
            ChatWebAPIUtils.sendRequestGetMyGroups();
        }
        // End hacking
        ChatMooUtils.noteRoomIsOpned(_rooms);
        // Performance improvement
        if(minimized == ChatConstants.WINDOW_MAXIMIZE){
            _rooms[data.roomId].isMessageLoading = true;
            //ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId);
            /*
            if(_rooms[data.roomId].hasOwnProperty('firstIdNewMessage')){
                
                ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId,_rooms[data.roomId].firstIdNewMessage);
                _rooms[data.roomId].firstIdNewMessage = 0;
            }else{
                ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId);
            }*/
            
            //ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId);
        }else{
           // setTimeout(function(){  ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId); }, 1);
            if(ChatMooUtils.isMobile()){
                _rooms[data.roomId].isMessageLoading = true;
                ChatWebAPIUtils.sendRequestGetRoomMessages(data.roomId);
            }
        }

    }


}
function _markMessagesIsLoaded(rId){
    if(_rooms.hasOwnProperty(rId)){
        _rooms[rId].messagesIsLoaded  = ChatConstants.WINDOW_MESSAGES_IS_LOADED;
    }
}
function _markMessagesIsLoading(rId){
    if(_rooms.hasOwnProperty(rId)){
        _rooms[rId].messagesIsLoaded  = ChatConstants.WINDOW_MESSAGES_IS_UNLOADED;
    }
}
function _addUserToARoom(roomId, users) {
    if (_hasRoom(roomId)) {
        if (users.length > 0) {
            for (var i = 0; i < users.length; i++) {
                if (_rooms[roomId].members.indexOf(users[i]) == -1) {
                    _rooms[roomId].members.push(users[i]);
                }
            }
        }
        ChatWebAPIUtils.sendRequestGetMyGroups();
        _renderChatWindows();
    }
}
function _caculateNewMessages() { return;

    var newMessages;
    var _messages = MessageStore.getAll();
    
    for (var key in _messages) {
        if (_messages.hasOwnProperty(key)) {
            newMessages = 0;
            if (_messages[key].length > 0) {
                for (var i = 0; i < _messages[key].length; i++) {
                    newMessages += (_messages[key][i].sender_id != ViewerStore.get('id')) ? parseInt(_messages[key][i].unseen) : 0;
                }
            }
            if( (newMessages == 0 && RoomStore.hadNewMessages(key)) || newMessages > 0 ){
                RoomStore.setNewMessages(key, newMessages);
                RoomStore.emitChange();
            }
        }
    }


}
function _newMessage(data) {
    
    if (data.hasOwnProperty('room_id')) {


        if(data.hasOwnProperty("sender_id")){
            if(data.sender_id != ViewerStore.get('id')){
                _hasNewMessage = true;
                _roomIdHasNewMessage = data.room_id;
                CounterUnseenMessageStore.addOne(data.room_id).emitChange(data.room_id);
            }
        }

        if (MessageStore.has(data.room_id)) {
            MessageStore.addNewMessage(data.room_id, data);
            _markMessagesIsSeenInRooms();
        } else {

            if (!_hasRoom(data.room_id)) {
                if(!ChatMooUtils.isMobile()){
                    ChatWebAPIUtils.sendRequestCreateChatWindowByRoomId(data.room_id,(ChatWebAPIUtils.isOpennedChatboxWhenANewMesasgeArrives()?ChatConstants.WINDOW_MAXIMIZE:ChatConstants.WINDOW_MINIMIZE));

                }else{
                    ChatWebAPIUtils.sendRequestCreateChatWindowByRoomId(data.room_id,ChatConstants.WINDOW_MINIMIZE);

                }
            }else{
               
                ChatWebAPIUtils.sendRequestGetRoomMessages(data.room_id);
            }
        }
        

    }
}
function _markMessagesIsSeenInRooms() {

    var messages;
    var messageIdsUnSeen = [];
    var roomIsSeen = [];
    if (_rooms.isCreated.length) {
        for (var i = 0; i < _rooms.isCreated.length; i++) {
            if (_rooms[_rooms.isCreated[i]].minimized == ChatConstants.WINDOW_MAXIMIZE) {
                messages = MessageStore.get(_rooms.isCreated[i]);
                var firstIdCurrentMessage = 0 ;
                
                if (messages instanceof Array) {
                    if (typeof messages.length != 'undefined') {
                        if( typeof messages[0] !='undefined'){
                           
                            if(messages[0].hasOwnProperty('id')){
                                firstIdCurrentMessage = messages[0].id;
                            }
                        }

                        for (j = 0; j < messages.length; j++) {
                            // Todo : Improvement for group later

                            if ((messages[j].unseen == "1" || messages[j].unseen == 1) && (messages[j].receiver_id == ViewerStore.get('id').toString() || messages[j].receiver_id == ViewerStore.get('id'))) {
                                messageIdsUnSeen.push(messages[j].unread_id);
                            }
                        }
                    }
                }
                
                if(_rooms[_rooms.isCreated[i]].hasOwnProperty('firstIdNewMessage')){
                    if(_rooms[_rooms.isCreated[i]].firstIdNewMessage != 0){
                        if(firstIdCurrentMessage == 0)
                        {
                            roomIsSeen.push(_rooms.isCreated[i]);
                        }else{
                            if(_rooms[_rooms.isCreated[i]].firstIdNewMessage < firstIdCurrentMessage){
                                roomIsSeen.push(_rooms.isCreated[i]);
                            }
                        }
                    }
                }

                CounterUnseenMessageStore.set(_rooms.isCreated[i],0);


            }

        }
    }

    if (messageIdsUnSeen.length > 0 || roomIsSeen.length > 0) {
        ChatWebAPIUtils.sendRequestMarkMessagesIsSeenInRooms(messageIdsUnSeen,roomIsSeen);
        CounterUnseenMessageStore.emitChange();
    }
}
function _markMessagesIsSeenInRoomsCallback(ids) {
    var messages;
    if (_rooms.isCreated.length) {
        for (i = 0; i < _rooms.isCreated.length; i++) {
            messages = MessageStore.get(_rooms.isCreated[i]);//myRooms[myRooms.isCreated[i]].messages;
            if (messages instanceof Array) {
                if (messages.length) {
                    for (j = 0; j < messages.length; j++) {
                        // Todo : Improvement for group later
                        if (ids.indexOf(messages[j].unread_id) > -1) {
                            // _rooms[_rooms.isCreated[i]].messages[j].unseen = "0";
                            
                            MessageStore.setMesageIsSeen(_rooms.isCreated[i], messages[j].id);
                        }
                    }
                }
            }

        }
    }
    _caculateNewMessages();
    
}
function _refeshARoom(rId) {
    if (_hasRoom(rId)) {
        ChatWebAPIUtils.sendRequestRefeshStatusARoomByRoomId(rId);
    }

}
function _refeshARoomCallback(data) {

    if (data.hasOwnProperty('id')) {
        if (_hasRoom(data.id)) {
            _rooms[data.id].first_blocked = data.first_blocked;
            _rooms[data.id].second_blocked = data.second_blocked;
            RoomStore.emitChange();
        }
    }


}
function _startTyping(data) {
    if (data.hasOwnProperty('rId') && data.hasOwnProperty('uId')) {
        if (_hasRoom(data.rId)) {
            if (_rooms[data.rId].hasOwnProperty('isTyping')) {
                if(_rooms[data.rId].isTyping.indexOf(data.uId) == -1 ){
                    _rooms[data.rId].isTyping.push(data.uId);
                }

            } else {
                _rooms[data.rId].isTyping = [data.uId];
            }
        }
    }
    RoomStore.emitChange();
}
function _stopTyping(data) {
  
    if (data.hasOwnProperty('rId') && data.hasOwnProperty('uId')) {
        if (_hasRoom(data.rId)) {
            if (_rooms[data.rId].hasOwnProperty('isTyping')) {
                var index = _rooms[data.rId].isTyping.indexOf(data.uId);
                if(index > -1){
                    _rooms[data.rId].isTyping.splice(index, 1);
                }
            }
        }
    }
    RoomStore.emitChange();
}
var RoomStore = assign({}, EventEmitter.prototype, {

    emitChange: function () {
        _markMessagesIsSeenInRooms();
        this.emit(CHANGE_EVENT);
    },

    /**
     * @param {function} callback
     */
    addChangeListener: function (callback) {
        this.on(CHANGE_EVENT, callback);
    },

    removeChangeListener: function (callback) {
        this.removeListener(CHANGE_EVENT, callback);
    },

    get: function (id) {
        return _rooms[id];
    },
    getFromUserId:function(id){
        if(_friendsRoom.hasOwnProperty(id)){
            if(_hasRoom(_friendsRoom.id)){
                return _rooms[_friendsRoom.id];
            }
        }
        if(_rooms.isCreated.length > 0){
            for(var i=0;i<_rooms.isCreated.length;i++){
                var rId = _rooms.isCreated[i]; 
                if(_rooms[rId].members.length == 1 && _rooms[rId].members[0] == id){
                    _friendsRoom[rId] = _rooms[rId].id;
                    return _rooms[_rooms[rId].id];
                }
            }
        }
        return {};
    },
    getRoomMobiIsActive:function(){
        if (_hasRoom(_roomMobiIsActive)) {
            return _rooms[_roomMobiIsActive];
        }
        return [];
    },
    getAll: function () {
        return _rooms;
    },
    getName: function (roomId) {
        if (_hasRoom(roomId)) {
            return (_rooms[roomId].hasOwnProperty('members')) ? UserStore.getNames(_rooms[roomId].members) : "";
        } else {
            _noteMissingRoom(roomId);
            return "";
        }
    },
    getStatusRoom: function (roomId) {

        if (_hasRoom(roomId) && typeof _rooms[roomId].members != 'undefined') {

            for (var i = 0; i < _rooms[roomId].members.length; i++) {
                if (UserStore.getStatus(_rooms[roomId].members[i]) == 1) {
                    return 1;
                }
            }
        } else {
            _noteMissingRoom(roomId);
            return 0;
        }

    },
    getLastestRoomIsActived:function(){
        return _rooms.lastestRoomIsActived;
    },
    getRoomIdHasNewMessage: function () {
        return _roomIdHasNewMessage;
    },
    minimizeChatWindow:function(rId,isMinimized){

        if (_hasRoom(rId)) {
            _rooms[rId].minimized = isMinimized ? ChatConstants.WINDOW_MINIMIZE : ChatConstants.WINDOW_MAXIMIZE;
        }
    },
    setRoomMobiIsActive:function(rId){
        _roomMobiIsActive = rId;
    },
    setNewMessages: function (roomId, count) {
        if (_hasRoom(roomId)) {
            _rooms[roomId].newMessages = count;
        }

    },
    hadNewMessages:function(roomId){
        if (_hasRoom(roomId)) {
            return (_rooms[roomId].newMessages  > 0);
        }else{
            return false;
        }
    },
    hasNewMessage: function () {
        return _hasNewMessage;
    },
    freeFlagHasNewMessage: function () {
        _hasNewMessage = false;
    },
    freeFlagIsFocused:function(rId){
        if(_hasRoom(rId)){
            _rooms[rId].isFocused = ChatConstants.NOT_FOCUSED_CHAT_WINDOW;
        }
    },
    playSound: function (roomId) {
        if (this.hasNewMessage() && roomId == this.getRoomIdHasNewMessage()) {
           /*
            window.require(["chat/js/lib/howler.core"], function (Howl) {
                var sound = new window.Howl({
                    src: [ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.mp3", ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.ogg", ChatWebAPIUtils.getSiteUrl() + "/chat/sound/beer_can_opening.aac"]
                }).play();
            });
            */

            _sound.play();
            this.freeFlagHasNewMessage();
        }
    },
    isGroup:function(rId){

        if (!_hasRoom(rId)) {
            return false;
        }
        if(!_rooms[rId].hasOwnProperty('isGroup')){
            return false;
        }
        return (_rooms[rId].isGroup == 0 )?false:true;
    },
    isBlocked: function (rId) {
        if (!_hasRoom(rId)) {
            return false;
        }
        return !( _rooms[rId].first_blocked == ChatConstants.ROOM_IS_UNBLOCKED && _rooms[rId].second_blocked == ChatConstants.ROOM_IS_UNBLOCKED);
    },
    isBlocker: function (rId, uId) {
        if (!_hasRoom(rId)) {
            return false;
        }
        return (_rooms[rId].first_blocked == uId || _rooms[rId].second_blocked == uId);
    },
    markMessagesIsLoaded:function(rId){
        _markMessagesIsLoaded(rId);
        this.emitChange();
    },
    markMessagesIsLoading:function(rId){
        _markMessagesIsLoading(rId);
        this.emitChange();
    },
    setFirstIdNewMessage:function(rId,idMessage){
        _rooms[rId].firstIdNewMessage = idMessage;
    }
});

RoomStore.dispatchToken = ChatAppDispatcher.register(function (action) {

    switch (action.type) {
        case ActionTypes.CREATE_A_ROOM_FOR_A_USER:
            ChatAppDispatcher.waitFor([FriendStore.dispatchToken]);
            _createChatWindow(action.userId);
            break;
        case ActionTypes.CREATE_A_ROOM_BY_ROOM_ID:
            ChatAppDispatcher.waitFor([FriendStore.dispatchToken]);
            _createChatWindowByRoomId(action.rId);
            break;
        case ActionTypes.CREATE_A_ROOM_FOR_A_USER_BY_USER_CALLBACK:
            _createChatWindowByUserCallback(action.data);
            break;
        case ActionTypes.CREATE_A_ROOM_FOR_A_USER_BY_SYSTEM_CALLBACK:
            _createChatWindowBySystemCallback(action.data);
            break;
        case ActionTypes.ADD_USERS_TO_A_ROOM_BY_SYSTEM_CALLBACK:
            _addUserToARoom(action.roomId, action.users);
            break;
        case ActionTypes.MARK_MESSAGES_IS_LOADED_FOR_A_ROOM:
            _markMessagesIsLoaded(action.roomId)
            break;
        case ActionTypes.ACTIVE_A_ROOM:
            _activeRoom(action.roomId);
            break;
        case ActionTypes.DESTROY_A_ROOM:
            ChatAppDispatcher.waitFor([MessageStore.dispatchToken]);
            _destroyRoom(action.roomId);
            RoomStore.emitChange();
            break;
        case ActionTypes.DESTROY_ALL_ROOM:
            ChatAppDispatcher.waitFor([MessageStore.dispatchToken]);
            _destroyAllRoom();
            RoomStore.emitChange();
            break;
        case ActionTypes.RERENDER_ALL_ROOMS:
            _renderChatWindows();
            break;
        case ActionTypes.MINIMIZE_A_ROOM:
            _minimizeChatWindow(action.roomId, action.isMinimized);
            break;
        case ActionTypes.CACULATE_NEW_MESSAGES_FOR_ALL_ROOM:
            _caculateNewMessages();
            break;
        case ActionTypes.GET_NEW_MESSAGE_FOR_A_ROOM_CALLBACK:
            _newMessage(action.message);
            break;
        case ActionTypes.GET_MESSAGE_FOR_A_ROOM_CALLBACK:
            if(_hasRoom(action.data.id)){
                _rooms[action.data.id].isMessageLoading = false;
            }

            ChatAppDispatcher.waitFor([MessageStore.dispatchToken]);
            _caculateNewMessages();
            break;
        case ActionTypes.MARK_MESSAGES_IS_SEEN_IN_ROOMS_CALLBACK:
            _markMessagesIsSeenInRoomsCallback(action.ids);
            break;
        case ActionTypes.REFESH_A_ROOM_BY_ROOM_ID:
            _refeshARoom(action.roomId);
            break;
        case ActionTypes.REFESH_A_ROOM_BY_ROOM_ID_CALLBACK:
            _refeshARoomCallback(action.data);
            break;
        case ActionTypes.USER_IS_START_TYPING_IN_A_ROOM:
            _startTyping(action.data);
            break;
        case ActionTypes.USER_IS_STOP_TYPING_IN_A_ROOM:
            _stopTyping(action.data);
            break;

        default:
        // do nothing
    }

});

module.exports = RoomStore;