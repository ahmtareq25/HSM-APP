INSERT INTO `companies` (`id`, `name`, `email`, `created_at`, `updated_at`) VALUES
    (1, 'Sipay Electronics Ltd.', 'company@sipay.com','2015-11-04 10:52:01', '2015-11-04 10:52:01');

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
    ('96962cdc-58ac-44e4-ab02-c07772a0e430', NULL, 'Sipay API Password Grant Client', 'nkC1MygeCFXVOMCLSwpXptTj0ShBy0zh1YFHtfwI', 'users', 'http://localhost', 0, 1, 0, '2022-06-20 03:30:19', '2022-06-20 03:30:19');

INSERT INTO `users`
(`id`, `company_id`, `name`, `usergroup_ids`, `email`, `email_verified_at`, `password`, `language`, `permission_version`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 1, 'Sipay', '2', 'admin@sipay.com', NULL, '$2y$10$uoDrkMBhBQmr./Ae30acou9ddAKvDKn0N.UJQNwOjU0tS/NvPKTt2', 'en', 0, NULL, '2022-06-20 03:30:10', NULL);

# create app_settings table
CREATE TABLE `app_settings` (`id` INT NOT NULL AUTO_INCREMENT , `company_id` INT NOT NULL DEFAULT '0' COMMENT 'from pk of company table' , `user_id` INT NOT NULL DEFAULT '0' COMMENT 'from pk of user table' , `client_id` VARCHAR(100) NOT NULL , `client_secret` VARCHAR(100) NOT NULL , `current_token` VARCHAR(255) NOT NULL , `status` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '1 = Active, 0 = Inactive' , `created_at` DATETIME NOT NULL , `updated_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `app_settings` CHANGE `current_token` `current_token` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

INSERT INTO `app_settings` (`id`, `company_id`, `user_id`, `client_id`, `client_secret`, `current_token`, `status`, `created_at`, `updated_at`) VALUES (NULL, '1', '2', 'ac1e1d06c1eb175cf1b49f085c440469', '1d179a6cb2820221bb34ffe02a01412a', NULL, '1', '2022-06-20 11:00:58.000000', NULL);

CREATE TABLE `card_informations` (`id` INT(11) NOT NULL AUTO_INCREMENT,`application_id` INT(11) NOT NULL,`hsm_token` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_general_ci',`hsm_data` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',`brand_token` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',`created_at` DATETIME NULL DEFAULT NULL,`updated_at` DATETIME NULL DEFAULT NULL,PRIMARY KEY (`id`) USING BTREE) COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
ALTER TABLE `app_settings` ADD COLUMN `whitelist_ips` TEXT NULL DEFAULT NULL AFTER `current_token`;


