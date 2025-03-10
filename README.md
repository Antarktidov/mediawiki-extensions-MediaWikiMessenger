Расширение, которое добавляет мессенджер с поддержкой множетсва каналов и вики-разметки в сообщениях.

ДАННОЕ РАСШИРЕНИЕ ЕЩЁ НЕ ГОТОВО К РЕЛИЗУ И НЕ ДОЛЖНО БЫТЬ ИСПОЛЬЗОВАНО НА РЕАЛЬНЫХ (ПРОДАКШЕН) СЕРВЕРАХ.

THIS EXTENSION IS NOT READY FOR RELEASE AND SHOULD NOT BE USED ON PRODUCTION SERVERS.

# Установка
1. Скачайте расширение в папку **extensions** вашей копии MediaWiki с пмощью гита (```git clone https://github.com/Antarktidov/mediawiki-extensions-MediaWikiMessenger.git```) или вручную.
2. Папка с расширением должна лежать внутри папки extensions вашей копии MediaWiki и называться **MediaWikiMessenger**.
3. Подключите расширение, добавив код ```wfLoadExtension( 'MediaWikiMessenger' );``` в файл **LocalSettings.php**, который находится в папке с вашей копией MediaWiki.
4. Запустите скрипт обновления [MediaWiki (updtae.php)](https://www.mediawiki.org/wiki/Manual:Update.php/ru).
5. Скачайте менеджер пакетов для PHP Composer, если он у вас ещё не установлен.
6. Откройте в терминале папку с расширением и запустите из командной строки следующую команду:
```bash
composer install
```

# Использование
После установки мессенджера перейдите на страницу **Служебная:Создать канал мессенджера** и создайте канал.
Для работы сокетов откройте папку с расширением, перейдите в подпапку **includes** и далее в подпапку **sockets** и запустите в командной строке следующую команду:
```bash
php Chat.php
```
Данная команда должна быть запущена всё время, пока вы используете чат.
Далее перейдите на Служебная:Мессенджер, выберите канал и начните использовать мессенджер. Вы можете использовать вики-разметку в своих сообщениях.

# Совместимость с другими расширениями
Данное расширение поддерживает:
1. Аватрки из расширения [SocialProfile](https://www.mediawiki.org/wiki/Extension:SocialProfile/ru)
2. [Портабельные инфбокосы](https://www.mediawiki.org/wiki/Extension:PortableInfobox/ru)
3. Спойлеры из расширения [SpoilerSpan](https://github.com/Antarktidov/mediawiki-extensions-SpoilerSpan)

# Совместимость с требуемым ПО
Данное расширение было протестировано вместе с:
1. MediaWiki 1.43.0
2. PHP 8.12.2
3. MySQL
4. Windows (сервер и клиент)
5. Старый скин Vector

# Сообщения об ошибках и предложениях
Просьба собщать об ошибках и давать свои предложения по развитию расширения на [специальной github-странице](https://github.com/Antarktidov/mediawiki-extensions-MediaWikiMessenger/issues).

# Обновления
## Версия 1.1.0
* Добавлены реакции под сообщениями как в дискорде и телегрраме. Доступны также кастомные реакции. Чтобы добавить кастомные реакции, перейдите на страницу **MediaWiki:MessengerReactions** и добавтье код по следующему образцу:
```wikitext
:название_кастоной_реакции_1: Имя_файла_кастомной_реакции_1.расширние_файла
:название_кастоной_реакции_2: Имя_файла_кастомной_реакции_2.расширние_файла
```
Например:
```wikitext
:kekw: Kekw.png
:ut: Unity-Chan.png
```
Важно! Используйте нижнее подчёркивание ```_``` вместо пробелов в названиях реакций и названиях файлов реакций.

  ![реакции](https://raw.githubusercontent.com/Antarktidov/mediawiki-extensions-MediaWikiMessenger/refs/heads/dev/images/MWMessengerReactions.png)

* Удалены заменители веб-сокетов.

  Веб-сокеты (или их заменители) позволяют вам обмениваться сообщениями вместе с другими пользователями в режиме реального времени. Другой пользователь написал, и у вас сразу появилось сообщение (если вы с ноим в чате). Увы, из-за заменителей сокетов чат работал некорректно, поэтому решено было их удалить, так что теперь вы сможете общаться только сами с собой. Но в планах добавить настоящие веб-сокеты. Следите за обновлениями.
  
## Весрия 1.2.0
* Добавлены сокеты. Теперь можно общаться сразу в нескольких вкладках с разных аккаунтов и сообщения, отправленые из одной вкладки, будут приходить в другую/все остальыне.
  
Известные проблеммы:
* Не получается удалить сообщение, отправленное другим пользователем после открытия канала (если есть права на удаление чукжих сообщений)
* Данное расширение по прежнему не безапасно, а с добавлением сокетов безопасность ухудшилось. Поэтому не рекомендуется использовать мессенжер на реальных (продакшен) серверах.

### Заключительные слова от разработчка.
Мне жаль это признавать, но похоже, тут, как говорится, наши полномочия всё. Расширение достигло такого уровня, что дальше его код становится трудно поддерживать и рефакторить с моими текущими знаниями. Разработка расширения приостановлена навсегда или на неопределённый срок. Спасибо всем, кто был с нами. Если вы были...
