<?php

namespace MediaWiki\Extension\MediaWikiMessenger\SpecialPages;

use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;

class MessengerMessageHistory extends \SpecialPage {

	public function __construct() {
		parent::__construct( 'MessengerMessageHistory' );
	}

    public function execute( $sub ) {
        $out = $this->getOutput();
        $out->setPageTitleMSg( $this->msg( 'mw-messenger-messenger-message-history-channel-special-page' ) );

        $user = $this->getUser();

        if (!$user->isAllowed( 'see_chat' ) &&  !$user->isAllowed( 'view_messages_history' )) {
            $out->addWikiMsg( 'viewing-messenger-message-history-not-allowed' );
            return;
        }

        $request = $this->getRequest();
        $message_id = $request->getText( 'message_id' );

        if ($message_id == null) {
            $see_history_btn_msg = wfMessage('mw-messenger-see-history-btn')->text();
            $out->addHtml("<form action=\"#\" method=\"get\">
                <input type=\"number\" name=\"message_id\" id=\"message_id\" value=\"0\">
                <button type=\"submit\">$see_history_btn_msg</button>
            </form>");
        }

        //echo $message_id;

        if ($message_id != null) {
            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
            $dbr = $dbProvider->getReplicaDatabase();
            $res = $dbr->newSelectQueryBuilder()
            ->select( [ 'mw_messenger_message_revision_id', 'mw_messenger_message_revision_text', 'mw_messenger_message_revision_user_id', 'created_at' ] )
            ->from( 'mw_messenger_message_revision' )
            ->where( [ 'mw_messenger_message_revision_message_id' => $message_id, 'is_deleted' => 0 ] )
            //->orderBy( 'created_at', \Wikimedia\Rdbms\SelectQueryBuilder::SORT_ASC )
            ->caller( __METHOD__ )
            ->fetchResultSet();

            $out->addModules(['ext.MessengerMessageHistory']);
            
            foreach ( $res as $row ) {

                

                    $resUser = $dbr->newSelectQueryBuilder()
                    ->select( 'user_name' )
                    ->from( 'user' )
                    ->where("user_id = {$row->mw_messenger_message_revision_user_id}")
                    ->caller( __METHOD__ )
                    ->fetchResultSet();
    
                    $username = '';
    
                    foreach ($resUser as $userRow) {
                        $username = $userRow->user_name;
                    }

            $user_avatar_wikitext = '';

            if (class_exists( 'SocialProfileHooks' )) {
                $user_avatar_wikitext = "<div class=\"mw-messenger-user-avatar\">{{#avatar:{$username}}}</div>";
            }

            $out->addHtml( "
            <div id=\"mw-messenger-channel-messages\">
                <div class=\"mw-messenger-message\">");
                    $out->addWikiTextAsInterface( $user_avatar_wikitext);
                     $out->addHtml("<div class=\"mw-messenger-not-avatar\">
                        <div class=\"mw-messenger-message-header\">
                            <span class=\"mw-messenger-message-author\">");
                    $out->addWikiTextAsInterface("[[User:$username|$username]]");
                             $out->addHtml("</span>
                            <span class=\"mw-messenger-message-header-right\">
                                {$row->created_at}
                            </span>
                        </div>
                        <div class=\"mw-messenger-message-body\">");
                    $out->addWikiTextAsInterface("{$row->mw_messenger_message_revision_text}");
                             $out->addHtml("</div>
                    </div>
                </div>
            </div>" );
            }
        }
    }
}