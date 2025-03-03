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
        $out->addHTML(	
						'<div id="mw-messenger">
							<div id="mw-messenger-channels-list">
								<ul v-for="channel in channels.channels">
									<li><a @click.prevent="getChannelMessages(channel.id)" href="#">{{channel.name}}</a></li>
								</ul>
							</div>
							<div id="mw-messenger-channel-area">
								<div id="mw-messenger-channel-messages">
									<div v-for="message in reversedMessages" class="mw-messenger-message" v-html="message.parsedMessageText"></div>
								</div>
							</div>
						</div>'
					);
	}
    	/** @inheritDoc */
	protected function getGroupName() {
		return 'other';
	}
}