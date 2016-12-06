var log = require("./chatLog");
var cache = require("./chatCache");
var chatDB = (function () {
    /*  Query define

     End query define*/
    var mysql = require("mysql");
    var connection ;
    var host,user , pass , database ,port, prefix ,sqlString;
    var pool = undefined;
    var config = function (host,user,pass,database,prefix,source,port) {
        this.host = host;
        this.user = user;
        this.pass = pass;
        this.database = database;
        this.prefix = prefix ;
        this.port   = port;
        switch(source) {
            case "mooSocial":
                this.sqlString = require('./query/moo')(prefix);

                break;

            default:
        }

    };
    var debug = function(){
        log.info(this.host,this.user,this.pass)
    };
    var query = function(sql,callback,isCached,userId){
        if( typeof isCached === 'undefined'){
            isCached = false;
        }
        userId = typeof userId !== 'undefined' ? userId:0;
        /*
        var result = false;
        
        var config ={
            host     : this.host,
            user     : this.user,
            password : this.pass,
            database : this.database
        }   
        if (port != '' && typeof port != 'undefined'){
            config.port = port;
        }
        connection = mysql.createConnection(config);
        connection.connect();

        connection.query(sql, function(err, rows, fields) {
            if (err) throw callback(err);

            callback(err,rows);
        });

        connection.end();
        return result;
        */
        // Pooling
        var result = false; 
        if ( typeof  this.pool === 'undefined'){
            var config ={
                //connectionLimit: 100,
                host     : this.host,
                user     : this.user,
                password : this.pass,
                database : this.database
            }
            if (port != '' && typeof port != 'undefined'){
                config.port = port;
            }
            this.pool  = mysql.createPool(config);
        }else{
            if(!isCached){
                this.pool.getConnection(function(err, connection) {
                    // connected! (unless `err` is set)
                    if (err) {
                        connection.release();
                        log.error("pool.getConnection error", err);
                        return;
                    }

                    connection.query(sql, function(err, rows, fields) {
                        if (err) throw callback(err);
                        //if (err){callback(err);return;}
                        callback(err,rows);
                    });
                    connection.release();
                    // Don't use the connection here, it has been returned to the pool.
                });
            }else{
                var noCacheCallback = function(key){
                    this.pool.getConnection(function(err, connection) {
                        // connected! (unless `err` is set)
                       
                        connection.query(sql, function(err, rows, fields) {
                            if (err) throw callback(err);
                            cache.set(key,rows);
                            callback(err,rows);

                            connection.release();
                            // Don't use the connection here, it has been returned to the pool.
                        });
                    });
                }.bind(this);
                cache.query(sql,callback,noCacheCallback,userId);
            }

        }
        return result;
    };
  
    return {
        config : config,
        query  : query,
        debug  : debug,
        prefix : prefix,
        sqlString : sqlString,
        source:sqlString,
        abc:prefix,
        mysql:mysql,
        connection:connection
    };
}());
module.exports =  chatDB ;