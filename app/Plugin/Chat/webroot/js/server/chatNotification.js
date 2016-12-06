var chatSocket = require('./chatSocket.js');
var chatDB = require("./chatDB.js");
var _io;
var chatNotification = (function () {
    var setIO = function (io) {
        _io = io;
    };
    var imOnline = function (userId) {
        _io.emit("serverInfoRefeshCallback");
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriends, [userId]), function (err, rows) {
            if (err) {
                _io.emit('error',err);
            } else {

                if (rows.length != 0) {
                    for (var i = 0; i < rows.length; i++) {

                        if (chatSocket.isUserOnline(rows[i].id)) {
                            //socket.broadcast.to('mooUser.'+rows[i].id).emit('friendIsLogged', socket.userId);
                            _io.to('mooUser.' + rows[i].id).emit('friendIsLogged', userId);
                        }

                    }
                }
            }
        });
    };
    var imOffline = function (userId) {
        _io.emit("serverInfoRefeshCallback");
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriends, [userId]), function (err, rows) {
            if (err) {
                _io.emit('error',err);
            } else {

                if (rows.length != 0) {
                    for (var i = 0; i < rows.length; i++) {
                        if (chatSocket.isUserOnline(rows[i].id)) {
                            //console.log("friendIsLogout ",new Date().toISOString().slice(0, 19).replace('T', ' '));
                            _io.to('mooUser.' + rows[i].id).emit('friendIsLogout', userId);
                        }

                    }
                }
            }
        });
    };
    var imLogged = function (socket) {
        socket.emit("userIsLogged", socket.userId);  
    };
    
    return {
        setIO: setIO,
        imOnline: imOnline,
        imOffline: imOffline,
        imLogged: imLogged
    };
}());
module.exports = chatNotification;