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

		if (!$user->isAllowed( 'see_chat' ) ) {
				$out->addWikiMsg( 'messenger-not-allowed' );
				return;
		}

		$isUserCanDeleteOtherUsersMessages = $user->isAllowed( 'delete_messages' );

		$userId = $user->getId();

		$out->addJsConfigVars([
			'isUserCanDeleteOtherUsersMessages' => $isUserCanDeleteOtherUsersMessages,
			'userId' => $userId,
			'wgChatSocialAvatars' => class_exists( 'SocialProfileHooks' ),
		]);

		$out->addModules(['ext.PortableInfobox.styles']);
		$out->addModules(['ext.SpoilerSpan']);

        $out->addHTML(	
						'<div id="mw-messenger" v-cloak>
							<div id="mw-messenger-channels-list">
								<ul v-for="channel in channels.channels">
									<li><a @click.prevent="getChannelMessages(channel.id)" href="#">{{channel.name}}</a></li>
								</ul>
							</div>
							<div v-if="isChannelSet" id="mw-messenger-channel-area">
								<button class="load-older-btn" @click.prevent="getMoreMessages(currentChannelId)">{{mwMessengerLoadOldMessagesBtnTxt}}</button>
								<div id="mw-messenger-channel-messages">
									<div v-for="message in reversedMessages" class="mw-messenger-message">
										<div v-if="!message.isMessageEditorOpen" class="mw-messenger-message-message-editor-closed">
											<div v-if="wgChatSocialAvatars" class="mw-messenger-user-avatar" v-html="message.user_avatar"></div>
											<div class="mw-messenger-not-avatar">
												<div class="mw-messenger-message-header">
													<span class="mw-messenger-message-author">
													<a v-bind:href="scriptPath+\'/index.php/User:\'+message.user_name">{{message.user_name}}</a>
													</span>
													<span class="mw-messenger-message-header-right">
														<a @click.prevent="openMessageEditor(message.mw_messenger_message_id)" href="#" v-if="userId === +message.mw_messenger_message_user_id">{{mwMessengerEditMessageBtnTxt}}</a>
														<a @click.prevent="deletedMessage(message.mw_messenger_message_id)" href="#" v-if="userId === +message.mw_messenger_message_user_id || isUserCanDeleteOtherUsersMessages">{{mwMessengerDeleteMessageBtnTxt}}</a>
													</span>
												</div>
												<div class="mw-messenger-message-body" v-html="message.parsedMessageText"></div>
												<div class="mw-messenger-message-reactions">
													<span class="mw-messenger-message-reactions-reaction">
														<span class="mw-messenger-message-reactions-reaction-reaction">😀</span>
														<span class="mw-messenger-message-reactions-reaction-counter">1</span>
													</span>
													<span class="mw-messenger-message-reactions-reaction">
														<span class="mw-messenger-message-reactions-reaction-reaction"><img src="http://localhost/mediawiki-1.43.0/index.php/Служебная:FilePath/M.png"></span>
														<span class="mw-messenger-message-reactions-reaction-counter">1</span>
													</span>
												</div>
											</div>
										</div>
										<div v-if="message.isMessageEditorOpen" class="message-editor-open">
											<div class="mw-messenger-message-header">
												<span class="mw-messenger-message-author">
													<a v-bind:href="scriptPath+\'/index.php/User:\'+message.user_name">{{message.user_name}}</a>
												</span>
											</div>
											<form @submit.prevent="updateMyMessage(message.mw_messenger_message_id)">
												<textarea v-model="this.editedMessage.text" name="editedMessage" id="editedMessage"></textarea>
												<div class="mw-messenger-message-editor-footer-btns">
													<button type="submit">{{mwMessengerSaveEditedMessageBtnTxt}}</button>
													<button @click.prevent="closeMessageEditor(message.mw_messenger_message_id)" type="button" class="btn btn-danger">{{mwMessengerCancelEditMessageBtnTxt}}</button>
												</div>
											</form>
										</div>
									</div>
								</div>
								<button v-if="currentMessagesPage" class="load-newest-btn" @click.prevent="getNewestMessages(currentChannelId)">{{mwMessengerLoadNewishMessagesBtnTxt}}</button>
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