SET NAMES utf8mb4;
SET AUTOCOMMIT=0;
START TRANSACTION;
-- UPSERT: deletes existing review by gyg_review_id then inserts fresh

-- Review gyg_id:121069521 — delete existing if present, then insert fresh
SET @old_cid_0 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='121069521' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_0 AND @old_cid_0 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_0 AND @old_cid_0 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Julie', 'julie@getyourguide.com', '2026-02-07 06:18:12', '2026-02-07 06:18:12', 'During our tour of Thailand we have taken many trips this trip was the highlight of our holiday. Starting with a local market then onto the elephant sanctuary. It was amazing to be amongst these wonderful animals who were wandering around with their owner unchained. Fed and washed them with our small group. Then on to monkey temple stopping at view points. Day 2 Long tail boat to diamond cave then an hour jungle walk followed by a coffee made by a camp fire and bamboo then a floating lunch and swim stop. The treetop resort is wonderful and monkeys joining you for breakfast. Food excellent and staff super friendly but the true star of this trip was Nuna a true professional kind, informative and funny an asset to the company thank you', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '121069521');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:120913329 — delete existing if present, then insert fresh
SET @old_cid_1 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='120913329' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_1 AND @old_cid_1 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_1 AND @old_cid_1 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Johana', 'johana@getyourguide.com', '2026-01-29 07:05:03', '2026-01-29 07:05:03', '10/10 trip. We enjoyed this activity from beginning to end. Our guide, Aris, was absolutely amazing. Aris explained everything in detail, talked openly about the local reality, and answered all of our questions. She truly cares about animals, and visiting the elephant sanctuary showed us that the elephants are very well taken care of. The driver made us feel completely safe throughout the trip. Sleeping in the tree house was a unique and unforgettable experience. The kayak rafting and jungle activities were spectacular as well. Visiting Khao Sok National Park and swimming in the lake with no other people around was absolutely worth it. Don’t hesitate to book this trip, it’s 100% worth it. Thank you again Aris❤️', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '120913329');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:119919930 — delete existing if present, then insert fresh
SET @old_cid_2 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119919930' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_2 AND @old_cid_2 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_2 AND @old_cid_2 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Yuval', 'yuval@getyourguide.com', '2025-12-08 06:27:14', '2025-12-08 06:27:14', 'Nuna was our guide and she was so nice and kind. We had a great time!!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119919930');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:119552128 — delete existing if present, then insert fresh
SET @old_cid_3 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119552128' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_3 AND @old_cid_3 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_3 AND @old_cid_3 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Niina', 'niina@getyourguide.com', '2025-11-18 07:26:05', '2025-11-18 07:26:05', 'The whole experience was amazing. We had lots of fun. I do recommend this trip. Aris our guide was great💎💎💎', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119552128');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:119421960 — delete existing if present, then insert fresh
SET @old_cid_4 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119421960' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_4 AND @old_cid_4 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_4 AND @old_cid_4 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Stephen', 'stephen@getyourguide.com', '2025-11-12 06:27:22', '2025-11-12 06:27:22', 'Very varied and high quality your both days were fantastic. Nuna was a joy of a guide and the accommodation was very memorable. A little bit more information on what to bring each day prior would’ve been nice but this is a nit pick.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119421960');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:119265255 — delete existing if present, then insert fresh
SET @old_cid_5 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119265255' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_5 AND @old_cid_5 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_5 AND @old_cid_5 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Patricia Ann', 'patricia.ann@getyourguide.com', '2025-11-05 02:07:08', '2025-11-05 02:07:08', 'The activities and scenery and guide was 11/10. The only thing to improve on our opinion is more information from the booking about itinerary and what’s needed and when. We were unsure about our end transportation and no one could answer this question until after we met the guide. We should have been provided that up front. Wasn’t sure what to wear for which day until guide arrived and that would he helpful for planning. The actual activities were beyond awesome and the highlight of our Thailand trip - food at the Rock and Tree house was very good and our tour guide Pol our tour guide was patient , kind and so eager to show us all the beauty of the park', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119265255');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:119222887 — delete existing if present, then insert fresh
SET @old_cid_6 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119222887' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_6 AND @old_cid_6 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_6 AND @old_cid_6 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Liam', 'liam@getyourguide.com', '2025-11-03 03:15:00', '2025-11-03 03:15:00', 'Absolutely spectacular. Guide was amazing, shout out to Mike! Brilliant way to explore Khao Sok national park. Highly recommend!!!!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119222887');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:117765456 — delete existing if present, then insert fresh
SET @old_cid_7 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='117765456' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_7 AND @old_cid_7 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_7 AND @old_cid_7 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Franz', 'franz@getyourguide.com', '2025-09-13 04:35:32', '2025-09-13 04:35:32', 'The Trip was amazing. Our Guide Isi was super fun and very accommodating. Also our Driver Sumit was really nice. Everything always went smoothly with no delays whatsoever.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '117765456');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:116411059 — delete existing if present, then insert fresh
SET @old_cid_8 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='116411059' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_8 AND @old_cid_8 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_8 AND @old_cid_8 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Lauren', 'lauren@getyourguide.com', '2025-08-04 01:51:07', '2025-08-04 01:51:07', 'This trip has been the highlight of our holiday so far! It is two days filled with activities. On the first day, we were driven to a local market, onto the elephant sanctuary, and then we kayaked down a river and stopped for a coffee in the jungle before heading to the treehouse resort. The food and drinks provided were all of good quality and all of the activities were amazing. On day two, we were driven to a small temple and viewpoint before the pier at the lake. We then took a longtail boat and saw the most exceptional scenery I’ve ever seen! We stopped for a walk through the jungle where our guide showed us the wildlife and then had lunch at one of the raft houses and a swim, before heading back to the minibus. It was an amazing two days and our guide, Tanya was outstanding! Top tips - remember long clothes for visiting temple, water shoes would be great and plenty of sun cream / insect repellent! I HIGHLY recommend this trip, worth every penny!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '116411059');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113965058 — delete existing if present, then insert fresh
SET @old_cid_9 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113965058' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_9 AND @old_cid_9 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_9 AND @old_cid_9 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Kacper', 'kacper@getyourguide.com', '2025-05-04 12:28:22', '2025-05-04 12:28:22', 'Great 2 days, activities were very well planned out. The treehouse overnight stay was amazing just the scenery was amazing to wake up to, plus the food was amazing. Can’t forget our tour guide she was good laugh and just amazing. Highly recommend.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113965058');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:112129656 — delete existing if present, then insert fresh
SET @old_cid_10 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='112129656' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_10 AND @old_cid_10 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_10 AND @old_cid_10 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'lorraine', 'lorraine@getyourguide.com', '2025-02-11 02:37:39', '2025-02-11 02:37:39', 'The overall experience was excellent. We were a small group and our guide was great. She made sure we got to places early to avoid the crowds and was great at spotting the wildlife. She took us out of our comfort zone which made the trip all the better for us.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '112129656');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:111722056 — delete existing if present, then insert fresh
SET @old_cid_11 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='111722056' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_11 AND @old_cid_11 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_11 AND @old_cid_11 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Julie', 'julie@getyourguide.com', '2025-01-12 07:17:49', '2025-01-12 07:17:49', 'A fantastic two day trip with a varied range of activities to give you a true Thai experience. Both days were very different but equally great and memorable. Very friendly guide. The overnight tree houses were superb. Would definitely recommend', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '111722056');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:111028922 — delete existing if present, then insert fresh
SET @old_cid_12 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='111028922' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_12 AND @old_cid_12 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_12 AND @old_cid_12 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Julie', 'julie@getyourguide.com', '2024-12-02 01:30:27', '2024-12-02 01:30:27', 'A fantastic trip, highly recommend, our guide Nona was great, a trip packed full of memories.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '111028922');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:110228029 — delete existing if present, then insert fresh
SET @old_cid_13 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='110228029' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_13 AND @old_cid_13 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_13 AND @old_cid_13 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Kevin', 'kevin@getyourguide.com', '2024-10-20 14:45:25', '2024-10-20 14:45:25', 'Our guide Mike, was excellent, made us feel welcome from the moment we were picked up. Although English is not his 1st language, he was excellent and very informative. The Elephant sanctuary was excellent, not rushed and plenty of time with the animals. The treehouse hotel was also excellent. We thoroughly enjoyed it, we did unfortunately have a very "awkward" guest who refused to listen to any of the information given by Mike and complained about everything from where she sat in the van, being really tired because she had to take sickness pills to go on a boat, and no vegetarian food, although she clearly said she had no food issues. We were all told at the evening meal we should be ready to leave at 8.30 in the morning, she asked what was the latest she could have breakfast as 8.30 was very early. She wasn''t ready and then said she hadn''t been told. She then didn''t want to go on the hike. Made it uncomfortable for all. Mike dealt with it very well and continued to be professional.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '110228029');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:108044343 — delete existing if present, then insert fresh
SET @old_cid_14 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='108044343' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_14 AND @old_cid_14 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_14 AND @old_cid_14 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Jason', 'jason@getyourguide.com', '2024-07-21 11:49:34', '2024-07-21 11:49:34', 'Aris was our tour guide and she did such a great job! Such a nice and enthusiastic guide who truly enjoys being out in nature and showing tourists like us around. Khao Sok national park is so beautiful, highly recommend! The room was also great.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '108044343');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:108043017 — delete existing if present, then insert fresh
SET @old_cid_15 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='108043017' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_15 AND @old_cid_15 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_15 AND @old_cid_15 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Justin', 'justin@getyourguide.com', '2024-07-21 10:41:13', '2024-07-21 10:41:13', 'Amazing experience with our guide Aris, she went above and beyond to show us as many animals as possible. She has a good sense of humor and kept us laughing all the time.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '108043017');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:107950040 — delete existing if present, then insert fresh
SET @old_cid_16 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='107950040' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_16 AND @old_cid_16 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_16 AND @old_cid_16 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Danny', 'danny@getyourguide.com', '2024-07-17 05:10:31', '2024-07-17 05:10:31', 'Our tour guide was fabulous really showed and let us experience the beauty of the jungle and lake. And took care of the scary critters! Would recommend to anyone such a lovely trip.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '107950040');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:107081444 — delete existing if present, then insert fresh
SET @old_cid_17 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='107081444' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_17 AND @old_cid_17 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_17 AND @old_cid_17 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Audra', 'audra@getyourguide.com', '2024-06-03 08:50:56', '2024-06-03 08:50:56', 'Our guide, Aris, was the best part. Knowledgeable, fun, professional, friendly and all the good things a guide should be!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '107081444');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:105825370 — delete existing if present, then insert fresh
SET @old_cid_18 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='105825370' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_18 AND @old_cid_18 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_18 AND @old_cid_18 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Neil', 'neil@getyourguide.com', '2024-04-01 00:22:07', '2024-04-01 00:22:07', 'This was a very interesting and lovely trip And one of the best experiences was our lovely GUIDE ❤️ OIL', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '105825370');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:105530643 — delete existing if present, then insert fresh
SET @old_cid_19 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='105530643' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_19 AND @old_cid_19 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_19 AND @old_cid_19 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Iakovos', 'iakovos@getyourguide.com', '2024-03-14 03:59:35', '2024-03-14 03:59:35', 'Every moment on this 2 day trip is something to remember! It takes all the beautiful activities that you can make in the area and puts them together in a well organised trip with a great hotel to stay at night and save some go and come back travelling time from main tourist areas! Great Guide!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '105530643');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:105463143 — delete existing if present, then insert fresh
SET @old_cid_20 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='105463143' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_20 AND @old_cid_20 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_20 AND @old_cid_20 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Jake', 'jake@getyourguide.com', '2024-03-09 09:23:31', '2024-03-09 09:23:31', 'So good. 10/10 setting, unbelievable. Monkeys everywhere at the treehouse, then trying to steal your breakfast whilst staff use sling shots to get them away 😂', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '105463143');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:104251367 — delete existing if present, then insert fresh
SET @old_cid_21 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='104251367' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_21 AND @old_cid_21 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_21 AND @old_cid_21 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Nicky', 'nicky@getyourguide.com', '2023-11-22 14:10:24', '2023-11-22 14:10:24', 'Aris was very informative, particularly about the wildlife. The trip was packed full of different activities. The tree house was wonderful. The landscape stunning. Brilliant trip', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '104251367');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:104226388 — delete existing if present, then insert fresh
SET @old_cid_22 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='104226388' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_22 AND @old_cid_22 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_22 AND @old_cid_22 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Abigail', 'abigail@getyourguide.com', '2023-11-20 06:12:01', '2023-11-20 06:12:01', 'I loved this tour and it exceeded my expectations. I’m so happy I booked it, totally worth every penny. The food was amazing and I was never hungry or thirsty the entire time. Beautiful scenery and such a variety of activities. The accommodations were very cool. Comfortable beds and a very cool experience (keep in mind there will be wildlife as you’re in the jungle). The best part of the tour was Oil our guide. She went above and beyond to ensure we had everything we needed at all times. She truly loves her job and wants everyone to have the best experiences. She was very knowledgeable and has such a kind heart. Would 100% recommend booking this.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '104226388');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:104178781 — delete existing if present, then insert fresh
SET @old_cid_23 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='104178781' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_23 AND @old_cid_23 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_23 AND @old_cid_23 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Joseph', 'joseph@getyourguide.com', '2023-11-15 13:24:19', '2023-11-15 13:24:19', 'We fed and bathed elephants, saw monkeys, stayed in a unique room in the jungle, got an amazing boat across a beautiful lake - this trip had it all. Oil was the best guide, so helpful and kind - she really went above and beyond. Jack was a great driver too, we felt very safe in his minibus', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '104178781');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:103916178 — delete existing if present, then insert fresh
SET @old_cid_24 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='103916178' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_24 AND @old_cid_24 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_24 AND @old_cid_24 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Julie', 'julie@getyourguide.com', '2023-10-26 13:38:03', '2023-10-26 13:38:03', 'The whole trip was awesome, especially our guide Oil.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '103916178');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:103530556 — delete existing if present, then insert fresh
SET @old_cid_25 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='103530556' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_25 AND @old_cid_25 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_25 AND @old_cid_25 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Claudia', 'claudia@getyourguide.com', '2023-10-02 09:42:04', '2023-10-02 09:42:04', 'Everything was perfectly organized and our tourguide Isi was amazing, very knowledgeable and helpful and made the trip and unforgettable experience for the whole group. I can only recommend the tour to everyone!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '103530556');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:102262525 — delete existing if present, then insert fresh
SET @old_cid_26 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='102262525' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_26 AND @old_cid_26 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_26 AND @old_cid_26 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Nadine', 'nadine@getyourguide.com', '2023-07-27 07:24:37', '2023-07-27 07:24:37', 'Einzigartig.! Great experience, amazing trip, and overnight stay for the books.!!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '102262525');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:101032104 — delete existing if present, then insert fresh
SET @old_cid_27 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='101032104' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_27 AND @old_cid_27 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_27 AND @old_cid_27 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Benjamin', 'benjamin@getyourguide.com', '2023-04-26 16:02:03', '2023-04-26 16:02:03', 'We enjoyed it a lot. It was a perfect tour. We and our kids (5 and 2 years old) have enjoyed the tour a lot. Would do it again.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '101032104');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:100567245 — delete existing if present, then insert fresh
SET @old_cid_28 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='100567245' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_28 AND @old_cid_28 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_28 AND @old_cid_28 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Tess Jensen', 'tess.jensen@getyourguide.com', '2023-03-12 01:23:57', '2023-03-12 01:23:57', 'This was by far the best tour I have been on. All owed to our amazing tour guide - Oil. She was so informational, kind, personal, full of surprises and went way beyond normal tour guide responsibilies. We had so many laughs and enjoyed all the activities immensely. The time with elephants did not feel rushed, which was one of my fears, so I was extremly happy for that. The treehouse stay was incredible. The river boat tour was too much fun. We saw snakes, monkeys, and huge spiders on the way. It was one of the most beautiful places I have ever been to. The long boat tour was beautiful and relaxing. Everything was so very perfect on the trip. The highlight of a trip all through Thailand and Cambodia.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '100567245');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:100566456 — delete existing if present, then insert fresh
SET @old_cid_29 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='100566456' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_29 AND @old_cid_29 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_29 AND @old_cid_29 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Emma', 'emma@getyourguide.com', '2023-03-11 21:07:38', '2023-03-11 21:07:38', 'I normally hate guided tours, but Oil exceeding all expectations that I had! I learned so much about Thailand and actually had me asking questions and wanting to learn more. It was so hard to leave Oil, she was one of the most interesting and loving people I have ever met. I will be back to stay at the treehouse and do another tour with Oil :)', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '100566456');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:15223751 — delete existing if present, then insert fresh
SET @old_cid_30 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='15223751' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_30 AND @old_cid_30 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_30 AND @old_cid_30 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'shannon', 'shannon@getyourguide.com', '2022-12-18 07:16:40', '2022-12-18 07:16:40', 'Very well organized and professional. The guides were precious and kind and passionate about what they do. I would recommend it''s a very unique and beautiful experience. Great for families to.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '15223751');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:100310884 — delete existing if present, then insert fresh
SET @old_cid_31 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='100310884' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_31 AND @old_cid_31 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_31 AND @old_cid_31 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (14755, 'Miguel', 'miguel@getyourguide.com', '2023-01-20 02:49:41', '2023-01-20 02:49:41', 'The experience was amazing overall. One thing I disliked was that the tour supposedly was in english. But since guide was German and several Germans came to the tour, it ended up talking to much in german and me feeling a little out of the conversation. He did still translate the essentials, but not nice :( Another dislike was an unfortunate racist comment the people in my country Georgia. Even tho the guide confessed afterwards ge never visited my country. I think this kind of racist comments should be avoided at all cost. Besides that, all perfect. Recommended', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '100310884');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '3');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '4');

