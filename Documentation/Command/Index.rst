.. include:: ../Includes.txt

.. _command:

=======
Command
=======

The extension ships with two console commands.

.. tip::

   These console commands can be setup to run from a Scheduler task
   using **Class** `Execute console commands` and then as **Schedulable Command**
   either `qbank:updateqbankfilestatuscommand: Update status of downloaded QBank files.qbank:updateqbankfilestatuscommand: Update status of downloaded QBank files.`
   or `qbank:updateqbankfiledatacommand: Update metadata and file on downloaded QBank files.`


UpdateQbankFileStatusCommand
============================

This command can be used to update status of downloaded QBank files.
Updates QBank files in TYPO3 with "change date" and "replaced by" from QBank.
This information is used in the '`UpdateQbankFileDataCommand`
to decide if files needs to be update.

.. code-block:: bash

   vendor/bin/typo3 qbank:updateqbankfilestatuscommand

The command have three options:

- `--limit n`
   Limit number of files to check per run. (default 10)
- `--interval n`
   Interval (in seconds) until files checked again (default 86400).
- `--check`
   Only display information, don\'t update records.


UpdateQbankFileDataCommand
==========================

This command can be used to update metadata and file on downloaded QBank files.
Checks for QBank files were metadata us updated or file is replaced,
and depending on `basic.autoUpdate` configuration in :ref:`global-configuration-options`
update metadata and replace file.

.. code-block:: bash

   vendor/bin/typo3 qbank:updateqbankfiledatacommand


The command have three options:

- `--limit n`
   Limit number of files to check per run. (default 10)
- `--check`
   Only display information, don\'t update records.
