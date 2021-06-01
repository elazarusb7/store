# jbs_samhsa_pep_utility

This module is intended for a single use to migrate taxonomy terms from the "Tags" vocabulary to corresponding terms in other vocaularies.

It is intended to be called from drush:

`$ drush taxonomy-migrate migrate`

it is recommended that you pipe the output to a log file to retain for your records.  The output is nearly 2K lines for 577 products.

`$ drush taxonomy-migrate migrate > migrate.log`


