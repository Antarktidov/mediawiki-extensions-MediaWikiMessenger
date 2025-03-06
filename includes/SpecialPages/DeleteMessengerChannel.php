<?php

namespace MediaWiki\Extension\MediaWikiMessenger\SpecialPages;

use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;

class DeleteMessengerChannel extends \SpecialPage {

	public function __construct() {
		parent::__construct( 'DeleteMessengerChannel' );
	}

    public function execute( $sub ) {
		$out = $this->getOutput();
		$out->setPageTitleMSg( $this->msg( 'mw-messenger-delete-messenger-channel-special-page' ) );

        $user = $this->getUser();

		if (!$user->isAllowed( 'see_chat' ) &&  !$user->isAllowed( 'delete_channels' )) {
			$out->addWikiMsg( 'deleting-messenger-channels-not-allowed' );
            return;
		}

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
            $dbr = $dbProvider->getReplicaDatabase();

            $res = $dbr->newSelectQueryBuilder()
            ->select( ['mw_messenger_channel_id', 'name', 'is_chatmods_private', 'is_chatadmins_private',
            'is_writing_restricted_to_chatmods', 'is_writing_restricted_to_chatadmins',
            'is_deleted'] )
            ->from('mw_messenger_channel')
            ->where('is_deleted = 0')
            ->fetchResultSet();

            $choose_channel_msg =  wfMessage('mw-messenger-delete-message-choose-channel')->text();
            $choose_channel_btn =  wfMessage('mw-messenger-delete-message-delete-channel-btn')->text();

            $out->addHtml("<form method=\"post\" action=\"#\">
            <label for=\"channel_select\">$choose_channel_msg</label>
                    <br>
                    <select name=\"channel_id\" id=\"channel_select\">");
                    $out->addHtml("<option value=\"\">----</option>");

            foreach ( $res as $row ) {
                    $out->addHtml("<option value=\"{$row->mw_messenger_channel_id}\">{$row->mw_messenger_channel_id} — {$row->name}</option>");
            }

            $out->addHtml("</select>
            <input type=\"submit\" value=\"$choose_channel_btn\" class=\"cdx-button\">
            </form>");
        } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $channel_id = $this->test_input($_POST["channel_id"]);

            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
            $dbw = $dbProvider->getPrimaryDatabase();
            $dbw->newUpdateQueryBuilder()
                ->update('mw_messenger_channel')
                ->set(['is_deleted' => 1])
                ->where("mw_messenger_channel_id = $channel_id")
                ->caller(__METHOD__)
                ->execute();

                $out->addWikiMsg('mw-messenger-delete-channel-channel-deleted');
        }
    }

    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }
}