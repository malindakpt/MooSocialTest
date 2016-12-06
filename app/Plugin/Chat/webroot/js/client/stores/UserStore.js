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
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var Immutable = require('immutable');
var _ = require('lodash');

var userRecord = Immutable.Record({
    id:0,
    avatar:"",
    gender:"",
    is_hidden:0,
    is_logged:0,
    name:"",
    url:""
});
var _records = Immutable.Map();

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';

var _missingUser = [];
var _tmp_cancel_updateMissingUser = false; // Support for loading too much windows

function _noteMissingUser(id){
    if(_missingUser.length > 0){
        for(var i =0;i<=_missingUser.length;i++){
            if (id == _missingUser[i]){
                return 0;
            }
        }
    }
    _missingUser.push(id);
}
function _clearMissingUser(){
    _missingUser = [];
}
var UserStore = assign({}, EventEmitter.prototype, {

    emitChange: function() {
        this.emit(CHANGE_EVENT);
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

    get: function(key) {
        return _records.get(key).toObject();
    },
    getAll:function(){
        return _records.toObject();
    },
    add:function(user){
        _records = _records.set(user.id,new userRecord({
            id:user.id,
            avatar:user.avatar,
            gender:user.gender,
            is_hidden:user.is_hidden,
            is_logged:user.is_logged,
            name:user.name,
            url:user.url
        }));
    },
    getName:function(key){
        if(_records.has(key)){
            return  _records.get(key).name ;
        }else{
            _noteMissingUser(key);
            return "";
        }
    },
    getAvatar:function(key){
        if(_records.has(key)){
            return _records.get(key).avatar;
        }else{
            //_noteMissingUser(key);
            return "";
        }
    },
    getNames:function(uIds){

        var names="";
        if( typeof uIds == 'undefined'){
            return names;
        }
        for(var i=0;i<uIds.length;i++){
            var name = this.getName(uIds[i]);
            if (name != ""){
                if(names == ""){
                    names = name;
                }else{
                    names += ', ' +name;
                }
            }
        }

        return names;
    },
    getStatus:function(key){
        if(_records.has(key)){
            return _records.get(key).is_logged;
        }else{
            _noteMissingUser(key);
            return 0;
        }
    },
    updateMissingUser:function(){

        if(_missingUser.length){
            
            ChatWebAPIUtils.sendRequestForUpdatingUsers(_missingUser);
        }
    },
    stopUpdateMissingUser:function(yes){
        _tmp_cancel_updateMissingUser = yes;
    },
    setStatus:function(uId,status){
        if (_records.has(uId)) {
            _records = _records.update(uId, function(item) {
                return item.set("is_logged",status);
            });
        }
    }
});

UserStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.USER_ADD_CALLBACK:

            if(action.users.length && mooConfig){
                for(var i=0;i<action.users.length;i++){
                    action.users[i].avatar = ChatWebAPIUtils.getAvatarLinkFromDataUser(action.users[i]);
                    action.users[i].url    = ChatWebAPIUtils.getProfileLinkFromDataUser(action.users[i]);
                    UserStore.add(action.users[i]);
                }
            }
            _clearMissingUser();
            UserStore.emitChange();
            break;
        case ActionTypes.STOP_UPDATE_MISSING_USER:
            UserStore.stopUpdateMissingUser(action.yes);
            break;
        default:
        // do nothing
    }

});

module.exports = UserStore;