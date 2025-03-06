CREATE TABLE mw_messenger_message_revision (
  mw_messenger_message_revision_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mw_messenger_message_revision_text TEXT NOT NULL,
  mw_messenger_message_revision_user_id INTEGER NOT NULL,
  mw_messenger_message_revision_message_id INTEGER NOT NULL,
  is_deleted INTEGER DEFAULT 0 NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);