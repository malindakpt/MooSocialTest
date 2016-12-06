/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatAppDispatcher = require('../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../constants/ChatConstants');
var UserStore = require('./UserStore');
var ViewerStore = require('./ViewerStore');
var __ = require('../utils/ChatMooI18n').i18next;
var EventEmitter = require('events').EventEmitter;
EventEmitter.prototype._maxListeners = 100;
var assign = require('object-assign');

var Immutable = require('immutable');
var _ = require('lodash');

var messageRecord = Immutable.Record({
    id:0,
    content:"",
    note_content_html:"",
    note_one_emoj_only:0,
    receiver_id:0,
    room_id:0,
    sender_id:0,
    time:0,
    type:"text",
    unread_id:0,
    unseen:0
});
var roomMessageRecord = Immutable.Record({
    messages:Immutable.Map(),
    isScrollToBottom: true,
    scrollToIfNeed : 0,
    isAllowedLoadMoreMessages:true,
    isMessageLoading:false
});
var _roomMessages = Immutable.Map();

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';


function _resetRoomMessages(rId){
    var roomMessage = _roomMessages.get(rId,new roomMessageRecord());
    roomMessage = roomMessage.set("messages",Immutable.Map());
    _roomMessages = _roomMessages.set(rId,roomMessage);
}
function _update(rId,options){
    var roomMessages = _roomMessages.get(rId,new roomMessageRecord()).merge(options);
    _roomMessages = _roomMessages.set(rId,roomMessages);
}
function _addOneMessageToRoom(rId,message){
    var roomMessage = _roomMessages.get(rId,new roomMessageRecord());
    var messages = roomMessage.get("messages",Immutable.Map());
    var  newMessage = new messageRecord({
        id:message.id,
        content:message.content,
        note_content_html:message.note_content_html,
        note_one_emoj_only:message.note_one_emoj_only,
        receiver_id:message.receiver_id,
        room_id:message.room_id,
        sender_id:message.sender_id,
        time:message.time,
        type:message.type,
        unread_id:message.unread_id,
        unseen:message.unseen
    });

    if(!messages.has(message.id)){

        messages = messages.set(message.id,newMessage);
        roomMessage = roomMessage.set("messages",messages);
        _roomMessages = _roomMessages.set(rId,roomMessage);
    }

}
function _addRoomMessages(rId,messages){
    if(_.isArray(messages)){
        _(messages).each(function(message){
            _addOneMessageToRoom(rId,message);
        });
        _sortRoomMessages(rId);
    }
}
function _sortRoomMessages(rId){
    if(_roomMessages.has(rId)){
        var roomMessages = _roomMessages.get(rId,new roomMessageRecord());
        var messages = roomMessages.get("messages");
        messages = messages.sort(function(a,b){
            return a.id - b.id ;
        });
        _roomMessages  = _roomMessages.set(rId,roomMessages.set("messages",messages));
    }
}
var _getRoomMessagesCallback = function (data) {
    if(data.hasOwnProperty('id') && data.hasOwnProperty('messages')){
        _addRoomMessages(data.id,data.messages);
        _update(data.id,{
            isScrollToBottom:true,
            scrollToIfNeed:0,
            isAllowedLoadMoreMessages:true,
            isMessageLoading:false
        });
        MessageStore.emitChange(data.id);
    }

};
var _getRoomMessagesMoreCallback = function(data){
    if(data.hasOwnProperty('id') && data.hasOwnProperty('messages')){


        var options = {};
        var roomMessages = _roomMessages.get(data.id,new roomMessageRecord());
        if(_.isArray(data.messages)){
            //_resetRoomMessages(data.id);
            _addRoomMessages(data.id,data.messages);
            //var messages = _roomMessages.get(data.id,new roomMessageRecord()).get("messages").concat(roomMessages.get("messages"));
            //roomMessages = roomMessages.set("messages",messages);
            //_roomMessages = _roomMessages.set(data.id,roomMessages);
            options = {isScrollToBottom:false,scrollToIfNeed:data.scrollIfNeed};
        }else{
            options = {isAllowedLoadMoreMessages:false};
        }
        options.isMessageLoading = false;
        _update(data.id,options);
        MessageStore.emitChange(data.id);
    }
};

