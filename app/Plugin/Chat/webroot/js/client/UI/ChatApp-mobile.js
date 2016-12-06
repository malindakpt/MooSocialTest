var React = require('react');
var FriendStatusWindow = require('../UI/FriendStatusWindow-mobile');
var ChatWindows = require('../UI/ChatWindows-mobile');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var ViewerStore = require('../stores/ViewerStore');
var PoupWindow = require('../UI/PoupWindow');
var CHAT_CONSTANTS = require('../constants/ChatConstants');
//var Perf = require('react-addons-perf');
//window.Perf = Perf;
//window.Perf.start();
var ChatApp = React.createClass({
    getInitialState: function () {
        return {
            owner: ViewerStore.getAll(),
            isGuest: ViewerStore.isGuest(),
            modalReportData: {isOpen: false, rId: 0},
            openChatWithOneUser: {hash:Math.random(),id:0},
            openChatRoom: {hash:Math.random(),id:0},
        };
    },
    componentDidMount: function () {

        if (ChatWebAPIUtils.boot(true)) {
            window.addEventListener('resize', this.handleResize);
        }
        ViewerStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        window.removeEventListener('resize', this.handleResize);
        ViewerStore.removeChangeListener(this._onChange);
    },
    _onChange: function () {

        this.setState({owner: ViewerStore.getAll(), isGuest: ViewerStore.isGuest()});

    },
    componentDidUpdate: function () {
        if (!this.state.isGuest) {
            ChatWebAPIUtils.sendRequestGetMyFriends();
            ChatWebAPIUtils.sendRequestGetMyGroups();
        }

    },
    handleResize: function (e) {
        ChatWebAPIUtils.reRenderAllRooms();
    },
    render: function () {

        if (this.state.isGuest) {
            return (<div id="moochat"></div>);
        } else {
            return (
                <div id="moochat">
                    <FriendStatusWindow key={1} openChatWithOneUser={this.state.openChatWithOneUser}
                                        openChatRoom={this.state.openChatRoom}/>
                    <PoupWindow  />
                </div>
            );
        }
    },
    // Public API

    openChatWithOneUser: function (uId) {
        this.setState({openChatWithOneUser:{hash:Math.random(),id:uId}});
        ChatWebAPIUtils.createChatWindowForAUser(uId);
    },
    openChatRoom: function (rId) {
        this.setState({openChatRoom:{hash:Math.random(),id:rId}});
        ChatWebAPIUtils.createChatWindowByRoomId(rId);
    }
});

module.exports = ChatApp;
