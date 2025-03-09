<?php

namespace MediaWiki\Extension\MediaWikiMessenger\Api;

use ApiBase;
use ApiMain;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBQueryError;

class AddReactionToMessage extends ApiBase {
    public function __construct( ApiMain $main, $name ) {
        parent::__construct( $main, $name );
    }

    public function execute() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = $this->getUser();

            if (!$user->isAllowed('see_chat')) {
                return;
            }

            $user_id = $user->getId();

            $message_id = $this->test_input($_POST["message_id"]);
            $reaction = $this->test_input($_POST["reaction"]);
            $reaction_type = $this->test_input($_POST["reaction_type"]);

            try {
                $dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_PRIMARY);

                if ($this->validateUnicodeEmoji($reaction)) {

                    if ($reaction_type === 'standard') {
                        // Проверка, что такой реакции с типом standard не существует
                        $res = $dbw->newSelectQueryBuilder()
                            ->select(['mw_messenger_standard_reaction_id'])
                            ->from('mw_messenger_standard_reaction')
                            ->where([
                                'mw_messenger_standard_reaction_user_id' => $user_id,
                                'mw_messenger_standard_reaction_message_id' => $message_id,
                                'mw_messenger_standard_reaction_filename' => $reaction
                            ])
                            ->caller(__METHOD__)
                            ->fetchField();

                        if (!$res) {
                            // Добавление реакции в базу данных mw_messenger_standard_reaction
                            $dbw->newInsertQueryBuilder()
                                ->insert('mw_messenger_standard_reaction')
                                ->row([
                                    'mw_messenger_standard_reaction_user_id' => $user_id,
                                    'mw_messenger_standard_reaction_message_id' => $message_id,
                                    'mw_messenger_standard_reaction_filename' => $reaction
                                ])
                                ->caller(__METHOD__)
                                ->execute();
                        }
                                            
                    }
                } else if ($reaction_type === 'custom') {
                    // Проверка, что такой реакции с типом custom не существует
                    $res = $dbw->newSelectQueryBuilder()
                        ->select(['mw_messenger_custom_reaction_id'])
                        ->from('mw_messenger_custom_reaction')
                        ->where([
                            'mw_messenger_custom_reaction_user_id' => $user_id,
                            'mw_messenger_custom_reaction_message_id' => $message_id,
                            'mw_messenger_custom_reaction_filename' => $reaction
                        ])
                        ->caller(__METHOD__)
                        ->fetchField();

                    if (!$res) {
                        // Добавление реакции в базу данных mw_messenger_custom_reaction
                        $dbw->newInsertQueryBuilder()
                            ->insert('mw_messenger_custom_reaction')
                            ->row([
                                'mw_messenger_custom_reaction_user_id' => $user_id,
                                'mw_messenger_custom_reaction_message_id' => $message_id,
                                'mw_messenger_custom_reaction_filename' => $reaction
                            ])
                            ->caller(__METHOD__)
                            ->execute();
                    }
                }
            } catch ( DBQueryError $e ) {
                $this->getResult()->addValue( null, $this->getModuleName(), [
                    'result' => 'error',
                    'error' => $e->getMessage()
                ] );
            }
        }
    }

    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        //$data = htmlspecialchars($data);
        return $data;
    }

    private function validateUnicodeEmoji($char) {
        // Регулярное выражение для эмодзи с учетом комбинированных символов
        /*$pattern = '/^(\p{Emoji_Modifier_Base}\p{Emoji_Modifier}?|\p{Emoji_Presentation}|\p{Emoji}\x{FE0F})$/u';
        
        if (!preg_match($pattern, $char)) {
            return ['valid' => false, 'error' => 'Invalid emoji character'];
        }*/
        
        // Проверка на разрешенные диапазоны
        $code = mb_ord($char, 'UTF-8');
        $allowedRanges = [
            [0x1F600, 0x1F64F],  // Smileys
            [0x1F300, 0x1F5FF],  // Misc Symbols
            [0x1F600, 0x1F64F],  // Основные лица
            [0x1F910, 0x1F92F],  // Дополнительные лица
            [0x1F970, 0x1F971],  // Влюбленные лица
            [0x1F973, 0x1F976],  // Другие эмоции
            [0x1F9B0, 0x1F9B9],  // Волосы и тело
            [0x1F9D0, 0x1F9DF],  // Фантастические существа
            [0x1F400, 0x1F43F],  // Животные
            [0x1F980, 0x1F98F],  // Насекомые
            [0x1F990, 0x1F9BF],  // Другие природа
            [0x1F32D, 0x1F37F],  // Еда и напитки
            [0x1F680, 0x1F6FF],  // Транспорт
            [0x1F3A0, 0x1F3FF],  // Места и объекты
            [0x1F1E6, 0x1F1FF],  // Флаги стран
            [0x1F3F4, 0x1F3F4],  // Особые флаги
        ];
        
        foreach ($allowedRanges as $range) {
            if ($code >= $range[0] && $code <= $range[1]) {
                return true;
            }
        }
        
        return false;
    }

    /** @inheritDoc */
    public function getAllowedParams() {
        return [
            'message_id' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ],
            'reaction' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'reaction_type' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
        ];
    }
}