CREATE TABLE mw_messenger_channel (
  mw_messenger_channel_id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  is_deleted INTEGER DEFAULT 0 NOT NULL,
  is_chatmods_private INTEGER DEFAULT 0 NOT NULL,
  is_chatadmins_private INTEGER DEFAULT 0 NOT NULL,
  is_writing_restricted_to_chatmods INTEGER DEFAULT 0 NOT NULL,
  is_writing_restricted_to_chatadmins INTEGER DEFAULT 0 NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);