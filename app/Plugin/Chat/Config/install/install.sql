CREATE TABLE IF NOT EXISTS `{PREFIX}chat_user_is_connecting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `socket_id` varchar(255) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL ,
  PRIMARY KEY (`id`)
);

CREATE TABLE `{PREFIX}chat_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) NOT NULL DEFAULT '0',
  `session_id` varchar(255) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `{PREFIX}chat_status_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL DEFAULT '0',
  `room_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `unseen` tinyint(1) NOT NULL DEFAULT '1',
  `delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quick_unssen_messages` (`room_id`,`user_id`,`unseen`)
);

CREATE TABLE IF NOT EXISTS `{PREFIX}chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(455) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `first_blocked` int(11) NOT NULL DEFAULT '0',
  `second_blocked` int(11) NOT NULL DEFAULT '0',
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `has_joined` varchar(455) NOT NULL,
  `latest_mesasge_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `{PREFIX}chat_rooms_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `joined` datetime NOT NULL,
  `blocked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ;




CREATE TABLE IF NOT EXISTS `{PREFIX}chat_users_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `room_is_opened` text NOT NULL,
  `sound` tinyint(1) NOT NULL DEFAULT '1',
  `hide_group` tinyint(1) NOT NULL DEFAULT '0',
  `first_time_using` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `{PREFIX}chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT 0,
  `content` text CHARACTER SET utf8 NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'text',
  `note_content_html` text CHARACTER SET utf8 NOT NULL,
  `note_one_emoj_only` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `{PREFIX}chat_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `by_user` int(11) NOT NULL,
  `created` timestamp DEFAULT CURRENT_TIMESTAMP,
  `reason` text,
  PRIMARY KEY (`id`)
);
INSERT IGNORE INTO `{PREFIX}acos` (`group`, `key`, `description`) VALUES
  ('chat', 'allow_chat', 'Allow chat '),
  ('chat', 'allow_send_picture', 'Allow send picture'),
  ('chat', 'allow_send_files', 'Allow send files'),
  ('chat', 'allow_user_emotion', 'Allow use emotion'),
  ('chat', 'allow_chat_group', 'Allow chat group');

CREATE TABLE IF NOT EXISTS `{PREFIX}chat_cached_query_user_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `new_friend` tinyint(1) NOT NULL DEFAULT '0',
  `new_block` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fast_update` (`id`,`user_id`,`new_friend`,`new_block`)
);
