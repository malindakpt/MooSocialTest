var chatDB = require("./chatDB");
var mooAuth = require("./chatAuth");
var chatSocket = require('./chatSocket');
var ChatMooEmoji = require("./chatMooEmoji");
var chatConstants = require("./chatConstants");
var log = require("./chatLog");
var cache = require("./chatCache");
var _io;
var _ = require('lodash');

// Core conversations integration
var updateNotificationCounter={}
var updateNotificationCounterCallback=function(uId){
    var isCallback = false;
    if(!updateNotificationCounter.hasOwnProperty(uId)){
        updateNotificationCounter[uId] = true;
        isCallback = true;
    }else{
        if(updateNotificationCounter[uId] === false){
            updateNotificationCounter[uId] = true;
            isCallback = true;
        }
    }
    if(isCallback){
        setTimeout(function(){
            _updateNotifcationChatCount(uId);

            updateNotificationCounter[uId] = false;
        }, 2000);
    }
};

var _updateNotifcationChatCount = function(uId){
    /*
    chatDB.query(chatDB.mysql.format(chatDB.sqlString.updateNotificationForMember, [uId,uId]),function(err,result){
        if (err) {
            log.error("_updateNotifcationChatCount error", err);
        } else {

        }
    });
    */
    chatDB.query(chatDB.mysql.format(chatDB.sqlString.countNewChatMessage, [uId]),function(err,result){
        if (err) {
            log.error("_updateNotifcationChatCount countNewChatMessage error", err);
        } else {

            chatDB.query(chatDB.mysql.format(chatDB.sqlString.updateNotificationForMemeber, [ result[0].count,uId]),function(err,result){
                if (err) {
                    log.error("_updateNotifcationChatCount error", err);
                } else {

                }
            });
        }
    });

};
// End core conversations integration
var _isOwnerFriend = function (ids, socket) {

    for (var i = 0; i < ids.length; i++) {
        if (socket.myFriendsId.indexOf(ids[i]) == -1) {
            return false;
        }
    }
    return true;
};
var _parseRoomCode = function (friendIds, socket) {
    if (Array.isArray(friendIds)) {
        var tmp = friendIds.concat([socket.userId]);
        tmp.sort(function (a, b) {
            return a - b
        });
        return tmp.join('.');
    } else {
        var now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        return "roomcode error at " + now;
    }
};

