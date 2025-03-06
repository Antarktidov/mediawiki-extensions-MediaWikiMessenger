CREATE TABLE mw_messenger_message (
  mw_messenger_message_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mw_messenger_message_user_id INTEGER NOT NULL,
  mw_messenger_message_channel_id INTEGER NOT NULL,
  is_editing_restricted_to_chatmods INTEGER DEFAULT 0 NOT NULL,
  is_editing_restricted_to_chatadmins INTEGER DEFAULT 0 NOT NULL,
  is_deleted INTEGER DEFAULT 0 NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);