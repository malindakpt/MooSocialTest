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
var Bloodhound = require('bloodhound-js');
var UserStore = require('../stores/UserStore');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var Immutable = require('immutable');
var _ = require('lodash');
var ChatMooUtils = require('../utils/ChatMooUtils');
var CounterUnseenMessageStore = require('../stores/CounterUnseenMessageStore');
var keyWordCache = [];
var friendRecord = Immutable.Record({
    id:0,
    avatar:"",
    gender:"",
    is_hidden:0,
    is_logged:0,
    name:"",
    roomId:0,
    url:""
});
var _records = Immutable.Map();

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';


var engine = new Bloodhound({
    initialize: false,
    local: [],
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name')
});

var findAFriend = "";
var engineBloodHoundCallback = function (users) {
    _hideItemsFriendInStatusWindow(_getKey());
    if (users.length) {
        for (var i = 0; i < users.length; i++) {
            if(_records.has(users[i].id)){
                _records = _records.update(users[i].id, function(item) {
                    return item.set("is_hidden",ChatConstants.ITEM_SHOW);
                });
            }
        }
    }
    FriendStore.emitChange();
};
var _getKey = function(){
    return _records.keySeq().toArray();
};
var getKeyOnline = function(){
    return _records.filter(function(item){
        return item.is_logged === ChatConstants.USER_LOGGED;
    }).keySeq().toArray();
};
var _setStatus = function (users, is_logged, is_hidden) {
    if (users.length) {
            for (var i = 0; i < users.length; i++) {
                UserStore.setStatus(users[i],is_logged);
                if (_records.has(users[i])) {
                    _records = _records.update(users[i], function(item) {
                        return item.set("is_logged",is_logged).set("is_hidden",is_hidden);
                    });
                } else {
                    // It means that need to refesh friendlist
                    return false;
                }
            }
    }
    return true;
};
function _hideItemsFriendInStatusWindow(users) {
    _setItemsFriendInStatusWindow(users,ChatConstants.ITEM_HIDE);
};
function _showItemsFriendInStatusWindow(users) {
    _setItemsFriendInStatusWindow(users,ChatConstants.ITEM_SHOW);
};
function _setItemsFriendInStatusWindow(users,status){
    if (users.length) {
        for (var i = 0; i < users.length; i++) {
            if(_records.has(users[i])){
                _records = _records.update(users[i], function(item) {
                    return item.set("is_hidden",status);
                });
            }
        }
    }
};
function _showItemsFriendHasNewMessage(){
    var users = _getKey();
    if (users.length) {
        for (var i = 0; i < users.length; i++) {
                    if(_getCounterUnSeenMessageFromUser(users[i]) > 0){
                        _records = _records.update(users[i], function(item) {
                            return item.set("is_hidden",ChatConstants.ITEM_SHOW);
                        });
                    }
        }
    }
}
function _getCounterUnSeenMessageFromUser(uId){
    if(_records.has(uId)){
        var roomId = _records.get(uId).roomId;
        if(roomId != 0 && roomId != undefined){
            return CounterUnseenMessageStore.get(roomId);
        }
    }
    return 0;
}
function _friendsFilter(name){
    findAFriend = name;
    if (name == '') {
        _hideItemsFriendInStatusWindow(_getKey());
        _showItemsFriendInStatusWindow(getKeyOnline());
        if (!ChatWebAPIUtils.isHideOfflineUser()){
            _showItemsFriendInStatusWindow(_getKey());
            // Fixing show offline user on mobi in case he send new message

        }else{

            if(ChatMooUtils.isMobile()){
                _showItemsFriendHasNewMessage();
            }
        }
        FriendStore.emitChange();
    } else {

        if(_.includes(keyWordCache,name)){
            engine.search(name, engineBloodHoundCallback.bind(this), engineBloodHoundCallback.bind(this));
        }else{
            ChatWebAPIUtils.sendRequestSearchName(name);
        }
    }
}

