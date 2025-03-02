CREATE TABLE /*_*/mw_messenger_message_revision (
  mw_messenger_message_revision_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  mw_messenger_message_revision_user_id INT NOT NULL,
  mw_messenger_message_channel_id INT NOT NULL,
  is_deleted TINYINT(1) DEFAULT 1 NOT NULL,
  PRIMARY KEY(
    mw_messenger_message_revision_id
  )
) /*$wgDBTableOptions*/;