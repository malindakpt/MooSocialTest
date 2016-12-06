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
     * @param {array} rawGroups
     */
    receiveAll: function(rawGroups) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.RECEIVE_RAW_GROUPS,
            rawGroups: rawGroups
        });
    }

};
