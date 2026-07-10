<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `sections` ADD `chat_id` TEXT NULL DEFAULT NULL AFTER `section_name`, ADD `bot_token` TEXT NULL DEFAULT NULL AFTER `chat_id`;
ALTER TABLE `school_information` ADD `feedback_tg_chat_id` TEXT NULL DEFAULT NULL AFTER `whatsapp`;
ALTER TABLE `website_config` ADD `telegram_bot_token` TEXT NULL DEFAULT NULL AFTER `imgbb_api_key`;
ALTER TABLE `website_config` CHANGE `razorpay_charge_percentage` `razorpay_charge_percentage` DECIMAL(10,5) NOT NULL DEFAULT '0';
ALTER TABLE `website_config` CHANGE `gst_on_razorpay_charge` `gst_on_razorpay_charge` DECIMAL(10,5) NOT NULL DEFAULT '0';";

	echo "<span class='text-info'>Executing $sql</span>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>