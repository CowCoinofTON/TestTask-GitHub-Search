CREATE TABLE `search_results` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `search_query` varchar(255) NOT NULL,
 `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci