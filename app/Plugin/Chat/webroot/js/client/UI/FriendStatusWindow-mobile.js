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
var ViewerStore = require('../stores/ViewerStore');
var MessageStore = require('../stores/MessageStore');
var CounterUnseenMessageStore = require('../stores/CounterUnseenMessageStore');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var ChatMooUtils = require('../utils/ChatMooUtils');
var ChatWindows = require('../UI/ChatWindows-mobile');
var __ = require('../utils/ChatMooI18n').i18next;


var _getRoomInfo =function(idUser,idGroup){
    var room = {id:0,minimized:ChatConstants.WINDOW_MINIMIZE};
    if(idUser != 0){
        room = RoomStore.getFromUserId(idUser);
    }
    if(idGroup != 0){
        room = RoomStore.get(idGroup);
    }
    if(room == undefined){
        return {id:0,minimized:ChatConstants.WINDOW_MINIMIZE};
    }
    return room;
};
var ChatSettings = React.createClass({
    handleChatSound: function () {

        if (this.state.isChatSoundEnable) {
            ChatMooUtils.turnOffSound();
        } else {
            ChatMooUtils.turnOnSound();
        }
        this.setState({isChatSoundEnable: !this.state.isChatSoundEnable});
    },
    handleTurnOffChat: function () {
        this.props.handleTurnOffChatIsClick();
    },
    handleCloseAllChatsWindow: function () {
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

    },
    handleBlockSetting: function () {
        window.location.href = ChatMooUtils.getChatBlockSettingURL();
    },
    handleChatHistory: function () {
        window.location.href = ChatMooUtils.getChatHistoryURL();
    },
    handleSelectAction:function(e){

        switch(e.target.value) {
            case "chat_sounds":
                this.handleChatSound();
                break;
            case "block_settings":
                this.handleBlockSetting();
                break;
            case "chat_history":
                this.handleChatHistory();
                break;
            case "hide_groups":
                this.handleHideGroups();
                break;
            case "turn_off_chat":
                this.handleTurnOffChat();
                break;
            default:

        }
    },
    getInitialState:function() {
        return {
            isChatSoundEnable: ChatMooUtils.getChatSoundState(),
            isHideGroup: ChatMooUtils.getHideGroupState()
        }
    },
    render:function(){

        var textChatSound = (!this.state.isChatSoundEnable) ? __.t("enable_chat_sounds") : __.t("disable_chat_sounds");
        var soundOption = (ChatMooUtils.isChatSoundGlobalEnable())?<option value="chat_sounds">{textChatSound}</option>:"";
        var textHideGroup = (this.state.isHideGroup == ChatConstants.HIDE_GROUP_ENABLE) ? __.t("show_groups") : __.t("hide_groups");

        return <select style={{width:"40px"}} onChange={this.handleSelectAction} value="">
            <option >{__.t("chose_an_action")}</option>
            {soundOption}
            <option value="block_settings">{__.t("block_settings")}</option>
            <option value="chat_history">{__.t("chat_history")}</option>
            <option value="hide_groups">{textHideGroup}</option>
            <option value="turn_off_chat">{__.t("turn_off_chat")}</option>
        </select>;
    }
});

