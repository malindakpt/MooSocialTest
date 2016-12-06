# Todo List
- [x] Authentication
- [x] FriendStatus
- [x] ChatWindow
- [x] One-on-one chat
- [x] Group chat
- [x] Chat status
- [x] Send pic
- [x] Send file
- [x] Chat history
- [ ] [Mobi Integration] Androi integration
- [ ] [Mobi Integration] iOS integration
- [ ] Broadcast messages
- [x] Supports Smileys Emojis
- [x] Backend Management
- [ ] Document 

# CONTENTS OF THIS FILE
* About
* Configuration and features
* Building 
* Developing

# About 
## Socket Properties
* isLogged : It is used to determine user is validated 
* userId   : If isLogged  is false , userId will be 0 . If isLogged is true , user will more than 0 .
* myFriendsId : It holds all id friends which owner have .
* roomsId   : It holds all id rooms which owner join .
## myRooms Properties 
* isActived : It holds all id rooms which is actived.
* lastestRoomIsActived : It is the current room which is actived by user .
* lastestRoomIsCreated : It is the current room is created by system .
* myRooms.id : It hold the room object which includes the Properties belows : 
** id  
** messages : The latest 100 mesasges in that room 
** members  : It holds all id user which joined that room , excluded the id of viewer .
** minimized : It holds the rom window status. 
## Booting 
* For each time booting the chat application 
**  ChatUserIsConnecting table will be empty 

## Authentication 

* User has token for each session 
* User can have many  token 
* Token is created after user login 
* Token is delete after user logout 
* Token is saved in table ChatTokens 

## FriendStatus  
* Client will update the friendsStatus window when 
 * First connection is authenticated . 
 * One friend online 
 * One friend offline 
* Events are being used 
 * client#getMyFriendsCallBack ,client#getMyFriendsOnlineCallback , client#friendIsLogged , client#friendIsLogout
 * server#getMyFriends , server#getMyFriendsOnline

## Structure of application 
* ChatApp.js  : Main application
* BootingRule : Processing all the rules at application's booting . 
* MooSQL.js   : Handling mysql connection to moosocial database .
* Authentication.js : Handling user's authentication 



# Configuration and features
* Real-time message delivery
* Quick Activation

# Building 
  * Go to  /app/Plugin/Chat/webroot/js/server the run the commandline bellow 
  ```
  $ npm install
  $ node .
  ``` 
  * You can follow this guide https://www.digitalocean.com/community/tutorials/how-to-set-up-a-node-js-application-for-production-on-ubuntu-16-04 to set up your chat application .
# Developing

# Events 
| Client   | Server | Note |
| -------- | -------- |-----|
| userIsLogged |    | Callback from server after verifying token |
|  | getMyFriends   | Client want to get the list of his online friends  |
| getMyFriendsCallBack |    | Callback from server after processing "getMyFriends" server event , it returns user object array |
| friendIsLogged |     | Server notificate client to know one friends is online |
| friendIsLogout |     | Server notificate client to know one friends is offline |

# Users Roles 

 * Allow chat 
 * Allow send picture 
 * Allow send files 
 * Allow use emotion 
 * Allow chat group 
 /Library/WebServer/Documents/moolab/2.3.2/app/webroot/chat
 
# Notice for production 
 * More css and js files can be in the directory /Library/WebServer/Documents/moolab/2.3.2/app/webroot/chat
 * Moosocial Event System requires 
   * MooView.afterLoadMooCore
   * MooView.beforeRenderRequreJsConfig
   * Auth.afterIdentify
   * AppController.doBeforeFilter
   * MooView.beforeMooConfigJSRender 
   * Controller.User.afterLogout
 * All chat icons is using https://fonts.googleapis.com/icon?family=Material+Icons , you can add     
 <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:400,300,500,700"/>    
 <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons"/>
 to your default layout .
 * /Library/WebServer/Documents/moolab/2.3.2/app/webroot/theme/adm/css/layout/css/layout.css
 
 