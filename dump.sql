-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 03 2022 г., 22:25
-- Версия сервера: 5.7.36-0ubuntu0.18.04.1
-- Версия PHP: 8.0.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `vkedu`
--

-- --------------------------------------------------------

--
-- Структура таблицы `access_tokens`
--

CREATE TABLE `access_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `without_limits` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `access_tokens`
--

INSERT INTO `access_tokens` (`id`, `user_id`, `access_token`, `time`, `ip`, `without_limits`) VALUES
(5, 2, '1d485fc83635ae277be4a8116a318223434baf8067175876', 1649008376, '178.49.27.8', 0),
(12, 1, '6b8200754ecbee915573dc9dc1a5f2aaa06419513a2afcf3', 1649013561, '178.70.153.245', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `file_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`id`, `file_id`, `user_id`, `filename`) VALUES
(2, '6249d552d0b15', 1, 'Files/6249d552cf937.jpeg'),
(3, '6249d58d59931', 1, 'Files/6249d58d58ff0.jpeg'),
(4, '6249db9e949c3', 2, 'Files/6249db9e94717.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `newsfeed_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `newsfeed_id`) VALUES
(7, 2, 1),
(8, 2, 2),
(9, 1, 2),
(10, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `newsfeed`
--

CREATE TABLE `newsfeed` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `text` text NOT NULL,
  `files` text,
  `views` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `newsfeed`
--

INSERT INTO `newsfeed` (`id`, `user_id`, `time`, `text`, `files`, `views`) VALUES
(1, 1, 1649005918, 'Первый пост на этом сайте!', '6249d552d0b15', 103),
(2, 2, 1649007440, 'Крашаааа', 'NULL', 71);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nickname` varchar(25) NOT NULL,
  `photo_id` varchar(50) DEFAULT NULL,
  `reg_time` int(11) NOT NULL,
  `reg_ip` varchar(100) NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `nickname`, `photo_id`, `reg_time`, `reg_ip`, `login`, `password`) VALUES
(1, 'kkphp', '6249d552d0b15', 1649005713, '178.70.153.245', 'i@korolevsky.me', '$2y$10$OWUofIcOozqqfb7yqb448ue3KjT70PSaABY4D8ShVpAaHq0GAenZ.'),
(2, 'vlados', '6249db9e949c3', 1649007412, '178.49.27.8', 'vladik@yandex.ru', '$2y$10$wzEYgo3L75OPhFKvbciOgeTicEZmixEJ3CrD0UrODFkuGSQs7rOSq');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `access_tokens`
--
ALTER TABLE `access_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `newsfeed`
--
ALTER TABLE `newsfeed`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `access_tokens`
--
ALTER TABLE `access_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `newsfeed`
--
ALTER TABLE `newsfeed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
