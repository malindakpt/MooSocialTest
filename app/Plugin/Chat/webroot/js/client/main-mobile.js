// browserify -t [ babelify --presets [ react ] ]   main-mobile.js -s mooChat >  mooChat-mobile.js
//  webpack main-mobile.js mooChat-mobile.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main-mobile.js -s mooChat | uglifyjs -c -m >  mooChat-mobile.js && mv mooChat-mobile.js mooChat.test.js && java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --js mooChat.test.js --js_output_file mooChat-mobile.js
var React = require('react');
var ReactDOM = require('react-dom');
var ChatApp  = require('./UI/ChatApp-mobile.js');

var elemDiv = document.createElement('div');
elemDiv.id = "appChat";
document.body.appendChild(elemDiv);
var chatComponent = ReactDOM.render(<ChatApp />, elemDiv);
//ReactDOM.render(<ChatApp />, document.getElementById('app'));
module.exports = {
    openChatWithOneUser:function(uId){
        chatComponent.openChatWithOneUser(uId);
    },
    openChatRoom:function(rId){
        chatComponent.openChatRoom(rId);
    }
}