-- Review gyg_id:119665786 — delete existing if present, then insert fresh
SET @old_cid_32 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119665786' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_32 AND @old_cid_32 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_32 AND @old_cid_32 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (16171, 'Kylie', 'kylie@getyourguide.com', '2025-11-24 04:09:18', '2025-11-24 04:09:18', 'Tour was awesome!! Quick little visit to a local market and great facts galore from our guide. Beautiful view from viewpoint, fun and laughs watching the monkeys at the monkey temple followed by relaxing and fun kayak down the river where we spotted monkeys, lizards, frogs, birds and 3 trees snakes. Very lucky!! Lunch at the treehouse hotel was substantial and good for people who aren''t into spice. Unfortunately no monkeys to be spotted there but looks like a lovely place to stay. Unfortunately straight after lunch the rains arrived and by rains I mean heavy torrential rains!! We arrived at the elephants to go and wash them in the pouring rain. Tour operator couldn''t do anything about the rain but definitely would have preferred no rain for this activity but if rains were to hit out of all the activities on the tour this was the most suitable as you get wet anyway. Learnt lots about the elephant and got to make them some fibre balls and feed them bananas. Our kids loved washing them!!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119665786');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '3');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '3');

-- Review gyg_id:112177299 — delete existing if present, then insert fresh
SET @old_cid_33 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='112177299' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_33 AND @old_cid_33 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_33 AND @old_cid_33 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (16171, 'Kerry Jane', 'kerry.jane@getyourguide.com', '2025-02-14 01:37:08', '2025-02-14 01:37:08', 'I had a fantastic day out with the guide Ollie and the driver Ep. Ellie was very knowledgeable and switched back and forth between English and German easily, keeping all guest included and informed. He also.had a lovely relationship with the Thai driver and the canoe guides who took us down the river. Khao Sok is beautiful park and the trip to the Elephant conservation centre was just amazing, bathing the elephants was so much fun. Lunch out at the treetop restaurant was delicious. It was a well organised day and Ollie handled the mistake the office made, putting two guests on the wrong trip without any fuss. He quickly re-arranged the rest of the day, se we could fit everything in without missing out, that the delay in taking the guests back to their hotel had caused. I was very impressed with the staff and each activity and step included in the day. would definitely recommend this trip ... it was fantastic!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '112177299');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:14861556 — delete existing if present, then insert fresh
SET @old_cid_34 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='14861556' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_34 AND @old_cid_34 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_34 AND @old_cid_34 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (16171, 'sandra', 'sandra@getyourguide.com', '2022-10-10 17:21:06', '2022-10-10 17:21:06', 'My husband & i booked this experience while staying in Khao Lak & we are so glad we did, we got to meet our amazing tour guide Arisaya. She is passionate about her country & can see why, its amazing. She took us to a local market, showing us different things we wouldnt normally.next we went to the elephants, so amazing. we got to Bathe, feed & see where they lived. id recommend taking a waterproof camera or waterproof phone case to take pictures, itll be worth it. we was provided with ponchos due to the rain that we could use for the duration of the experience. we got to see a beautiful waterfall & having a great lunch in a treehouse (which is a place you can stay at aswell) we did a canoe trip, it was a tad wet but the rain didnt ruin our canoe trip again waterproof phone case that goes around your neck is really ideal. we was provided with water or soft drink during the trip. Arisaya informed us all the way through the day on what we would be doing.This is a must if your visiting.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '14861556');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:106280842 — delete existing if present, then insert fresh
SET @old_cid_35 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='106280842' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_35 AND @old_cid_35 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_35 AND @old_cid_35 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (16171, 'Charlotte', 'charlotte@getyourguide.com', '2024-04-26 07:18:52', '2024-04-26 07:18:52', 'The guide was good and was to have a taste of a few differnet things, canoeing, elephants, temple. we were sat at the back of the minibus and it was too hot, it made the travel miserable.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '106280842');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '2');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '2');

-- Review gyg_id:119696862 — delete existing if present, then insert fresh
SET @old_cid_36 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='119696862' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_36 AND @old_cid_36 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_36 AND @old_cid_36 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Kerry', 'kerry@getyourguide.com', '2025-11-25 16:59:14', '2025-11-25 16:59:14', 'we had the most amazing tour today, our guide was brilliant, full of knowledge and always making sure the group was ok and offering drinks, seeing the elephants was just epic loved every minute, the whole tour was awesome, totally recommend', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '119696862');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:116186000 — delete existing if present, then insert fresh
SET @old_cid_37 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='116186000' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_37 AND @old_cid_37 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_37 AND @old_cid_37 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Bali', 'bali@getyourguide.com', '2025-07-27 19:55:52', '2025-07-27 19:55:52', 'Excellent! Guide was knowledge and explained everything. She answered all my questions with ease and was very thourgh.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '116186000');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:116127926 — delete existing if present, then insert fresh
SET @old_cid_38 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='116127926' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_38 AND @old_cid_38 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_38 AND @old_cid_38 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Kate', 'kate@getyourguide.com', '2025-07-26 03:12:50', '2025-07-26 03:12:50', 'The trip was excellent; Johnny walkers and Ben were great guides, we saw loads and learn a lot would 100% recommend', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '116127926');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:116050837 — delete existing if present, then insert fresh
SET @old_cid_39 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='116050837' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_39 AND @old_cid_39 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_39 AND @old_cid_39 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Cheryl', 'cheryl@getyourguide.com', '2025-07-23 13:45:21', '2025-07-23 13:45:21', 'Markus had excellent knowledge of all areas and the trip had lots of places to visit . great for the money and seeing new places.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '116050837');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:115035583 — delete existing if present, then insert fresh
SET @old_cid_40 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='115035583' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_40 AND @old_cid_40 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_40 AND @old_cid_40 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Emma', 'emma@getyourguide.com', '2025-06-16 09:11:42', '2025-06-16 09:11:42', 'fantastic guide. very relaxed trip and so much fun. memories of a lifetime made. We were kept in the know and offered drinks regularly throughout the trip. The lunch was delicious too!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '115035583');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113785716 — delete existing if present, then insert fresh
SET @old_cid_41 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113785716' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_41 AND @old_cid_41 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_41 AND @old_cid_41 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Bryan', 'bryan@getyourguide.com', '2025-04-27 22:25:37', '2025-04-27 22:25:37', 'Absolutely amazing day! Jib was an outstanding and very knowledgeable guide. Her English was very good, as well. It was a full day if activities and learning. The van was very comfy and air conditioned. The food for lunch was very good. The elephant experience is unforgettable. This day trip was the highlight of our vacation to Thailand. Thank you so much for the memories!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113785716');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113672013 — delete existing if present, then insert fresh
SET @old_cid_42 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113672013' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_42 AND @old_cid_42 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_42 AND @old_cid_42 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Fiona', 'fiona@getyourguide.com', '2025-04-24 00:08:06', '2025-04-24 00:08:06', 'Our guide Tanya was lovely lady, very funny and informative, very good driver and trainee all very friendly. Lovely air-conditioned van fully stacked with cold water and cola for the journey, plus delicious fruit. They all took good care of us. Delicious lunch before bamboo rafting. Experience with elephants was amazing, as was turtle conservation centre. Thoroughly enjoyed the whole day, would highly recommend', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113672013');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113546662 — delete existing if present, then insert fresh
SET @old_cid_43 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113546662' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_43 AND @old_cid_43 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_43 AND @old_cid_43 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Joanne', 'joanne@getyourguide.com', '2025-04-20 02:08:29', '2025-04-20 02:08:29', 'From start to finish this trip was truly amazing. Jenny our guide was very welcoming and informative about each activity. Learning about the Tsunami, the locals and turtles & elephant sanctuaries. Our driver was very thoughtful giving us iced wipes to cool us down. I can honestly say this was the best excursion I have ever experienced. I wouldn''t hesitate to highly recommend this trip and the company running them. Thank you!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113546662');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113495506 — delete existing if present, then insert fresh
SET @old_cid_44 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113495506' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_44 AND @old_cid_44 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_44 AND @old_cid_44 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Stuart', 'stuart@getyourguide.com', '2025-04-18 08:35:11', '2025-04-18 08:35:11', 'We really enjoyed the day. We received up to date information prior to commencing the trip. The transport was air conditioned and very comfortable. Throughout the day the tour guide provided a friendly and informative experience. This is a value for money experience with the elephant sanctuary, temple, turtle sanctuary and much more and throughout we were supplied with refreshments. Loved the experience.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113495506');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:113238469 — delete existing if present, then insert fresh
SET @old_cid_45 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='113238469' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_45 AND @old_cid_45 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_45 AND @old_cid_45 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Jane', 'jane@getyourguide.com', '2025-04-08 10:50:44', '2025-04-08 10:50:44', 'Amazing day trip - guide (Gift) was excellent and gave us loads on information. Elephants were wonderful and also saw turtles and a local temple. Wonderful lunch with excellent food and catered for vegetarians- then bamboo rafting on the river which was lovely and relaxing. Highly recommend!', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '113238469');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:112764087 — delete existing if present, then insert fresh
SET @old_cid_46 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='112764087' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_46 AND @old_cid_46 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_46 AND @old_cid_46 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Jordan', 'jordan@getyourguide.com', '2025-03-16 14:10:45', '2025-03-16 14:10:45', 'I had a great time! Tip, our guide was very informative and always checking in (along with the driver) to see if we needed water or a soft drink. We started off by going to visit the elephants, followed by visiting the turtle marine centre. Afterwards, we headed to the temple where we got to learn a little bit about the Buddist religion and culture. We had a fantastic family style lunch (which there was plenty of and they continued to bring more out as the plates started to empty) and then went on the bamboo rafts. For me the bamboo rafts were the highlight, and it was so peaceful and tranquil. I was definitely shown a lot of animals hanging around or sleeping in the trees. After that we visited a waterfall, where tip gave everyone a decent chunk of time to hang, enjoy the water and chill after our packed morning and afternoon. Last but not least, we then went to visit the Tsunami Memorial - I learned so much about the event, and how it impacted the locals.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '112764087');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:112451436 — delete existing if present, then insert fresh
SET @old_cid_47 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='112451436' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_47 AND @old_cid_47 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_47 AND @old_cid_47 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Chris', 'chris@getyourguide.com', '2025-02-28 09:46:08', '2025-02-28 09:46:08', 'all the activities, rafting, turtles & elephants were excellent, visiting the tsunami memorial was sobering, but all brilliantly explained by our wonderful guide Kaew who was knowledgeable but also felt like she was a friend to the group, super fun & honestly just the best guide ever! lunch was amazing, catered to vegans & meat eaters alike. Our lovely driver today, I can''t remember his name as it was only said once but he was super helpful, lovely and went out of his way to do things for us all. Thank you both so so much! we had an wonderful day', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '112451436');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:112310137 — delete existing if present, then insert fresh
SET @old_cid_48 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='112310137' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_48 AND @old_cid_48 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_48 AND @old_cid_48 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Jayne', 'jayne@getyourguide.com', '2025-02-21 01:39:43', '2025-02-21 01:39:43', 'This must be at the top of my list as one of the best trips I have been on. The elephant sanctuary was an amazing experience to feed, bathe and clean them. The turtle sanctuary was interesting to learn how our plastics are endangering our ocean life, learnt so much from the visit the Tsunami memorial. Along with visiting a temple and bamboo rafting down a river, overall the day was great value for money with so much to do, thank you for making special memories for me', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '112310137');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:111910243 — delete existing if present, then insert fresh
SET @old_cid_49 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='111910243' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_49 AND @old_cid_49 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_49 AND @old_cid_49 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Nicola', 'nicola@getyourguide.com', '2025-01-26 12:27:57', '2025-01-26 12:27:57', 'Absolutely everything was amazing. There was plenty of soft drinks available throughout the day. The tour guide and driver were such a laugh and both spoke really good English. Just the right amount of time at each stop and the lunch exceeded our expectation, it was delicious and plentiful. Had the best day ever. Thank you.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '111910243');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:111794597 — delete existing if present, then insert fresh
SET @old_cid_50 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='111794597' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_50 AND @old_cid_50 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_50 AND @old_cid_50 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Nicola', 'nicola@getyourguide.com', '2025-01-17 12:06:13', '2025-01-17 12:06:13', 'What a day!!! An amazing experience from start to finish Por our guide and driver Bang are so friendly and knowledgable from pick up from the hotel to all the activities and drop off kept us all informed throughout the day! Excellent value for money lunch was amazing lots of food!! Soft drinks water and refreshing wipes offered throughout the day if there is one trip your thinking of doing this has to be it!! Take a towel change of clothes for comfort and your sunscreen and bug spray One experience we won’t forget', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '111794597');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:109229206 — delete existing if present, then insert fresh
SET @old_cid_51 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='109229206' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_51 AND @old_cid_51 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_51 AND @old_cid_51 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Allan', 'allan@getyourguide.com', '2024-09-07 10:31:40', '2024-09-07 10:31:40', 'Full day guide Tanya was fantastic we loved every minute', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '109229206');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:108947355 — delete existing if present, then insert fresh
SET @old_cid_52 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='108947355' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_52 AND @old_cid_52 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_52 AND @old_cid_52 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Margaret', 'margaret@getyourguide.com', '2024-08-26 10:42:19', '2024-08-26 10:42:19', 'Excellent, well-timed, not rushed tour. Our knowledgeable, calm & caring guide willingly answered our queries and helped with photographs when we were with the elephants & waterfall. All parts of this tour were well planned & scheduled, we felt ahead of the rush. Lunch was generous & very tasty. Soft drinks and refreshing wipes were offered throughout the trip. We had a fantastic day, memories were made with some huge laughs (rafting - we definitely got wet) and emotional moments (elephant experience & tsunami exhibition). We took this trip during the rainy season; water was high for rafting, the waterfall looked amazing but we did not swim - it was running a little too fast for us but some local visitors were enjoying the waters. My tips - take towels, change of clothes (there were plenty of clean facilities along route although I didn’t notice any at the waterfall) & have cash.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '108947355');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:108341266 — delete existing if present, then insert fresh
SET @old_cid_53 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='108341266' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_53 AND @old_cid_53 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_53 AND @old_cid_53 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Frog', 'frog@getyourguide.com', '2024-08-03 16:38:29', '2024-08-03 16:38:29', 'this was so much better than it already sounds, packed day full of great experiences, friendly, funny knowledgeable guide ( mike) and great driver, plenty of water and soft drink provided throughout, great meal, rafting was fun with beautiful scenery, temples where great too, mike answers any questions you have about anything you see, feeding and bathing elephants was amazing, turtles where good too, and learning about the tsunami was a great insight of a horrible past which thankfully this fantastic place has recovered from. if I could give the tour 10 stars I would .', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '108341266');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:106926845 — delete existing if present, then insert fresh
SET @old_cid_54 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='106926845' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_54 AND @old_cid_54 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_54 AND @old_cid_54 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Joel', 'joel@getyourguide.com', '2024-05-26 14:14:13', '2024-05-26 14:14:13', 'Very much recommend this tour. A special shoutout to our guide, Jake! Always ready to answer questions, making sure everyone knew what was coming up and what to expect when we get there. He was knowledgeable without being too much. The time with the elephants was def the highlight. Thanks Jake for a ton of great pictures! PloiSoy, our elephant was amazing and so gentle. He loved being scrubbed down hard! The Vietnamese bamboo rafting was also a great experience. Adee-saw was our river guide and was able to point out some sleeping snakes in the trees and a small, baby crocodile. Jake and our driver… (I didn’t catch his name) were very good to keep us hydrated with cold water and cola at each stop, along with an ice cold towelette. So refreshing! And our lunch was fantastic! Very good Thai chicken stir fry, rice, vegetables and deep fried prawns and onion rings. Super good food and plenty of it! We would highly recommend this trip. Five stars and two thumbs up!!! Well done! 👏', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '106926845');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:104957132 — delete existing if present, then insert fresh
SET @old_cid_55 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='104957132' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_55 AND @old_cid_55 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_55 AND @old_cid_55 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Gill', 'gill@getyourguide.com', '2024-01-26 09:51:23', '2024-01-26 09:51:23', 'Fantastic tour - lots of different things to do - elephants were wonderful, loved the turtles, bamboo rafting was great fun and washed off the day at the waterfall - Oil our guide was great and we had a lovely lunch - highly recommended this tour', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '104957132');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:104166994 — delete existing if present, then insert fresh
SET @old_cid_56 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='104166994' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_56 AND @old_cid_56 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_56 AND @old_cid_56 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Jeevan', 'jeevan@getyourguide.com', '2023-11-14 10:41:36', '2023-11-14 10:41:36', 'Our guide was very nice and knowledgeable. We really saw a lot and could get a feeling around the ways around here. The elephants were the highlight but the rafting was very calming. our driver took as around without any issues. it was a great experience. unfortunately there was a communication issue which lead to us waiting for an hour for our pick-up at the side of a busy road.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '104166994');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:103720591 — delete existing if present, then insert fresh
SET @old_cid_57 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='103720591' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_57 AND @old_cid_57 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_57 AND @old_cid_57 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Lisa', 'lisa@getyourguide.com', '2023-10-13 13:54:27', '2023-10-13 13:54:27', 'Elephants sanctuary was amazing, the guide was very informative. Lunch was absolutely lovely,', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '103720591');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:103617424 — delete existing if present, then insert fresh
SET @old_cid_58 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='103617424' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_58 AND @old_cid_58 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_58 AND @old_cid_58 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Kevin', 'kevin@getyourguide.com', '2023-10-07 09:54:50', '2023-10-07 09:54:50', 'We had such an adventurous day. The weather was rainy most of the day but this didn’t stop the fun. Our guide was informative and entertaining. The elephants were amazing and river rafting a hoot. All in all a fun packed day 😃', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '103617424');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:103172111 — delete existing if present, then insert fresh
SET @old_cid_59 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='103172111' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_59 AND @old_cid_59 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_59 AND @old_cid_59 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Kylie', 'kylie@getyourguide.com', '2023-09-11 09:42:13', '2023-09-11 09:42:13', 'A truly amazing day!! The bamboo rafting and elephants will never be forgotten. Our guide “Johnny Walker” was fabulous, he had so much local knowledge and enthusiasm. Our driver Pianni ( may be spelt wrong) was the best, he looked after us so well', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '103172111');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:100996025 — delete existing if present, then insert fresh
SET @old_cid_60 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='100996025' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_60 AND @old_cid_60 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_60 AND @old_cid_60 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Jacqui', 'jacqui@getyourguide.com', '2023-04-23 11:50:06', '2023-04-23 11:50:06', 'Absolutely fantastic day , we were 11 in total of all different ages and the whole day was amazing , Jake our guide was incredibly and the driver Mr ( apologies I’ve forgotten his name ) was amazing very attentive with ice cold drinks and wipes all the time . The lunch provided was one of the best we had very fresh, variety and plentiful. We would all highly recommend this trip . 5 star plus', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '100996025');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:13580450 — delete existing if present, then insert fresh
SET @old_cid_61 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='13580450' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_61 AND @old_cid_61 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_61 AND @old_cid_61 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Glenn', 'glenn@getyourguide.com', '2022-04-10 12:35:44', '2022-04-10 12:35:44', 'absolutely amazing day. our guide Tim was fantastic with local knowledge and history. a full on day, the elephants and bamboo rafting were the highlight of our trip. thank you', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '13580450');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:121282986 — delete existing if present, then insert fresh
SET @old_cid_62 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='121282986' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_62 AND @old_cid_62 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_62 AND @old_cid_62 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Anders', 'anders@getyourguide.com', '2026-02-17 07:32:22', '2026-02-17 07:32:22', 'Great guide, well organized, could have had more time with the elephants. No unnecessary waiting which was very good. Food was ok and drinks available the whole time. Good driver with proper vehicle with AC.', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '121282986');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

