<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class GetChannels extends ApiBase {
    public function __construct( ApiMain $main, $name ) {
        parent::__construct( $main, $name );
    }

    public function execute() {

        $user = $this->getUser();

        if (!$user->isAllowed('see_chat')) {
            return;
        }

        try {
            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
            $dbr = $dbProvider->getReplicaDatabase();

            $res = $dbr->newSelectQueryBuilder()
            ->select( ['mw_messenger_channel_id', 'name', 'is_chatmods_private', 'is_chatadmins_private',
            'is_writing_restricted_to_chatmods', 'is_writing_restricted_to_chatadmins',
            'is_deleted'] )
            ->from('mw_messenger_channel')
            ->where('is_deleted = 0')
            ->fetchResultSet();

            $channel_names = [];

            foreach ( $res as $row ) {
                array_push($channel_names, $row->name);
            }

            $this->getResult()->addValue( null, $this->getModuleName(), [
                'channel_names' => $channel_names,
            ] );
        } catch ( DBQueryError $e ) {
            $this->getResult()->addValue( null, $this->getModuleName(), [
                'result' => 'error',
                'error' => $e->getMessage()
            ] );
        }
    }

    public function getAllowedParams() {
        return [];
    }
}