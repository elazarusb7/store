<?php

namespace Drupal\migrate_files_and_images\Commands;

use Drush\Commands\DrushCommands;
use Drupal\migrate_files_and_images\Controller\ImportFilesAndImages;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class FilesImagesMigrateCommands extends DrushCommands {
    /**
     * migrate files from source to destination.
     *
     * @command migrate_files_and_images:migrate_files
     * @aliases migrate-files
     * @usage migrate_files_and_images:migrate_files
     */
    public function migrate_files_and_images($limit = 0) {
        $migrate = new ImportFilesAndImages();
        if ($migrate->truncateCrossReference()) {

            drush_print('Migrating Managed Files');
            $result = $migrate->executeManagedFilesImport($limit);
            if ($result['managed_error_message']) {
                drush_print($result['managed_error_message']);
            }
            else {
                drush_print($result['managed_processed'] . ' files imported.');
                drush_print($result['managed_not_found'] . ' files not found in public://d7 directory.');
                drush_print('Log for this migration stored in: ' . \Drupal::service('file_system')->realpath($migrate->managedCsvFileName));
            }
//We only migration managed files and images and don't need to rest. Commenting out for now
            /*drush_print(' ');
            drush_print('Migrating Inline Files in Nodes');
            $result = $migrate->executeInlineImagesImport($limit);
            if ($result['inline_error_message']) {
                drush_print($result['inline_error_message']);
            }
            else {
                drush_print($result['inline_processed'] . ' files imported.');
                drush_print($result['inline_not_found'] . ' files not found in public:// directory.');
                drush_print('Log for this migration stored in: ' . \Drupal::service('file_system')->realpath($migrate->inlineCsvFileName));
            }

            drush_print(' ');
            drush_print('Migrating Inline Files in Blocks');
            $result = $migrate->executeblockImagesImport($limit);
            if ($result['block_error_message']) {
                drush_print($result['block_error_message']);
            }
            else {
                drush_print($result['block_processed'] . ' files imported.');
                drush_print($result['block_not_found'] . ' files not found in public:// directory.');
                drush_print('Log for this migration stored in: ' . \Drupal::service('file_system')->realpath($migrate->blockCsvFileName));
            }*/
        }

        else {
            drush_print('Could not truncate files_cross_reference table!');
        }

        $this->output()->writeln('Hello World!');
    }
}