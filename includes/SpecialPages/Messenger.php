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
		$out->addModules(['ext.SpoilerSpan']);
		$out->setPageTitleMSg( $this->msg( 'mw-messenger-special-page' ) );
        $out->addHTML("<h2>This is VueExampleSpecialPage</h2>");
		$out->addHTML('<div v-cloak id="vue-example-special-page">
        <p>message_from_vue_js: {{message_from_vue_js}}</p>
        <div v-html="parsedWikiText"></div>
    </div>');
	}
    	/** @inheritDoc */
	protected function getGroupName() {
		return 'other';
	}
}