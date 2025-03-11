<?php
/**
 * Special page aliases for the MediaWikiMessenger extension.
 *
 * @file
 * @ingroup Extensions
 */

$specialPageAliases = [];

/** English */
$specialPageAliases['en'] = [
	'Messenger' => [ 'Messenger' ],
	'CreateMessengerChannel' => [ 'CreateMessengerChannel' ],
	'DeleteMessengerChannel' => [ 'DeleteMessengerChannel' ],
	'MessengerMessageHistory' => [ 'MessengerMessageHistory' ],
];

/** Russian */
$specialPageAliases['ru'] = [
	'Messenger' => [ 'Мессенджер' ],
	'CreateMessengerChannel' => [ 'Создать канал мессенджера' ],
	'DeleteMessengerChannel' => [ 'Удалить канал мессенджера' ],
	'MessengerMessageHistory' => [ 'История сообщения мессенджера' ],
];