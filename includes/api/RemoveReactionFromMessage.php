<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class RemoveReactionFromMessage extends ApiBase {
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
            $reaction = $this->test_input($_POST["reaction"]);
            $reaction_type = $this->test_input($_POST["reaction_type"]);

            try {
                $dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_PRIMARY);

                if ($reaction_type === 'standard') {
                    $dbw->newDeleteQueryBuilder()
                        ->deleteFrom('mw_messenger_standard_reaction')
                        ->where([
                            'mw_messenger_standard_reaction_user_id' => $user_id,
                            'mw_messenger_standard_reaction_message_id' => $message_id,
                            'mw_messenger_standard_reaction_filename' => $reaction
                        ])
                        ->caller(__METHOD__)
                        ->execute();
                    $numDeleted = $dbw->affectedRows();
                } else if ($reaction_type === 'custom') {
                    $dbw->newDeleteQueryBuilder()
                        ->deleteFrom('mw_messenger_custom_reaction')
                        ->where([
                            'mw_messenger_custom_reaction_user_id' => $user_id,
                            'mw_messenger_custom_reaction_message_id' => $message_id,
                            'mw_messenger_custom_reaction_filename' => $reaction
                        ])
                        ->caller(__METHOD__)
                        ->execute();
                    $numDeleted = $dbw->affectedRows();
                }
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
            'message_id' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ],
            'reaction' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'reaction_type' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
        ];
    }
}