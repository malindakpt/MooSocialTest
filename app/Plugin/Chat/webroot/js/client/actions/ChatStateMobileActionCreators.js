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
    showIconStatus: function() {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MOBILE_SHOW_ICON_STATUS
        });
    },
    showFriendsWindow: function() {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MOBILE_SHOW_FRIENDS_WINDOW
        });
    },
    showChatWindow:function(){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.MOBILE_SHOW_CHAT_WINDOW
        });
    }
    

};
