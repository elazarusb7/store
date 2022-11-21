--
-- These queries will strip user identifiable information from the SAMHSA Store database
--
-- This script can be run from the project root using: drush sql:cli < scripts/store_sanitize_pii.sql
--

-- Empty tables we do not need to carry over at all
TRUNCATE `cache_admin_menu`;
TRUNCATE `cache_block`;
TRUNCATE `cache_bootstrap`;
TRUNCATE `cache_config`;
TRUNCATE `cache_container`;
TRUNCATE `cache_data`;
TRUNCATE `cache_default`;
TRUNCATE `cache_discovery`;
TRUNCATE `cache_dynamic_page_cache`;
TRUNCATE `cache_entity`;
TRUNCATE `cache_explinklist`;
TRUNCATE `cache_features`;
TRUNCATE `cache_feeds_http`;
TRUNCATE `cache_field`;
TRUNCATE `cache_filter`;
TRUNCATE `cache_form`;
TRUNCATE `cache_htmlpurifier`;
TRUNCATE `cache_image`;
TRUNCATE `cache_libraries`;
TRUNCATE `cache_menu`;
TRUNCATE `cache_metatag`;
TRUNCATE `cache_page`;
TRUNCATE `cache_path`;
TRUNCATE `cache_render`;
TRUNCATE `cache_rest`;
TRUNCATE `cache_rules`;
TRUNCATE `cache_token`;
TRUNCATE `cache_toolbar`;
TRUNCATE `cache_update`;
TRUNCATE `cache_variable`;
TRUNCATE `cache_views`;
TRUNCATE `cache_views_data`;
TRUNCATE `ctools_css_cache`;
TRUNCATE `ctools_object_cache`;
TRUNCATE `views_data_export_object_cache`;
TRUNCATE `semaphore`;
TRUNCATE `sessions`;
TRUNCATE `watchdog`;

--
-- Basic Drupal user tables
--
UPDATE `users_field_data`
SET `name` = CONCAT('User ', uid),
    `mail` = CONCAT('user_', uid, '@example.com'),
    `init` = CONCAT('user_', uid, '@example.com')
WHERE uid NOT IN (
  -- Do not modify any users that have administrator role
  SELECT u.entity_id FROM user__roles u WHERE u.roles_target_id = 'administrator')
;

--
-- Commerce module related tables
--

-- commerce_order: mail, ip_address,
UPDATE `commerce_order`
SET `ip_address` = '127.0.0.1',
    `mail`       = CONCAT('order_', order_id, '@example.com')
;

-- commerce_order__field_justification.field_justification_value
UPDATE `commerce_order__field_justification`
SET `field_justification_value` = 'Ipsum lorem'
;

-- commerce_order_report__billing_address
UPDATE `commerce_order_report__billing_address`
SET `billing_address_country_code`        = 'US',
    `billing_address_administrative_area` = 'NE',
    `billing_address_locality`            = 'Omaha',
    `billing_address_postal_code`         = 91234,
    `billing_address_address_line1`       = '4222 Clinton Way',
    `billing_address_organization`        = 'SkyNet Intergalactic',
    `billing_address_given_name`          = 'Ed',
    `billing_address_family_name`         = 'Poe'
;

-- commerce_order_report__mail.mail_value
UPDATE `commerce_order_report__mail`
SET `mail_value` = CONCAT('entity_', entity_id, '@example.com')
;

-- profile__address -- many
UPDATE `profile__address`
SET `address_country_code`        = 'US',
    `address_administrative_area` = 'NE',
    `address_locality`            = 'Omaha',
    `address_postal_code`         = 91234,
    `address_address_line1`       = '4222 Clinton Way',
    `address_organization`        = 'SkyNet Intergalactic',
    `address_given_name`          = 'Ed',
    `address_family_name`         = 'Poe'
;

-- profile_revision__address
UPDATE `profile_revision__address`
SET `address_country_code`        = 'US',
    `address_administrative_area` = 'CA',
    `address_locality`            = 'San Jose',
    `address_postal_code`         = 95124,
    `address_address_line1`       = '2030 Veronica Pl',
    `address_organization`        = 'SkyNet Intergalactic',
    `address_given_name`          = 'Nathan',
    `address_family_name`         = 'Hawthorne'
;

-- profile_revision__field_phone_number.field_phone_number_value
UPDATE `profile_revision__field_phone_number`
SET `field_phone_number_value` = 2131239876
;
