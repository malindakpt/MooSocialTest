// browserify -p browserify-derequire -t [ babelify --presets [ react ] ] ChatApp.js -o ui.js
// java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --js node_modules/mooChat.test.js --js_output_file node_modules/mooChat.js
// java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --compilation_level ADVANCED_OPTIMIZATIONS --js node_modules/mooChat.test.js --js_output_file node_modules/mooChat.min.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat | uglifyjs -c -m >  node_modules/mooChat.js && mv node_modules/mooChat.js node_modules/mooChat.test.js && java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar  --compilation_level ADVANCED_OPTIMIZATIONS --js node_modules/mooChat.test.js --js_output_file node_modules/mooChat.js
// uglifyjs --compress --mangle -- /Library/WebServer/Documents/moolab/2.3.2/app/Plugin/Chat/webroot/js/client/ui.js
// browserify -p browserify-derequire -t [ babelify --presets [ react ] ] main.js -o node_modules/mooChat.js
// browserify -t [ babelify --presets [ react ] ] main.js -o node_modules/mooChat.js
// browserify -p browserify-derequire -t [ babelify --presets [ react ] ]   main.js -s beep >  node_modules/mooChat.js
// browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat >  node_modules/mooChat.js
// browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat  >  node_modules/mooChat.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat >  node_modules/mooChat.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat >  node_modules/mooChat.js && mv node_modules/mooChat.js node_modules/mooChat.test.js && java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --js node_modules/mooChat.test.js --js_output_file node_modules/mooChat.js
// uglifyjs node_modules/mooChat.js -c -m -o node_modules/mooChat.min.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat | uglifyjs -c -m >  node_modules/mooChat.js && mv node_modules/mooChat.js node_modules/mooChat.test.js && java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --js node_modules/mooChat.test.js --js_output_file node_modules/mooChat.js
// NODE_ENV=production browserify -t [ babelify --presets [ react ] ]   main.js -s mooChat | uglifyjs -c -m >  mooChat.js && mv mooChat.js mooChat.test.js && java -jar /Users/duy/Documents/tool/moo-release/compiler-latest/compiler.jar --js mooChat.test.js --js_output_file mooChat.js
var React = require('react');
var ReactDOM = require('react-dom');
var ChatApp  = require('./UI/ChatApp.js');

var elemDiv = document.createElement('div');
document.body.appendChild(elemDiv);
var chatComponent = ReactDOM.render(<ChatApp />, elemDiv);

//ReactDOM.render(<ChatApp />, document.getElementById('app'));

//module.exports = function mooChat() {}
module.exports = {
    openChatWithOneUser:function(uId){
        chatComponent.openChatWithOneUser(uId);
    },
    openChatRoom:function(rId){
        chatComponent.openChatRoom(rId);
    }
}