var FriendStatusItemWindow = React.createClass({
    handleFriendItemClick : function () {
        this.props.enableChatWindow(this.props.friend.id,0);
        ChatWebAPIUtils.createChatWindowForAUser(this.props.friend.id);
    },
    render:function(){
        var display = (this.props.friend.is_hidden == ChatConstants.ITEM_SHOW)?"block":"none";
        var user_status = (this.props.friend.is_logged == 1)?"moochat_available":"moochat_offline";
        var room = _getRoomInfo(this.props.friend.id,0);

        var unseen =(room.id != 0 && room.id != undefined)?<RoomUnseenStatusItemWindow room={room}/>:"";
        var review_message =(room.id != 0 && room.id != undefined)?<RoomReviewMessageItemWindow room={room}/>:"";

        
        return <li className="item" style={{display: display}}  id={'moochat_userlist_'+this.props.friend.id} onClick={this.handleFriendItemClick}>
            <a href="#">
                {unseen}
                <div className="left pull-left">
                    <img src={this.props.friend.avatar} width="50" height="50" />
                </div>
                <div className="right">
                    <div className="name ">{this.props.friend.name} <span className={" moochat_status moochat_userscontentdot " + user_status}></span></div>
                    {review_message}
                </div>
            </a>
        </li>;
    }

});
var FriendStatusItemWindows = React.createClass({
    render:function(){
        var FriendStatusItemWindows = [];
        // friendlist data
        if (this.props.friends.key) {
            this.props.friends.key.map(function (key) {
                FriendStatusItemWindows.push(<FriendStatusItemWindow
                                                                     createChatWindow={this.props.createChatWindow}
                                                                     key={key}
                                                                     friend={this.props.friends[key]}
                                                                     enableChatWindow={this.props.enableChatWindow}/>);
            }.bind(this));
        }
        return (<ul className="moochat_mobile_userlist">{FriendStatusItemWindows}</ul>);
    }
});
// End Item Friends UI
var GroupItemWindow = React.createClass({
    handleGroupItemClick : function () {
        this.props.enableChatWindow(0,this.props.group.id);
        ChatWebAPIUtils.createChatWindowByRoomId(this.props.group.id);
    },
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
        var room = _getRoomInfo(0,this.props.group.id);
        var unseen =(room.id != 0 && room.id != undefined)?<RoomUnseenStatusItemWindow room={room}/>:"";
        var review_message =(room.id != 0 && room.id != undefined)?<RoomReviewMessageItemWindow room={room}/>:"";

        return <li className="item mooGroup " id={'moochat_userlist_'+this.props.group.id} onClick={this.handleGroupItemClick}>
            <a href="#">
                {unseen}
                <div className={showImage + " moochat_userscontentavatar "}>
                    {img1}{img2}{img3}{img4}
                </div>
                <div className="right">
                    <div className="name">{this.props.name}</div>
                    {review_message}
                </div>
            </a>
        </li>;
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
                GroupItemWindows.push(<GroupItemWindow key={i} group={this.props.groups[i]} name={name} enableChatWindow={this.props.enableChatWindow}/>);
            }

            return (<div className="mooGroup"><div className="mooGroup_title">{__.t("group_conversations")}</div> <ul className="moochat_mobile_userlist">{GroupItemWindows}</ul></div>);
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
        return <span style={{display:roomsDisplayUnseenNewMessages}} className="moochat_mobile_message">{roomsUnseenNewMessages}</span>;
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
        var newMessageStyle = (this.state.newMessages == 0 ) ? "hidden" : "visible";
        if (ChatMooUtils.getChatSoundState() == ChatConstants.SOUND_ENABLE && ChatMooUtils.isChatSoundGlobalEnable()) {
            RoomStore.playSound(this.props.room.id);
        }

        return <span
            id={"unseenRoom_"+this.props.room.id}
            className="moochat_new_count"
            style={{visibility: newMessageStyle}}>
            {this.state.newMessages}
        </span>;
    }
});
var RoomReviewMessageItemWindow = React.createClass({
    componentDidMount:function(){
        MessageStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        MessageStore.removeChangeListener(this._onChange);
    },
    _onChange: function(roomId) {
        if(roomId == this.props.room.id || roomId == 0){
            this.setState({latestMesasge:MessageStore.getLatestMesasge(this.props.room.id)});
        }
    },
    getInitialState:function() {
        return {latestMesasge:MessageStore.getLatestMesasge(this.props.room.id)};
    },
    render:function(){
        return  <div className="moochat_review_message" dangerouslySetInnerHTML={{__html: this.state.latestMesasge}}></div>

    }
});
// FriendStatus UI