-- Review gyg_id:111946703 — delete existing if present, then insert fresh
SET @old_cid_63 = (SELECT comment_id FROM `wp_commentmeta` WHERE `meta_key`='gyg_review_id' AND `meta_value`='111946703' LIMIT 1);
DELETE FROM `wp_commentmeta` WHERE `comment_id` = @old_cid_63 AND @old_cid_63 IS NOT NULL;
DELETE FROM `wp_comments` WHERE `comment_ID` = @old_cid_63 AND @old_cid_63 IS NOT NULL;
INSERT INTO `wp_comments` (`comment_post_ID`, `comment_author`, `comment_author_email`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_approved`, `comment_type`) VALUES (15166, 'Alan', 'alan@getyourguide.com', '2025-01-29 02:50:52', '2025-01-29 02:50:52', 'Excellent day out rafting was great only negative we would liked longer with the elephants. Lunch was excellent and cold drinks available all day good trip', '1', 'st_reviews');
SET @last_comment_id = LAST_INSERT_ID();
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_reviews', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_star', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'comment_rate', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'gyg_review_id', '111946703');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_tour-guide', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_driver', '5');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_food', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_service', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_itinerary', '4');
INSERT IGNORE INTO `wp_commentmeta` (`comment_id`, `meta_key`, `meta_value`) VALUES (@last_comment_id, 'st_stat_transport', '5');

COMMIT;
