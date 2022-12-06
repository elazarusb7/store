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
   * Migrate files from source to destination.
   *
   * @command migrate_files_and_images:migrate_files
   * @aliases migrate-files
   * @usage migrate_files_and_images:migrate_files
   */
  public function migrate_files_and_images($limit = 0) {
    $migrate = new ImportFilesAndImages();
    if ($migrate->truncateCrossReference()) {

      $this->output()->writeln('Migrating Managed Files');
      $result = $migrate->executeManagedFilesImport($limit);
      if ($result['managed_error_message']) {
        $this->output()->writeln($result['managed_error_message']);
      }
      else {
        $this->output()->writeln($result['managed_processed'] . ' files imported.');
        $this->output()->writeln($result['managed_not_found'] . ' files not found in public://d7 directory.');
        $this->output()->writeln('Log for this migration stored in: ' . \Drupal::service('file_system')->realpath($migrate->managedCsvFileName));
      }
    }

    else {
      $this->output()->writeln('Could not truncate files_cross_reference table!');
    }

    $this->output()->writeln('Hello World!');
  }
}
