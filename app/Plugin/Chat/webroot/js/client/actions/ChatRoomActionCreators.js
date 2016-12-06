/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

var ChatAppDispatcher = require('../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../constants/ChatConstants');

var ActionTypes = ChatConstants.ActionTypes;

module.exports = {
    /**
     * @param {array} rawFriends
     */
    createForAUser: function(uId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.CREATE_A_ROOM_FOR_A_USER,
            userId: uId
        });
    },
    /**
     * @param {array} rId
     */
    createByRoomId: function(rId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.CREATE_A_ROOM_BY_ROOM_ID,
            rId: rId
        });
    },
    refeshStatusARoom:function(data){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.REFESH_A_ROOM_BY_ROOM_ID_CALLBACK,
            data: data
        });
    },
    /**
     * @param {array} data
     */
    createForAUserByUserCallback: function(data) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.CREATE_A_ROOM_FOR_A_USER_BY_USER_CALLBACK,
            data: data
        });
    },
    /**
     * @param {array} data
     */
    createForAUserBySystemCallback: function(data) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.CREATE_A_ROOM_FOR_A_USER_BY_SYSTEM_CALLBACK,
            data: data
        });
    },
    addUsersToARoomCallback: function(roomId,users) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.ADD_USERS_TO_A_ROOM_BY_SYSTEM_CALLBACK,
            roomId: roomId,
            users:users
        });
    },
    /**
     * @param {array} data
     */
    markMessagesIsSeenInRoomsCallback: function(data) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MARK_MESSAGES_IS_SEEN_IN_ROOMS_CALLBACK,
            ids: data
        });
    },
    /**
     * @param {string} roomId
     */
    activeARoom: function(roomId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.ACTIVE_A_ROOM,
            roomId: roomId
        });
    },
    /**
     * @param {string} roomId
     */
    destroyARoom: function(roomId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.DESTROY_A_ROOM,
            roomId: roomId
        });
    },
    destroyAllRoom: function() {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.DESTROY_ALL_ROOM,
        });
    },
    reRenderAllRooms:function(){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.RERENDER_ALL_ROOMS
        });
    },
    /**
     * @param {string} roomId
     * @param {boolean} isMinimized
     */
    minimizeARoom:function(roomId, isMinimized){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MINIMIZE_A_ROOM,
            roomId:roomId,
            isMinimized:isMinimized
        });
    },

    caculateNewMessages:function(){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.CACULATE_NEW_MESSAGES_FOR_ALL_ROOM
        });
    } ,
    refeshARoom:function(roomId){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.REFESH_A_ROOM_BY_ROOM_ID,
            roomId:roomId
        });
    },
    startTyping:function(data){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.USER_IS_START_TYPING_IN_A_ROOM,
            data:data
        });
    },
    stopTyping:function(data){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.USER_IS_STOP_TYPING_IN_A_ROOM,
            data:data
        });
    },
    markMessagesIsLoaded:function(roomId){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MARK_MESSAGES_IS_LOADED_FOR_A_ROOM,
            roomId:roomId
        });
    }
};
