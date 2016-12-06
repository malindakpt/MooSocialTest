/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatAppDispatcher = require('../../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../../constants/admin/ChatConstants');
var EventEmitter = require('events').EventEmitter;
var assign = require('object-assign');
var ChatWebAPIUtils = require('../../utils/admin/ChatWebAPIUtils');


var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';
var _user = {};
var _missingUser = [];

function _hasUser(id){
    return _user.hasOwnProperty(id);
}
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
        return _user[key];
    },
    getAll:function(){
        return _user;
    },
    add:function(user){
        _user[user.id] = user;
    },
    getName:function(key){

        if(_hasUser(key)){
            return _user[key].name;
        }else{
            _noteMissingUser(key);
            return "";
        }
    },
    getAvatar:function(key){
        if(_hasUser(key)){
            return _user[key].avatar;
        }else{
            //_noteMissingUser(key);
            return "";
        }
    },
    getNames:function(uIds){

        var names="";
        if( typeof uIds == 'undefined' ){
            return names;
        }
        if (uIds.length == 0 ){
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
        if(_hasUser(key)){
            return _user[key].is_logged;
        }else{
            _noteMissingUser(key);
            return 0;
        }
    },
    updateMissingUser:function(){

        if(_missingUser.length){

            ChatWebAPIUtils.sendRequestForUpdatingUsers(_missingUser);
        }
    }
});

UserStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.RECEIVE_RAW_USERS:
            
            if(action.users.length ){
                for(var i=0;i<action.users.length;i++){
                    //action.users[i].avatar = ChatWebAPIUtils.getSiteUrl() + '/uploads/users/avatar/' + action.users[i].id + '/50_square_' + action.users[i].avatar;
                    UserStore.add(action.users[i]);
                }
            }
            
            _clearMissingUser();
            UserStore.emitChange();
            break;
        default:
        // do nothing
    }

});

module.exports = UserStore;