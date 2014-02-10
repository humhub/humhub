-- phpMyAdmin SQL Dump
-- version 4.0.0-rc2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. Sep 2013 um 06:31
-- Server Version: 5.1.66-0+squeeze1
-- PHP-Version: 5.3.3-7+squeeze15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



--
-- Datenbank: `zamm_clean`
--

--
-- Daten für Tabelle `group`
--

INSERT INTO `group` (`id`, `space_id`, `name`, `description`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, NULL, 'Example Group', '', '2012-10-16 01:06:19', 1, '2012-10-16 01:06:19', 1);

--
-- Daten für Tabelle `registry`
--

INSERT INTO `registry` (`id`, `name`, `value`, `value_text`, `module_id`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'dbVersion', '20', NULL, NULL, '0000-00-00 00:00:00', 0, '2013-06-08 15:15:45', 0);

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `guid`, `user_invite_id`, `wall_id`, `group_id`, `status`, `super_admin`, `username`, `email`, `password`, `auth_mode`, `firstname`, `lastname`, `title`, `street`, `zip`, `city`, `country`, `state`, `about`, `tags`, `phone_private`, `phone_work`, `mobile`, `fax`, `language`, `im_skype`, `im_msn`, `im_icq`, `im_xmpp`, `url`, `url_facebook`, `url_linkedin`, `url_xing`, `url_youtube`, `url_vimeo`, `url_flickr`, `url_myspace`, `url_googleplus`, `url_twitter`, `receive_email_notifications`, `receive_email_messaging`, `receive_email_activities`, `last_activity_email`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'b2fc71a5-abe6-adad-805f-588de32036f4', NULL, 2, 1, 1, 1, 'admin', 'l.bartholemy@zeros.ones.de', '___enc___08a593a5bec48fa3eb0c1a697968638527e83f81', 'local', 'System', 'Admin', '', '', '', 'Server', 'Germany', '', NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 3, 3, 3, '2013-06-17 07:05:01', '2012-10-16 01:05:08', NULL, '2013-06-17 05:22:54', 1);

