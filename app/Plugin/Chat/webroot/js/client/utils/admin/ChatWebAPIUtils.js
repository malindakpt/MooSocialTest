/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

var ChatGeneralActionCreators = require('../../actions/admin/ChatGeneralActionCreators');
var ChatMonitorActionCreators = require('../../actions/admin/ChatMonitorActionCreators');
var ChatUserActionCreators = require('../../actions/admin/ChatUserActionCreators');
var ChatRoomActionCreators = require('../../actions/admin/ChatRoomActionCreators');
var ChatMooUtils = require('../../utils/ChatMooUtils');
var CHAT_CONSTANTS = require('../../constants/admin/ChatConstants');

var _mySocket = null;
function getServerInfoCallback(data){
    ChatGeneralActionCreators.setupServerInfo(data);
}
function serverInfoRefeshCallback(){
    _mySocket.emit("getServerInfo");
}

function getMonitorMessagesCallback(data){
    ChatMonitorActionCreators.getMonitorMessagesCallback(data);
}
function serverInfoRefeshMonitorCallback(data){
    _mySocket.emit("getServerInfo");
    _mySocket.emit("getMonitorMessages",50);
}
function getUsersCallback(data) {
    ChatUserActionCreators.getUsersCallback(data);
}
function getRoomsCallback(data) {
    ChatRoomActionCreators.getRoomsCallback(data);
}
module.exports = {
    getServerUrl: function () {
        return ChatMooUtils.getServerUrl();
    },
    getSiteUrl: function () {
        return ChatMooUtils.getSiteUrl();
    },
    initGeneralSocket: function () {

        _mySocket = require('socket.io-client')(this.getServerUrl(), {
            reconnection: false
        });
        _mySocket.on("getServerInfoCallback", getServerInfoCallback);
        _mySocket.on("serverInfoRefeshCallback", serverInfoRefeshCallback);
        _mySocket.on('connect_error', function() {
            ChatGeneralActionCreators.setupServerStatus(CHAT_CONSTANTS.SERVER_IS_OFFLINE);
        });
        _mySocket.on('connect', function() {
            ChatGeneralActionCreators.setupServerStatus(CHAT_CONSTANTS.SERVER_IS_ONLINE);
            _mySocket.emit("getServerInfo");
        });
    },
    initMonitorSocket:function(){
        _mySocket = require('socket.io-client')(this.getServerUrl(), {
            reconnection: false
        });
        _mySocket.on("getServerInfoCallback", getServerInfoCallback);
        _mySocket.on("serverInfoRefeshCallback", serverInfoRefeshMonitorCallback);
        _mySocket.on("getMonitorMessagesCallback", getMonitorMessagesCallback);
        _mySocket.on("getUsersCallback", getUsersCallback);
        _mySocket.on("getRoomsCallback", getRoomsCallback);

        _mySocket.on('connect_error', function() {
            ChatGeneralActionCreators.setupServerStatus(CHAT_CONSTANTS.SERVER_IS_OFFLINE);
        });
        _mySocket.on('connect', function() {
            ChatGeneralActionCreators.setupServerStatus(CHAT_CONSTANTS.SERVER_IS_ONLINE);
            _mySocket.emit("getServerInfo");
            _mySocket.emit("getMonitorMessages",50);
        });
    },
    sendRequestForUpdatingUsers: function(users){
        _mySocket.emit("getUsers", users);
    },
    sendRequestForUpdatingRooms: function(rIds){
        _mySocket.emit("getRooms", rIds);
    },
};
