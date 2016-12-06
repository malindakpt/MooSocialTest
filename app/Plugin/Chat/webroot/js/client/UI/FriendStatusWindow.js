/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
var React = require('react');
var ChatConstants = require('../constants/ChatConstants');
var FriendStore = require('../stores/FriendStore');
var RoomStore = require('../stores/RoomStore');
var UserStore = require('../stores/UserStore');
var GroupStore = require('../stores/GroupStore');
var CounterUnseenMessageStore = require('../stores/CounterUnseenMessageStore');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var ChatMooUtils = require('../utils/ChatMooUtils');
var __ = require('../utils/ChatMooI18n').i18next;

var handleFriendItemClick = function () {
    ChatWebAPIUtils.createChatWindowForAUser(this.props.friend.id)
};
var handleGroupItemClick = function () {
    ChatWebAPIUtils.createChatWindowByRoomId(this.props.group.id);
};
var ChatSettings = React.createClass({
    handleChatSound: function () {

        if (this.state.isChatSoundEnable) {
            ChatMooUtils.turnOffSound();
        } else {
            ChatMooUtils.turnOnSound();
        }
        this.setState({isChatSoundEnable: !this.state.isChatSoundEnable});
        this.props.handleButtonSettingIsClicked();
    },
    handleTurnOffChat: function () {
        this.props.handleTurnOffChatIsClick();
    },
    handleCloseAllChatsWindow: function () {
        this.props.handleButtonSettingIsClicked();
        ChatWebAPIUtils.destoryAllRoom();
    },
    handleHideGroups: function () {
        if (this.state.isHideGroup == ChatConstants.HIDE_GROUP_ENABLE) {
            this.props.handleHideGroupsIsClicked(false);
            //ChatMooUtils.hideGroup(false);
        } else {
            this.props.handleHideGroupsIsClicked(true);
            //ChatMooUtils.hideGroup(true);
        }
        this.setState({isHideGroup: !this.state.isHideGroup});
        this.props.handleButtonSettingIsClicked();
    },
    handleBlockSetting: function () {
        window.location.href = ChatMooUtils.getChatBlockSettingURL();
    },
    handleChatHistory: function () {
        window.location.href = ChatMooUtils.getChatHistoryURL();
    },
    getInitialState:function() {
        return {
            isChatSoundEnable: ChatMooUtils.getChatSoundState(),
            isHideGroup: ChatMooUtils.getHideGroupState()
        }
    },
    render:function(){
        var displayChatSoundGlboal = (ChatMooUtils.isChatSoundGlobalEnable())?"display":"none";
        var classChatSound = (this.state.isChatSoundEnable) ? "moochat_icon_checked " : "";
        var classHideGroup = (this.state.isHideGroup == ChatConstants.HIDE_GROUP_ENABLE) ? "moochat_icon_checked " : "";
        var display = (this.props.isShowed) ? "block" : "none";


        return (        <div id="moochat_optionsbutton" className="moochat_tab moochat_floatL" unselectable="on">
            <div id="moochat_optionsbutton_icon" className="moochat_optionsimages"
                 onClick={this.props.handleButtonSettingIsClicked}>
                <i className="material-icons">settings</i>
            </div>

            <div className="moochat-option-content" style={{display:display}}>
                <div onClick={this.handleChatSound} style={{display:displayChatSoundGlboal}}><span
                    className={classChatSound + " moochat_menu_user_settings_poup"}><span>{__.t("chat_sounds")}</span></span>
                </div>
                <div><span className="moochat_menu_user_settings_poup"
                           onClick={this.handleBlockSetting}>{__.t("block_settings")}</span>
                </div>
                <div><span className="moochat_menu_user_settings_poup"
                           onClick={this.handleChatHistory}>{__.t("chat_history")}</span>
                </div>
                <div className="seperate"></div>
                <div><span className="moochat_menu_user_settings_poup" onClick={this.handleCloseAllChatsWindow}>{__.t("close_all_chat_tabs")}</span>
                </div>
                <div><span className={classHideGroup +" moochat_menu_user_settings_poup"}
                           onClick={this.handleHideGroups}>{__.t("hide_groups")}</span></div>

                <div><span onClick={this.handleTurnOffChat}
                           className="moochat_menu_user_settings_poup">{__.t("turn_off_chat")}</span></div>
            </div>
        </div>);


    }
});

