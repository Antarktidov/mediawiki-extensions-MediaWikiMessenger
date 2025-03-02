CREATE TABLE /*_*/mw_messenger_channel (
  mw_messenger_channel_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  name LONGTEXT NOT NULL,
  is_deleted TINYINT(1) DEFAULT 1 NOT NULL,
  is_chatmods_private TINYINT(1) DEFAULT 0 NOT NULL,
  is_chatadmins_private TINYINT(1) DEFAULT 0 NOT NULL,
  is_writing_restricted_to_chatmods TINYINT(1) DEFAULT 0 NOT NULL,
  is_writing_restricted_to_chatadmins TINYINT(1) DEFAULT 0 NOT NULL,
  PRIMARY KEY(mw_messenger_channel_id)
) /*$wgDBTableOptions*/;