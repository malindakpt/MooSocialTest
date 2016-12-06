var _numberUsersSocket = {};
var _usersTimeRecentOffline = {};
var chatDB = require("./chatDB.js");

var chatSocket = (function () {

    var setNumberUsersSocket = function (numberUsersSocket) {
        _numberUsersSocket = numberUsersSocket;
    };
    var get = function (name) {
        switch (name) {
            case "numberUsersSocket":
                return _numberUsersSocket;
                break;
            case "usersTimeRecentOffline":
                return _usersTimeRecentOffline;
                break;
            default:
                return ''
        }
    };
    var add1ToNumberUsersSocket = function (userId) {
        if (_numberUsersSocket.hasOwnProperty(userId)) {
            _numberUsersSocket[userId] += 1;
        } else {
            _numberUsersSocket[userId] = 1;
        }
    };
    var sub1FromNumberUsersSocket = function(userId){
        _numberUsersSocket[userId] -= 1;
        _usersTimeRecentOffline[userId] = Date.now();
    };
    var isUserFirstTimeConnecting = function (userId) {
        if (_numberUsersSocket[userId] == 1) {
            return false;
        }
        if (!_usersTimeRecentOffline.hasOwnProperty(userId)) {
            return true;
        } else {

            if (Date.now() - _usersTimeRecentOffline[userId] > 1000) {
                return true;
            } else {
                return false;
            }
        }

    };
    var isUserLatestConnecting = function (socket) {
        return (_numberUsersSocket[socket.userId] <= 0);
    };
    var isUserOnline=function(userId){
        if(_numberUsersSocket.hasOwnProperty(userId)){
            if(_numberUsersSocket[userId]>=1){
                return true;        
            }
        }
        return false;
    };
    var initFriendsAndBlocker = function(socket){
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getAllMyFriendId, [socket.userId]), function (err, rows) {
            if (err) {
                log.error("initFriendsAndBlocker", err);
            } else {


                if (rows.length != 0) {
                    for (var i = 0; i < rows.length; i++) {
                        socket.myFriendsId.push(rows[i].id);
                    }
                }

            }
            chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyBlockers, [socket.userId]), function (err, rows) {
                if (err) {
                    log.error("initFriendsAndBlocker getMyBlockers", err);
                } else {


                    if (rows.length != 0) {

                        for (var i = 0; i < rows.length; i++) {
                            socket.myBlockersId.push(rows[i].object_id);
                        }
                    }

                }
            },true,socket.userId);
        },true,socket.userId);


    };

    return {
        get:get,
        setNumberUsersSocket: setNumberUsersSocket,
        isUserFirstTimeConnecting: isUserFirstTimeConnecting,
        isUserLatestConnecting: isUserLatestConnecting,
        isUserOnline:isUserOnline,
        add1ToNumberUsersSocket:add1ToNumberUsersSocket,
        sub1FromNumberUsersSocket:sub1FromNumberUsersSocket,
        initFriendsAndBlocker:initFriendsAndBlocker
    };
}());
module.exports = chatSocket;