var FriendStatusItemWindow = React.createClass({

    render:function(){
        var display = "none";
        var user_status = "moochat_offline";

        if (this.props.friend.is_hidden == ChatConstants.ITEM_SHOW) {
            display = "block";
        }
        if (this.props.friend.is_logged == 1) {
            user_status = "moochat_available";
        }
        return (
            <div id={'moochat_userlist_'+this.props.friend.id}
                 style={{display: display}}
                 className="moochat_userlist"
                 onClick={handleFriendItemClick.bind(this)}
            ><span
                className="moochat_userscontentavatar"><img
                className="moochat_userscontentavatarimage"
                src={this.props.friend.avatar}/></span><span
                className="moochat_userscontentname">{this.props.friend.name}</span><span
                id={'moochat_buddylist_typing_'+this.props.friend.id}
                className="moochat_buddylist_typing"></span><span
                className={"moochat_userscontentdot "+ user_status}></span>
                <div className="moochat_deviceType moochat_floatR moochat_mobile_offline" style={{display: 'none'}}>
                    Web
                </div>
            </div>
        );
    }

});
var FriendStatusItemWindows = React.createClass({
    render:function(){
        var FriendStatusItemWindows = [];
        // friendlist data
        if (this.props.friends.key) {
            this.props.friends.key.map(function (key) {
                FriendStatusItemWindows.push(<FriendStatusItemWindow createChatWindow={this.props.createChatWindow}
                                                                     key={key} friend={this.props.friends[key]}/>);
            }.bind(this));
        }
        return (<div>{FriendStatusItemWindows}</div>);
    }
});
// End Item Friends UI
var GroupItemWindow = React.createClass({

    render:function(){
        var img1,img2,img3,img4, showImage;
        showImage="";
        img1=img2=img3=img4="";
        img1 = <img className="moochat_userscontentavatarimage" src={UserStore.getAvatar(this.props.group.members[0])}/>;
        img2 = <img className="moochat_userscontentavatarimage" src={UserStore.getAvatar(this.props.group.members[1])}/>;

        if(this.props.group.members.length > 2){
            img3 = <img className="moochat_userscontentavatarimage" src={UserStore.getAvatar(this.props.group.members[2])}/>;

        }
        if(this.props.group.members.length > 3){
            img4 = <img className="moochat_userscontentavatarimage" src={UserStore.getAvatar(this.props.group.members[3])}/>;
        }
        if(img3 == ''){
            showImage = "two_member";
        }
        else if(img3 != '' && img4 == ''){
            showImage = "three_member";
        }


        return (
            <div id={'moochat_userlist_'+this.props.group.id}
                 style={{display: "block"}}
                 className="moochat_userlist"
                 onClick={handleGroupItemClick.bind(this)}
                 data-tip={this.props.name}
                 data-place="top"
            >
                <span className={"moochat_userscontentavatar " + showImage}>
                    {img1}{img2}{img3}{img4}
                </span>
                <span className="moochat_userscontentname">{this.props.name}</span>
                <span id={'moochat_buddylist_typing_'+this.props.group.id}
                      className="moochat_buddylist_typing"></span>
                <span className={"moochat_userscontentdot "}></span>
            </div>
        );
    }

});
var GroupItemWindows = React.createClass({
    componentDidUpdate:function(){
        UserStore.updateMissingUser();
    },
    render:function(){

        var GroupItemWindows = [];
        if (this.props.groups.length > 0 && !this.props.isHideGroup) {
            for(var i=0;i<this.props.groups.length;i++){
                var name = UserStore.getNames(this.props.groups[i].members);
                GroupItemWindows.push(<GroupItemWindow key={i} group={this.props.groups[i]} name={name}/>);
            }

            return (<div className="mooGroup"><div className="mooGroup_title">{__.t("group_conversations")}</div> {GroupItemWindows}</div>);
        }else{
            return (<div></div>);
        }

    }
});
var RoomUnseenMessages = React.createClass({
    componentDidMount:function(){
        CounterUnseenMessageStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        CounterUnseenMessageStore.removeChangeListener(this._onChange);
    },
    _onChange: function (roomId) {
        this.setState({data:CounterUnseenMessageStore.getAll()});
    },
    getInitialState:function() {
        return {data:CounterUnseenMessageStore.getAll()};
    },
    render:function(){
        var roomsUnseenNewMessages = 0;
        if(this.props.ids.length > 0 ){
            for(var i=0;i<this.props.ids.length;i++){
                roomsUnseenNewMessages += ((CounterUnseenMessageStore.get(this.props.ids[i]) != 0) ? 1 : 0);

            }
        }
        var roomsDisplayUnseenNewMessages = (roomsUnseenNewMessages != 0) ? 'block' : 'none ';
        return <div id="moochat_unseenUserCount"
                    style={{display:roomsDisplayUnseenNewMessages}}>{roomsUnseenNewMessages}</div> ;
    }
});
var RoomUnseenStatusItemWindow = React.createClass({
    componentDidMount:function(){
        CounterUnseenMessageStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        CounterUnseenMessageStore.removeChangeListener(this._onChange);
    },
    handleCloseUnseenWindow:function(){
        ChatWebAPIUtils.destroyARoom(this.props.room.id)
    },
    _onChange: function(roomId) {

        if(roomId == this.props.room.id || roomId == 0){
            this.setState({newMessages:CounterUnseenMessageStore.get(this.props.room.id)});
        }
    },
    getInitialState:function() {
        return {newMessages:CounterUnseenMessageStore.get(this.props.room.id)};
    },
    hancleClickActiveUnseenWindwow:function(){
        ChatWebAPIUtils.activeARoom(this.props.room.id);
    },
    render:function(){
        /*
        if(this.props.room.hasOwnProperty('newMessages')){
            var newMessageStyle = (this.props.room.newMessages == 0 ) ? "hidden" : "visible";
        }else{
            var newMessageStyle = "hidden";
        }
        */
        var newMessageStyle = (this.state.newMessages == 0 ) ? "hidden" : "visible";
        if (ChatMooUtils.getChatSoundState() == ChatConstants.SOUND_ENABLE && ChatMooUtils.isChatSoundGlobalEnable()) {
            RoomStore.playSound(this.props.room.id);
        }

        return (
            <div onClick={this.hancleClickActiveUnseenWindwow} id={"unseenRoom_"+this.props.room.id} className="moochat_unseenUserList ">
                <div className="moochat_unreadCount moochat_floatL"
                     style={{visibility: newMessageStyle}}>{this.state.newMessages}</div>
                <div className="moochat_userName moochat_floatL"
                     >{this.props.roomTitle}</div>
                <div className="moochat_unseenClose moochat_floatR" onClick={this.handleCloseUnseenWindow}>
                    <i className="material-icons">clear</i>
                </div>
            </div>
        );
    }
});

