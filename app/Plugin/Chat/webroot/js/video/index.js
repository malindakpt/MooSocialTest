/*
var fs = require('fs');
var PeerServer = require('peer').PeerServer;

var server = PeerServer({
    port: 9000,
    ssl: {
        key: fs.readFileSync('/private/etc/apache2/server.key'),
        cert: fs.readFileSync('/private/etc/apache2/server.crt')
    }
});
*/

var fs = require('fs');
var http = require('http');
var https = require('https');
var privateKey  = fs.readFileSync('/private/etc/apache2/server.key', 'utf8');
var certificate = fs.readFileSync('/private/etc/apache2/server.crt', 'utf8');

var credentials = {key: privateKey, cert: certificate};

var express = require('express');
var app = express();
var ExpressPeerServer = require('peer').ExpressPeerServer;

app.get('/', function(req, res, next) { res.send('Hello world!'); });

//var server = app.listen(9000);

var options = {
    debug: true
}

//app.use('/api', ExpressPeerServer(server, options));

// OR

var server = require('https').createServer(credentials,app);

app.use('/peerjs', ExpressPeerServer(server, options));

server.listen(9000);

var connected = [];
server.on('connection', function (id) {
    var idx = connected.indexOf(id); // only add id if it's not in the list yet
    if (idx === -1) {connected.push(id);}
    console.log(connected);
});
server.on('disconnect', function (id) {
    var idx = connected.indexOf(id); // only attempt to remove id if it's in the list
    if (idx !== -1) {connected.splice(idx, 1);}
});