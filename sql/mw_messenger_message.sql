CREATE TABLE /*_*/mw_messenger_message (
  mw_messenger_message_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  mw_messenger_message_user_id INT NOT NULL,
  mw_messenger_message_channel_id INT NOT NULL,
  is_editing_restricted_to_chatmods TINYINT(1) DEFAULT 0 NOT NULL,
  is_editing_restricted_to_chatadmins TINYINT(1) DEFAULT 0 NOT NULL,
  is_deleted TINYINT(1) DEFAULT 1 NOT NULL,
  PRIMARY KEY(mw_messenger_message_id)
) /*$wgDBTableOptions*/;