CREATE TABLE `tokens` (
  `token` varchar(100) CHARACTER SET utf8 NOT NULL,
  `user_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `expires` bigint(20) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;