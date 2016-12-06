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
    openReportModal: function(rId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_OPEN_REPORT_MODAL,
            rId: rId
        });
    },
    closeReportModal: function(rId) {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_CLOSE_REPORT_MODAL
        });
    },
    openAlertModal:function(title,body){
        
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_OPEN_ALERT_MODAL,
            data: {title:title,body:body}
        });
    },
    closeAlertModal: function() {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_CLOSE_ALERT_MODAL
        });
    },
    openAlertYesNoModal:function(title,body,noButton,yesButton,callback){
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_OPEN_ALERT_YES_NO_MODAL,
            data: {title:title,body:body,noButton:noButton,yesButton:yesButton,callback:callback}
        });
    },
    closeAlertYesNoModal: function() {
        ChatAppDispatcher.dispatch({
            type: ActionTypes.POUP_CLOSE_ALERT_YES_NO_MODAL
        });
    },
};
