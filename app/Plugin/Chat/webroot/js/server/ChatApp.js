// Setup basic express server

var express = require('express');
var app = express();
var server = require('http').createServer(app);
// For https
/*
var fs = require('fs');
var privateKey = fs.readFileSync( 'privatekey.pem' );
var certificate = fs.readFileSync( 'certificate.pem' );

var server = require('https').createServer({
    key: privateKey,
    cert: certificate
}, app).listen(3000);
*/
var io = require('socket.io')(server);
var port = process.env.PORT || 3000;

var chatSocket = require('./chatSocket');
var chatNotification = require('./chatNotification');
chatNotification.setIO(io);

var auth = require("./chatAuth");
var user = require('./chatUser');
var message  = require("./chatMessage");
var chatInfo = require('./chatInfo');
var log = require("./chatLog");

require('./chatConfig.js').getMooConfig(function (host, login, password, database, prefix, sourceSql, mysqlPort) {
    // Setup moosocial integration
    var chatDB = require("./chatDB");
    chatDB.config(host, login, password, database, prefix, sourceSql, mysqlPort);

    require("./BootingRules").run();

    server.listen(port, function () {

        log.info('Server listening at port %d', port);

    });

// Routing
    app.use(express.static(__dirname + '/public'));


    io.on('connection', function (socket) {
        // Hacking for all "on" event to make sure the socked is authenticated
        socket.isLogged = false;
        socket.userId = 0;
        socket.myFriendsId = [];
        socket.myBlockersId = [];
        socket.roomsId = {actived: []};
        var onevent = socket.onevent;
        socket.onevent = function (packet) {
            
            if (socket.isLogged) {
                onevent.call(this, packet);
            }else{
                if(packet.hasOwnProperty('data')){
                    if (packet.data instanceof Array) {
                        
                        if(packet.data.length > 0){
                            switch(packet.data[0]) {
                                case 'getServerInfo':
                                case 'getMonitorMessages':
                                case 'getUsers':
                                case 'getRooms':    
                                    onevent.call(this, packet);
                                    break;
                                default:

                            }
                        }
                    }



                }
            }

        };
        // End hacking

        auth.execute(socket);
        user.init(io, socket);
        message.init(io);
        // when the user disconnects.. perform this
        socket.on('disconnect', function () {


            var userId = socket.userId;

            chatSocket.sub1FromNumberUsersSocket(userId);
            setTimeout(function () {

                if (chatSocket.isUserLatestConnecting(socket)) {
                    chatNotification.imOffline(userId);
                }
            }, 1100);
        });
        // Server info
        socket.on('getServerInfo',function(){
            chatInfo.getServerInfo(this);
        });        
        socket.on('getMonitorMessages',function(limit){
            chatInfo.getMonitorMessages(this,limit);
        });
        socket.on('getRooms',function(ids){
            chatInfo.getRooms(this,ids);
        });
        // End server info
        // User init
        socket.on("getMyFriendsOnline", function () {
            user.getMyFriendsOnline(this);
        });
        socket.on("getMyFriends", function (ids) {
            user.getMyFriends(this,ids);
        });
        socket.on("getUsers", function (ids) {
            user.getUsers(this,ids);
        });  
        socket.on("getUsersByRoomIdsAtBooting", function (rIds) {
            user.getUsersInRooms(this,rIds);
        });
        
        socket.on("setOffline", function () {
            user.setOffline(this);
        });
        socket.on("setOnline", function () {
            user.setOnline(this);
        });
        socket.on("getMyGroups", function () {
            user.getMyGroupsConversations(this);
        });
        socket.on("startTyping", function (rId) {
            user.startTyping(this,rId);
        });
        socket.on("stopTyping", function (rId) {
            user.stopTyping(this,rId);
        });
        socket.on("searchFriend", function (name) {
            user.searchFriend(this,name);
        });
        // End user init 
        // Message init
        socket.on("createChatWindowByUser", function (data) {
            message.createRoom(this,data.friendIds,data.isAllowedSendToNonFriend);
        });
        socket.on("createChatWindowByRoomId", function (data) {
            message.getMembersInARoom(this,data);
        });
        socket.on("refeshStatusChatWindowByRoomId", function (rId) {
            message.refeshStatusARoom(this,rId);
        });
        socket.on("sendTextMessage", function (data) {
            message.setTextMessage(this,data);
        });
        socket.on("getRoomMessages", function (data) {
            message.getRoomMessages(this,data.roomId,data.limit,data.firstIdNewMessage);
        });
        socket.on("getRoomMessagesMore", function (data) {
            message.getRoomMessagesMore(this,data.rId,data.mIdStart,data.limit);
        });
        socket.on("markMessagesIsSeenInRooms", function (data) {
            message.markMessagesIsSeenInRooms(this,data.messageIdsUnSeen,data.roomIsSeen);
        });
        socket.on("getRoomHasUnreadMessage", function () {
            message.getRoomHasUnreadMessage(this);
        });
        socket.on("deleteConversation", function (rId) {
            message.deleteConversation(this,rId);
        });
        socket.on("reportMessageSpam", function (data) {
            message.reportMessageSpam(this,data);
        });
        socket.on("leaveConversation", function (rId) {
            message.leaveConversation(this,rId);
        });
        socket.on("addUsersToARoom", function (friendIds, roomId) {
            message.addUsersToARoom(this,friendIds, roomId);
        });
        socket.on("blockMessages", function (rId) {
            message.blockMessages(this, rId);
        });
        socket.on("unblockMessages", function (rId) {
            message.unblockMessages(this,rId);
        });
        // End message init 
    });
});


