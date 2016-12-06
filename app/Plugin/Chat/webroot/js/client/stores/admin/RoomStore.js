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
var _room = {};
var _missingMemberInARoom = [];

function _hasRoom(id){
    return _room.hasOwnProperty(id);
}
function _noteMissingMemberInARoom(id){
    if(_missingMemberInARoom.length > 0){
        for(var i =0;i<=_missingMemberInARoom.length;i++){
            if (id == _missingMemberInARoom[i]){
                return 0;
            }
        }
    }
    _missingMemberInARoom.push(id);
}
function _clearMissingEntries(){
    _missingMemberInARoom = [];
}
var RoomStore = assign({}, EventEmitter.prototype, {

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
        return _room[key];
    },
    getAll:function(){
        return _room;
    },
    add:function(room){
        _room[room.id] = room;
    },
    getMembers:function(key){

        if(_hasRoom(key)){
            return _room[key].members;
        }else{
            _noteMissingMemberInARoom(key);
            return [];
        }
    },
    
    updateMissingEntries:function(){

        if(_missingMemberInARoom.length){


                ChatWebAPIUtils.sendRequestForUpdatingRooms(_missingMemberInARoom);

           
        }
    }
});

RoomStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.RECEIVE_RAW_ROOMS:
       
            if(action.rooms.length ){
                for(var i=0;i<action.rooms.length;i++){
                    action.rooms[i].members = [];
                    var tmp = action.rooms[i].code.split(".");
                    for(j=0;j<tmp.length;j++){
                        action.rooms[i].members.push(parseInt(tmp[j]));
                    }
                    RoomStore.add(action.rooms[i]);
                }
            }

            _clearMissingEntries();
            RoomStore.emitChange();
            break;
        default:
        // do nothing
    }

});

module.exports = RoomStore;