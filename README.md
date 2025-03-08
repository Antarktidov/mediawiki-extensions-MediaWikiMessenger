Расширение, которое добавляет мессенджер с поддержкой множетсва каналов и вики-разметки в сообщениях.
ДАННОЕ РАСШИРЕНИЕ ЕЩЁ НЕ ГОТОВО К РЕЛИЗУ И НЕ ДОЛЖНО БЫТЬ ИСПОЛЬЗОВАНО НА РЕАЛЬНЫХ (ПРОДАКШЕН) СЕРВЕРАХ.
THIS EXTENSION IS NOT READY FOR RELEASE AND SHOULD NOT BE USED ON PRODUCTION SERVERS.

# Установка
1. Скачайте расширение в папку **extensions** вашей копии MediaWiki с пмощью гита (```git clone https://github.com/Antarktidov/mediawiki-extensions-MediaWikiMessenger.git```) или вручную.
2. Папка с расширением должна лежать внутри папки extensions вашей копии MediaWiki и называться **MediaWikiMessenger**.
3. Подключите расширение, добавив код ```wfLoadExtension( 'MediaWikiMessenger' );``` в файл **LocalSettings.php**, который находится в папке с вашей копией MediaWiki.
4. Запустите скрипт обновления [MediaWiki (updtae.php)](https://www.mediawiki.org/wiki/Manual:Update.php/ru).

# Использование
После установки мессенджера перейдите на страницу **Служебная:Создать канал мессенджера** и создайте канал.
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

  # Сообщения об ошибках и предложениях
  Просьба собщать об ошибках и давать свои предложения по развитию расширения на [специальной github-странице](https://github.com/Antarktidov/mediawiki-extensions-MediaWikiMessenger/issues).
