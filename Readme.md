#Delta_Shop

_Использует фреймворк [https://symfony.com] версии 4.4_

##Развернуть проект

- Склонироварть репозторий

`
git clone git@github.com:AlessandroWea/delta_shop.git
`

- Установка зависимостей Composer
  
`
  composer install
  `


- Установить npm

`npm install
`

- Запустить команду

`
npm run build
`


### Подключение к БД

- Создайте файл .env.local


- Пропишите строку в фале .env.local
  <code>
  DATABASE_URL="mysql://root:@127.0.0.1:3306/delta_shop?serverVersion=8.0&charset=utf8mb4"
  </code>


- Консольная команда для создания БД

`
bin/console doctrine:database:create
`
- Выполните миграцию

`bin/console doctrine:schema:update --force
`