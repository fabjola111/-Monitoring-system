
CREATE TABLE `alarme` (
  `id` int(11) NOT NULL,
  `mesazh_id` int(11) NOT NULL,
  `femije_id` int(11) NOT NULL,
  `prind_id` int(11) NOT NULL,
  `niveli` enum('low','medium','high') DEFAULT 'low',
  `pershkrimi` text DEFAULT NULL,
  `data_krijimit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alarme`
--

INSERT INTO `alarme` (`id`, `mesazh_id`, `femije_id`, `prind_id`, `niveli`, `pershkrimi`, `data_krijimit`) VALUES
(1, 3, 3, 2, 'medium', 'Mesazh me fjalë të ndaluara: idiot', '2026-06-03 13:54:23'),
(2, 9, 3, 2, 'medium', 'Mesazh me fjalë të ndaluara: budalla', '2026-06-03 15:34:20'),
(3, 12, 4, 2, 'medium', 'Mesazh me fjalë të ndaluara: ndyresire', '2026-06-07 13:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `code_hash`, `expires_at`, `created_at`) VALUES
(7, 7, '2c38092244fb8a898b21125d17f27c9405e5bf2f5da31727504b96ef185a1d47', '2026-06-13 23:40:20', '2026-06-13 21:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `fjale_ndaluara_prind`
--

CREATE TABLE `fjale_ndaluara_prind` (
  `id` int(11) NOT NULL,
  `prind_id` int(11) NOT NULL,
  `fjala` varchar(100) NOT NULL,
  `lloji` varchar(100) NOT NULL DEFAULT 'personalizuar',
  `data_krijimit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fjale_ndaluara_prind`
--

INSERT INTO `fjale_ndaluara_prind` (`id`, `prind_id`, `fjala`, `lloji`, `data_krijimit`) VALUES
(1, 2, 'ndyresire', 'ofendim', '2026-06-04 15:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `user1`, `user2`, `created_at`) VALUES
(1, 3, 4, '2026-06-03 13:51:00'),
(2, 3, 6, '2026-06-03 15:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, 4, 3, 'accepted', '2026-06-03 13:00:00'),
(2, 6, 3, 'accepted', '2026-06-03 15:26:55');


-- --------------------------------------------------------

--
-- Table structure for table `mesazhe`
--

CREATE TABLE `mesazhe` (
  `id` int(11) NOT NULL,
  `dergues_id` int(11) NOT NULL,
  `marres_id` int(11) NOT NULL,
  `teksti` text NOT NULL,
  `status` enum('normal','i_dyshimte','ofendues') DEFAULT 'normal',
  `score` int(11) DEFAULT 0,
  `fjale_ndaluara` text DEFAULT NULL,
  `lloje_fjalesh` text DEFAULT NULL,
  `lsf_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lsf_json`)),
  `is_read` tinyint(4) DEFAULT 0,
  `data_krijimit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mesazhe`
--

INSERT INTO `mesazhe` (`id`, `dergues_id`, `marres_id`, `teksti`, `status`, `score`, `fjale_ndaluara`, `lloje_fjalesh`, `lsf_json`, `is_read`, `data_krijimit`) VALUES
(1, 4, 3, 'hii', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"hii\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 1, '2026-06-03 13:51:19'),
(2, 3, 4, 'ckemi', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"ckemi\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 1, '2026-06-03 13:51:35'),
(3, 3, 4, 'ti je idiot', 'i_dyshimte', 40, 'idiot', 'ofendim', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"ti\",\"je\",\"idiot\"],\"bad_words\":[\"idiot\"],\"bad_word_types\":[\"ofendim\"],\"uppercase_count\":0},\"syntactic_features\":[\"strukturë e drejtpërdrejtë ndaj personit tjetër\"],\"score\":40,\"status\":\"i_dyshimte\"}', 1, '2026-06-03 13:54:23'),
(4, 6, 3, 'hiii', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"hiii\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 1, '2026-06-03 15:27:45'),
(5, 3, 6, 'ckemi', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"ckemi\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 1, '2026-06-03 15:28:16'),
(6, 3, 6, 'budalle', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"budalle\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 1, '2026-06-03 15:28:25'),
(7, 3, 6, 'buda', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"buda\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 0, '2026-06-03 15:29:36'),
(8, 3, 6, 'budalle', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"budalle\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 0, '2026-06-03 15:29:44'),
(9, 3, 6, 'budalla', 'i_dyshimte', 30, 'budalla', 'ofendim', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"tokens\":[\"budalla\"],\"bad_words\":[\"budalla\"],\"bad_word_types\":[\"ofendim\"],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":30,\"status\":\"i_dyshimte\"}', 0, '2026-06-03 15:34:20'),
(10, 4, 3, 'me fal', 'normal', 0, '', '', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"original_text\":\"me fal\",\"normalized_text\":\"me fal\",\"tokens\":[\"me\",\"fal\"],\"bad_words\":[],\"bad_word_types\":[],\"uppercase_count\":0},\"syntactic_features\":[],\"score\":0,\"status\":\"normal\"}', 0, '2026-06-07 13:55:13'),
(11, 4, 3, 'libri eshte idjot', 'normal', 5, 'idjot', 'ofendim', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"original_text\":\"libri eshte idjot\",\"normalized_text\":\"libri eshte idjot\",\"tokens\":[\"libri\",\"eshte\",\"idjot\"],\"bad_words\":[\"idjot\"],\"bad_word_types\":[\"ofendim\"],\"uppercase_count\":0},\"syntactic_features\":[\"fjala e ndaluar përdoret për objekt jo-personal\"],\"score\":5,\"status\":\"normal\"}', 0, '2026-06-07 13:55:20'),
(12, 4, 3, 'ti je ndyresire', 'i_dyshimte', 45, 'ndyresire', 'ofendim', '{\"algorithm\":\"LSF - Lexical Features dhe Syntactic Features\",\"lexical_features\":{\"original_text\":\"ti je ndyresire\",\"normalized_text\":\"ti je ndyresire\",\"tokens\":[\"ti\",\"je\",\"ndyresire\"],\"bad_words\":[\"ndyresire\"],\"bad_word_types\":[\"ofendim\"],\"uppercase_count\":0},\"syntactic_features\":[\"fjala e ndaluar i drejtohet një personi\",\"strukturë e drejtpërdrejtë ndaj personit tjetër\"],\"score\":45,\"status\":\"i_dyshimte\"}', 0, '2026-06-07 13:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 3, '📩 ilir të ka dërguar një ftesë për shok', 'friend_request', 1, '2026-06-03 13:00:00'),
(2, 4, '📩 Ftesa jote u pranua nga ola', 'accepted', 1, '2026-06-03 13:51:00'),
(3, 3, 'ilir të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 13:51:19'),
(4, 4, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 13:51:35'),
(5, 4, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 13:54:23'),
(6, 2, 'Alert: fëmija ola përdori komunikim të papërshtatshëm (i_dyshimte).', 'alert_komunikimi', 1, '2026-06-03 13:54:23'),
(7, 3, '📩 suzi të ka dërguar një ftesë për shok', 'friend_request', 1, '2026-06-03 15:26:55'),
(8, 6, '📩 Ftesa jote u pranua nga ola', 'accepted', 1, '2026-06-03 15:27:04'),
(9, 3, 'suzi të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:27:45'),
(10, 6, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:28:16'),
(11, 6, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:28:25'),
(12, 6, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:29:36'),
(13, 6, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:29:44'),
(14, 6, 'ola të ka dërguar një mesazh.', 'new_message', 1, '2026-06-03 15:34:20'),
(15, 2, 'Alert: fëmija ola përdori komunikim të papërshtatshëm (i_dyshimte).', 'alert_komunikimi', 1, '2026-06-03 15:34:20'),
(16, 3, 'ilir të ka dërguar një mesazh.', 'new_message', 0, '2026-06-07 13:55:13'),
(17, 3, 'ilir të ka dërguar një mesazh.', 'new_message', 0, '2026-06-07 13:55:20'),
(18, 3, 'ilir të ka dërguar një mesazh.', 'new_message', 0, '2026-06-07 13:56:02'),
(19, 2, 'Alert: fëmija ilir përdori komunikim të papërshtatshëm (i_dyshimte).', 'alert_komunikimi', 1, '2026-06-07 13:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `perdorues`
--

CREATE TABLE `perdorues` (
  `id` int(11) NOT NULL,
  `emri` varchar(50) NOT NULL,
  `mbiemri` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_pass` varchar(255) DEFAULT NULL,
  `is_email_verified` tinyint(4) DEFAULT 0,
  `roli` enum('femije','prind') NOT NULL,
  `prind_id` int(11) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ditelindja` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perdorues`
--

INSERT INTO `perdorues` (`id`, `emri`, `mbiemri`, `email`, `password`, `confirm_pass`, `is_email_verified`, `roli`, `prind_id`, `foto_profil`, `created_at`, `ditelindja`) VALUES
(2, 'Fabi', 'Muja', 'fabjolamuja@gmail.com', '$2y$10$GozLyfu/Y1WCzJEWM9jY6upxSPYGTe7s6LidARf22np9ddpnQ2zBO', NULL, 1, 'prind', NULL, NULL, '2026-06-03 12:55:29', NULL),
(3, 'ola', 'Muja', 'mujafabjola@gmail.com', '$2y$10$xSGz4YSee/9KyeBldQDfJ.AExafqbV7tZroA1UFr/YOhZx1mRJzz.', NULL, 1, 'femije', 2, NULL, '2026-06-03 12:56:57', NULL),
(4, 'ilir', 'Muja', 'ilirmuja991@gmail.com', '$2y$10$OxEu64Q3hJBuYc9BgTDJZ.YgVrvaP1rZrzMnraGAfDWulF5oHeJY2', NULL, 1, 'femije', 2, NULL, '2026-06-03 12:59:05', NULL),
(5, 'Rita', 'Onuzi', 'ritaonuzi32@gmail.com', '$2y$10$oTftR42dEGBLgZSB7x1G9OADzbVEu11Vys8O6F1pXysbPlJTeL.Vi', NULL, 1, 'prind', NULL, NULL, '2026-06-03 15:25:08', NULL),
(6, 'suzi', 'onuzi', 'onuzirita0@gmail.com', '$2y$10$GBm5el1ugg..nJESX8tAaeBsB9/HNsAh0i0R7PUFW37Ry/w33wdle', NULL, 1, 'femije', 5, NULL, '2026-06-03 15:26:21', NULL),
(7, 'aaaa', 'bbbb', 'fabjola168@gmail.com', '$2y$10$XYiM1JgkDWNFiKccVmzWW.NfqZ8Z9LZFda.V3sbAGeOFyr2/p.m96', NULL, 0, 'prind', NULL, NULL, '2026-06-13 21:30:20', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alarme`
--
ALTER TABLE `alarme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesazh_id` (`mesazh_id`),
  ADD KEY `femije_id` (`femije_id`),
  ADD KEY `prind_id` (`prind_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fjale_ndaluara_prind`
--
ALTER TABLE `fjale_ndaluara_prind`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user1`,`user2`),
  ADD KEY `user2` (`user2`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `login_verifications`
--
ALTER TABLE `login_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mesazhe`
--
ALTER TABLE `mesazhe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dergues_id` (`dergues_id`),
  ADD KEY `marres_id` (`marres_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `perdorues`
--
ALTER TABLE `perdorues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_prind` (`prind_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alarme`
--
ALTER TABLE `alarme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `fjale_ndaluara_prind`
--
ALTER TABLE `fjale_ndaluara_prind`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_verifications`
--
ALTER TABLE `login_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mesazhe`
--
ALTER TABLE `mesazhe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `perdorues`
--
ALTER TABLE `perdorues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alarme`
--
ALTER TABLE `alarme`
  ADD CONSTRAINT `alarme_ibfk_1` FOREIGN KEY (`mesazh_id`) REFERENCES `mesazhe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alarme_ibfk_2` FOREIGN KEY (`femije_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alarme_ibfk_3` FOREIGN KEY (`prind_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user1`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`user2`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_verifications`
--
ALTER TABLE `login_verifications`
  ADD CONSTRAINT `login_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mesazhe`
--
ALTER TABLE `mesazhe`
  ADD CONSTRAINT `mesazhe_ibfk_1` FOREIGN KEY (`dergues_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mesazhe_ibfk_2` FOREIGN KEY (`marres_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `perdorues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `perdorues`
--
ALTER TABLE `perdorues`
  ADD CONSTRAINT `fk_prind` FOREIGN KEY (`prind_id`) REFERENCES `perdorues` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
