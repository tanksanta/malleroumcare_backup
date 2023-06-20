USE malleroumcare;

ALTER TABLE `g5_write_care_files`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_care_news`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_center_case`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_center_education`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_center_meet`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_center_story`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_copy`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_cs_center`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_event`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_event_board`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_event_ended`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_faq`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_free`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_gallery`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_info`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_lab`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_notice`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_notice_test2`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_notice_user`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_proposal`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_qa`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_rental`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_rental_board`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_rental_ended`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_sample`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_sample_board`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_sample_ended`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;

ALTER TABLE `g5_write_used_market`
	CHANGE COLUMN `wr_content` `wr_content` LONGTEXT NOT NULL COLLATE 'utf8_general_ci' AFTER `wr_subject`;
