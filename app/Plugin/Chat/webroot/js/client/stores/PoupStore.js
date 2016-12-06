/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var ChatAppDispatcher = require('../dispatcher/ChatAppDispatcher');
var ChatConstants = require('../constants/ChatConstants');
var EventEmitter = require('events').EventEmitter;
var assign = require('object-assign');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');


var ActionTypes = ChatConstants.ActionTypes;
var CHANGE_EVENT = 'change';
var _poup = {
    report:{isOpen: false,rId:0},
    alert:{isOpen: false,title:"",body:""},
    alertYN:{isOpen: false,title:"",body:"",noButton:"",yesButton:""}
};
var PoupStore = assign({}, EventEmitter.prototype, {

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
        return _poup[key];
    },
    getAll:function(){
        return _poup;
    }
});
function _openReportModal(rId){
    _poup.report = {isOpen: true,rId:rId};
}
function _closeReportModal(){
    _poup.report = {isOpen: false,rId:0};
}
function _openAlertModal(data){
    _poup.alert = {isOpen: true,title:data.title,body:data.body};
}
function _closeAlertModal(){
    _poup.alert = {isOpen: false,title:"",body:""};
}
function _openAlertYesNoModal(data){
    _poup.alertYN = {isOpen: true,title:data.title,body:data.body,noButton:data.noButton,yesButton:data.yesButton,callback:data.callback};
}
function _closeAlertYesNoModal(){
    _poup.alertYN = {isOpen: false,title:"",body:"",noButton:"",yesButton:""};
}
PoupStore.dispatchToken = ChatAppDispatcher.register(function(action) {

    switch(action.type) {
        case ActionTypes.POUP_OPEN_REPORT_MODAL:
            _openReportModal(action.rId);
            PoupStore.emitChange();
            break;
        case ActionTypes.POUP_OPEN_ALERT_MODAL:
            _openAlertModal(action.data);
            PoupStore.emitChange();
            break;
        case ActionTypes.POUP_OPEN_ALERT_YES_NO_MODAL:
            _openAlertYesNoModal(action.data);
            PoupStore.emitChange();
            break;
        case ActionTypes.POUP_CLOSE_REPORT_MODAL:
            _closeReportModal(); 
            PoupStore.emitChange();
            break;
        case ActionTypes.POUP_CLOSE_ALERT_MODAL:
            _closeAlertModal();
            PoupStore.emitChange();
            break;
        case ActionTypes.POUP_CLOSE_ALERT_YES_NO_MODAL:
            _closeAlertYesNoModal();
            PoupStore.emitChange();
            break;
        default:
        // do nothing
    }

});

module.exports = PoupStore;