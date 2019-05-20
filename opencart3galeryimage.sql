-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 20 2019 г., 21:21
-- Версия сервера: 5.7.26-0ubuntu0.16.04.1
-- Версия PHP: 7.0.33-0ubuntu0.16.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `admin_opencart3gal`
--

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album`
--

CREATE TABLE `oc_album` (
  `album_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_description`
--

CREATE TABLE `oc_album_description` (
  `album_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_filter`
--

CREATE TABLE `oc_album_filter` (
  `album_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_image`
--

CREATE TABLE `oc_album_image` (
  `album_image_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_video` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_image_description`
--

CREATE TABLE `oc_album_image_description` (
  `album_image_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_path`
--

CREATE TABLE `oc_album_path` (
  `album_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_to_layout`
--

CREATE TABLE `oc_album_to_layout` (
  `album_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `oc_album_to_store`
--

CREATE TABLE `oc_album_to_store` (
  `album_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `oc_album`
--
ALTER TABLE `oc_album`
  ADD PRIMARY KEY (`album_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `oc_album_description`
--
ALTER TABLE `oc_album_description`
  ADD PRIMARY KEY (`album_id`,`language_id`),
  ADD KEY `name` (`name`);

--
-- Индексы таблицы `oc_album_filter`
--
ALTER TABLE `oc_album_filter`
  ADD PRIMARY KEY (`album_id`,`filter_id`);

--
-- Индексы таблицы `oc_album_image`
--
ALTER TABLE `oc_album_image`
  ADD PRIMARY KEY (`album_image_id`),
  ADD KEY `product_id` (`album_id`);

--
-- Индексы таблицы `oc_album_path`
--
ALTER TABLE `oc_album_path`
  ADD PRIMARY KEY (`album_id`,`path_id`);

--
-- Индексы таблицы `oc_album_to_layout`
--
ALTER TABLE `oc_album_to_layout`
  ADD PRIMARY KEY (`album_id`,`store_id`);

--
-- Индексы таблицы `oc_album_to_store`
--
ALTER TABLE `oc_album_to_store`
  ADD PRIMARY KEY (`album_id`,`store_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `oc_album`
--
ALTER TABLE `oc_album`
  MODIFY `album_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `oc_album_image`
--
ALTER TABLE `oc_album_image`
  MODIFY `album_image_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
