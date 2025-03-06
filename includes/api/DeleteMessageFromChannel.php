<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class DeleteMessageFromChannel extends ApiBase {
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

            try {
                $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
                $dbr = $dbProvider->getReplicaDatabase();
                $res = $dbr->newSelectQueryBuilder()
                    ->select('mw_messenger_message_id')
                    ->from('mw_messenger_message')
                    ->where("mw_messenger_message_id = $message_id")
                    ->caller(__METHOD__)
                    ->fetchResultSet();

                foreach ($res as $row) {
                    if ($row->mw_messenger_message_id !== $message_id && !$user->isAllowed('delete_messages')) {
                        return;
                    }
                }

                $dbw = $dbProvider->getPrimaryDatabase();
                $dbw->newUpdateQueryBuilder()
                    ->update('mw_messenger_message')
                    ->set(['is_deleted' => 1])
                    ->where("mw_messenger_message_id = $message_id")
                    ->caller(__METHOD__)
                    ->execute();

                $this->getResult()->addValue(null, $this->getModuleName(), [
                    'result' => 'Message successfully deleted.',
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
        ];
    }
}