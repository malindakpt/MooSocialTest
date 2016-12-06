var React = require('react');
var ReactDOM = require('react-dom');
var CHAT_CONSTANTS = require('../constants/ChatConstants');
var FriendStore = require('../stores/FriendStore');
var UserStore = require('../stores/UserStore');
var RoomStore = require('../stores/RoomStore');
var ViewerStore = require('../stores/ViewerStore');
var MessageStore = require('../stores/MessageStore');
var ChatWebAPIUtils = require('../utils/ChatWebAPIUtils');
var ChatMooUtils = require('../utils/ChatMooUtils');
var ReactTooltip = require("react-tooltip");
var moment = require('../utils/ChatMooI18n').moment;
var __ = require('../utils/ChatMooI18n').i18next;
var validUrl = require('valid-url');
var _typing = {};
var _timeout = {};
var _stopTyping = function(rId){
    if(_typing.hasOwnProperty(rId)){
        _typing[this.props.room.id] = false;
    }
    ChatWebAPIUtils.sendRequestStopTyping(rId);
}
var _clickElement = function (id) {
    var evt = new MouseEvent("click", {
        view: window,
        bubbles: true,
        cancelable: true
    });
    document.getElementById(id).dispatchEvent(evt);

};
var ChatWindowSettings = React.createClass({
    handleChatSettingsClick:function(e){
        e.stopPropagation();
        this.setState({isShowChatSettings: !this.state.isShowChatSettings}); //, isScrollToBottom: false
    },
    handleSeeFullConversationClicked:function(e){
        this.handleChatSettingsClick(e);
        window.location.href = ChatMooUtils.getChatFullConversationURL() + "/" + this.props.roomId;
    },
    handleAddFilesClicked:function(e){
        this.handleChatSettingsClick(e);
        _clickElement('moochat-add-files-button-' + this.props.roomId);

    },
    handleAddFriendsToChatClicked:function(e){
        this.handleChatSettingsClick(e);
        this.props.showAddFriend(e);
    },
    handleDeleteConversationClicked:function(e){
        this.handleChatSettingsClick(e);

        //if (confirm("Delete This Entire Conversation?")) {
        //    ChatWebAPIUtils.sendRequestDeleteConversation(this.props.roomId);
        //}
        var rId = this.props.roomId;
        ChatWebAPIUtils.openAlertYesNoModal(__.t("delete_this_entire_conversation"),__.t("once_you_delete_your_copy____it_can_not_done") , __.t("cancel"), __.t("delete_conversation"), function () {
            ChatWebAPIUtils.sendRequestDeleteConversation(rId);
        });
    },
    handleBlockMessagesClicked:function(e){
        this.handleChatSettingsClick(e);
        var rId = this.props.roomId;
        ChatWebAPIUtils.openAlertYesNoModal(__.t("block_messages"), __.t("stop_getting_messages_from",{name:RoomStore.getName(rId)}) , __.t("cancel"), __.t("button_block_messages"), function () {
            ChatWebAPIUtils.sendRequestBlockMessages(rId);
        });
    },
    handleUnblockMessagesClicked:function(e){
        this.handleChatSettingsClick(e);
        ChatWebAPIUtils.sendRequestUnblockMessages(this.props.roomId);
    },
    handleLeaveConversationClicked:function(e){
        this.handleChatSettingsClick(e);
        var rId = this.props.roomId;
        ChatWebAPIUtils.openAlertYesNoModal(__.t("leave_conversation"),__.t("you_will_stop_receiving_messages") , __.t("cancel"), __.t("button_leave_conversation"), function () {
            ChatWebAPIUtils.sendRequestLeaveConversation(rId);
        });
    },
    handleReportAsSpamClicked:function(e){
        this.handleChatSettingsClick(e);
        ChatWebAPIUtils.openReportModal(this.props.roomId);
    },
    getInitialState:function() {
        return {
            isShowChatSettings: false
        }
    },
    render:function(){
        var displayChatGroup = (ChatMooUtils.isAllowedChatGroup())?"block":"none";
        var displaySendFiles = (ChatMooUtils.isAllowedSendFiles())?"block":"none";
        var moochat_popup_plugins_style = (this.state.isShowChatSettings) ? "block" : "none";
        var specialMenu = "";
        if (typeof this.props.members != 'undefined') {
            if (this.props.members.length == 1) {
                // User menu
                if(RoomStore.isBlocked(this.props.roomId) && RoomStore.isBlocker(this.props.roomId,ViewerStore.get('id'))){
                    specialMenu = <div className="moochat_plugins_dropdownlist">
                        <div className="moochat_pluginsicon moochat_chathistory moochat_floatL">
                            <i className="material-icons">info</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL" onClick={this.handleUnblockMessagesClicked}>{__.t("unblock_messages")}</div>
                    </div>;
                }else{
                    specialMenu = <div className="moochat_plugins_dropdownlist">
                        <div className="moochat_pluginsicon moochat_chathistory moochat_floatL">
                            <i className="material-icons">info</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL" onClick={this.handleBlockMessagesClicked}>{__.t("block_messages")}</div>
                    </div>;
                }

            } else {
                // Group menu
                specialMenu = <div className="moochat_plugins_dropdownlist">
                    <div className="moochat_pluginsicon moochat_chathistory moochat_floatL">
                        <i className="material-icons">info</i>
                    </div>
                    <div className="moochat_plugins_name moochat_floatL" onClick={this.handleLeaveConversationClicked}>{__.t("menu_leave_conversation")}</div>
                </div>;
            }
        }

        return <div className="moochat_plugins_dropdown moochat_floatR">
            <div className="moochat_plugins_dropdown_icon moochat_tooltip"
                 onClick={this.handleChatSettingsClick} data-tip={__.t("options")} data-place="top" >
                <i className="material-icons">arrow_drop_down</i>
            </div>
            <div className="moochat_popup_plugins" style={{display:moochat_popup_plugins_style }}>

                <div className="moochat_plugins moochat_floatR" style={{overflow: 'hidden'}}>
                    <div className="moochat_plugins_dropdownlist">
                        <div className="moochat_pluginsicon moochat_clearconversation moochat_floatL">
                            <i className="material-icons">view_headline</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL"
                             onClick={this.handleSeeFullConversationClicked}>{__.t("see_full_conversation")}</div>
                    </div>
                    <div className="moochat_plugins_dropdownlist" style={{display: displaySendFiles}}>
                        <div className="moochat_pluginsicon moochat_audiochat moochat_floatL">
                            <i className="material-icons">attachment</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL" onClick={this.handleAddFilesClicked}>{__.t("add_files")}</div>
                    </div>
                    <div className="moochat_plugins_dropdownlist" style={{display:displayChatGroup}}>
                        <div className="moochat_pluginsicon moochat_block moochat_floatL">
                            <i className="material-icons">group_add</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL"
                             onClick={this.handleAddFriendsToChatClicked}>{__.t("add_friends_to_chat")}</div>
                    </div>
                    <div className="moochat_plugins_dropdownlist">
                        <div className="moochat_pluginsicon moochat_broadcast moochat_floatL">
                            <i className="material-icons">delete</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL"
                             onClick={this.handleDeleteConversationClicked}>{__.t("delete_conversation")}</div>
                    </div>
                    {specialMenu}

                    <div className="moochat_plugins_dropdownlist">
                        <div className="moochat_pluginsicon moochat_chathistory moochat_floatL">
                            <i className="material-icons">flag</i>
                        </div>
                        <div className="moochat_plugins_name moochat_floatL" onClick={this.handleReportAsSpamClicked}>{__.t("report_as_spam_or_abuse")} </div>
                    </div>

                </div>

            </div>
        </div>;
    }
});
var ChatMooEmoji = React.createClass({
    emojIsClicked:function(text){
        this.props.emojIsClicked(text);
    },
    render:function(){
        var emoj = [];
        var i = 0;
        ChatMooUtils.getEmojiJson().forEach(function (e) {
            i++;
            emoj.push(<span onClick={this.emojIsClicked.bind(this,e.emoji)} key={i} title={e.text}
                            className={e.class}></span>);
        }.bind(this));
        var display = (this.props.isShow) ? "block" : "none";
        var displayEmotion = (ChatMooUtils.isAllowedEmotion())?"block":"none";
        return (<div  style={{display:displayEmotion}} >
                <div className="moochat_buttonicon moochat_buttonemoticon chatwindow-emoji"
                     onClick={this.props.handleButtonEmojIsClicked}>
                    <i className="material-icons">insert_emoticon</i>
                </div>
                <div style={{display:display}} className="moochat_iconlist">{emoj}</div>
            </div>
        );
    }
});
var ChatBlocked = React.createClass({
    handleUnBlockIsClicked:function(e){
        ChatWebAPIUtils.sendRequestUnblockMessages(this.props.room.id);
    },
    render:function(){
        display = (RoomStore.isBlocked(this.props.room.id) && RoomStore.isBlocker(this.props.room.id,ViewerStore.get('id')))? "block":"none";
        return <div className="_54_-" style={{display:display}}>
            {__.t("messages_from_name_are_blocked",{name:RoomStore.getName(this.props.room.id)})}  <a href="#" onClick={this.handleUnBlockIsClicked}>{__.t("unblock")}</a>
        </div>;
    }
});
var ChatURLPreview = React.createClass({
    handleRemoveLink:function(e){
        this.props.hanldeCloseLinkPreview(e);
    },
    render:function(){
        if(!this.props.data.show){
            return <div></div>;
        }else{

            var response = this.props.data.data;
            if(response.hasOwnProperty('result')){
                var data = response.result;

                var title = (data.hasOwnProperty("title"))?data.title:"";
                var description = (data.hasOwnProperty("description"))?data.description:"";
                var type = (data.hasOwnProperty("type"))?data.type:"";
                var img = (data.hasOwnProperty("image"))? <span style={{"backgroundImage":"url(" + data.image + ")"}} className="img_link"></span> :"";
                var code = (data.hasOwnProperty("code"))?data.code:"";
                if (title != "" || description !="" || type!="" || img != "" || code!=""){
                    return <div className="moochat_link_attach"><span onClick={this.handleRemoveLink} className="remove_review_link"><i className="material-icons">clear</i></span> {img}<div className="linkcontent"><div style={{"fontWeight":"bold"}}>{title}</div><div className="link_description">{description}</div></div></div>;
                }else{
                    return <div></div>
                }
            }
            return <div className="moochat_iconlist"><div className="chat-spinner">
                <div className="bounce1"></div>
                <div className="bounce2"></div>
                <div className="bounce3"></div>
            </div></div>;
        }

    }
});
var ChatAddFriends = React.createClass({
    getInitialState:function() {
        return {
            usersIsChoosen: [],
            usersSuggestion: [],
            userSuggestionText: '',
            isFocusUserSuggestionText: false,
            runSearchEngine: false
        }
    },
    engineBloodHoundCallback:function(users) {
        this.setState({usersSuggestion: users});
    },
    handleInputSuggestion:function(e){
        var name = e.target.value;
        if(FriendStore.isCachedKeyword(name)){
            this.setState({userSuggestionText: name, runSearchEngine: true});
        }else{
            ChatWebAPIUtils.sendRequestSearchName(name,(function(){
                this.setState({userSuggestionText: name, runSearchEngine: true});
            }).bind(this));
        }

        // FriendStore.getBloodhoundEngine().search(e.target.value, this.engineBloodHoundCallback, this.engineBloodHoundCallback);
    },
    handleDoneButtonIsClicked:function(){
        var tmp = this.state.usersIsChoosen.slice(0);
        if(ChatMooUtils.isAllowedChatGroup()){
            this.props.handleAddFriendSubmit(tmp);
        }
        this.setState({usersIsChoosen:[],userSuggestionText: ''});
    },
    handleRemoveUserIsChoosen:function(i){
        var tmp = this.state.usersIsChoosen.splice(0);
        tmp.splice(i, 1);
        this.setState({"usersIsChoosen": tmp, isFocusUserSuggestionText: true});
    },
    handleAddUserIsChoosen:function(i){
        var tmp = this.state.usersIsChoosen.splice(0);
        tmp.push(this.state.usersSuggestion[i].id);
        this.setState({
            usersIsChoosen: tmp,
            userSuggestionText: '',
            isFocusUserSuggestionText: true,
            usersSuggestion: []
        });
    },
    componentDidUpdate:function(){
        var state = {};
        if (this.state.isFocusUserSuggestionText) {
            ReactDOM.findDOMNode(this.refs.userSuggestionText).focus();
            state.isFocusUserSuggestionText = false;
        }
        if (this.state.runSearchEngine) {
            FriendStore.getBloodhoundEngine().search(this.state.userSuggestionText, this.engineBloodHoundCallback, this.engineBloodHoundCallback);
            state.runSearchEngine = false;
        }
        if (state.hasOwnProperty('isFocusUserSuggestionText') || state.hasOwnProperty('runSearchEngine')) {
            this.setState(state);
        }
    },
    render:function() {
        var friends = FriendStore.getAll();
        var display = (this.props.isShow) ? "block" : "none";

        var suggestDisplay = "none";
        var widthInputSuggestion = 'auto';
        var placeholderInputSuggestion = __.t("add_friends_to_this_chat");
        var suggestItems = [];
     
        var itemsIsChoosen = [];
        var members = RoomStore.get(this.props.roomId).members;
   
        if (this.state.usersSuggestion.length > 0) {
            suggestDisplay = "block";

            for (var i = 0; i < this.state.usersSuggestion.length; i++) {
                if (members.indexOf(this.state.usersSuggestion[i].id)  == -1 && this.state.usersIsChoosen.indexOf(this.state.usersSuggestion[i].id) == -1 && suggestItems.length < 6)
                    
                    suggestItems.push(<div key={i} className="suggestion-item tt-suggestion tt-selectable"
                                           onClick={this.handleAddUserIsChoosen.bind(this,i)}>
                        <img alt="" src={this.state.usersSuggestion[i].avatar}/>
                        <span className="text">{this.state.usersSuggestion[i].name}</span>
                    </div>);
            }
        }
        if (this.state.usersIsChoosen.length > 0) {
            widthInputSuggestion = '20px';
            placeholderInputSuggestion = '';
            for (var i = 0; i < this.state.usersIsChoosen.length; i++) {
                itemsIsChoosen.push(<span key={i}
                                          className="tag label label-info">{friends[this.state.usersIsChoosen[i]].name}
                    <span data-role="remove" onClick={this.handleRemoveUserIsChoosen.bind(this,i)}></span></span>);
            }
        }
        return (
            <div className="_54_-" style={{display:display}}>

                <table className="uiGrid _51mz" cellSpacing="0" cellPadding="0">
                    <tbody>
                    <tr className="_51mx">
                        <td className="_51m- vTop _54__">
                            <div className="clearfix uiTokenizer uiInlineTokenizer">
                                <div className="tokenarea hidden_elem"></div>

                                <div className="uiTypeahead" id="js_6x">
                                    <div className="wrap"><input type="hidden" autoComplete="off"
                                                                 className="hiddenInput"/>
                                        <div className="innerWrap">
                                            <div>
                                                <div className="bootstrap-tagsinput">
                                                    {itemsIsChoosen}
                                                     <span className="twitter-typeahead" style={{position: 'relative', display: 'inline-block'}}>
            <input type="text"
                   className="tt-hint"
                   readOnly=""
                   autoComplete="off"
                   spellCheck="false"
                   tabIndex="-1"
                   dir="ltr"
                   style={{position: 'absolute', top: '0px', left: '0px', borderColor: 'transparent', boxShadow: 'none', opacity: 1, background: 'none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0)'}}/>
            <input
                type="text" placeholder={placeholderInputSuggestion} className="tt-input" autoComplete="off"
                spellCheck="false" dir="auto"
                style={{position: 'relative', verticalAlign: 'top', width: widthInputSuggestion, backgroundColor: 'transparent'}}
                size="24"
                onChange={this.handleInputSuggestion}
                value={this.state.userSuggestionText}
                ref="userSuggestionText"
            />
            <pre
                aria-hidden="true"
                style={{position: 'absolute', visibility: 'hidden', whiteSpace: 'pre', fontSize: '12px', fontStyle: 'normal', fontVariant: 'normal', fontWeight: 400, wordSpacing: '0px', letterSpacing: '0px', textIndent: '0px', textRendering: 'auto', textTransform: 'none'}}>t</pre>
            <div
                className="tt-menu"
                style={{position: 'absolute', top: '100%', left: '0px', zIndex: '100', display: suggestDisplay}}>
            <div className="tt-dataset tt-dataset-friends_userTagging">
                {suggestItems}
            </div>
        </div>
        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td className="_51m- vTop _51mw">
                            <label className="doneButton uiButton uiButtonConfirm">
                                <input value={__.t("button_done")} type="submit" onClick={this.handleDoneButtonIsClicked}/>
                            </label>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        );
    }
});
var ChatMessage = React.createClass({
    imageClick:function(e){
        e.preventDefault();
        ChatMooUtils.popupImage(this.refs.image);
    },
    handleImageLoaded:function(){
        this.props.scrollToBottom();
    },
    fileClick:function(e){
        e.preventDefault();
        var url = ChatWebAPIUtils.getSiteUrl() + '/uploads/chat/room-' + this.props.data.room_id + '/' + this.props.data.content;
        var win = window.open(url, '_blank');
        win.focus();
    },
    render:function(){
        var isSelf = this.props.data.sender_id == ViewerStore.get('id');
        var chatter = (isSelf) ? "" : (UserStore.get(this.props.data.sender_id));
        var avatar = "";
        if (typeof chatter != 'undefined') {
            if (chatter.hasOwnProperty('avatar')) {
                avatar = <a className="moochat_floatL" href={chatter.url}>
                    <img className="ccmsg_avatar"
                         src={chatter.avatar}
                         data-tip={chatter.name} data-place="left"/>
                </a>;
            }
        }

        var chatboxmessagecontentClassName = isSelf ? "moochat_chatboxmessagecontent moochat_self" : "moochat_chatboxmessagecontent ";
        var spanHtmlSentNotificaiton = <span className="moochat_sentnotification"><i className="material-icons">done</i></span>;
        var msgArrowClassName = isSelf ? "selfMsgArrow" : "msgArrow";
        var content = this.props.data.content;
        var content_ex = "";
        var type = this.props.data.type;
        if (type == "file" && content.match(/\.(jpeg|jpg|gif|png)$/) != null) {
            type = "image";
        }
        switch (type) {
            case "link":
                var note_content_html = validUrl.isUri(this.props.data.note_content_html)?"<a target=\"_blank\" href="+this.props.data.note_content_html +">"+this.props.data.note_content_html+"</a>":this.props.data.note_content_html;
                content = <div className="mooText" dangerouslySetInnerHTML={{__html: note_content_html}}></div>;
                var contentParse = JSON.parse(this.props.data.content);
                if(contentParse.hasOwnProperty("dataURL")){
                    var data = contentParse.dataURL;
                    if(data.hasOwnProperty("type")){
                        var title = (data.hasOwnProperty("title"))?data.title:"";
                        var description = (data.hasOwnProperty("description"))?data.description:"";
                        var type = (data.hasOwnProperty("type"))?data.type:"";
                        var img = (data.hasOwnProperty("image"))? <span style={{"backgroundImage":"url(" + data.image + ")"}} className="img_link"></span> :"";
                        var imgURL = (data.hasOwnProperty("image"))? data.image :"";
                        var code = (data.hasOwnProperty("code"))?data.code:"";
                        var url = (data.hasOwnProperty("url"))?data.url:"";

                        switch (type) {
                            case "link":
                                content_ex = <a target="_blank" href={url} ><div className="mooText mooText_linkparse">{img}<div className="linkcontent"><div style={{"fontWeight":"bold"}}>{title}</div><div className="link_description">{description}</div></div></div></a>;
                                break;
                            case "video":
                                code = code.replace(/(width=")\d+("\W+height=")\d+/, '$1190$2100');
                                content_ex = <div className="mooText mooText_videoparse"><div className="linkvideoContent" dangerouslySetInnerHTML={{__html: code}}></div><div className="videoParseDetail"><div style={{"fontWeight":"bold"}}>{title}</div><div className="link_description">{description}</div></div></div>;
                                break;
                            case "photo":
                                content_ex =
                                    <div className="mooText_linkPhotoparse"><a ref="image" onLoad={this.handleImageLoaded} onClick={this.imageClick} className="imagemessage mediamessage"  href={imgURL}>
                                        <img className="file_image" type="image" src={imgURL} style={{maxHeight:'70px'}}/>
                                    </a></div>;
                                break;
                            default:
                        }
                    }
                }

                break;
            case "image":
                var image = ChatWebAPIUtils.getSiteUrl() + '/uploads/chat/room-' + this.props.data.room_id + '/' + content;
                content =
                    <a ref="image" onLoad={this.handleImageLoaded} onClick={this.imageClick} className="imagemessage mediamessage"  href={image}>
                        <img className="file_image" type="image" src={image} style={{maxHeight:'70px'}}/>
                    </a>;
                break;
            case "file":
                var file = ChatWebAPIUtils.getSiteUrl() + '/uploads/chat/room-' + this.props.data.room_id + '/' + content;
                content = <a ref="file" onClick={this.fileClick}  href={image}
                             style={{cursor:'pointer'}}  >
                    <i className="moochat_icon_more moochat_icon_more_file"></i>
                    <div style={{maxWidth:"140px",fontWeight:'bold',display:"inline-block"}}>{content}</div>
                </a>;
                break;
            case "system":
                avatar = "";
                var system = JSON.parse(this.props.data.content);
                var user;
                switch (system.action) {
                    case "left_the_conversation":
                        content =
                            <div className="mooChat_miniNoticed"><i className="material-icons">remove_circle_outline</i>
                                { __.t("name_left_the_conversation",{"name":chatter.name})}
                            </div>;

                        break;
                    case "added":
                        userB = "";
                        for (var i = 0; i < system.usersId.length; i++) {

                            userB += ((i == 0) ? " " : ", ") + UserStore.getName(system.usersId[i]);


                        }
                        content = <div className="mooChat_miniNoticed"><i className="material-icons">person_add</i>
                            { __.t("userA_added_userB",{"userA":((isSelf) ? __.t("you") : chatter.name),"userB":userB})}
                        </div>;

                    default:
                }

                break;
            default:
                chatboxmessagecontentClassName += (this.props.data.note_one_emoj_only == 1) ? " only_emoji" : "";

                content = <div className="mooText" dangerouslySetInnerHTML={{__html: this.props.data.note_content_html}}></div>;
        }
        var unixTime = parseInt(this.props.data.time + '000');
        var time = moment(unixTime).startOf('min').fromNow();
        return (
            <div className="moochat_chatboxmessage" id={'moochat_message_' + this.props.data.id}>
                {avatar}
                <div className={chatboxmessagecontentClassName} data-tip={time}>
                    {content}{content_ex}
                    <div>
                    {spanHtmlSentNotificaiton}<span className="moochat_time"> {time}</span>
                        </div>
                </div>

            </div>
        );
    }
});
var ChatTypingStatus = React.createClass({
    render:function(){
        if(this.props.room.hasOwnProperty("isTyping")){
            if(this.props.room.isTyping.length>0){
                var avatars = [];


                for (var i=0;i<this.props.room.isTyping.length;i++){
                    // For debug 
                    //this.props.room.isTyping = [1,2,3,4,5];
                    var chatter =  (UserStore.get(this.props.room.isTyping[i]));
                    if (chatter != null){
                        avatars.push(<a key={i} className="moochat_floatL" href="">
                            <img className="ccmsg_avatar"
                                 src={chatter.avatar}
                                 title={chatter.name}/>
                        </a>);
                    }

                }
                return <div className="chatTyping_content">{avatars}
                    <div className="chatTyping_text">
                    <div className="_5pd7"></div>
                    <div className="_5pd7"></div>
                    <div className="_5pd7"></div>
                    </div>
                </div>;
                //var key = (this.props.room.isTyping.length == 1)?"name_is_typing":"names_are_typing";
                //return <div >{__.t(key,{name:UserStore.getNames(this.props.room.isTyping)})}</div>;
            }else{
                return <div>

                </div>;
            }
        }else{
            return <div >

            </div>;
        }
        
    }
});
var ChatWindow = React.createClass({
    componentDidMount:function(){
        MessageStore.addChangeListener(this._onChange);
        var room = RoomStore.get(this.props.room.id);
        if(this.props.room.minimized == CHAT_CONSTANTS.WINDOW_MAXIMIZE){
            

            if(room.hasOwnProperty("messagesIsLoaded")){

                if(room.messagesIsLoaded == CHAT_CONSTANTS.WINDOW_MESSAGES_IS_UNLOADED){
                    RoomStore.markMessagesIsLoaded(room.id);
                    ChatWebAPIUtils.sendRequestGetRoomMessages(room.id);
                }
            }
        }
        if(room.isFocused == CHAT_CONSTANTS.IS_FOCUSED_CHAT_WINDOW){ 
            RoomStore.freeFlagIsFocused(room.id);
            ReactDOM.findDOMNode(this.refs.mooChatTextarea).focus();
        }
        ReactTooltip.rebuild();
        // Autobot for test multi user chat to one room

        var roomId = this.props.room.id;
        //if(this.props.room.id == 2 ) {
        /*
            if (ViewerStore.get('id') != 1) {
                setInterval(function () {
                    var text = "";
                    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                    for (var i = 0; i < 15; i++)
                        text += possible.charAt(Math.floor(Math.random() * possible.length));

                    ChatWebAPIUtils.sendRequestTextMessage(text, roomId, "text");
                }, 100);

            }
          */
        //}

    },
    componentWillUnmount: function () {
        MessageStore.removeChangeListener(this._onChange);
    },
    _onChange: function (roomId) {
        if(roomId == this.props.room.id){
            this.setState({messages: MessageStore.get(this.props.room.id), isScrollToBottom: MessageStore.isScrollToBottom(this.props.room.id),isMessageLoading:MessageStore.isMessageLoading(this.props.room.id)});
        }
    },
    getInitialState:function() {
        return {
            user_status: 'moochat_offline',
            chatMessage: '',
            messages: MessageStore.get(this.props.room.id),
            isShowAddFriend: false,
            isShowChatSettings: false,
            isScrollToBottom: true,
            isFocusChatTexarea: false,
            emojIsShow: false,
            dataURLPreview:{show:false,data:{}},
            isMessageLoading:MessageStore.isMessageLoading(this.props.room.id)
        };
    },
    componentDidUpdate:function(){
        this._scrollToBottom();
        this._suggestForAddingFriends();
        if (this.state.isFocusChatTexarea) {
            ReactDOM.findDOMNode(this.refs.mooChatTextarea).focus();
            this.setState({isFocusChatTexarea: false});
        }

        if(this.props.room.minimized == CHAT_CONSTANTS.WINDOW_MAXIMIZE){
            var room = RoomStore.get(this.props.room.id);

            if(room.hasOwnProperty("messagesIsLoaded")){
                
                if(room.messagesIsLoaded == CHAT_CONSTANTS.WINDOW_MESSAGES_IS_UNLOADED){
                    RoomStore.markMessagesIsLoaded(room.id);
                    ChatWebAPIUtils.sendRequestGetRoomMessages(room.id);
                }
            }
            
            if(room.isFocused == CHAT_CONSTANTS.IS_FOCUSED_CHAT_WINDOW){ 
                RoomStore.freeFlagIsFocused(room.id);
                ReactDOM.findDOMNode(this.refs.mooChatTextarea).focus();
            }
        }
        ReactTooltip.rebuild();
    },
    _scrollToBottom: function () {
        if (this.state.isScrollToBottom) {
            var ul = ReactDOM.findDOMNode(this.refs.messageList);
            if (typeof ul != 'undefined') {
                if (ul != null) {
                    ul.scrollTop = ul.scrollHeight;
                }

            }

        }else{
            var i = MessageStore.getScrollToIfNeed(this.props.room.id);
            if(i > 0){
                MessageStore.freeScrollToIfNeed(this.props.room.id);
                var ul = ReactDOM.findDOMNode(this.refs.messageList);
                if (typeof ul != 'undefined') {
                    if (ul != null) {
                        ul.scrollTop = document.getElementById('moochat_message_'+i).offsetTop;
                    }

                }

            }
        }

    },
    _suggestForAddingFriends:function(){

        if (this.state.isShowAddFriend) {
            ChatMooUtils.initFriendSuggestForARoom(this.props.room.id, RoomStore.get(this.props.room.id).members, FriendStore.getBloodhoundEngine());
        }
    },
    handleChatChange:function(e){
        if (e.target.value != "\n") {
            this.setState({chatMessage: e.target.value});
        }
    },
    handleSendPicture:function(e){
        if(!ChatMooUtils.isAllowedSendPicture()){
            return;
        }


        files = e.target.files;
        var data = new FormData();
        var error = 0;
        var mesasges = "";
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            
            if (!file.type.match('image.*')) {
                mesasges = __.t("Images only");
                error = 1;
            } else if (file.size > ChatMooUtils.getUploadFileLimitOnSite()) {
                mesasges = __.t("file_is_too_large") + ChatMooUtils.getUploadFileLimitOnSite();
                error = 1;
            } else {
                data.append('image', file, file.name);
                data.append('roomId', this.props.room.id);
            }
        }
        if (!error) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ChatWebAPIUtils.getSiteUrl() + '/chats/send-picture', true);
            xhr.send(data);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var json = JSON.parse(xhr.responseText);
                    if (json.hasOwnProperty('error_code')) {
                        if (json.error_code == 0) {
                            ChatWebAPIUtils.sendRequestTextMessage(json.result.filename, this.props.room.id, "image");
                        } else {
                            ChatWebAPIUtils.openAlertModal(__.t("warning"),xhr.responseText);
                        }
                    }

                    console.log(json);
                } else {
                    console.log(" Error in upload, try again.");
                }
            }.bind(this);
        }else{
            ChatWebAPIUtils.openAlertModal(__.t("warning"),mesasges);
        }
    },
    handleButtonEmojIsClicked:function(e){
        this.setState({emojIsShow: !this.state.emojIsShow});
    },
    handleEmojIsClicked:function(emoji){
        if(!ChatMooUtils.isAllowedEmotion()){
            return;
        }
        var message = this.state.chatMessage + emoji;
        this.setState({
            chatMessage: message,
            "emojIsShow": false,
            isFocusChatTexarea: true
        });
    },
    handleSendFile:function(e){
        if(!ChatMooUtils.isAllowedSendFiles()){
            return;
        }
        files = e.target.files;
        var data = new FormData();
        var error = 0;
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            
            if (file.size > ChatMooUtils.getUploadFileLimitOnSite()) {
                mesasges = __.t("file_is_too_large") + ChatMooUtils.getUploadFileLimitOnSite();
                error = 1;
            } else {
                data.append('file', file, file.name);
                data.append('roomId', this.props.room.id);
            }


        }
        if (!error) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ChatWebAPIUtils.getSiteUrl() + '/chats/send-files', true);
            xhr.send(data);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var json = JSON.parse(xhr.responseText);
                    if (json.hasOwnProperty('error_code')) {
                        if (json.error_code == 0) {
                            ChatWebAPIUtils.sendRequestTextMessage(json.result.filename, this.props.room.id, "file");
                        } else {
                            try {
                                var error = JSON.parse(xhr.responseText);
                                if(error.hasOwnProperty("error_code") && error.hasOwnProperty('result')){
                                    if(error.error_code == 1){
                                        ChatWebAPIUtils.openAlertModal(__.t("warning"),error.result.error);
                                    }
                                }
                            } catch (e) {
                                ChatWebAPIUtils.openAlertModal(__.t("warning"),xhr.responseText);
                            }

                        }
                    }

                    console.log(json);
                } else {
                    console.log(" Error in upload, try again.");
                }
            }.bind(this);
        }else{
            ChatWebAPIUtils.openAlertModal(__.t("warning"),mesasges);
        }
    },
    handleChatKeyPress:function(e){

        if (e.which == 13 && !e.shiftKey) {
            var message = e.target.value;
            if (!message) {
                return false;
            }
            //ChatWebAPIUtils.sendRequestTextMessage(message, this.props.room.id, "text");
            //this.setState({chatMessage: ''});
            var rId = this.props.room.id;
            if(RoomStore.isBlocked(rId) ){
                if(RoomStore.isBlocker(this.props.room.id,ViewerStore.get('id'))){
                    ChatWebAPIUtils.openAlertYesNoModal(__.t("unblock_messgages"), __.t("you_and_name_will_be_able_to_send",{name:RoomStore.getName(rId)}), __.t("cancel"), __.t("button_unblock_messgages"), function () {
                        ChatWebAPIUtils.sendRequestUnblockMessages(rId);
                    });
                }else{
                    ChatWebAPIUtils.openAlertModal(__.t("warning"),__.t("this_person_not_receving_messgaes_from_you"));
                }

            }else{
                var tmp = document.createElement("DIV");
                tmp.innerHTML = message.trim();
                message = tmp.textContent || tmp.innerText || "";
                if(message != ""){
                    if(this.state.dataURLPreview.show){
                        var response = this.state.dataURLPreview.data ;
                        if(response.hasOwnProperty('result')){
                            ChatWebAPIUtils.sendRequestTextMessage({message:message,dataURL:response.result}, this.props.room.id, "link");
                        }else{
                            ChatWebAPIUtils.sendRequestTextMessage(message, this.props.room.id, "text");
                        }

                    }else{
                        ChatWebAPIUtils.sendRequestTextMessage(message, this.props.room.id, "text");
                    }
                }

                }

                this.setState({chatMessage: '',dataURLPreview:{show:false,data:{}}});

        }else{
            if(!RoomStore.isBlocked(rId) ){
                if(!_typing.hasOwnProperty(this.props.room.id)){
                    _typing[this.props.room.id] = true;
                    ChatWebAPIUtils.sendRequestStartTyping(this.props.room.id);
                    _timeout[this.props.room.id] = setTimeout(_stopTyping.bind(this,this.props.room.id), 500);
                }else{
                    if( _typing[this.props.room.id] === false){
                        _typing[this.props.room.id] = true;
                        ChatWebAPIUtils.sendRequestStartTyping(this.props.room.id);
                        _timeout[this.props.room.id] = setTimeout(_stopTyping.bind(this,this.props.room.id), 500);
                    }else{
                        if(_timeout.hasOwnProperty(this.props.room.id)){
                            clearTimeout(_timeout[this.props.room.id]);
                        }
                        _timeout[this.props.room.id] = setTimeout(_stopTyping.bind(this,this.props.room.id), 500);
                    }
                }
            }
        }

    },
    handleCloseWindow:function(e){
        e.stopPropagation();
        ReactTooltip.hide();
        ChatWebAPIUtils.destroyARoom(this.props.room.id);
    },
    hanldeCloseLinkPreview:function(e){
        this.setState({dataURLPreview:{show:false,data:{}}});
    },
    handleTabTitleWindowClick:function(){
        ChatWebAPIUtils.minimizeARoom(this.props.room.id);
    },
    handleAddFriendClick:function(e){
        if (typeof e != 'undefined') {
            e.stopPropagation();
        }
        if(!RoomStore.isBlocked(this.props.room.id)){
            this.setState({isShowAddFriend: !this.state.isShowAddFriend, isScrollToBottom: false});
        }


    },
    handleChatOnPaste:function(e){
        // common browser -> e.originalEvent.clipboardData
        // uncommon browser -> window.clipboardData
        var clipboardData = e.clipboardData || e.originalEvent.clipboardData || window.clipboardData;
        var pastedData = clipboardData.getData('text');
        var that=this;

        if (validUrl.isUri(pastedData)){
            that.setState({dataURLPreview:{show:true,data:{}}});
           ChatMooUtils.getDataFromURL(pastedData,function(data){
               that.setState({dataURLPreview:{show:true,data:data}})
           });
        }
    },
    showAddFriend:function(e){
        if (typeof e != 'undefined') {
            e.stopPropagation();
        }
        
        if(!RoomStore.isBlocked(this.props.room.id)){
            this.setState({isShowAddFriend: true, isScrollToBottom: false});
        }


    },
    handleAddFriendSubmit:function(users){

        //var val = ChatMooUtils.getFriendSuggestIsChoosenInARoom(this.props.room.id);
        if (users.length > 0) {
         
            var members = RoomStore.get(this.props.room.id).members;
       
            //if (members.length == 1) {
            if(!RoomStore.isGroup(this.props.room.id)){
                // create new
                users = users.concat(members);
                ChatWebAPIUtils.createChatGroupWindowForUsers(users);
            } else {
                // add more   
                ChatWebAPIUtils.addUsersToARoom(users, this.props.room.id);
            }

        }
        this.setState({isShowAddFriend: !this.state.isShowAddFriend, isScrollToBottom: false});


    },
    handleChatNameIsClicked:function(e){
        e.stopPropagation();
        window.location.href = ChatMooUtils.getChatFullConversationURL() + "/" + this.props.room.id;

    },
    handleChatSettingsClick:function(e){
        e.stopPropagation();
        this.setState({isShowChatSettings: !this.state.isShowChatSettings, isScrollToBottom: false});

    },
    handleMouseWheel:function(event, d){
        var e = event || window.event;
        var deltaX = e.deltaX * -30 ||
            e.wheelDeltaX / 4 ||
            0;
        var deltaY = e.deltaY * -30 ||
            e.wheelDeltaY / 4 ||
            (typeof e.wheelDeltaY == 'undefined' &&
            e.wheelDelta / 4) ||
            e.detail * -10 ||
            0;
        e.currentTarget.scrollTop -= deltaY;
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
        //e.cancelBubble = true;
        //e.returnValue = false;

        return false;
    },
    handleChatScrolling:function(e){
        var ul = ReactDOM.findDOMNode(this.refs.messageList);
        if (typeof ul != 'undefined') {
            if (ul != null) {
                var rId = this.props.room.id;
                
                if(ul.scrollTop == 0  && MessageStore.isAllowedLoadMoreMessages(rId)){

                    var mIdStart = MessageStore.getStartMesageId(rId);
                    if(mIdStart != 0){
                        //this.setState({isScrollToBottom: false});
                        //RoomStore.markMessagesIsLoading(rId);
                        MessageStore.setMessageLoading(rId);
                        setTimeout(function(){ ChatWebAPIUtils.sendRequestGetRoomMessagesMore(rId,mIdStart,ChatMooUtils.getMoreMessageLimit()); }, 200);
                        //ChatWebAPIUtils.sendRequestGetRoomMessagesMore(rId,mIdStart,10);
                    }

                }
            }

        }

    },
    render:function(){
        if (RoomStore.hasNewMessage() && RoomStore.getRoomIdHasNewMessage() == this.props.room.id && this.props.room.minimized == CHAT_CONSTANTS.WINDOW_MAXIMIZE) {
            RoomStore.freeFlagHasNewMessage();
        }
        var displaySendPicutre = (ChatMooUtils.isAllowedSendPicture())?"block":"none";

        var displaySendFiles = (ChatMooUtils.isAllowedSendFiles())?"block":"none";
        var displayChatGroup = (ChatMooUtils.isAllowedChatGroup())?"block":"none";

        // Messages
        var messages = [];



            for (var key in this.state.messages) {
                if (this.state.messages.hasOwnProperty(key)) {
                    messages.push(<ChatMessage  scrollToBottom={this._scrollToBottom} data={this.state.messages[key]} key={key} users={this.props.users}
                                               friends={this.props.friends}/>);
                }
            }


        // End Messages

        // Room Title

        var roomTitle = RoomStore.getName(this.props.room.id);
        var moochatStatusStyle = (RoomStore.getStatusRoom(this.props.room.id) == 1) ? "moochat_available" : "moochat_offline";
        
        // End Room Title

        // Room behavior
        var mooChatWindowClassName = (this.props.room.minimized == CHAT_CONSTANTS.WINDOW_MINIMIZE) ? "moochat_tabpopup moochat_tabopen_bottom" : "moochat_tabpopup moochat_tabopen moochat_tabopen_bottom"; // Default is opend
        // End Room behavior

        var messagesHeight = (this.state.isShowAddFriend) ? "336px" : "336px";
        var moochat_popup_plugins_style = (this.state.isShowChatSettings) ? "block" : "none";

        if (typeof this.props.room.id == 'undefined') {
            return <div></div>;
        }
        var moochat_room_loading  =  (this.state.isMessageLoading) ? "block" : "none";

        return (
            <div key={this.props.room.id} id={"moochat_user_popup_" + this.props.room.id}
                 className={mooChatWindowClassName}
                 style={{display: 'none', left: this.props.left+'px'}}>
                <div className="moochat_tabtitle" onClick={this.handleTabTitleWindowClick}>
                    <div className={"moochat_userscontentdot moochat_floatL "+moochatStatusStyle}></div>
                    <span className="moochat_typing" style={{display: 'none'}}></span>
                    <div className="moochat_name" data-tip={roomTitle} data-place="top" onClick={this.handleChatNameIsClicked}>{roomTitle}</div>
                    <div className="moochat_closebox moochat_floatR moochat_tooltip" onClick={this.handleCloseWindow}
                         data-tip={__.t("close_tab")} data-place="top">
                        <i className="material-icons">clear</i>
                    </div>
                    <div className="moochat_icon_more_2  moochat_icon_more_2_addfriend protip" style={{display:displayChatGroup}}
                         data-tip={__.t("add_more_friends_to_chat")}
                         data-place="top"
                         onClick={this.handleAddFriendClick}>
                        <i className="material-icons">group_add</i>
                    </div>


                    <ChatWindowSettings roomId={this.props.room.id} showAddFriend={this.showAddFriend}
                                        members={this.props.room.members}/>
                    <div className="moochat_icon_more_2  moochat_icon_more_2_addfriend protip"
                         data-tip={__.t("add_more_friends_to_chat")}
                         data-place="top"
                         onClick={this.handleAddFriendClick}></div>
                </div>
                <div className="moochat_tabsubtitle moochat_tabcontent">
                    <ChatAddFriends isShow={this.state.isShowAddFriend} roomId={this.props.room.id}
                                    handleAddFriendSubmit={this.handleAddFriendSubmit}/>
                    <ChatBlocked room ={this.props.room}/>

                    <div onWheel={this.handleMouseWheel}
                         onScroll={this.handleChatScrolling}
                         className="moochat_tabcontenttext"
                         id={"moochat_tabcontenttext_"+ this.props.room.id}
                         ref="messageList" style={{height:messagesHeight}}>
                        <div className="chat-spinner" style={{display:moochat_room_loading}}>
                            <div className="bounce1"></div>
                            <div className="bounce2"></div>
                            <div className="bounce3"></div>
                        </div>
                        
                        {messages}
                        <ChatTypingStatus room={this.props.room}/>
                    </div>

                    <div className="moochat_tabcontentinput">
                        <form className="mooChatForm"  ref="mooChatForm">
                            <textarea
                                ref="mooChatTextarea"
                                className="moochat_textarea placeholder"
                                placeholder={__.t("type_a_message")}
                                onChange={this.handleChatChange}
                                onKeyPress={this.handleChatKeyPress}
                                value={this.state.chatMessage}
                                style={{height: '16px', 'overflowY': 'hidden'}}
                                onPaste={this.handleChatOnPaste}
                            ></textarea>
                        </form>

                        <div className="moochat_buttonicon moochat_buttoncamera moochat_floatL" style={{display:displaySendPicutre }}>

                            <div className="_3jk">
                                <i className="material-icons">photo_camera</i>
                                <form action="/ajax/mercury/upload.php" className="_vzk"
                                      method="post" encType="multipart/form-data">
                                    <input onChange={this.handleSendPicture} type="file" className="_n _2__f _5f0v" title={__.t("add_photo")}
                                           name="attachment[]" multiple="" accept="image/*" />
                                </form>
                            </div>
                        </div>
                        <ChatMooEmoji
                            emojIsClicked={this.handleEmojIsClicked}
                            isShow={this.state.emojIsShow}
                            handleButtonEmojIsClicked={this.handleButtonEmojIsClicked}/>

                        <ChatURLPreview data={this.state.dataURLPreview} hanldeCloseLinkPreview={this.hanldeCloseLinkPreview} />
                        <div className="moochat_buttonicon moochat_buttonfiles moochat_floatL" style={{display:displaySendFiles}}>

                            <div className="_3jk">
                                <i className="material-icons">attachment</i>
                                <form action="/ajax/mercury/upload.php" className="_vzk"  method="post"
                                      encType="multipart/form-data">
                                    <input id={"moochat-add-files-button-"+this.props.room.id}
                                           onChange={this.handleSendFile} type="file" className="_n _2__f _5f0v"  title={__.t("add_files")}
                                           name="attachment[]" multiple="" accept="*" />
                                </form>

                            </div>
                        </div>
                        <div className="moochat_buttonicon moochat_buttoncamera moochat_floatL"></div>

                    </div>
                    <div style={{clear:'both'}}></div>
                </div>
            </div>
        )
    }
});

