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
     * @param {int} code
     */
    setupServerStatus: function(code) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.SETUP_SERVER_STATUS,
            status: code
        });
    },
    setupServerInfo: function(info) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.SETUP_SERVER_INFO,
            info: info
        });
    }
};
