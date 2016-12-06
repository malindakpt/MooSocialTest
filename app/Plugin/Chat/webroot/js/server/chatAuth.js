//var chatDB;
var chatDB = require("./chatDB.js");
var chatSocket = require('./chatSocket.js');
var chatNotification = require('./chatNotification.js');
var chatUser = require('./chatUser.js');
var cache = require("./chatCache");

var chatAuth = (function () {


    var initDataSource = function (chatdb) {
        chatDB = chatdb;

    };
    var isLogged = function (socket, callback) {
        if (socket.isLogged) {
            callback(socket);
        }
        return socket.isLogged;
    };
    var id = function (socket) {
        return socket.userId;
    };
    var execute = function (socket) {

        socket.isLogged = false;
        socket.userId = 0;
        // Check token is valided

        if ((socket.handshake.query.chat_token)) {
            chatDB.query(chatDB.mysql.format(chatDB.sqlString.checkTokenIsExists, [socket.handshake.query.chat_token]), function (err, rows) {
                if (err) {
                    //io.emit('error');
                } else {
                   
                    if (rows.length == 0) {
                        socket.emit("userIsLogged", 0);
                    } else {
                        socket.isLogged = true;
                        socket.userId = rows[0].user_id;
                        socket.join('mooUser.' + socket.userId);
                        chatUser.setStatus(socket.userId, socket.handshake.query.chat_status);

                        if (chatSocket.isUserFirstTimeConnecting(socket.userId) && !chatUser.isOffline(socket.userId)) {
                            chatNotification.imOnline(socket.userId);

                        }
                        chatNotification.imLogged(socket);
                        chatSocket.add1ToNumberUsersSocket(socket.userId);
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyStatCached, [socket.userId]), function (err, rows) {
                            if (err) {
                                log.error("chatDB.sqlString.getMyStatCached", err);
                            } else {
                                if (rows.length == 0) {

                                }else{

                                    if(rows[0].new_friend == 1 || rows[0].new_block == 1){
                                        cache.emptyQuery(socket.userId);
                                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.setMyStatCached, [socket.userId]), function (err, rows) {
                                            if (err) {
                                                log.error("chatDB.sqlString.setMyStatCached", err);
                                            } else {

                                            }}
                                        );
                                    }
                                }
                            }
                            chatSocket.initFriendsAndBlocker(socket);
                        });

                    }
                }
            });
        } else {

        }


    };
    return {
        initDataSource: initDataSource,
        isLogged: isLogged,
        id: id,
        execute: execute,
        chatDB: chatDB
    };
}());


module.exports = chatAuth;