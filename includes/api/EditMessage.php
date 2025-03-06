<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class EditMessage extends ApiBase {
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
            $message_id = $this->test_input($_POST["message_id"]);
            $edited_message_text = $this->test_input($_POST["edited_message"]);

            try {
                $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
                $dbw = $dbProvider->getPrimaryDatabase();

                // Проверка существования сообщения
                $dbr = $dbProvider->getReplicaDatabase();
                $res = $dbr->newSelectQueryBuilder()
                    ->select('mw_messenger_message_id')
                    ->from('mw_messenger_message')
                    ->where("mw_messenger_message_id = $message_id")
                    ->caller(__METHOD__)
                    ->fetchResultSet();

                $messageExists = false;
                foreach ($res as $row) {
                    if ($row->mw_messenger_message_id === $message_id) {
                        $messageExists = true;
                        break;
                    }
                }

                if (!$messageExists) {
                    $this->getResult()->addValue(null, $this->getModuleName(), [
                        'result' => 'error',
                        'error' => 'Message not found.'
                    ]);
                    return;
                }

                // Вставка новой ревизии сообщения в таблицу mw_messenger_message_revision
                $dbw->insert(
                    'mw_messenger_message_revision',
                    [
                        'mw_messenger_message_revision_text' => $edited_message_text,
                        'mw_messenger_message_revision_user_id' => $user_id,
                        'mw_messenger_message_revision_message_id' => $message_id,
                        'is_deleted' => 0
                    ],
                    __METHOD__
                );

                $this->getResult()->addValue(null, $this->getModuleName(), [
                    'result' => 'Message successfully updated.',
                ]);
            } catch (DBQueryError $e) {
                $this->getResult()->addValue(null, $this->getModuleName(), [
                    'result' => 'error',
                    'error' => $e->getMessage()
                ]);
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
            'message_id' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ],
            'edited_message' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
        ];
    }
}