var FriendStatusWindow = React.createClass({
    componentWillReceiveProps: function(nextProps) {

        if(nextProps.openChatWithOneUser.hash != this.props.openChatWithOneUser.hash && nextProps.openChatWithOneUser.id != 0){
            console.log("componentWillReceiveProps",nextProps.openChatWithOneUser.hash,this.props.openChatWithOneUser.hash);
            if (!this.state.isOffline) {
                this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked , friendsWindowsIsShowed:true});
                this.enableChatWindow(nextProps.openChatWithOneUser.id,0);
            } else {
                ChatMooUtils.turnOnChat();
                this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked, friendsWindowsIsShowed:true, isOffline: false});
                this.enableChatWindow(nextProps.openChatWithOneUser.id,0);
            }
        }
        if(nextProps.openChatRoom.hash != this.props.openChatRoom.hash && nextProps.openChatRoom.id != 0){
            console.log("componentWillReceiveProps",nextProps.openChatWithOneUser.hash,this.props.openChatWithOneUser.hash);
            if (!this.state.isOffline) {
                this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked , friendsWindowsIsShowed:true});
                this.enableChatWindow(0,nextProps.openChatRoom.id);
            } else {
                ChatMooUtils.turnOnChat();
                this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked, friendsWindowsIsShowed:true, isOffline: false});
                this.enableChatWindow(0,nextProps.openChatRoom.id);
            }
        }
    },
    getInitialState:function() {
        var isOffline = (ChatMooUtils.getChatStatus() == ChatConstants.USER_OFFLINE) ? true : false;
        var isHideGroup = (ChatMooUtils.getHideGroupState() == ChatConstants.HIDE_GROUP_ENABLE) ? true : false;
        return {
            isOffline: isOffline,
            isHideGroup:isHideGroup,
            mooChatStatusIsClicked: false,
            friendsWindowsIsShowed:false,
            chatWindowIsShowed:false,
            searhFriendsIsShowed: false,
            idUserIsBeingUsedForCreatingARoom:0,
            idGroupIsBeingUsedForCreatingARoom:0,
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
        //ChatMooUtils.initSlimScroll('#moochat_userscontent');
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
    handleSearchFriendsIsCliked:function(){
        this.setState({searhFriendsIsShowed: !this.state.searhFriendsIsShowed});
    },
    handleChatSubmit:function(e){
        e.preventDefault();
    },
    handleHideGroupsIsClicked:function(isHide){
        ChatMooUtils.hideGroup(isHide);
        this.setState({isHideGroup:isHide});
    },
    handleTurnOffChatIsClick:function(){
        ChatMooUtils.turnOffChat();
        this.setState({mooChatStatusIsClicked: false, isOffline: true,friendsWindowsIsShowed:false});
    },
    handleChatStatusClick : function () {
        if (!this.state.isOffline) {
            this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked , friendsWindowsIsShowed:true});
        } else {
            ChatMooUtils.turnOnChat();
            this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked, friendsWindowsIsShowed:true, isOffline: false});
        }
    },
    handleCloseFriendsWindows:function(){
        this.setState({mooChatStatusIsClicked: !this.state.mooChatStatusIsClicked , friendsWindowsIsShowed:false});
    },
    enableChatWindow:function(friendId,groupId){
        var room = _getRoomInfo(friendId,groupId);
        RoomStore.setRoomMobiIsActive(room.id);
        this.setState({friendsWindowsIsShowed:false,chatWindowIsShowed:true,idUserIsBeingUsedForCreatingARoom:friendId,idGroupIsBeingUsedForCreatingARoom:groupId});
    },
    closeChatWindow:function(){
        var room = _getRoomInfo(this.state.idUserIsBeingUsedForCreatingARoom,this.state.idGroupIsBeingUsedForCreatingARoom);
        RoomStore.setRoomMobiIsActive(0);
        if(room.id != 0 && this.state.chatWindowIsShowed){
            RoomStore.minimizeChatWindow(room.id,true);
        }
        this.setState({friendsWindowsIsShowed:true,chatWindowIsShowed:false,idUserIsBeingUsedForCreatingARoom:0,idGroupIsBeingUsedForCreatingARoom:0});
    },
    componentDidUpdate:function(){
        if(ChatMooUtils.isTurnOffForFirstTimeUsing()){
            ChatMooUtils.setFirsTimeUsing(false);
            this.setState({mooChatStatusIsClicked: false, isOffline: true});
        }
    },
    render:function() {
        // control
        // offline status
        var classUserIconStatus = !this.state.isOffline ? 'moochat_user_available2' : 'moochat_user_offline2';
       var classIconStatus = !this.state.isOffline ? 'moochat_icon_available2' : 'moochat_icon_offline2';
        // end offline status
        //    show/hide sidebar friendlist
        var mooChatStatusDisplay = this.state.mooChatStatusIsClicked ? 'none' : 'block';
        var friendsWindowsDisplay = this.state.friendsWindowsIsShowed ? 'block' : 'none';
        var chatWindowDisplay = this.state.chatWindowIsShowed ? 'block' : 'none';
        var searchDisplay = this.state.searhFriendsIsShowed ? 'block' : 'none';

        //    end show/hide sidebar friendlist
        if(this.state.mooChatStatusIsClicked){
            ChatMooUtils.hideBodyChildNode();
         
        }else{
            ChatMooUtils.showBodyChildNode();

        }
        // set how many friend online
        var countFriends = 0;
        if (this.state.friends.keyonline) {
            countFriends = this.state.friends.keyonline.length;
        }
        var userstabText = !this.state.isOffline ? countFriends : <div><i className="material-icons">person</i><i className="moochat_icon_offline"></i></div>;
        var mobileClassIcon = !this.state.isOffline ? "moochat_mobile_icon" : "moochat_mobile_icon offline";
        var mobileClassOnline = !this.state.isOffline ? "moochat_mobile_online" : "moochat_mobile_offline";
        // end set how many friend online

        var name = UserStore.getName(ViewerStore.get('id'));
        var avatar = <img className="moochat_userscontentavatarimage" src={UserStore.getAvatar(ViewerStore.get('id'))}/>;
        var room = _getRoomInfo(this.state.idUserIsBeingUsedForCreatingARoom,this.state.idGroupIsBeingUsedForCreatingARoom);
        if(room.id != 0 && this.state.chatWindowIsShowed){
            RoomStore.minimizeChatWindow(room.id,false);
        }
        var badges = <div  id="moochat_mobile" onClick={this.handleChatStatusClick}  style={{display: mooChatStatusDisplay}}>
            <div className={mobileClassIcon}>
                <i className="material-icons">chat_bubble</i>
                <span className={mobileClassOnline}>{userstabText}</span>
                <RoomUnseenMessages ids={this.state.rooms.isCreated}/>
            </div>
        </div>;
        if(this.state.isOffline){
            badges = <div id="moochat_mobile" onClick={this.handleChatStatusClick}  style={{display: mooChatStatusDisplay}}><div className="moochat_mobile_icon offline">{__.t("offline")}</div></div>
        }
        return (
        <div>
            {badges}

            <div className="moochat_mobile_list" style={{display:friendsWindowsDisplay}}>
                <div className="moochat_mobile_header friendChatAll">
                    <div className="moochat_username">{avatar}{name}</div>
                    <div className="closeChatMobile">
                    <i className="material-icons" onClick={this.handleCloseFriendsWindows}>clear</i>
                        </div>
                    <div className="optionChatMobile">
                    <i className="material-icons">more_vert</i>
                    <ChatSettings
                                  handleTurnOffChatIsClick={this.handleTurnOffChatIsClick}
                                  handleHideGroupsIsClicked={this.handleHideGroupsIsClicked}
                    />
                    </div>
                    <div className="searchChatMobile">
                    <i className="material-icons" onClick={this.handleSearchFriendsIsCliked}>search</i>
                        </div>
                </div>
                <div className="moochat_search" style={{display:searchDisplay}}>
                    <form onSubmit={this.handleChatSubmit}>


                        <input id="moochat_search" type="text" name="moochat_search" ref="filterInput"
                               className="moochat_search moochat_search_light textInput"
                               placeholder={__.t("type_to_find_a_user")} onChange={this.filterTrigger}/>
                    </form>
                </div>
                <div className="friend_title">{__.t("friends")}</div>
                <FriendStatusItemWindows friends={this.state.friends}
                                         createChatWindow={this.props.createChatWindow}
                                         enableChatWindow={this.enableChatWindow}
                />
                <GroupItemWindows groups={this.state.groups} isHideGroup={this.state.isHideGroup} enableChatWindow={this.enableChatWindow} />

            </div>

            <div style={{display:chatWindowDisplay}}>
                <ChatWindows
                    closeChatWindow={this.closeChatWindow}
                    friends={this.state.friends}
                    users={this.state.users}
                    idUserIsBeingUsedForCreatingARoom={this.state.idUserIsBeingUsedForCreatingARoom}
                    idGroupIsBeingUsedForCreatingARoom={this.state.idGroupIsBeingUsedForCreatingARoom}
                    enableChatWindow={this.enableChatWindow}
                />
            </div>

        </div>
        );
    }
});
module.exports = FriendStatusWindow;
