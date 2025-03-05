<?php

namespace MediaWiki\Extension\MediaWikiMessenger\SpecialPages;

class Messenger extends \SpecialPage {

	public function __construct() {
		parent::__construct( 'Messenger' );
	}

    /**
	 * Run your code here and render content to the browser.
	 *
	 * @param string $sub Optional subpage in the title, as in [[Special:HelloWorld/subpage]].
	 */

	public function execute( $sub ) {
		$out = $this->getOutput();
		$out->addModules(['ext.MediaWikiMessenger']);
		$out->setPageTitleMSg( $this->msg( 'mw-messenger-special-page' ) );

		$user = $this->getUser();

		if (!$user->isAllowed( 'chat' ) ) {
				$out->addWikiMsg( 'messenger-not-allowed' );
				return;
		}

		$isUserCanDeleteOtherUsersMessages = $user->isAllowed( 'delete_messages' );

		$out->addJsConfigVars([
			'isUserCanDeleteOtherUsersMessages' => $isUserCanDeleteOtherUsersMessages,
		]);

        $out->addHTML(	
						'<div id="mw-messenger" v-cloak>
							<div id="mw-messenger-channels-list">
								<ul v-for="channel in channels.channels">
									<li><a @click.prevent="getChannelMessages(channel.id)" href="#">{{channel.name}}</a></li>
								</ul>
							</div>
							<div v-if="reversedMessages.length" id="mw-messenger-channel-area">
								<div id="mw-messenger-channel-messages">
									<div v-for="message in reversedMessages" class="mw-messenger-message">
										<div class="mw-messenger-message-header"><span class="mw-messenger-message-author"><a v-bind:href="scriptPath+\'/index.php/User:\'+message.user_name">{{message.user_name}}</a></span><span class="mw-message-right">Правая часть шапки сообщения</span></div>
										<div class="mw-messenger-message-body" v-html="message.parsedMessageText"></div>
									</div>
								</div>
								<form id="mw-messenger-textarea-send-message" @submit.prevent="sendMyMessage">
									<textarea v-model="myMessage.text" name="message" id="message-text"></textarea>
									<button>{{mwMessengerSendMessageBtnTxt}}</button>
								</form>
							</div>
						</div>'
					);
	}
    	/** @inheritDoc */
	protected function getGroupName() {
		return 'other';
	}
}