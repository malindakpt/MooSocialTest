var os = require("os");
var user = require('./chatUser.js');
var message  = require("./chatMessage");
var chatDB = require("./chatDB.js");
var log = require("./chatLog");
module.exports =(function () {
    var getServerInfo = function(socket){
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.countTotalMessage), function (err, result) {
            if (err) {
                log.error("getServerInfo err", err);
            } else {
                var moo_total_messages = result[0].count;
                socket.emit('getServerInfoCallback',{
                    os_type:  os.type(),
                    os_platform:os.platform(),
                    os_arch:os.arch(),
                    os_cpu :os.cpus(),
                    //os_homedir:os.homedir(),
                    os_loadavg:os.loadavg(),
                    os_release:os.release(),
                    os_tmpdir:os.tmpdir(),
                    os_hostname:os.hostname(),
                    os_total_memory:os.totalmem()/1000000,
                    os_free_memory : os.freemem()/1000000,
                    moo_users_chatting:user.countUsersOnline(),
                    moo_total_messages:moo_total_messages,
                });
            }
        });
        
    };
    var getMonitorMessages = function (socket,limit) {
        chatDB.query(chatDB.mysql.format(chatDB.sqlString.getMessage,[limit]), function (err, results) {
            if (err) {
                log.error("getMonitorMessages err", err);
            } else {
                socket.emit('getMonitorMessagesCallback',results);
            }
        });
    };
    var getRooms = function (socket,ids) {

        var query = chatDB.sqlString.rooms.replace("%IN%", "'" + ids.join("','") + "'");
        chatDB.query(chatDB.mysql.format(query), function (err, rows) {
            if (err) {
                log.error("getRooms error", err);
            } else {
                socket.emit("getRoomsCallback", rows);

            }
        });
    };
    return {
        getServerInfo:getServerInfo,
        getMonitorMessages:getMonitorMessages,
        getRooms:getRooms
    }
}());

