.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.  Check: ÄÖÜäöüß

.. include:: ../Includes.txt

.. _Command Reference:

Command Reference
=================

.. note::
  This reference uses ``./typo3/cli_dispatch.php extbase`` as the command to
  invoke.

The commands in this reference are shown with their full command identifiers.
On your system you can use shorter identifiers, whose availability depends
on the commands available in total (to avoid overlap the shortest possible
identifier is determined during runtime).

To see the shortest possible identifiers on your system as well as further
commands that may be available, use::

  ./typo3/cli_dispatch.php extbase help

.. note::
  Some commands accept parameters. See ``./typo3/cli_dispatch.phpsh extbase help <command identifier>`` for more information about a specific command.

The following reference was automatically generated from code on 01-02-16

.. contents:: Available Commands
  :local:
  :depth: 1
  :backlinks: top




yaml_configuration:export:backendgroups
***************************************

**Export be_groups table to yml file**





Options
^^^^^^^

``--file``
  Path to the yml file. It is advised to store this outside of the web root.
``--skip-columns``
  A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
``--include-deleted``
  Export deleted records. Default: **false**
``--include-hidden``
  Export hidden/disable records. Default: **false**




yaml_configuration:export:backendusers
**************************************

**Export be_users table to yml file**





Options
^^^^^^^

``--file``
  Path to the yml file. It is advised to store this outside of the web root.
``--skip-columns``
  A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
``--include-deleted``
  Export deleted records. Default: **false**
``--include-hidden``
  Export hidden/disable records. Default: **false**
``--be-user-match-group-by-title``
  If set to true, references to table be_groups in field ``usergroup`` are stored as group titles not as uid's. Make sure to exclude field ``uid`` in ``--skip-columns`` in order to prevend duplicate entries for primary key ``uid`` when importing the records. Default: **false**




yaml_configuration:export:frontendgroups
****************************************

**Export fe_groups table to yml file**





Options
^^^^^^^

``--file``
  Path to the yml file. It is advised to store this outside of the web root.
``--skip-columns``
  A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
``--include-deleted``
  Export deleted records. Default: **false**
``--include-hidden``
  Export hidden/disable records. Default: **false**




yaml_configuration:export:frontendusers
***************************************

**Export fe_users table to yml file**





Options
^^^^^^^

``--file``
  Path to the yml file. It is advised to store this outside of the web root.
``--skip-columns``
  A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
``--include-deleted``
  Export deleted records. Default: **false**
``--include-hidden``
  Export hidden/disable records. Default: **false**




yaml_configuration:export:table
*******************************

**Export a table to yml file**



Arguments
^^^^^^^^^

``--table``
  The name of the table to export



Options
^^^^^^^

``--file``
  Path to the yml file. It is advised to store this outside of the web root.
``--skip-columns``
  A comma separated list of column names to skip. Default: **uc,crdate,lastlogin,tstamp**
``--include-deleted``
  Dump deleted records. Default: **false**
``--include-hidden``
  Dump hidden/disable records. Default: **false**




yaml_configuration:import:backendgroups
***************************************

**Import backend groups from yml file**

Import backend groups from yml file into be_users table. Existing records will be updated.



Options
^^^^^^^

``--match-fields``
  Comma separated list of fields used to match configurations to database records. Default: **title**
``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed




yaml_configuration:import:backendusers
**************************************

**Import backend users from yml file**

Import backend users from yml file into be_users table. Existing records will be updated.



Options
^^^^^^^

``--match-fields``
  Comma separated list of fields used to match configurations to database records. Default: **username**
``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed
``--be-user-match-group-by-title``
  If set to true, references to table ``be_groups`` in field ``usergroup`` are matched using group titles not uid's. Make sure to use option ``--be-user-match-group-by-title`` set to true when exporting the records. Default: **false**




yaml_configuration:import:frontendgroups
****************************************

**Import frontend groups from yml file**

Import frontend groups from yml file into fe_users table. Existing records will be updated.



Options
^^^^^^^

``--match-fields``
  Comma separated list of fields used to match configurations to database records. Default: **title**
``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed




yaml_configuration:import:frontendusers
***************************************

**Import frontend users from yml file**

Import frontend users from yml file into fe_users table. Existing records will be updated.



Options
^^^^^^^

``--match-fields``
  Comma separated list of fields used to match configurations to database records. Default: **username**
``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed




yaml_configuration:import:table
*******************************

**Import table data from yml file**

Import table data from yml file. Existing records will be updated.

Arguments
^^^^^^^^^

``--table``
  The name of the table to export
``--match-fields``
  Comma separated list of fields used to match configurations to database records.



Options
^^^^^^^

``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed




yaml_configuration:tsconfig:generate
************************************

**Generate TSConfig configuration files from a YAML configuration**





Options
^^^^^^^

``--file``
  Path to the yml file you wish to import. If none is given, all yml files in directories named 'Configuration' will be parsed




Tidy/Format the exported yaml
=============================

You can tidy exported yaml files using an online tool like: http://www.yamllint.com/.
If you do not trust online tools, you can also convert it locally with e.g. the following npm packages: ``js-yaml`` and ``json2yaml``.

.. code-block:: bash

   # Install dependencies globally (node.js https://nodejs.org/en/ must be installed in order to run the following command)
	npm install -g js-yaml json2yaml
   # Example: reformatting exported be_groups.yml to be_groups.formatted.yml
	js-yaml be_groups.yml | json2yaml > ./be_groups.formatted.yml
