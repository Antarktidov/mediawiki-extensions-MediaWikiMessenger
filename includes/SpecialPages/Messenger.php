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
								<ul>
									<li><a v-for="channel_name in channels[\'channel_names\']" href="#">{{channel_name}}</a></li>
								</ul>
							</div>
							<div id="mw-messenger-channel-area">
								<div id="mw-messenger-channel-messages">
									<div id="mw-messenger-message">Тут должен быть текст сообщения</div>
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