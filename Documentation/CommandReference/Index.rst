.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.  Check: ÄÖÜäöüß

.. include:: ../Includes.txt

.. _Command Reference:

Command Reference
=================

.. note::
  This reference uses the official TYPO3 CLI command ``./<DocumentRoot>/typo3/sysext/core/bin/typo3`` as the command to
  invoke.

The commands in this reference are shown with their full command identifiers.
On your system you can use shorter identifiers, whose availability depends
on the commands available in total (to avoid overlap the shortest possible
identifier is determined during runtime).

To see the shortest possible identifiers on your system as well as further
commands that may be available, use::

.. code-block:: bash

	./typo3 list

.. note::
  Some commands accept parameters (argument and options). See ``./typo3 <command identifier> --help`` for more information about a specific command.

.. contents:: Available Commands
  :local:
  :depth: 1
  :backlinks: top




yaml_configuration:yaml:export
*******************************

**Export a table to YAML file**



Arguments
^^^^^^^^^

``table``
  The name of the table to export. (Example: ``be_users``)
``file``
  Path to the yaml file. It is advised to store this outside of the web root. (Example: ``/absolute/path/to/filename.yaml``)



Options
^^^^^^^

``--indent-level``
  Indent level to make the yaml file human readable. Default: **2** (Example ``--indent-level=4``)
``--force-override``
  Force override an existing yaml file. Default: **false** (Example ``--force-override``)
``--skip-columns``
  A comma separated list of column names to skip. Default: **crdate,cruser_id,lastlogin,tstamp,uc** (Example: ``--skip-columns='crdate,cruser_id,lastlogin,tstamp,uc,uid'``)
``--use-only-columns``
  A comma separated list of column names to skip. (Example: ``--use-only-columns='uid,title'``)
``--include-deleted``
  Export deleted records. Default: **false** (Example ``--include-deleted``)
``--include-hidden``
  Export hidden/disabled records. Default: **false** (Example ``--include-hidden``)




yaml_configuration:yaml:import
*******************************

**Import table data from YAML file**

Existing records will be updated.

Arguments
^^^^^^^^^

``table``
  The name of the table which you want to import. (Example: ``be_users``)
``file``
  Path to the yaml file. If none is given, all yaml/yml files in directories named 'Configuration/YamlConfiguration' of **every active extension** will be parsed.



Options
^^^^^^^

``matchFields``
  Comma separated list of fields used to match configurations to database records. Default: **uid** (Example: ``--matchFields='uid,title'``)




Tidy/Format the exported yaml
=============================

You can tidy exported yaml files using an online tool like: http://www.yamllint.com/.
If you do not trust online tools, you can also convert it locally with e.g. the following npm packages: ``js-yaml`` and ``json2yaml``.

.. code-block:: bash

   # Install dependencies globally (node.js https://nodejs.org/en/ must be installed in order to run the following command)
	npm install -g js-yaml json2yaml
   # Example: reformatting exported be_groups.yml to be_groups.formatted.yml
	js-yaml be_groups.yml | json2yaml > ./be_groups.formatted.yml
