var _query ={
    checkTokenIsExists : "SELECT user_id FROM prefix_chat_tokens WHERE token= ?",
    getMyStatCached:"SELECT * FROM prefix_chat_cached_query_user_stats WHERE user_id = ?",
    setMyStatCached:"UPDATE prefix_chat_cached_query_user_stats SET new_friend=0,new_block=0 WHERE user_id = ?",
    getMyFriends : "SELECT User.id,User.name,User.avatar,0 AS is_logged,1 AS is_hidden ,gender FROM prefix_users AS User LEFT JOIN prefix_roles AS Role ON (User.role_id = Role.id) INNER JOIN prefix_friends AS Friend ON (User.id = Friend.friend_id) WHERE Friend.user_id = ? AND active = '1' limit 100",
    getMyBlockers : "SELECT object_id FROM prefix_user_blocks WHERE user_id = ?",
    getMyFriendsHaveIds:"SELECT User.id,User.name,User.avatar,0 AS is_logged,1 AS is_hidden ,gender FROM prefix_users AS User  WHERE User.id IN (%IN%) AND active = '1'",
    getAllMyFriendId : "SELECT User.id FROM prefix_users AS User  LEFT JOIN prefix_friends AS Friend ON (User.id = Friend.friend_id) WHERE Friend.user_id = ? AND active = '1'",
    getMyFriendsHaveName : "SELECT User.id,User.name,User.avatar,0 AS is_logged,1 AS is_hidden ,gender FROM prefix_users AS User LEFT JOIN prefix_roles AS Role ON (User.role_id = Role.id) INNER JOIN prefix_friends AS Friend ON (User.id = Friend.friend_id) WHERE Friend.user_id = ? AND active = '1' AND User.name LIKE ? ",
    getMyGroups : "SELECT room_id,members FROM (SELECT count(user_id) as number ,GROUP_CONCAT(CONVERT(user_id, CHAR(10))) AS members , room_id FROM prefix_chat_rooms_members WHERE room_id in (SELECT room_id FROM prefix_chat_rooms_members WHERE user_id = ?) GROUP BY room_id) AS TMP WHERE number > 2",
    getMyGroups2: "SELECT TMP.room_id,TMP.members FROM (SELECT count(user_id) as number ,GROUP_CONCAT(CONVERT(user_id, CHAR(10))) AS members , room_id FROM prefix_chat_rooms_members WHERE room_id in (SELECT room_id FROM prefix_chat_rooms_members WHERE user_id = ?) GROUP BY room_id) AS TMP LEFT JOIN prefix_chat_rooms AS p ON TMP.room_id = p.id WHERE  p.is_group=1",
    users:"SELECT id,name,avatar,0 AS is_logged,1 AS is_hidden,gender FROM prefix_users  WHERE id IN (%IN%)",
    usersInRooms:"SELECT id,name,avatar,0 AS is_logged,1 AS is_hidden,gender FROM prefix_users  WHERE id IN (SELECT user_id FROM prefix_chat_rooms_members WHERE room_id  IN (%IN%))",
    rooms:"SELECT id,code FROM prefix_chat_rooms  WHERE id IN (%IN%)",
    checkRomByCode:"SELECT id,first_blocked,second_blocked,is_group,has_joined FROM prefix_chat_rooms WHERE code= ?",
    updateCodeRoom:"UPDATE prefix_chat_rooms SET code = ? WHERE id = ? ",
    updateCodeRoomAndHasJoined:"UPDATE prefix_chat_rooms SET code = ? , has_joined=? WHERE id = ? ",
    getMemberInARoom:"SELECT user_id FROM prefix_chat_rooms_members WHERE room_id = ? ",
    checkUserIsMemberInARoom:"SELECT user_id FROM prefix_chat_rooms_members WHERE room_id = ? and user_id = ? ",
    updateFirstBlockInARoom:"UPDATE prefix_chat_rooms SET first_blocked = ? WHERE id = ? ",
    secondFirstBlockInARoom:"UPDATE prefix_chat_rooms SET second_blocked = ? WHERE id = ? ",
    getRoomInfo:"SELECT * FROM prefix_chat_rooms WHERE id = ? ",
    getUnreadMesageInARoom:"SELECT COUNT(s.id) as newMessages,s.message_id FROM prefix_chat_status_messages as s LEFT JOIN prefix_chat_messages as m ON s.message_id = m.id WHERE s.room_id=? AND s.user_id=? AND s.unseen=1 AND m.sender_id != ? ORDER BY s.message_id",
    leaveARoom:"DELETE FROM prefix_chat_rooms_members WHERE room_id = ? AND user_id = ?",
    createRom:"INSERT INTO prefix_chat_rooms SET ?",
    joinRom:"INSERT INTO prefix_chat_rooms_members SET ?",
    getRoomHasUnReadMessage:"SELECT DISTINCT(room_id) FROM prefix_chat_status_messages WHERE user_id = ? AND unseen=1 ",
    setMyFriendMesasge:"INSERT INTO prefix_chat_messages SET ?",
    countTotalMessage:"SELECT COUNT(*) AS count FROM prefix_chat_messages",
    getMessage:"SELECT id,content,sender_id,room_id,type,UNIX_TIMESTAMP(created) AS created  FROM prefix_chat_messages ORDER BY id DESC LIMIT ? ",
    setMesasgeStatus:"INSERT INTO prefix_chat_status_messages SET ?",
    setMesasgeStatusIsSeen:"UPDATE prefix_chat_status_messages SET unseen = 0 WHERE id IN (%IN%) AND user_id = ?",
    setMesasgeStatusIsSeen2:"UPDATE prefix_chat_status_messages SET unseen = 0 WHERE room_id IN (%IN%) AND user_id = ?",
    getMyGroupMesasge:"SELECT * FROM (SELECT m.id,m.room_id,m.type, m.content,m.note_content_html,m.note_one_emoj_only, UNIX_TIMESTAMP(m.created) AS time ,m.sender_id , IFNULL(u.unseen,0) as unseen,IFNULL(u.user_id,0) as receiver_id , IFNULL(u.id,0) as unread_id FROM prefix_chat_messages as m LEFT JOIN  prefix_chat_status_messages  as u ON ( m.id =u.message_id and u.user_id = ?) WHERE m.room_id = ? AND u.delete = 0 GROUP BY id ORDER BY time DESC  LIMIT ? ) TMP ORDER BY id ",
    getMyFriendMesasge:"SELECT * FROM (SELECT m.id,m.room_id,m.type, m.content,m.note_content_html,m.note_one_emoj_only, UNIX_TIMESTAMP(m.created) AS time ,m.sender_id , IFNULL(u.unseen,0) as unseen,IFNULL(u.user_id,0) as receiver_id , IFNULL(u.id,0) as unread_id FROM prefix_chat_messages as m LEFT JOIN  prefix_chat_status_messages  as u ON ( m.id =u.message_id and u.user_id = ?) WHERE m.room_id = ? AND u.delete = 0  GROUP BY id ORDER BY time DESC  LIMIT ? ) TMP ORDER BY id ",
    getMyFriendMesasgeToId:"SELECT * FROM (SELECT m.id,m.room_id,m.type, m.content,m.note_content_html,m.note_one_emoj_only, UNIX_TIMESTAMP(m.created) AS time ,m.sender_id , IFNULL(u.unseen,0) as unseen,IFNULL(u.user_id,0) as receiver_id , IFNULL(u.id,0) as unread_id FROM prefix_chat_messages as m LEFT JOIN  prefix_chat_status_messages  as u ON ( m.id =u.message_id and u.user_id = ?) WHERE m.room_id = ? AND u.delete = 0 AND  m.id >= ? GROUP BY id ORDER BY time DESC   ) TMP ORDER BY id ",
    //getMoreMyFriendMesasge:"SELECT * FROM (SELECT m.id,m.room_id,m.type, m.content,m.note_content_html,m.note_one_emoj_only, UNIX_TIMESTAMP(m.created) AS time ,m.sender_id , IFNULL(u.unseen,0) as unseen,IFNULL(u.user_id,0) as receiver_id , IFNULL(u.id,0) as unread_id FROM prefix_chat_messages as m LEFT JOIN  prefix_chat_status_messages  as u ON ( m.id =u.message_id and u.user_id = ?) WHERE m.room_id = ? AND u.delete = 0 AND  m.id < ? GROUP BY id ORDER BY time DESC  LIMIT ? ) TMP ORDER BY id ",
    getMoreMyFriendMesasge:"SELECT m.id,m.room_id,m.type, m.content,m.note_content_html,m.note_one_emoj_only, UNIX_TIMESTAMP(m.created) AS time ,m.sender_id , IFNULL(u.unseen,0) as unseen,IFNULL(u.user_id,0) as receiver_id , IFNULL(u.id,0) as unread_id FROM prefix_chat_messages as m LEFT JOIN  prefix_chat_status_messages  as u ON ( m.id =u.message_id and u.user_id = ?) WHERE m.room_id = ? AND u.delete = 0 AND  m.id < ?  ORDER BY id DESC  LIMIT ? ",
    deleteRoomMesasge:"UPDATE prefix_chat_status_messages SET `delete` = 1 WHERE room_id= ? AND user_id= ?",
    reportRoomMesasge:"INSERT INTO prefix_chat_reports SET ?",
    checkReportRoomMesasgeIsExists:"SELECT id FROM prefix_chat_reports WHERE room_id = ? AND by_user = ?",
    //updateNotificationForMember:"UPDATE prefix_users SET chat_count = (SELECT COUNT(*) FROM (SELECT room_id FROM prefix_chat_status_messages WHERE user_id = ? and unseen=1 group by room_id) AS A) WHERE id=?" // Get ER_LOCK_DEADLOCK: Deadlock found when trying to get lock; try restarting transaction
    countNewChatMessage:"SELECT COUNT(*) AS count FROM (SELECT room_id FROM prefix_chat_status_messages WHERE user_id = ? and unseen=1 group by room_id) AS A",
    updateNotificationForMemeber:"UPDATE prefix_users SET chat_count = ? WHERE id=?",
    updateLatestMessageIdForARoom:"UPDATE prefix_chat_rooms SET latest_mesasge_id = ? WHERE id=?"
}
function mooSQL(prefix){
    
    var tmpQuery = "";
    for(var key in _query) {
        tmpQuery = _query[key].toString();
        _query[key] =  tmpQuery.replace(/prefix_/g,prefix);

    }
    return _query;
}

module.exports = mooSQL;


