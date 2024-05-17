-- Description: For connecting users to their courses after they purchase
-- >>> Up >>>
CREATE TABLE IF NOT EXISTS `{!!prefix!!}users_courses` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(11) unsigned NOT NULL,
`course_id` int(11) unsigned NOT NULL,
`order_id` int(11) unsigned NOT NULL,
`magic_link` varchar(255) COLLATE {!!collate!!} NOT NULL,
INDEX (`user_id`, `course_id`, `order_id`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={!!charset!!};
-- >>> Down >>>
DROP TABLE IF EXISTS `{!!prefix!!}users_courses`;