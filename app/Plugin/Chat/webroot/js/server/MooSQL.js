var MooSQL = (function () {
    /*  Query define

        End query define*/
    var mysql = require("mysql");
    var connection ;
    var host,user , pass , database , prefix ;
    var config = function (host,user,pass,database,prefix) {
        this.host = host;
        this.user = user;
        this.pass = pass;
        this.database = database;
        this.prefix = prefix ;
        var tmpQuery = "";
        /*
        for(var key in this) {
           
            if (key.indexOf("_query") == 0){
                tmpQuery = this[key].toString();
                //this[key] = (this[key]).replace("prefix_",prefix);
                this[key] =  tmpQuery.replace(/prefix_/g,prefix);
            }
        }
        */
    };
    var debug = function(){
        
    };
    var query = function(sql,callback){
        var result = false;
        connection = mysql.createConnection({
            host     : this.host,
            user     : this.user,
            password : this.pass,
            database : this.database
        });
        connection.connect();

        connection.query(sql, function(err, rows, fields) {
            if (err) throw callback(err);
          
            callback(err,rows);
        });

        connection.end();
        return result;
    };
    return {
        config : config,
        query  : query,
        debug  : debug,
        prefix : prefix,
        mysql:mysql,
        connection:connection
    };
}());


module.exports =  MooSQL ;

