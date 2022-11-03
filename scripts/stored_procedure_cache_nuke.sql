--
-- Custom cache clear stored procedure for store.samhsa.gov (Updated 27OCT2022)
--

DELIMITER $$

DROP PROCEDURE IF EXISTS `delcache` $$

CREATE PROCEDURE `delcache`()
BEGIN
  -- BEGIN PASTE
TRUNCATE `cache_admin_menu`;
TRUNCATE `cache_block`;
TRUNCATE `cache_bootstrap`;
TRUNCATE `cache_config`;
TRUNCATE `cache_container`;
TRUNCATE `cache_data`;
TRUNCATE `cache_default`;
TRUNCATE `cache_discovery`;
TRUNCATE `cache_discovery_migration`;
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
TRUNCATE `cache_migrate`;
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
-- END PASTE
TRUNCATE `semaphore`;
TRUNCATE `watchdog`;
ALTER TABLE watchdog
    AUTO_INCREMENT = 1;
END $$

DELIMITER ;
