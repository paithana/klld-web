SELECT 
    `post_id`, 
    FROM_UNIXTIME(`check_in`) AS `formatted_date`,
    `check_in`, 
    `check_out`, 
    `status`, 
    `adult_price`, 
    `child_price`, 
    `price`, 
    `wp_posts`.`post_title`
FROM `wp_st_tour_availability`
LEFT JOIN `wp_posts` ON `wp_st_tour_availability`.`post_id` = `wp_posts`.`ID`
WHERE `check_in` > UNIX_TIMESTAMP() ORDER BY `check_in` ASC;


UPDATE `wp_st_tour_availability`
    SET `adult_price` = 3400,
        `child_price` = 3100,
        `price` = 34000
    WHERE `post_id` IN (28002,28370,30326);