.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.  Check: ÄÖÜäöüß

.. include:: ../Includes.txt

.. _user-manual:

===========
User Manual
===========

The import and export tasks can be long running processes. Therefore they are executed using the command line dispatcher.

You can execute the dispathcer from the root of your website:

.. code-block:: bash

	./typo3/cli_dispatch.phpsh

The available tasks can be found under the *extbase* cliKey:

.. code-block:: bash

    ./typo3/cli_dispatch.phpsh extbase help

    The following commands are currently available:

    EXTENSION "YAML_CONFIGURATION":
    -------------------------------------------------------------------------------
      tsconfig:generate                        Generate TSConfig configuration
                                               files from a YAML configuration

      export:backendusers                      Export be_users table to yml file
      export:backendgroups                     Export be_groups table to yml file
      export:frontendusers                     Export fe_users table to yml file
      export:frontendgroups                    Export fe_groups table to yml file
      export:table                             Export a table to yml file

      import:backendusers                      Import backend users from yml file
      import:backendgroups                     Import backend groups from yml file
      import:frontendusers                     Import frontend users from yml file
      import:frontendgroups                    Import frontend groups from yml file
      import:table                             Import table data from yml file

.. note::
	Some commands accept parameters. See './typo3/cli_dispatch.phpsh extbase help <command identifier>' for more information about a specific command.

Please see the :ref:`Command Reference` for an explanation of the commands.