var ChatWindowMinimizedItem = React.createClass({
    componentDidMount:function(){
        CounterUnseenMessageStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        CounterUnseenMessageStore.removeChangeListener(this._onChange);
    },
    handleTabTitleWindowClick:function(){
        ChatWebAPIUtils.maximizeARoom(this.props.room.id);
    },
    handleCloseWindowClick:function(){
        ChatWebAPIUtils.destroyARoom(this.props.room.id);
    },
    _onChange: function(roomId) {

        if(roomId == this.props.room.id || roomId==0){
            this.setState({newMessages:CounterUnseenMessageStore.get(this.props.room.id)});
        }
    },
    getInitialState:function() {
        return {newMessages:CounterUnseenMessageStore.get(this.props.room.id)};
    },
    render:function(){
        /*
        var newMessageClass = "";
        var newMessageDisplay = "none";
        if(this.props.room.hasOwnProperty('newMessages')){
            newMessageClass = (this.props.room.newMessages != 0) ? " moochat_new_message" : "";
            newMessageDisplay = (this.props.room.newMessages != 0) ? "block" : "none";
        }
        */

        var newMessageClass = (this.state.newMessages == 0)?"":" moochat_new_message";
        var newMessageDisplay = (this.state.newMessages == 0)?"none":" block";
        var moochat_tab_class = (this.props.isMinimized == ChatConstants.WINDOW_MINIMIZE) ? ("moochat_tab" + newMessageClass) : "moochat_tab moochat_tabclick moochat_usertabclick";
        var visibility = (this.props.isMinimized == ChatConstants.WINDOW_MINIMIZE) ? "visible" : "hidden";
        if (this.props.isMinimized == ChatConstants.WINDOW_MINIMIZE && ChatMooUtils.isChatSoundGlobalEnable() && ChatMooUtils.getChatSoundState() == ChatConstants.SOUND_ENABLE) {
            RoomStore.playSound(this.props.room.id);
        }
        var moochatStatusStyle = (RoomStore.getStatusRoom(this.props.room.id) == 1) ? "moochat_available" : "moochat_offline";
        return (
            <span className={moochat_tab_class} style={{visibility:visibility}}
                  onClick={this.handleTabTitleWindowClick}>
                <div className="moochat_unreadCount moochat_floatL"
                     style={{display : newMessageDisplay}}>{this.state.newMessages}</div>
                <div className={"moochat_userscontentdot moochat_floatL "+moochatStatusStyle}></div>

                <div className="moochat_user_shortname">{this.props.roomTitle}</div>
                <div className="moochat_closebox_bottom moochat_tooltip" onClick={this.handleCloseWindowClick}>
                    <i className="material-icons">clear</i>
                </div>
            </span>);
    }
});
// FriendStatus UI
var handleUsersTabClick = function () {
    if (!this.state.isOffline) {
        this.setState({mooChatTabIsClicked: !this.state.mooChatTabIsClicked});
    } else {
        ChatMooUtils.turnOnChat();
        this.setState({mooChatTabIsClicked: !this.state.mooChatTabIsClicked, isOffline: false});
    }
};
var handleUnseenRoomsClick = function () {

    this.setState({roomsUnSeenIsClicked: !this.state.roomsUnSeenIsClicked});
};

