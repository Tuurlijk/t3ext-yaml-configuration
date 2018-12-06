.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.  Check: ÄÖÜäöüß

.. include:: ../Includes.txt

.. _user-manual:

===========
User Manual
===========

The import and export tasks can be long running processes. Therefore they are executed using the command line interface of TYPO3.

You can execute the command from the root of your website:

.. code-block:: bash

	./<DocumentRoot>/typo3/sysext/core/bin/typo3

In a composer based TYPO3 installation the TYPO3 CLI executable can be found in the configured folder of your composer.json file in ``config.bin-dir``.
This is very likely outside the website root.

.. code-block:: bash

   ./typo3

The available tasks can be found with the *TYPO3* CLI:

.. code-block:: bash

    ./typo3 list

    Available commands:
     yaml
      yaml:export                                   Exports a database table into a YAML file
      yaml:import                                   Imports data into tables from YAML configuration

Please see the :ref:`Command Reference` for an explanation of the commands.
