.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
----------------

It provides several command-line controllers that help you to:

* Export records to YAML files
* Import records from YAML files
* Generate TSConfig from YAML files

It was born from the need to simplify the configuration of permissions in TYPO3. The example below shows how you can create a very specific set of rules for;
* what content elements
* can be placed in what column position
* on what document types
* on what page types
* by which groups

.. code-block:: yaml

    // Set 1
    //
    // Elements and plugins enabled for editors on regular pages with backend layout homepage

    [userFunc = MaxServ\YamlConfiguration\User\Condition::isInUserGroup(2)] && [userFunc = MaxServ\YamlConfiguration\User\Condition::hasDoktype(1)] && [userFunc = MaxServ\YamlConfiguration\User\Condition::hasBackendLayout(homepage)] && [userFunc = MaxServ\YamlConfiguration\User\Condition::hasColumnPosition(2)]
    TCEFORM {
        tt_content {
            CType.keepItems := addToList(image, textpic, companytoolbox_cardgrid, gridelements_view, image, text, textpic, list, bullets, table, html, div)
            list_type.keepItems := addToList(companycalendar_pi1, companycalendar_pi2)
        }
    }
    mod.wizards.newContentElement.wizardItems {
        common.show := addToList(image, textpic, companytoolbox_cardgrid, gridelements_view, image, text, textpic, list, bullets, table, html, div)
        plugins.show := addToList(companycalendar_pi1, companycalendar_pi2)
    }
    [global]

This is pretty complex (but effective) piece of TypoScript. To make it a bit easier to maintain we envisioned a clean YAML configuration from which we could generate this piece of TypoScript. The YAML to generate the above TypoScript looks much simpler and is a lot easier to understand and maintain:

.. code-block:: yaml

    ---
    TYPO3:
      TSConfig:
        TCEFORM:
          tt_content:
            set1:
              title: 'Set 1'
              description: 'Elements and plugins enabled for editors on regular pages with backend layout homepage'
              operator: '&&'
              userFunctions:
                - isInUserGroup(2)
                - hasDoktype(1)
                - hasBackendLayout(homepage)
                - hasColumnPosition(2)
              contentElements:
                - image
                - textpic
                - companytoolbox_cardgrid
                - gridelements_view
                - image
                - text
                - textpic
                - list
                - bullets
                - table
                - html
                - div
              plugins:
                - companycalendar_pi1
                - companycalendar_pi2

This concept proved to work so well that we extended it to storing backend user- and group-permission configurations. And when we had that, it turned out to be a small step to the generic import- and export any table to YAML commands.