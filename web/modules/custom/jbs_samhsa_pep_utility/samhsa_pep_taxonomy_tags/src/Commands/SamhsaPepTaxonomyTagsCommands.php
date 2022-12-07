<?php

namespace Drupal\samhsa_pep_taxonomy_tags\Commands;

use Drupal\samhsa_pep_taxonomy_tags\Controller\TaxonomyUpdate;
use Drupal\samhsa_pep_taxonomy_tags\Controller\Migrate;
use Drush\Commands\DrushCommands;

/**
 *
 */
class SamhsaPepTaxonomyTagsCommands extends DrushCommands {

  /**
   * Migrate PEP Product tags from Tags vocabulary to proper vocabularies.
   *
   * @command taxonomy:migrate
   * @aliases tmig,taxonomy-migrate
   */
  public function migrate($cmd = '', $options = ['test' => FALSE]) {
    $migrate = new Migrate(TRUE, $options['test']);
    $this->output()->writeln("\nRunning SAMHSA PEP Taxonomy migration $cmd...");

    switch ($cmd) {
      case 'addTerms':
        $migrate->newTerms();
        break;

      case 'assign':
        $migrate->assignTerms();
        break;

      case 'migrate':
        $migrate->migrate();
        break;

      case 'update':
        $update = new TaxonomyUpdate(TRUE, $options['test']);
        $update->update();
        break;

      default:
        $this->output()->writeln("useage:\n\tdrush taxonomy-migrate\n\t - show this message");
        $this->output()->writeln("\n\tdrush taxonomy-migrate migrate\n\t - run migration");
        $this->output()->writeln("\n\tdrush taxonomy-migrate update\n\t - apply updates");
        break;
    }
    if ($cmd != '') {
      if (isset($update)) {
        $this->output()->writeln($update->getStatus());
      }
      else {
        $this->output()->writeln($migrate->getStatus());
      }
    }
  }

}
