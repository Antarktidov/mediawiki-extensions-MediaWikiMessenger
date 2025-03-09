CREATE TABLE /*_*/mw_messenger_custom_reaction (
  mw_messenger_custom_reaction_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  mw_messenger_custom_reaction_filename LONGTEXT NOT NULL,
  mw_messenger_custom_reaction_user_id INT NOT NULL,
  mw_messenger_custom_reaction_message_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY(
    mw_messenger_custom_reaction_id
  )
) /*$wgDBTableOptions*/;