/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatAppDispatcher = require('../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../constants/ChatConstants');
var EventEmitter = require('events').EventEmitter;
EventEmitter.prototype._maxListeners = 100;
var assign = require('object-assign');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var Immutable = require('immutable');

var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';

var counterUnseenMessageRecord = Immutable.Record({counter:0});
var data = Immutable.Map();


var CounterUnseenMessageStore = assign({}, EventEmitter.prototype, {

    emitChange: function(roomId) { 
        roomId = typeof roomId !== 'undefined' ? roomId : 0;
        ChatWebAPIUtils.updateConversationCounter(this.getTotalCounter());
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
        return data.get(roomId, new counterUnseenMessageRecord()).counter;
    },
    set:function(roomId,value){
        data = data.set(roomId,new counterUnseenMessageRecord({counter:value}));
    },
    addOne:function(roomId){
        this.set(roomId,this.get(roomId)+1);
        return this;
    },
    getAll:function(){
        return data.toObject();
    },
    getTotalCounter:function(){
        var total = 0;
        data.forEach(function(item){
            total += (item.counter > 0)?1:0;
        });
        return total;
    }
});

CounterUnseenMessageStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.COUNTER_UNSEEN_MESSAGES_FROM_RAW_DATA:

            break;
        default:
        // do nothing
    }

});

module.exports = CounterUnseenMessageStore;