var winston = require('winston');

winston.add(winston.transports.File, 
    { filename: __dirname+'/log/error.log', 
      level: 'error', 
      timestamp: true, 
      json:false,
    });

var logger = new (winston.Logger)({
    transports: [
        new (winston.transports.Console),
        new (winston.transports.File)({
            name: 'info-file',
            filename: __dirname+'/log/info.log',
            level: 'info',
            timestamp: true,
            json:false
        }),
        new (winston.transports.File)({
            name: 'error-file',
            filename: __dirname+'/log/error.log',
            level: 'error',
            timestamp: true,
            json:false
        }),
        new (winston.transports.File)({
            name: 'handleExceptions-file',
            filename: __dirname+'/log/error-all.log',
            handleExceptions: true,
            humanReadableUnhandledException: true,
            timestamp: true,
            json:false
        })
    ]
});

module.exports = logger;