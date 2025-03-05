<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class PostMessageToChannel extends ApiBase {
    public function __construct( ApiMain $main, $name ) {
        parent::__construct( $main, $name );
    }

    public function execute() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = $this->getUser();

            if (!$user->isAllowed('see_chat')) {
                return;
            }

            $user_id = $user->getId();

            $message_text = $this->test_input($_POST["message_text"]);
            $channel_id = $this->test_input($_POST["channel_id"]);

            try {
                $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
                $dbw = $dbProvider->getPrimaryDatabase();

                // Вставка сообщения в таблицу mw_messenger_message
                $dbw->insert(
                    'mw_messenger_message',
                    [
                        'mw_messenger_message_user_id' => $user_id,
                        'mw_messenger_message_channel_id' => $channel_id,
                        'is_editing_restricted_to_chatmods' => 0,
                        'is_editing_restricted_to_chatadmins' => 0,
                        'is_deleted' => 0
                    ],
                    __METHOD__
                );

                // Получение ID вставленного сообщения
                $message_id = $dbw->insertId();

                // Вставка ревизии сообщения в таблицу mw_messenger_message_revision
                $dbw->insert(
                    'mw_messenger_message_revision',
                    [
                        'mw_messenger_message_revision_text' => $message_text,
                        'mw_messenger_message_revision_user_id' => $user_id,
                        'mw_messenger_message_revision_message_id' => $message_id,
                        'is_deleted' => 0
                    ],
                    __METHOD__
                );

                $this->getResult()->addValue( null, $this->getModuleName(), [
                    'message_text' => $message_text,
                    'channel_id' => $channel_id,
                    'user_id' => $user_id,
                    'message_id' => $message_id
                ] );
            } catch ( DBQueryError $e ) {
                $this->getResult()->addValue( null, $this->getModuleName(), [
                    'result' => 'error',
                    'error' => $e->getMessage()
                ] );
            }
        }
    }

    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        //$data = htmlspecialchars($data);
        return $data;
      }

    /** @inheritDoc */
    public function getAllowedParams() {
        return [
            'channel_id' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ],
            'message_text' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string',
            ],
        ];
    }
}