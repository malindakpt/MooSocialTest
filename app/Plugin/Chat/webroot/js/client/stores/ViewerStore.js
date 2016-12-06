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


var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';
var _user = {id:0}; // 0 means Guest

function _userIsLoggedCallback(uId){
    if (uId != 0){
        _user.id = uId;
        ViewerStore.emitChange();
        ChatWebAPIUtils.sendRequestForGetRoomHasUnreadMessage();
    }
}
var ViewerStore = assign({}, EventEmitter.prototype, {

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
    isGuest:function(){
        return (_user.id == 0)?true:false;
    }
});

ViewerStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.USER_IS_LOGGED_CALLBACK:
            _userIsLoggedCallback(action.uId);
            break;
        default:
        // do nothing
    }

});

module.exports = ViewerStore;