-- facebook_app_settings টেবিলে অনুপস্থিত URL কলামগুলো যোগ করুন
-- phpMyAdmin বা MySQL কনসোলে একবার চালান। যে কলাম আগে থেকেই আছে সেটা এরর দিলে সেই লাইন স্কিপ করুন।

ALTER TABLE facebook_app_settings ADD COLUMN redirect_url TEXT NULL;
ALTER TABLE facebook_app_settings ADD COLUMN privacy_policy_url TEXT NULL;
ALTER TABLE facebook_app_settings ADD COLUMN terms_of_service_url TEXT NULL;
ALTER TABLE facebook_app_settings ADD COLUMN data_deletion_url TEXT NULL;
