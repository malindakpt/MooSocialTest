var chatDB = require("./chatDB.js");
var log = require("./chatLog");
var BootingRules = (function () {

    var initDataSource = function(chatDB){
        chatDB = chatDB ;
    };
    var clearTableChatUserIsConnecting = function(){
        chatDB.query('TRUNCATE TABLE '+chatDB.prefix+'chat_user_is_connecting ', function(err,rows){
            if(err){
                //io.emit('error');
                log.error('ERROR : TRUNCATE TABLE ',err);
            } else {

            }
        });
    };
    var run = function(){
        //clearTableChatUserIsConnecting();

    };
    return {
        initDataSource : initDataSource,
        run:run
        //clearTableChatUserIsConnecting : clearTableChatUserIsConnecting
    };
}());


module.exports =  BootingRules ;