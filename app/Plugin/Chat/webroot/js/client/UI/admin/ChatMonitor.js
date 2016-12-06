var React = require('react');

var ChatWebAPIUtils = require('../../utils/admin/ChatWebAPIUtils');
var GeneralStore = require('../../stores/admin/GeneralStore');
var MonitorStore = require('../../stores/admin/MonitorStore');
var UserStore = require('../../stores/admin/UserStore');
var RoomStore = require('../../stores/admin/RoomStore');
var CHAT_CONSTANTS = require('../../constants/admin/ChatConstants');

var ChatMonitorMessages = React.createClass({
    render:function(){
            var items = [];
            var uIds  = [];
            var tmp;

            if (this.props.data.length > 0){
                for(var i=0;i<this.props.data.length;i++){
                    uIds = RoomStore.getMembers(this.props.data[i].room_id);
                    tmp = [];
                    if(uIds.length > 0){
                        for(var j=0;j<uIds.length;j++){
                            if(uIds[j] != this.props.data[i].sender_id){
                                tmp.push(uIds[j]);
                            }
                        }
                    }
                    var content = this.props.data[i].content;
                    var type = this.props.data[i].type;
                    switch (type) {
                        case "image":
                        case "file":
                            var file = ChatWebAPIUtils.getSiteUrl() + '/uploads/chat/room-' + this.props.data[i].room_id + '/' + content;
                            content = <div>{UserStore.getName(this.props.data[i].sender_id)} send a <a target="_blank" href={file}>file</a></div>;
                            break;
                        case "system":
                            var system = JSON.parse(content);
                            switch (system.action) {
                                case "left_the_conversation":
                                    content = <div>{UserStore.getName(this.props.data[i].sender_id)} left the conversation. </div>;
                                    break;
                                case "added":
                                    userB = "";
                                    for (var jj = 0; jj < system.usersId.length; jj++) {

                                        userB += ((jj == 0) ? " " : ", ") + UserStore.getName(system.usersId[jj]);


                                    }
                                    content = <div>{UserStore.getName(this.props.data[i].sender_id)} added {userB} </div>;
                                    break;
                            }
                            break;
                        default:
                    }

                    items.push(<li className="list-group-item" key={i} style={{borderBottom:"1px solid #ccc",paddingTop:"7px"}}>
                        <div className="col-md-2" style={{borderRight:"1px dotted #333"}}><span>{UserStore.getName(this.props.data[i].sender_id)}</span> wrote to <span>{UserStore.getNames(tmp)}</span></div>
                        <div className="col-md-8">{content}</div>
                        <div className="col-md-2">{this.props.data[i].created}</div>
                    </li>);
                }
            }
            return <ul className="list-group monitorChat">{items}</ul>;
        
    }
});
var ChatMonitor = React.createClass({
    getInitialState:function(){
        return {general: GeneralStore.getAll(),monitor:MonitorStore.getAll(),users:UserStore.getAll(),rooms:RoomStore.getAll()};
    },
    componentDidMount:function(){
        ChatWebAPIUtils.initMonitorSocket();
        GeneralStore.addChangeListener(this._onChange);
        MonitorStore.addChangeListener(this._onChange);
        UserStore.addChangeListener(this._onChange);
        RoomStore.addChangeListener(this._onChange);
    },
    componentWillUnmount:function(){
        GeneralStore.removeChangeListener(this._onChange);
        MonitorStore.removeChangeListener(this._onChange);
        UserStore.removeChangeListener(this._onChange);
        RoomStore.removeChangeListener(this._onChange);
    },
    _onChange:function(){
        this.setState({general: GeneralStore.getAll(),monitor:MonitorStore.getAll(),users:UserStore.getAll(),rooms:RoomStore.getAll()});
    },
    componentDidUpdate:function(){
        UserStore.updateMissingUser();
        RoomStore.updateMissingEntries();
    },
    render:function(){
        if(GeneralStore.isServerBeingChecked()){
            return <div className="note note-info">Checking</div>
        }

        if(GeneralStore.isServerOffline()){
            return <div className="note note-info">
               <p>MooChat is not working on your site, your chat server URL might be incorrect or your chat server is down</p>
                <p>You can go to <a href="./chat_settings">Settings</a>  to make sure that your chat server URL is correct or <a href="./chat_error">Error</a> to see the cause of problem which makes your server down.</p>
            </div>
        }

        if(GeneralStore.isServerOnline()){
            return <div className="note note-info" >
                <p>See what users are typing in real-time on your site</p>
                <p>USERS ONLINE : {this.state.general.info.moo_users_chatting}</p>
                <ChatMonitorMessages data={this.state.monitor} />
            </div>
        }

        return <div>Empty</div>;

    }
});

module.exports = ChatMonitor;