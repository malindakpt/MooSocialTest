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
var _general = {status:ChatConstants.SERVER_IS_BEING_CHECKED,info:''};


var GeneralStore = assign({}, EventEmitter.prototype, {

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
        return _general[key];
    },
    getAll:function(){
        return _general;
    },
    isServerOnline:function(){
        return _general.status == ChatConstants.SERVER_IS_ONLINE;
    },
    isServerOffline:function(){
        return _general.status == ChatConstants.SERVER_IS_OFFLINE;
    },
    isServerBeingChecked:function(){
        return _general.status == ChatConstants.SERVER_IS_BEING_CHECKED;
    }
});

GeneralStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.SETUP_SERVER_STATUS:
            _general.status = action.status;
            GeneralStore.emitChange();
            break;
        case ActionTypes.SETUP_SERVER_INFO:
            _general.info = action.info;
            GeneralStore.emitChange();
            break;
        default:
        // do nothing
    }

});

module.exports = GeneralStore;