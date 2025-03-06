<?php

namespace MediaWiki\Extension\MediaWikiMessenger\SpecialPages;

use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;

class CreateMessengerChannel extends \SpecialPage {

	public function __construct() {
		parent::__construct( 'CreateMessengerChannel' );
	}

    public function execute( $sub ) {
		$out = $this->getOutput();
		$out->setPageTitleMSg( $this->msg( 'mw-messenger-create-messenger-channel-special-page' ) );

        $user = $this->getUser();

		if (!$user->isAllowed( 'see_chat' ) &&  !$user->isAllowed( 'create_channels' )) {
			$out->addWikiMsg( 'creating-messenger-channels-not-allowed' );
            return;
		}

        if ($_SERVER["REQUEST_METHOD"] == "GET") {

            $channel_name_placeholder_msg = wfMessage('mw-messenger-crete-channel-inputbox-placeholder')->text();
            $create_channel_btn_msg =  wfMessage('mw-messenger-crete-channel-btn-placeholder')->text();

            $out->addHtml(  "<form method=\"post\" name=\"searchbox\" class=\"searchbox mw-inputbox-form\" action=\"#\">
                                <input style=\"margin-right: 10px;\" class=\"mw-inputbox-input cdx-text-input__input\" name=\"channel_name\" placeholder=\"$channel_name_placeholder_msg\" size=\"50\" dir=\"ltr\">
                                <input type=\"submit\" value=\"$create_channel_btn_msg\" class=\"cdx-button\">
                            </form>");
        } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $channel_name = $this->test_input($_POST["channel_name"]);

            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
                $dbw = $dbProvider->getPrimaryDatabase();

                $dbw->insert(
                    'mw_messenger_channel',
                    [
                        'name' => $channel_name,
                        'is_deleted' => 0
                    ],
                    __METHOD__
                );

                $out->addWikiMsg('mw-messenger-crete-channel-channel-created');
        }
    }

    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }
}