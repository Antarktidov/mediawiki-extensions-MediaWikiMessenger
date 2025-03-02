<?php

namespace MediaWiki\Extension\MediaWikiMessenger;

use DatabaseUpdater;

class Hooks {
    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        $dbUpdate = new DBUpdate();
        $dbUpdate->onLoadExtensionSchemaUpdates( $updater );
    }
}