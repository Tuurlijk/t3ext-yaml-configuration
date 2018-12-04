.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
----------------

It provides several CLI commands that help you to:

* Export records to YAML files
* Import records from YAML files


The backend users from the introduction package may be exported to:

.. code-block:: yaml

    ---
    TYPO3:
      Data:
        be_users:
          -
            admin: "1"
            avatar: "1"
            password: $P$C8UcO6VyaSObaEOqKbMSek1vaNsT4/.
            uid: "1"
            username: admin
          -
            uid: "2"
            username: _cli_lowlevel
          -
            uid: "3"
            username: _cli_scheduler
          -
            email: username@example.com
            file_permissions:
              - readFolder
              - writeFolder
              - addFolder
              - renameFolder
              - moveFolder
              - deleteFolder
              - readFile
              - writeFile
              - addFile
              - renameFile
              - replaceFile
              - moveFile
              - files_copy
              - deleteFile
            options: "3"
            password: M$P$CdwrJ1nl.WNj5s05Ic4NxpshZoQfsS/
            realName: "Simple McEditor"
            uid: "5"
            usergroup: "1"
            username: simple_editor
          -
            email: username@example.com
            file_permissions:
              - readFolder
              - writeFolder
              - addFolder
              - renameFolder
              - moveFolder
              - deleteFolder
              - readFile
              - writeFile
              - addFile
              - renameFile
              - replaceFile
              - moveFile
              - files_copy
              - deleteFile
            options: "3"
            password: M$P$CB3YmdgehsOynTVrcROrKlhSIpuR0S1
            realName: "Advanced McEditor"
            uid: "6"
            usergroup: "3"
            username: advanced_editor

Storing backend group configurations in YAML can ease the setup of a new site where complex group and permission systems are required. The configuration can be maintained in Git and imported / updated into the database when needed.