var MessageStore = assign({}, EventEmitter.prototype, {

    emitChange: function(roomId) {
        roomId = typeof roomId !== 'undefined' ? roomId : 0;
        this.emit(CHANGE_EVENT,roomId);
    },

    /**
     * @param {function} callback
     */
    addChangeListener: function(callback) {
        this.on(CHANGE_EVENT, callback);
    },

    removeChangeListener: function(callback) {
        this.removeListener(CHANGE_EVENT, callback);
    },
    
    get: function(roomId) {
        var roomMessage =  _roomMessages.get(roomId,new roomMessageRecord());
        return roomMessage.get("messages",Immutable.Map()).toArray();
    },

    getAll: function() {
        return _roomMessages;
    },
    getLatestMesasge:function(rId){
        if(this.has(rId)){
            var roomMessage =  _roomMessages.get(rId,new roomMessageRecord());
            var messages = roomMessage.get("messages",Immutable.Map());
            if(messages.isEmpty()){
                return "";
            }
            var message = messages.last();
            var content = message.content;
            var chatter = UserStore.get(message.sender_id);
            var name = chatter.name;
            if(message.sender_id == ViewerStore.get('id')){
                name = __.t("you");
            }
            switch (message.type) {
                case "image":
                    content = __.t("userA_added_a_photo",{"userA":name});
                    break;
                case "file":
                    content = __.t("userA_added_a_file",{"userA":name});
                    break;
                case "system":

                    var system = JSON.parse(message.content);
                    var user;
                    switch (system.action) {
                        case "left_the_conversation":
                            content = __.t("name_left_the_conversation",{"name":name});
                            break;
                        case "added":
                            userB = "";
                            for (var i = 0; i < system.usersId.length; i++) {
                                userB += ((i == 0) ? " " : ", ") + UserStore.getName(system.usersId[i]);
                            }
                            content = __.t("userA_added_userB",{"userA":name,"userB":userB});
                        default:
                    }

                    break;
                default:
                    content = message.note_content_html;
            }
            return content;
        }

        return "";
    },
    getScrollToIfNeed:function(rId){
        return  _roomMessages.get(rId,new roomMessageRecord()).get("scrollToIfNeed");
    },
    freeScrollToIfNeed:function(rId){
        _update(rId,{scrollToIfNeed:0});
    },
    destroy:function(roomId){
        _roomMessages = _roomMessages.delete(roomId);
    },
    has:function(roomId){
        return _roomMessages.get(roomId) != undefined ;
    },
    addNewMessage:function(roomId,message){
        if(!message.hasOwnProperty('time')){
            var a = new Date();
            message.time = Math.floor(a.getTime()/1000);
        }
        
        // Fix bug for slowly when multi chat in one room
        //TODO: We will limit 300 for chat group and unlimit for non-group
        /*
        if(_messages[roomId].length > 140){
            _messages[roomId] =  _messages[roomId].slice(Math.max(_messages[roomId].length - 40, 1));
            _messages[roomId].push(message);
        }else{
            _messages[roomId].push(message);
        }
        _isScrollToBottom[roomId] = true;
        _scrollToIfNeed[roomId] = 0 ;
        _isMessageLoading[roomId] = false;
        */
        _addOneMessageToRoom(roomId,message);
        _sortRoomMessages(roomId);
        _update(roomId,{isScrollToBottom:true,scrollToIfNeed:0,isMessageLoading:0});
        this.emitChange(roomId);
    },
    setMesageIsSeen:function(roomId,rawMessageId){
        /*
        if(_messages.hasOwnProperty(roomId)){
            _messages[roomId][rawMessageId].unseen = "0";
        }
        */
        if(_roomMessages.has(roomId)){


            var roomMessages = _roomMessages.get(roomId,new roomMessageRecord());
            var messages = roomMessages.get("messages");
            if(messages.has(rawMessageId)){
                messages = messages.update(rawMessageId, function(item) {
                    return item.set("unseen","0");
                });
                _roomMessages  = _roomMessages.set(roomId,roomMessages.set("messages",messages));
            }
        }
    },
    getStartMesageId:function(roomId){
      
        if(!this.has(roomId)){
            return 0;
        }else{
            var message = this.get(roomId);
            if(message.length > 0){
                return message[0].get("id");
            }else{
                return 0;
            }
        }
    },
    isScrollToBottom:function(rId){
        return  _roomMessages.get(rId,new roomMessageRecord()).get("isScrollToBottom");
    },
    isAllowedLoadMoreMessages:function(rId){
        return  _roomMessages.get(rId,new roomMessageRecord()).get("isAllowedLoadMoreMessages");
    },
    isMessageLoading:function(rId){
        return  _roomMessages.get(rId,new roomMessageRecord()).get("isMessageLoading");
    },
    setMessageLoading:function(rId){
        _update(rId,{isMessageLoading:true,isScrollToBottom:false});
        this.emitChange(rId);
    }
});

MessageStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.GET_MESSAGE_FOR_A_ROOM_CALLBACK:
            _getRoomMessagesCallback(action.data);
            break;
        case ActionTypes.GET_MESSAGE_MORE_FOR_A_ROOM_CALLBACK:
            _getRoomMessagesMoreCallback(action.data);
            break;
        case ActionTypes.DELETE_ALL_MESSAGE_MORE_FOR_A_ROOM:
            _resetRoomMessages(action.rId);
            break;
        default:
        // do nothing
    }

});

module.exports = MessageStore;