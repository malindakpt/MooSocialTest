/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

var ChatAppDispatcher = require('../../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../../constants/admin/ChatConstants');

var ActionTypes = ChatConstants.ActionTypes;

module.exports = {
    /**
     * @param {array} data
     */
    getMonitorMessagesCallback: function(data) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.RECEIVE_RAW_MONITOR_MESSAGES,
            data: data
        });
    }
};