var ChatWindows = React.createClass({
    componentDidMount:function(){
        UserStore.addChangeListener(this._onChange);
        FriendStore.addChangeListener(this._onChange);
        RoomStore.addChangeListener(this._onChange);
    },
    componentWillUnmount: function () {
        UserStore.removeChangeListener(this._onChange);
        FriendStore.removeChangeListener(this._onChange);
       RoomStore.removeChangeListener(this._onChange);

    },
    getInitialState:function() {
        return {users: UserStore.getAll(), friends: FriendStore.getAll(), rooms: RoomStore.getAll()};
    },
    _onChange: function () {
        this.setState({users: UserStore.getAll(), friends: FriendStore.getAll(), rooms: RoomStore.getAll()});
    },
    componentDidUpdate:function(){
        UserStore.updateMissingUser();
    },
    render:function(){
        var windows = [];
        var left_base = 232;  // 243
        var left_base_minized = 163;
        var left_room = ChatMooUtils.windowWidth() - 467;
        if (this.state.rooms.isCreated) {

            for (var i = 0; i < this.state.rooms.isCreated.length; i++) {



                //if (left_room > 150){
                windows.push(<ChatWindow
                    key={this.state.rooms.isCreated[i]}
                    room={this.state.rooms[this.state.rooms.isCreated[i]]}
                    left={left_room}
                    friends={this.state.friends}
                    users={this.state.users}
                    doModalReport={this.props.doModalReport}
                />);
                if (this.state.rooms[this.state.rooms.isCreated[i]].minimized == 0) {

                    left_room = left_room - left_base;
                } else {
                    left_room = left_room - left_base_minized;
                }
                //}
            }
        }
        return <div>{windows}<ReactTooltip key={99999} place="right" effect="solid"/></div>;
    }
});
module.exports = ChatWindows;