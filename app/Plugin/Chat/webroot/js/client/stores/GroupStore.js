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
var ViewerStore = require('../stores/ViewerStore');

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';
var _groups = [];
function _receiveGroups(rawGroups) {
    _groups = [];
    if(rawGroups.length > 0){

        for(var i=0;i<rawGroups.length;i++){
            if(rawGroups[i].hasOwnProperty("members")){ 
                var members = rawGroups[i].members.split(',');
                for (var ii=0;ii<members.length;ii++){
                    members[ii] = parseInt(members[ii]);
                }

                var index = members.indexOf(ViewerStore.get('id'));
                if (index > -1) {
                    members.splice(index, 1);
                }
                _groups.push({id:rawGroups[i].room_id,members:members});
            }
        }
    }

}
var GroupStore = assign({}, EventEmitter.prototype, {

    emitChange: function() {
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
        return _groups[id];
    },
    getAll: function() {
        return _groups;
    },
    isGroupExists:function(roomId){
        if(_groups.length > 0){
            if(_groups.length > 0){

                for(var i=0; i<_groups.length;i++){
                    if(roomId == _groups[i].id){
                        return true;
                    }
                }
            }
        }

        return false;

    }

});

GroupStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.RECEIVE_RAW_GROUPS:
            _receiveGroups(action.rawGroups);
            GroupStore.emitChange();
            break;
        



        default:
        // do nothing
    }

});

module.exports = GroupStore;