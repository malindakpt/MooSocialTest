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
    receiveAll: function(rawFriends) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.RECEIVE_RAW_FRIENDS,
            rawFriends: rawFriends
        });
    },
    /**
     * @param {array} rawFriends
     */
    add: function(rawFriends) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.ADD_RAW_FRIENDS,
            rawFriends: rawFriends
        });
    },
    /**
     * @param {array} friends
     */
    setOnline:function(friends){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.SET_ONLINE_FRIENDS,
            friends: friends
        });
    },
    /**
     * @param {array} friends
     */
    setOffline:function(friends){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.SET_OFFLINE_FRIENDS,
            friends: friends
        });
    },
    /**
     * @param {string} name
     */
    filter:function(name){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.FIND_A_FRIENDS,
            name: name
        });
    },
    searchFriendCallback:function(data){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.SEARCH_FRIEND_CALLBACK,
            data: data
        });
    },
    addByKeyword:function(data){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.ADD_RAW_FRIENDS_BY_KEYWORD,
            data: data
        });
    },

};