var ChatMessage = (function () {

    var createRoom = function (socket, friendIds,isAllowedSendToNonFriend) {
        if (!Array.isArray(friendIds)) {
            friendIds = [friendIds]
        }

        if (_isOwnerFriend(friendIds, socket) || isAllowedSendToNonFriend) {
            var code = _parseRoomCode(friendIds, socket);

            chatDB.query(chatDB.mysql.format(chatDB.sqlString.checkRomByCode, [code]), function (err, room) {
                if (err) {
                    log.error("createRoom err", err);
                } else {

                    if (room.length != 0) {
                        socket.roomsId.actived.push(room[0].id);
                        socket.roomsId[room[0].id] = friendIds.concat([socket.userId]);//[friendId, socket.userId]


                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getUnreadMesageInARoom, [room[0].id,socket.userId,socket.userId]), function (err, result) {
                            if (err) {
                                log.error("getMembersInARoom getRoomInfo  getUnreadMesageInARoom error", err);
                            } else {
                                if (result.length > 0 ){
                                    var firstIdNewMessage = (result[0].newMessages == 0)?0:result[0].message_id;
                                    
                                    socket.emit("createChatWindowByUserCallback", {
                                        roomId: room[0].id,
                                        members: friendIds,
                                        first_blocked:room[0].first_blocked,
                                        second_blocked:room[0].second_blocked,
                                        minimized:chatConstants.WINDOW_MAXIMIZE,
                                        firstIdNewMessage:firstIdNewMessage,
                                        is_group:room[0].is_group,
                                        has_joined:room[0].has_joined
                                    });
                                }
                            }

                        });
                    } else {
                        var now = new Date().toISOString().slice(0, 19).replace('T', ' ');


                        var rawRoom  = {
                            code: code,
                            created: now,
                            name: code
                        };
                        if(friendIds.length > 1){
                            rawRoom.is_group = 1;
                            rawRoom.has_joined = (friendIds.concat([socket.userId])).join('.');
                        }
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.createRom,rawRoom ), function (err, result) {
                            if (err) {
                            } else {
                                chatDB.query(chatDB.mysql.format(chatDB.sqlString.joinRom, {
                                    room_id: result.insertId,
                                    user_id: socket.userId,
                                    joined: now
                                }), function (err, rows) {
                                    cache.emptyQuery();
                                });

                                for (var i = 0; i < friendIds.length; i++) {
                                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.joinRom, {
                                        room_id: result.insertId,
                                        user_id: friendIds[i],
                                        joined: now
                                    }), function (err, rows) {
                                        cache.emptyQuery();
                                    });
                                }
                                socket.roomsId.actived.push(result.insertId);
                                socket.roomsId[result.insertId] = friendIds.concat([socket.userId]);//[friendId, socket.userId];

                                var isGroup = 0;
                                var hasJoined = "";
                                if(friendIds.length > 1){
                                    isGroup = 1;
                                    hasJoined = (friendIds.concat([socket.userId])).join('.');
                                }

                                socket.emit("createChatWindowByUserCallback", {
                                    roomId: result.insertId,
                                    members: friendIds,
                                    first_blocked:0,
                                    second_blocked:0,
                                    minimized:chatConstants.WINDOW_MAXIMIZE,
                                    firstIdNewMessage:0,
                                    is_group:isGroup,
                                    has_joined:hasJoined
                                });
                            }

                        });

                    }

                }
            });
        } else {
            socket.emit("createChatWindowByUserCallback", {});
        }
    };
    var addUsersToARoom = function (socket, friendIds, roomId) {
        if (!Array.isArray(friendIds)) {
            friendIds = [friendIds]
        }

        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomInfo, [roomId]),function(err,result){
            if (err) {
                log.error("addUsersToARoom getRoomInfo error", err);
            } else {
                if (result.length > 0 ){
                    var users_has_joined =  _.map(result[0].has_joined.split('.'), _.ary(parseInt, 1));
                }

                chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMemberInARoom, [roomId]), function (err, users) {
                    if (err) {
                        log.error("addUsersToARoom error", err);
                    } else {

                        var code = friendIds.slice(0);
                        code.push(socket.userId);
                        for (var i = 0; i < users.length; i++) {
                            if (users[i].user_id != socket.userId) {
                                code.push(users[i].user_id);
                            }
                        }
                        code.sort(function (a, b) {
                            return a - b
                        });
                        var has_joined = _.union(code,users_has_joined).join('.');
                        
                        code = code.join('.');
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.updateCodeRoomAndHasJoined, [code,has_joined, roomId]), function (err, result) {
                            if (err) {
                                log.error("addUsersToARoom at updateCodeRoom err", err);
                            } else {
                                var text = {action: "added",usersId:friendIds};
                                setTextMessage(socket, {
                                    roomId: roomId,
                                    text: JSON.stringify(text),
                                    type: "system"
                                });
                                var now = new Date().toISOString().slice(0, 19).replace('T', ' ');
                                for (var i = 0; i < friendIds.length; i++) {
                                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.joinRom, {
                                        room_id: roomId,
                                        user_id: friendIds[i],
                                        joined: now
                                    }), function (err, rows) {
                                        cache.emptyQuery();
                                        if (i == friendIds.length){
                                            socket.emit("addUsersToARoomCallback", {roomId:roomId,users:friendIds});
                                        }
                                    });
                                }
                            }
                        });


                    }
                },true,socket.userId);
            }
        });



    };
    var getMembersInARoom = function (socket, data) {
        var rId = data.roomId;
        var minimized = data.minimized;
        var isFocused = data.isFocused;
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMemberInARoom, [rId]), function (err, users) {
            if (err) {
                log.error("getMembersInARoom error", err);
            } else {        
                
                if (users.length != 0) {
                    var members = [];
                    var isMemberInThisRoom = false; // For preventing the hacking message of rooms
                    for (var i = 0; i < users.length; i++) {
                        if (users[i].user_id != socket.userId) {
                            members.push(users[i].user_id);
                        } else {
                            isMemberInThisRoom = true;
                        }
                    }
                    if (isMemberInThisRoom) {
                        socket.roomsId.actived.push(rId);
                        socket.roomsId[rId] = members;
                        var virtualItemId = (users.length == 2) ? members[0] : rId;
                 
                        socket.roomsId[rId].push(socket.userId);
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomInfo, [rId]), function (err, result) {
                            if (err) {
                                log.error("getMembersInARoom getRoomInfo error", err);
                            } else {
                                if (result.length > 0 ){
                                    var first_blocked = result[0].first_blocked;
                                    var second_blocked=result[0].second_blocked;
                                    var isGroup = result[0].is_group;
                                    var hasJoined = result[0].has_joined;
                                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.getUnreadMesageInARoom, [rId,socket.userId,socket.userId]), function (err, result) {
                                        if (err) {
                                            log.error("getMembersInARoom getRoomInfo  getUnreadMesageInARoom error", err);
                                        } else {
                                            if (result.length > 0 ){
                                                var firstIdNewMessage = (result[0].newMessages == 0)?0:result[0].message_id;
                                                socket.emit("createChatWindowBySystemCallback", {
                                                    roomId: rId,
                                                    members: members,
                                                    virtualItemId: virtualItemId,
                                                    first_blocked:first_blocked,
                                                    second_blocked:second_blocked,
                                                    minimized:minimized,
                                                    isFocused:isFocused,
                                                    newMessages:result[0].newMessages,
                                                    firstIdNewMessage:firstIdNewMessage,
                                                    is_group:isGroup,
                                                    has_joined:hasJoined
                                                });
                                            }
                                        }

                                    });

                                }
                            }
                        });
                    }
                } else {
                    socket.emit("createChatWindowBySystemCallback", {});
                }
            }
        },true,socket.userId);
    };
    var refeshStatusARoom = function (socket, rId) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomInfo, [rId]), function (err, result) {
            if (err) {
                log.error("blockMessages getRoomInfo error", err);
            } else {
                if (result.length > 0 ){
                    socket.emit("refeshStatusChatWindowByRoomIdCallback", result[0]);
                }
            }
        });
        
    };
    var setTextMessage = function (socket, data) {
        if (data.hasOwnProperty("roomId")) {
            if (socket.roomsId.actived.indexOf(data.roomId) > -1) { // make sure owner in this room for hacking message
                var note_content_html = "";
                switch(data.type) {
                    case "link":
                        if(data.text.hasOwnProperty("message")){
                            note_content_html = ChatMooEmoji.replace(data.text.message);
                        }
                        data.text = JSON.stringify(data.text);
                        break;
                    default:
                        note_content_html = ChatMooEmoji.replace(data.text);
                }
                var isOnlyOneEmoj = ChatMooEmoji.isOneEmojiOnly(data.text) ? 1 : 0;
                //var now = new Date(data.timestamps,data.timestamps).toISOString().slice(0, 19).replace('T', ' ');
                //var now = data.timestamps;
                
                var record = {
                    sender_id: socket.userId,
                    room_id: data.roomId,
                    //created: now,
                    //updated: now,
                    content: data.text,
                    note_content_html: note_content_html,
                    note_one_emoj_only: isOnlyOneEmoj,
                    type: data.type
                };
                chatDB.query(chatDB.mysql.format(chatDB.sqlString.setMyFriendMesasge, record), function (err, result) {
                    if (err) {
                        log.error("_querySetMyFriendMesasge err", err);
                    } else {
                        _io.emit("serverInfoRefeshCallback");
                        record.id = result.insertId;
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.updateLatestMessageIdForARoom, [result.insertId,data.roomId]), function (err, users) {
                            if (err) {
                                log.error("setTextMessage -> updateLatestMessageIdForARoom err", err);
                            }
                        });
                        record.unseen = 1; // Unread
                        //socket.roomsId[data.roomId].forEach(function (userId) {
                        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMemberInARoom, [data.roomId]), function (err, users) {
                            if (err) {
                                log.error("getMembersInARoom error", err);
                            } else {
                               
                                if (users.length != 0) {
                                    for (var i = 0; i < users.length; i++) {
                                            var record2 = {
                                                message_id: record.id,
                                                room_id: data.roomId,
                                                user_id: users[i].user_id
                                            };

                                            chatDB.query(chatDB.mysql.format(chatDB.sqlString.setMesasgeStatus, record2), (function(record2){
                                                return function (err, result) {
                                                    if (err) {
                                                        log.error("_querySetMesasgeStatus err", err);
                                                    } else {

                                                        record.unread_id = result.insertId;
                                                        record.receiver_id = record2.user_id;
                                                        _io.to('mooUser.' + record2.user_id).emit("newMessage", record);
                                                        updateNotificationCounterCallback(record2.user_id);
                                                    }
                                                };})(record2)
                                            );


                                    }
                                }
                            }
                        },true,socket.userId);
                           

                            

                    }
                });
            }
        }

    };
    var getRoomMessages = function (socket, roomId,limit,firstIdNewMessage) {


        if (socket.roomsId.actived.indexOf(roomId) > -1) { // make sure owner in this room for hacking message
            if(firstIdNewMessage == 0 ){

                chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriendMesasge, [socket.userId, roomId,limit]), function (err, rows) {
                    if (err) {
                        log.error("getRoomMessages error", err);
                    } else {

                        socket.emit("getRoomMessagesCallback", {id: roomId, messages: rows});
                    }
                });
            }else{

                chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriendMesasgeToId, [socket.userId, roomId,firstIdNewMessage]), function (err, rows) {
                    if (err) {
                        log.error("getRoomMessages error", err);
                    } else {
                        
                        socket.emit("getRoomMessagesCallback", {id: roomId, messages: rows});
                    }
                });
            }

        }
    };
    var getRoomMessagesMore = function (socket, roomId,mIdStart,limit) {
        if (socket.roomsId.actived.indexOf(roomId) > -1) { // make sure owner in this room for hacking message
            chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMoreMyFriendMesasge, [socket.userId, roomId,mIdStart,limit]), function (err, rows) {
                if (err) {
                    log.error("getRoomMessagesMore error", err);
                } else {
                    
                    socket.emit("getRoomMessagesMoreCallback", {id: roomId, messages: rows,scrollIfNeed:mIdStart});
                }
            });
        }
    };
    var markMessagesIsSeenInRooms = function (socket, ids,roomIsSeen) {
        var query = chatDB.sqlString.setMesasgeStatusIsSeen.replace("%IN%", "'" + ids.join("','") + "'");

        chatDB.query(chatDB.mysql.format(query, [socket.userId]), function (err, rows) {
            if (err) {
                log.error("markMessagesIsSeenInRooms error", err);
            } else {
                socket.emit("markMessagesIsSeenInRoomsCallback", ids);
                // Fix bug when users has many unread messeages 

                if(roomIsSeen.length > 0 ){
                  
                    var query2 = chatDB.sqlString.setMesasgeStatusIsSeen2.replace("%IN%", "'" + roomIsSeen.join("','") + "'");

                    chatDB.query(chatDB.mysql.format(query2, [socket.userId]), function (err, rows) {
                        if (err) {
                            log.error("setMesasgeStatusIsSeen2 error", err);
                        } else {
                            updateNotificationCounterCallback(socket.userId);
                        }
                    });
                }

                // Core conversations integration

            }
        });
    };
    var getRoomHasUnreadMessage = function (socket) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomHasUnReadMessage, [socket.userId]), function (err, rows) {
            if (err) {
                log.error("getRoomMessages error", err);
            } else {
                if (rows.length != 0) {
                    socket.emit("getRoomHasUnreadMessageCallback", rows);
                }

            }
        });
    };
    var deleteConversation = function (socket, rId) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.deleteRoomMesasge, [rId, socket.userId]), function (err, result) {
            if (err) {
                log.error("deleteConversation error", err);
            } else {
                socket.emit("deleteConversationCallback", rId);
            }
        });
    };
    var reportMessageSpam = function (socket, data) {
        var record = {
            room_id: data.rId,
            by_user: socket.userId,
            reason: data.reason
        };
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.checkReportRoomMesasgeIsExists, [data.rId, socket.userId]), function (err, rows) {
            if (err) {
                log.error("reportMessageSpam error", err);
            } else {
                if (rows.length == 0) {
                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.reportRoomMesasge, record), function (err, result) {
                        if (err) {
                            log.error("reportMessageSpam error", err);
                        } else {
                            socket.emit("reportMessageSpamCallback", {error: chatConstants.ERROR.NO_ERROR});
                        }
                    });
                } else {
                    socket.emit("reportMessageSpamCallback", {error: chatConstants.ERROR.REPORT_ROOM_MESSAGE_SPAM_IS_EXIST});
                }

            }
        });

    };
    var leaveConversation = function (socket, roomId) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMemberInARoom, [roomId]), function (err, users) {
            if (err) {
                log.error("leaveConversation error", err);
            } else {
                if (users.length > 2) {
                    var code = [];
                    for (var i = 0; i < users.length; i++) {
                        if (users[i].user_id != socket.userId) {
                            code.push(users[i].user_id);
                        }
                    }
                    code.sort(function (a, b) {
                        return a - b
                    });
                    code = code.join('.');

                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.updateCodeRoom, [code, roomId]), function (err, result) {
                        if (err) {
                            log.error("leaveConversation at updateCodeRoom error", err);
                        } else {
                            chatDB.query(chatDB.mysql.format(chatDB.sqlString.leaveARoom, [roomId, socket.userId]), function (err, result) {
                                if (err) {
                                    log.error("leaveConversation at leaveARoom error", err);
                                } else {
                                    cache.emptyQuery();
                                    var text = {action: "left_the_conversation"};
                                    setTextMessage(socket, {
                                        roomId: roomId,
                                        text: JSON.stringify(text),
                                        type: "system"
                                    });
                                    socket.emit("leaveConversationCallback", roomId);
                                }

                            });
                        }
                    });

                }

            }
        },true,socket.userId);

    };
    var blockMessages = function (socket, rId) {
        // Check user in this room
        // Do block
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.checkUserIsMemberInARoom, [rId,socket.userId]), function (err, rows) {
            if (err) {
                log.error("blockMessages error", err);
            } else {
                if(rows.length > 0 ){
                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomInfo, [rId]), function (err, result) {
                        if (err) {
                            log.error("blockMessages getRoomInfo error", err);
                        } else {

                            if (result.length > 0){
                                var query = "";
                                if (result[0].first_blocked == 0 && result[0].second_blocked != socket.userId){
                                    query = chatDB.sqlString.updateFirstBlockInARoom;
                                }else if (result[0].second_blocked == 0 && result[0].first_blocked != socket.userId) {
                                    query = chatDB.sqlString.secondFirstBlockInARoom;
                                }
                                if (query != ""){
                                    chatDB.query(chatDB.mysql.format(query, [socket.userId,rId]), function (err, result) {
                                        if (err){
                                            log.error("blockMessages update_BlockInARoom error", err);
                                        }else{
                                            chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriends, [socket.userId]), function (err, rows) {
                                                if (err) {
                                                    _io.emit('blockMessages update_BlockInARoom',err);
                                                } else {

                                                    if (rows.length != 0) {
                                                        for (var i = 0; i < rows.length; i++) {
                                                            if (chatSocket.isUserOnline(rows[i].id)) {
                                                                //log.error("friendIsLogout ",new Date().toISOString().slice(0, 19).replace('T', ' '));
                                                                _io.to('mooUser.' + rows[i].id).emit('blockMessagesCallback', rId);
                                                            }

                                                        }
                                                    }
                                                }
                                            });
                                            _io.to('mooUser.' + socket.userId).emit('blockMessagesCallback', rId);
                                        }
                                    });
                                }
                    
                            }
                            
                        }
                    });
                }

            }
        },true,socket.userId);

    };
    var unblockMessages = function (socket, rId) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.checkUserIsMemberInARoom, [rId,socket.userId]), function (err, rows) {
            if (err) {
                log.error("unblockMessages error", err);
            } else {
                if(rows.length > 0 ){
                    chatDB.query(chatDB.mysql.format(chatDB.sqlString.getRoomInfo, [rId]), function (err, result) {
                        if (err) {
                            log.error("unblockMessages getRoomInfo error", err);
                        } else {

                            if (result.length > 0){
                                var query = "";
                                if (result[0].second_blocked != socket.userId){
                                    query = chatDB.sqlString.updateFirstBlockInARoom;
                                }else if ( result[0].first_blocked != socket.userId) {
                                    query = chatDB.sqlString.secondFirstBlockInARoom;
                                }
                                if (query != ""){
                                    chatDB.query(chatDB.mysql.format(query, [0,rId]), function (err, result) {
                                        if (err){
                                            log.error("leaveConversation update_BlockInARoom error", err);
                                        }else{
                                            chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMyFriends, [socket.userId]), function (err, rows) {
                                                if (err) {
                                                    _io.emit('blockMessages update_BlockInARoom',err);
                                                } else {

                                                    if (rows.length != 0) {
                                                        for (var i = 0; i < rows.length; i++) {
                                                            if (chatSocket.isUserOnline(rows[i].id)) {
                                                                //log.error("friendIsLogout ",new Date().toISOString().slice(0, 19).replace('T', ' '));
                                                                _io.to('mooUser.' + rows[i].id).emit('unblockMessagesCallback', rId);
                                                            }

                                                        }
                                                    }

                                                }
                                            });
                                            _io.to('mooUser.' + socket.userId).emit('unblockMessagesCallback', rId);
                                        }
                                    });
                                }

                            }

                        }
                    });
                }

            }
        },true,socket.userId);

    };
    var totalMessages = function(){
        return 0;
    };
    var init = function (io) {
        _io = io;
    };


    return {
        init: init,
        createRoom: createRoom,
        getMembersInARoom: getMembersInARoom,
        setTextMessage: setTextMessage,
        getRoomMessages: getRoomMessages,
        getRoomMessagesMore:getRoomMessagesMore,
        markMessagesIsSeenInRooms: markMessagesIsSeenInRooms,
        getRoomHasUnreadMessage: getRoomHasUnreadMessage,
        deleteConversation: deleteConversation,
        reportMessageSpam: reportMessageSpam,
        leaveConversation: leaveConversation,
        addUsersToARoom:addUsersToARoom,
        blockMessages:blockMessages,
        unblockMessages:unblockMessages,
        refeshStatusARoom:refeshStatusARoom,
        totalMessages:totalMessages
    };
}());


module.exports = ChatMessage;