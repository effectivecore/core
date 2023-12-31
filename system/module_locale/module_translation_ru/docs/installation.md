

Установка
=====================================================================


Установка с помощью Docker
---------------------------------------------------------------------

Если у Вас в операционной системе имеется установленный и **запущенный** Docker,
Вы можете выполнить команды, которые приведут к сборке нового образа из эталонного
дистрибутива Linux. В этот образ будут установлены NGINX, PHP, Git, а через
репозиторий будет выгружена последняя версия данной системы.
Далее достаточно будет открыть браузер и набрать адрес `127.0.0.1:8080`
по которому Вы увидите страницу установки системы.

Выполните следующие команды для запуска системы через Docker:

    mkdir docker-effcore
    cd docker-effcore
    git clone https://github.com/effectivecore/docker.git .
    ./build.sh


Установка с помощью Composer
---------------------------------------------------------------------

Создание нового проекта в текущей директории:

    composer create-project effcore/core:dev-main .

Установка всех сторонних библиотек в случае когда проект разворачивается из архива:

    composer install

Пример установки библиотеки типа `effcore-bundle` или `effcore-module` (библиотека
будет размещена в директории `/modules`):

    composer require effcore/examples:dev-main

Пример установки универсальной сторонней библиотеки (библиотека
будет размещена в директории `/modules/vendors/packages`):

    composer require Vendor\Model


Установка с помощью пакетного архива
---------------------------------------------------------------------

Действия перед установкой:
- Когда Вы будете использовать MySQL для хранения данных, проверьте,
  есть ли у Вас учётные данные для этого (логин, пароль, имя базы данных).
  Если у Вас нет учётных данных, проверьте, есть ли у Вас база данных
  и учётная запись пользователя для неё через панель веб-хостинга (например,
  DirectAdmin, ISPmanager, cPanel, Plesk и другие).
  Если у Вас нет базы данных, создайте одну, а также учётную запись
  пользователя для работы с ней.
- Проверьте, есть ли у Вас учётные данные (имя узла сети, логин, пароль)
  для службы FTP, работающей на сервере веб-хостинга.
  Если у Вас нет учётных данных, создайте новый аккаунт
  пользователя FTP или сбросьте пароль существующего аккаунта
  через панель веб-хостинга.
- Проверьте, есть ли у вас FTP-клиент для передачи файлов
  на веб-сервер. Например: Transmit (mac), Cyberduck (mac|win),
  FileZilla (mac|win|linux) и другие.
- Проверьте, включена ли видимость скрытых файлов в архиваторе,
  операционной системе и FTP-клиенте.

Действия для установки:
- Скачайте дистрибутив системы в архиве с официального
  сайта этого проекта.
- Распакуйте архив системы в какой-нибудь локальный каталог.
  Все файлы должны быть распакованы (включая невидимые файлы как `.htaccess` и другие).
  Если Вы не можете видеть невидимые файлы в вашем архиваторе, сделайте их видимыми.
  Если Вы не можете видеть невидимые файлы в вашей ОС, сделайте их видимыми.
- Установите соединение с веб-сервером по протоколу FTP.
  Рекомендуется использовать "SFTP" или "FTP with implicit SSL", или
  "FTP with TSL/SSL".
- Через FTP-клиент перейдите в корневой каталог веб на веб-сервере
  и скопируйте все файлы этой системы из локального каталога
  в корневой каталог веб.
- Откройте веб-браузер и перейдите на свой домен (который Вы будете
  использовать для развёртывания вашего сайта).
- Если PHP включён, Вы увидите страницу установки системы.
- Если некоторые необходимые модули PHP отсутствуют, Вы получите
  сообщение о том, какие модули должны быть включены.
  Через панель веб-хостинга Вы можете включить отсутствующие
  модули и продолжить установку.
  На первое время также рекомендуется включить `display_errors`,
  `log_errors` и установить `error_reporting` в `E_ALL`.

