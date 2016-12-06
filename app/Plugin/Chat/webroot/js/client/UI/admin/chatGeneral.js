var React = require('react');

var ChatWebAPIUtils = require('../../utils/admin/ChatWebAPIUtils');
var GeneralStore = require('../../stores/admin/GeneralStore');
var CHAT_CONSTANTS = require('../../constants/admin/ChatConstants');

var ChatServerInfo = React.createClass({
    render:function(){
        if(this.props.info.hasOwnProperty('os_type')){

            var cpuInfo = [];
            if(this.props.info.hasOwnProperty('os_cpu')){
                for(var i=0;i<this.props.info.os_cpu.length;i++){
                    cpuInfo.push( <div key={i} >CPU {i} : {this.props.info.os_cpu[i].model}</div>);
                }
            }
            var loadavg = [];
            if(this.props.info.hasOwnProperty('os_loadavg')){
                for(var i=0;i<this.props.info.os_loadavg.length;i++){
                    var min = "";
                    if(i==0){min = "1"}
                    if(i==1){min = "5"}
                    if(i==2){min = "15"}
                    loadavg.push( <div key={i} >{min} : {this.props.info.os_loadavg[i]}</div>);
                }
            }
            return <ul className="list-group ChatGeneral_setting">
                <li className="list-group-item">
                    <div className="col-md-4">USERS CHATTING (in real-time on your site): </div>
                    <div className="col-md-7">{this.props.info.moo_users_chatting}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">TOTAL MESSAGES (in real-time on your site): </div>
                    <div className="col-md-7">{this.props.info.moo_total_messages}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The operating system name : </div>
                    <div className="col-md-7">{this.props.info.os_type}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The operating system platform : </div>
                    <div className="col-md-7">{this.props.info.os_platform}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The operating system release : </div>
                    <div className="col-md-7">{this.props.info.os_release}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The total amount of system memory : </div>
                    <div className="col-md-7">{this.props.info.os_total_memory} MB</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The amount of free system memory : </div>
                    <div className="col-md-7">{this.props.info.os_free_memory} MB</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The operating system CPU architecture : </div>
                    <div className="col-md-7">{this.props.info.os_arch}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">Information about each CPU/core installed : </div>
                    <div className="col-md-7">{cpuInfo}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The operating system's default directory for temporary files : </div>
                    <div className="col-md-7">{this.props.info.os_tmpdir}</div>
                </li>
                <li className="list-group-item">
                    <div className="col-md-4">The 1, 5, and 15 minute load averages : </div>
                    <div className="col-md-7">{loadavg}</div>
                </li>
            </ul>
        }else{
            return <div></div>
        }
    }
});
var ChatGeneral = React.createClass({
    getInitialState:function(){
        return {general: GeneralStore.getAll()};
    },
    componentDidMount:function(){
        ChatWebAPIUtils.initGeneralSocket();
        GeneralStore.addChangeListener(this._onChange);
    },
    componentWillUnmount:function(){
        GeneralStore.removeChangeListener(this._onChange);
    },
    _onChange:function(){
        this.setState({general: GeneralStore.getAll()});
    },
    componentDidUpdate:function(){
        
    },
    render:function(){
        if(GeneralStore.isServerBeingChecked()){
            return <div className="note note-info">Checking</div>
        }

        if(GeneralStore.isServerOffline()){
            return <div className="note note-info">
               <p>MooChat is not working on your site, your chat server URL might be incorrect or your chat server is down</p>
                <p>You can go to <a href="./chat_settings">Settings</a>  to make sure that your chat server URL is correct or <a href="./chat_errors">Error</a> to see the cause of problem which makes your server down.</p>
            </div>
        }

        if(GeneralStore.isServerOnline()){
            return <div className="note note-info" >
                <p>MooChat works. Below are quick statistics  of your site : </p>
                <ChatServerInfo info={this.state.general.info}/>
            </div>
        }

        return <div>Empty</div>;

    }
});

module.exports = ChatGeneral;