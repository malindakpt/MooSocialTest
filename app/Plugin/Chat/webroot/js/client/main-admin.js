
// browserify -t [ babelify --presets [ react ] ]   main-admin.js -s mooChat  >  ../mooChat-admin.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main-admin.js -s mooChat | uglifyjs -c -m >  ../mooChat-admin.js
var React = require('react');
var ReactDOM = require('react-dom');
var ChatGeneral  = require('./UI/admin/ChatGeneral');
var ChatMonitor  = require('./UI/admin/ChatMonitor');

module.exports = function mooChat(){
    return {
        renderGeneral:function(){
            ReactDOM.render(<ChatGeneral />, document.getElementById('chatGeneral'));
        },
        renderMonitor:function(){
            ReactDOM.render(<ChatMonitor />, document.getElementById('chatGeneral'));
        },
        
    };
}