var FriendStatusWindow = React.createClass({
    getInitialState:function() {
        var isOffline = (ChatMooUtils.getChatStatus() == ChatConstants.USER_OFFLINE) ? true : false;
        var isHideGroup = (ChatMooUtils.getHideGroupState() == ChatConstants.HIDE_GROUP_ENABLE) ? true : false;
        return {
            isOffline: isOffline,
            isHideGroup:isHideGroup,
            mooChatTabIsClicked: false,
            roomsUnSeenIsClicked: false,
            chatSettingsIsShowed: false,
            users: UserStore.getAll(),
            friends: FriendStore.getAll(),
            rooms: RoomStore.getAll(),
            groups: GroupStore.getAll()
        };
    },
    filterTrigger:function() {
        ChatWebAPIUtils.findAFriendByName(this.refs.filterInput.value);
    },
    componentDidMount:function(){
        ChatMooUtils.initSlimScroll('#moochat_userscontent');
        UserStore.addChangeListener(this._onChange);
        FriendStore.addChangeListener(this._onChange);
        RoomStore.addChangeListener(this._onChange);
        GroupStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        UserStore.removeChangeListener(this._onChange);
        FriendStore.removeChangeListener(this._onChange);
        RoomStore.removeChangeListener(this._onChange);
        GroupStore.removeChangeListener(this._onChange);
    },
    _onChange: function () {
        this.setState({
            friends: FriendStore.getAll(),
            rooms: RoomStore.getAll(),
            users: UserStore.getAll(),
            groups: GroupStore.getAll()
        });
    },
    handleChatSubmit:function(e){
        e.preventDefault();
    },
    handleButtonSettingIsClicked:function(){
        this.setState({chatSettingsIsShowed: !this.state.chatSettingsIsShowed});
    },
    handleHideGroupsIsClicked:function(isHide){
        ChatMooUtils.hideGroup(isHide);
        this.setState({chatSettingsIsShowed: !this.state.chatSettingsIsShowed,isHideGroup:isHide});
    },
    handleTurnOffChatIsClick:function(){
        ChatMooUtils.turnOffChat();
        this.setState({chatSettingsIsShowed: false, mooChatTabIsClicked: false, isOffline: true});
    },
    componentDidUpdate:function(){
        if(ChatMooUtils.isTurnOffForFirstTimeUsing()){
            ChatMooUtils.setFirsTimeUsing(false);
            this.setState({chatSettingsIsShowed: false, mooChatTabIsClicked: false, isOffline: true});
        }
    },
    render:function() {

        // control
        // offline status
        var classUserIconStatus = !this.state.isOffline ? 'moochat_user_available2' : 'moochat_user_offline2';
       var classIconStatus = !this.state.isOffline ? 'moochat_icon_available2' : 'moochat_icon_offline2'
        // end offline status
        //    show/hide sidebar friendlist
        var classMooChatTab = this.state.mooChatTabIsClicked ? 'moochat_tab moochat_tabclick moochat_userstabclick' : 'moochat_tab ';
        var classMooChatShowSidebar = this.state.mooChatTabIsClicked ? 'block' : 'none';
        //    end show/hide sidebar friendlist

        // set how many friend online
        var countFriends = 0;
        if (this.state.friends.keyonline) {
            countFriends = this.state.friends.keyonline.length;
        }
        var userstabText = !this.state.isOffline ? __.t("chat_count",{count:countFriends}) : __.t("offline");
        // end set how many friend online

        // set unseen rooms
        var left_base = ChatConstants.WINDOW_CHAT_MAXIMIZE_WIDTH;
        var left_base_minized = ChatConstants.WINDOW_CHAT_MINIMIZE_WIDTH;
        var left_room = ChatMooUtils.windowWidth() - ChatConstants.WINDOW_FIRST_CHAT_LEFT_MAGRIN_POSTION;
        var roomsUnseen = 0;
        var roomsUnseedList = [];
        var roomsUnseenNewMessages = 0;
        var moochat_chatboxes_width = 0;
        var moochat_chatboxes_wide_width = 587;
        var moochat_unseenUsers = [];
        var moochat_window_minimized = [];
        if (this.state.rooms.isCreated) {
            for (var i = 0; i < this.state.rooms.isCreated.length; i++) {

                var roomTitle = RoomStore.getName(this.state.rooms.isCreated[i]);
                // If user is missing - it will be add in userstore .
                if (left_room > ChatConstants.WINDOW_MINIUM_LEFT_POSTION_ALLOWED_MAXIMIZE) {
                    if (this.state.rooms[this.state.rooms.isCreated[i]].minimized == ChatConstants.WINDOW_MAXIMIZE) {
                        left_room = left_room - left_base;
                        moochat_chatboxes_width = moochat_chatboxes_width + left_base;
                    } else {
                        left_room = left_room - left_base_minized;
                        moochat_chatboxes_width = moochat_chatboxes_width + left_base_minized;
                    }


                    moochat_chatboxes_wide_width = moochat_chatboxes_wide_width + 237;
                    moochat_window_minimized.push(<ChatWindowMinimizedItem
                        key={i}
                        isMinimized={this.state.rooms[this.state.rooms.isCreated[i]].minimized}
                        room={this.state.rooms[this.state.rooms.isCreated[i]]}
                        roomTitle={roomTitle}
                    />);
                } else {
                    roomsUnseen++;
                    roomsUnseedList.push(this.state.rooms.isCreated[i]);
                    if(this.state.rooms[this.state.rooms.isCreated[i]].hasOwnProperty('newMessages')){
                        //roomsUnseenNewMessages += ((this.state.rooms[this.state.rooms.isCreated[i]].newMessages != 0) ? 1 : 0);
                        roomsUnseenNewMessages += ((CounterUnseenMessageStore.get(this.state.rooms.isCreated[i]) != 0) ? 1 : 0);
                    }
                    moochat_unseenUsers.push(<RoomUnseenStatusItemWindow
                        key={i}
                        room={this.state.rooms[this.state.rooms.isCreated[i]]}
                        roomTitle={roomTitle}
                    />);

                }
            }
        }

        var roomclassMooChatTab = this.state.roomsUnSeenIsClicked ? 'moochat_tab moochat_unseenList_open' : 'moochat_tab ';
        var roomDisplayChatboxCss = (roomsUnseen != 0) ? 'block' : 'none ';
        var roomDisplayUnseenusers = this.state.roomsUnSeenIsClicked ? 'block' : 'none ';
        var roomsDisplayUnseenNewMessages = (roomsUnseenNewMessages != 0) ? 'block' : 'none ';
        // end set  unseen rooms
        return (

            <div id="moochat_base" style={{left: 'auto', right: '235px'}}>
                <div id="loggedout" className="moochat_tab moochat_tooltip" title="Please login to use chat"
                     unselectable="on"
                     style={{display: 'none'}}></div>
                <div id="moochat_sidebar" style={{display: classMooChatShowSidebar}}>
                    <div id="moochat_userstab_popup" className="moochat_tabpopup">
                        <div onClick={handleUsersTabClick.bind(this)} className="moochat_userstabtitle">
                            <div className="moochat_userstabtitletext">{__.t("who_s_online")}</div>
                            <div className="moochat_minimizebox moochat_tooltip"
                                 id="moochat_minimize_userstab_popup"
                                 title="Minimize User Tab"><i className="material-icons">vertical_align_bottom</i></div>
                            <br /></div>

                        <div className="moochat_tabcontent moochat_tabstyle">

                            <div id="moochat_userscontent" unselectable="on" style={{overflow: 'hidden'}}>
                                <div id="moochat_userslist" style={{display: 'block'}}>
                                    <FriendStatusItemWindows friends={this.state.friends}
                                                             createChatWindow={this.props.createChatWindow}/>
                                    <GroupItemWindows groups={this.state.groups} isHideGroup={this.state.isHideGroup} />
                                </div>
                                <div id="moochat_userslist_jabber"></div>
                            </div>

                        </div>

                    </div>
                    <div id="moochat_bottomBar">
                        <div id="moochat_searchbar" className="moochat_floatL" style={{visibility: 'visible'}}>
                            <div id="moochat_searchbar_icon">
                                <div className="after"></div>
                            </div>

                            <form onSubmit={this.handleChatSubmit}>


                                <input id="moochat_search" type="text" name="moochat_search" ref="filterInput"
                                       className="moochat_search moochat_search_light textInput"
                                       placeholder={__.t("type_to_find_a_user")} onChange={this.filterTrigger}/>
                            </form>
                        </div>
                        <ChatSettings isShowed={this.state.chatSettingsIsShowed}
                                      handleButtonSettingIsClicked={this.handleButtonSettingIsClicked}
                                      handleTurnOffChatIsClick={this.handleTurnOffChatIsClick}
                                      handleHideGroupsIsClicked={this.handleHideGroupsIsClicked}
                        />
                    </div>
                </div>
                <div id="moochat_optionsbutton_popup" className="moochat_tabpopup" style={{display:'none'}}>
                    <div className="moochat_userstabtitle">
                        <div className="moochat_userstabtitletext">Chat Options</div>
                        <div className="moochat_minimizebox moochat_tooltip"
                             id="moochat_minimize_optionsbutton_popup"
                             title="Minimize Setting Tab"></div>
                        <br /></div>
                    <div className="moochat_tabsubtitle">Type your Name/Status and hit the enter key!</div>
                    <div className="moochat_tabcontent moochat_optionstyle">
                        <div id="guestsname" style={{display: 'block'}}><strong>My Name</strong><br/><input
                            type="text"
                            className="moochat_guestnametextbox"/>
                            <div className="moochat_guestnamebutton">Set my name</div>
                        </div>
                        <strong>My Status</strong><br/><textarea className="moochat_statustextarea"
                                                                 maxLength="140"></textarea>
                        <div style={{overflow:'hidden'}}>
                            <div className="moochat_statusbutton">Set my status</div>
                            <div className="moochat_statusmessagecount">127</div>
                        </div>
                        <div className="moochat_statusinputs"><strong>I am...</strong><br/><span
                            className="moochat_user_available"></span><span
                            className="moochat_optionsstatus available"
                            style={{'textDecoration': 'underline'}}>Available</span><span
                            className="moochat_optionsstatus2 moochat_user_in'visible'"></span><span
                            className="moochat_optionsstatus in'visible'"
                            style={{'textDecoration': 'none'}}>In'visible'</span>
                            <div style={{clear:'both'}}></div>
                            <span className="moochat_optionsstatus2 moochat_user_busy"></span><span
                                className="moochat_optionsstatus busy"
                                style={{'textDecoration': 'none'}}>Busy</span><span
                                className="moochat_optionsstatus2 moochat_user_in'visible'"></span><span
                                className="moochat_optionsstatus moochat_gooffline offline"
                                style={{'textDecoration': 'none'}}>Offline</span>
                            <br /></div>
                        <div className="moochat_options_disable">
                            <div><input type="checkbox" id="moochat_soundnotifications"
                                        style={{'verticalAlign': '-2px'}}/>Disable
                                sound notifications
                            </div>
                            <div style={{clear:'both'}}></div>
                            <div><input type="checkbox" id="moochat_popupnotifications"
                                        style={{'verticalAlign': '-2px'}}/>Disable
                                popup notifications
                            </div>
                            <div style={{clear:'both'}}></div>
                            <div><input type="checkbox" id="moochat_lastseen" style={{'verticalAlign': '-2px'}}/>Disable
                                last seen
                            </div>
                        </div>
                        <a className="moochat_manage_'block'list" href="javascript:void(0);"

                           style={{margin:'5px'}}>Manage 'block'ed users</a></div>
                </div>
        <span onClick={handleUsersTabClick.bind(this)} id="moochat_userstab" className={classMooChatTab}
              style={{display: 'inline'}}><span
            id="moochat_userstab_icon" className={classUserIconStatus}><i className="material-icons">person</i> <i className={classIconStatus}></i> </span><span
            id="moochat_userstab_text">{userstabText}</span></span>
                <div id="moochat_chatboxes" unselectable="on" style={{width: moochat_chatboxes_width+'px'}}>
                    <div id="moochat_chatboxes_wide" className="moochat_floatR"
                         style={{width: moochat_chatboxes_wide_width+'px'}}>
                        {moochat_window_minimized}
                    </div>
                </div>
                <div onClick={handleUnseenRoomsClick.bind(this)} id="moochat_chatbox_left"
                     className={roomclassMooChatTab} style={{display: roomDisplayChatboxCss}}>
                    <div className="moochat_tabalertlr"><i className="material-icons">chat_bubble</i></div>
                    <div className="moochat_tabtext">{roomsUnseen}</div>
                    <RoomUnseenMessages ids={roomsUnseedList}/>
                    <div id="moochat_chatbox_left_border_fix" style={{display: roomDisplayUnseenusers}}></div>
                </div>
                <div id="moochat_unseenUsers" style={{display: roomDisplayUnseenusers}}>
                    {moochat_unseenUsers}
                </div>


            </div>

        );
    }
});
module.exports = FriendStatusWindow;
