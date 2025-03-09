<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class GetChannelMessages extends ApiBase {
    public function __construct( ApiMain $main, $name ) {
        parent::__construct( $main, $name );
    }

    public function execute() {

        $user = $this->getUser();

        if (!$user->isAllowed('see_chat')) {
            return;
        }
        $params = $this->extractRequestParams();

        $channel_id = $params['channel_id'];

        try {
            $dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
            $dbr = $dbProvider->getReplicaDatabase();

            $res = $dbr->newSelectQueryBuilder()
            ->select( ['mw_messenger_channel_id', 'name', 'is_chatmods_private', 'is_chatadmins_private',
            'is_writing_restricted_to_chatmods', 'is_writing_restricted_to_chatadmins',
            'is_deleted'] )
            ->from('mw_messenger_channel')
            ->where('is_deleted = 0')
            ->where("mw_messenger_channel_id = $channel_id")
            ->fetchResultSet();

            $rows = [];

            foreach ( $res as $row ) {
                array_push($rows, $row);
            }

            if ((!$user->isAllowed('view_chatadmins_private_channels')) &&
                $rows->is_chatadmins_private ) {
                return;
            }

            if ((!$user->isAllowed('view_chatmods_private_channels')) &&
                $rows->is_chatmods_private ) {
                return;
            }

            $this->getResult()->addValue( null, $this->getModuleName(), [
                'channel' => $rows,
            ] );

            $res = $dbr->newSelectQueryBuilder()
            ->select( ['mw_messenger_message_id', 'mw_messenger_message_user_id',
            'mw_messenger_message_channel_id', 'is_editing_restricted_to_chatmods',
            'is_editing_restricted_to_chatadmins',
            'is_deleted', 'created_at'] )
            ->from('mw_messenger_message')
            ->where('is_deleted = 0')
            ->where("mw_messenger_message_channel_id = $channel_id")
            ->orderBy('mw_messenger_message_id', 'DESC') // Сортировка в обратном порядке
            ->limit($params['limit'] ?? 10) // Лимит на количество сообщений
            ->offset(($params['page'] ?? 0) * ($params['limit'] ?? 10)) // Смещение для пагинации
            ->fetchResultSet();

            $messages = [];

            foreach ( $res as $row ) {
                $mw_messenger_message_user_id = $row->mw_messenger_message_user_id;

                $userRes = $dbr->newSelectQueryBuilder()
                ->select( ['user_name'] )
                ->from('user')
                ->where("user_id = $mw_messenger_message_user_id")
                ->fetchResultSet();

                foreach ( $userRes as $userRow ) {
                    $row->user_name = $userRow->user_name;
                }

                $mw_messenger_message_revision_message_id = $row->mw_messenger_message_id;

                $revisionRes = $dbr->newSelectQueryBuilder()
                ->select( ['mw_messenger_message_revision_text', 'mw_messenger_message_revision_message_id'] )
                ->from('mw_messenger_message_revision')
                ->where("mw_messenger_message_revision_message_id = $mw_messenger_message_revision_message_id")
                ->fetchResultSet();

                foreach ( $revisionRes as $revisionRow ) {
                    $row->mw_messenger_message_revision_text = $revisionRow->mw_messenger_message_revision_text;
                    $row->mw_messenger_message_revision_message_id = $revisionRow->mw_messenger_message_revision_message_id;
                }

                // Получение кастомных реакций для сообщения
                $customReactionsRes = $dbr->newSelectQueryBuilder()
                ->select([
                    'mw_messenger_custom_reaction_filename',
                    'mw_messenger_custom_reaction_user_id'
                ])
                ->from('mw_messenger_custom_reaction')
                ->where("mw_messenger_custom_reaction_message_id = $mw_messenger_message_revision_message_id")
                ->fetchResultSet();

                $customReactions = [];
                foreach ( $customReactionsRes as $reactionRow ) {
                    $filename = $reactionRow->mw_messenger_custom_reaction_filename;
                    $userId = $reactionRow->mw_messenger_custom_reaction_user_id;
                    if (!isset($customReactions[$filename])) {
                        $customReactions[$filename] = [];
                    }
                    $customReactions[$filename][] = (int)$userId;
                }
                $row->customReactions = $customReactions;

                // Получение стандартных реакций для сообщения
                $standardReactionsRes = $dbr->newSelectQueryBuilder()
                ->select([
                    'mw_messenger_standard_reaction_filename',
                    'mw_messenger_standard_reaction_user_id'
                ])
                ->from('mw_messenger_standard_reaction')
                ->where("mw_messenger_standard_reaction_message_id = $mw_messenger_message_revision_message_id")
                ->fetchResultSet();

                $standardReactions = [];
                foreach ( $standardReactionsRes as $reactionRow ) {
                    $filename = $reactionRow->mw_messenger_standard_reaction_filename;
                    $userId = $reactionRow->mw_messenger_standard_reaction_user_id;
                    if (!isset($standardReactions[$filename])) {
                        $standardReactions[$filename] = [];
                    }
                    $standardReactions[$filename][] = (int)$userId;
                }
                $row->standardReactions = $standardReactions;

                array_push($messages, $row);
            }
            /*
             Работай с переменной $messages сдесь
             */

            $this->getResult()->addValue( null, $this->getModuleName(), [
                'messages' => $messages,
            ]);

        } catch ( DBQueryError $e ) {
            $this->getResult()->addValue( null, $this->getModuleName(), [
                'result' => 'error',
                'error' => $e->getMessage()
            ] );
        }
    }

    /** @inheritDoc */
    public function getAllowedParams() {
        return [
            'channel_id' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ],
            'page' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_DFLT => 0 // Значение по умолчанию
            ],
            'limit' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_DFLT => 10 // Значение по умолчанию
            ],
            'is_last' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'boolean'
            ]
        ];
    }
}