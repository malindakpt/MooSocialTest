var log = require("./chatLog");
var chatConfig = (function () {
    var fs = require("fs");
   
    var getMooConfig = function(callback){
        fs.readFile(require('path').join(__dirname, '../../../../../Config/config.php'), "utf8", function(error, data) {
            var re = /"(.*?)".*?=>.*?'(.*?)'/g;
            var configString = data.toString();
            var m;
            var config = {};
            while ((m = re.exec(configString)) !== null) {
                if (m.index === re.lastIndex) {
                    re.lastIndex++;
                }
                config[m[1]] = m[2];
            }
            if (config.hasOwnProperty('host') &&
                config.hasOwnProperty('login') &&
                config.hasOwnProperty('password') &&
                config.hasOwnProperty('database') &&
                config.hasOwnProperty('prefix') &&
                config.hasOwnProperty('port')
            ){
                callback(config.host,config.login,config.password,config.database,config.prefix,"mooSocial",config.port);
            }else{
                log.error("The config is not correct . For more infomation , see :",configString);
            }

        });
    };
   
  
    return {
        getMooConfig:getMooConfig
    };
}());
module.exports =  chatConfig ;