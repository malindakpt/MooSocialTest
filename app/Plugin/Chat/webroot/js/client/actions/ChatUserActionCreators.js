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
    add: function(users) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.USER_ADD_CALLBACK,
            users: users
        });
    },
    stopUpdateMissingUser:function(yes){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.STOP_UPDATE_MISSING_USER,
            yes: yes
        });
    }

};