function _setOnline(users){
    if (_setStatus(users, ChatConstants.USER_LOGGED, ChatConstants.ITEM_SHOW)) {
        _friendsFilter(findAFriend);
    } else {
        ChatWebAPIUtils.sendRequestGetMyFriends(users);
    }
}
function _setOffline(users){
    if (_setStatus(users, ChatConstants.USER_LOGOUT, ChatConstants.ITEM_HIDE)){
        _friendsFilter(findAFriend);
    }else {
        ChatWebAPIUtils.sendRequestGetMyFriends(users);
    }

}
function _addFriends(rawFriends,isClearSearchEngine) {
    // For search engine
    if(isClearSearchEngine){
        engine.clear();
    }
    engine.add(rawFriends);
    // End for search engine


    if (rawFriends.length && mooConfig) {
        for (i = 0; i < rawFriends.length; i++) {
            rawFriends[i].avatar = ChatWebAPIUtils.getAvatarLinkFromDataUser(rawFriends[i]);
            rawFriends[i].url = ChatWebAPIUtils.getProfileLinkFromDataUser(rawFriends[i]);
            // Update userStore
            UserStore.add(rawFriends[i]);
            if(!_records.has(rawFriends[i].id)){ // Fixing for Mobi 35 on MOOPLUGIN-470
                _records = _records.set(rawFriends[i].id,new friendRecord({
                    id:rawFriends[i].id,
                    avatar:rawFriends[i].avatar,
                    gender:rawFriends[i].gender,
                    is_hidden:rawFriends[i].is_hidden,
                    is_logged:rawFriends[i].is_logged,
                    name:rawFriends[i].name,
                    roomId:rawFriends[i].roomId,
                    url:rawFriends[i].url
                }));
            }
        }

    }
}
var FriendStore = assign({}, EventEmitter.prototype, {

    emitChange: function() {
        _records = _records.sort(function(a,b){
            return b.is_logged - a.is_logged ;
        });
        this.emit(CHANGE_EVENT);
    },

    /**
     * @param {function} callback
     */
    addChangeListener: function(callback) {
        this.on(CHANGE_EVENT, callback);
    },
    /**
     * @param {function} callback
     */
    removeChangeListener: function(callback) {
        this.removeListener(CHANGE_EVENT, callback);
    },
    get: function(id) {
        return _records.get(id);
    },
    has:function(id){
        return _records.get(id) != undefined ;
    },
    getAll:function(){

        var friends = _records.toObject();
        friends.key = _getKey();
        friends.keyonline = getKeyOnline();
        return friends;
    },
    /**
     * @param {string} uId
     */
    isRoomCreated:function(uId){
        var record = _records.get(uId,new friendRecord());
        return (record.roomId == 0)?false:true;
    },
    /**
     * @param {string} uId
     */
    getRoomIdOfAUser:function(uId){

        var record = _records.get(uId,new friendRecord());
        return record.roomId;
    },
    /**
     * @param {string} uId
     */
    setRoomIdOfAUser:function(uId,roomId){

        if(_records.has(uId)){
            _records = _records.update(uId, function(item) {
                return item.set("roomId",roomId);
            });
        }

    },
    getBloodhoundEngine:function(){
        return engine;
    },
    isCachedKeyword:function(name){
        if(_.includes(keyWordCache,name)){return true}
        return false;
    }
});

FriendStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.RECEIVE_RAW_FRIENDS:
            _addFriends(action.rawFriends,true);
            if (!ChatWebAPIUtils.isHideOfflineUser()){
                _showItemsFriendInStatusWindow(_getKey());
            }
            FriendStore.emitChange();
            break;

        case ActionTypes.FIND_A_FRIENDS:
            _friendsFilter(action.name);
            break;
        case ActionTypes.SET_OFFLINE_FRIENDS:
            _setOffline(action.friends);
            break;

        case ActionTypes.SET_ONLINE_FRIENDS:
            _setOnline(action.friends);
            break;

        case ActionTypes.ADD_RAW_FRIENDS:
            _addFriends(action.rawFriends,false);
            if (!ChatWebAPIUtils.isHideOfflineUser()){
                _showItemsFriendInStatusWindow(_getKey());
            }
            FriendStore.emitChange();
            break;
        case ActionTypes.SEARCH_FRIEND_CALLBACK:
            keyWordCache.push(action.data.name);
            _addFriends(action.data.rawFriends,false);
            _friendsFilter(action.data.name);
            break;
        case ActionTypes.ADD_RAW_FRIENDS_BY_KEYWORD:
            keyWordCache.push(action.data.name);
            _addFriends(action.data.rawFriends,false);
            break;
        default:
        // do nothing
    }

});

module.exports = FriendStore;