<?php

namespace MediaWiki\Extension\MediaWikiMessenger;

use DatabaseUpdater;

class DBUpdate implements \MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook {
    public function onLoadExtensionSchemaUpdates( $updater ) {
        $dbType = $updater->getDB()->getType();
        $dir = __DIR__ . '/../sql';

        if ( $dbType !== 'mysql' ) {
            $dir .= "/$dbType";
        }

        $updater->addExtensionTable( 'mw_messenger_channel', "$dir/mw_messenger_channel.sql" );
        $updater->addExtensionTable( 'mw_messenger_message', "$dir/mw_messenger_message.sql" );
        $updater->addExtensionTable( 'mw_messenger_message_revision', "$dir/mw_messenger_message_revision.sql" );
    }
}