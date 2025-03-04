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
                //return;
            }

            $user_id = $user->getId();

            $message_text = PostMessageToChannel::test_input($_POST["message_text"]);
            $channel_id = PostMessageToChannel::test_input($_POST["channel_id"]);

            $this->getResult()->addValue( null, $this->getModuleName(), [
                'message_text' => $message_text,
                'channel_id' => $channel_id,
                'user_id' => $user_id,
            ] );
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