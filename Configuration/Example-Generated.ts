// Doktype: 1: Standard

// Backend Layout: 2: Home

// Column: 2: Header

// Access: Editor (role_recateur)
[usergroup = 82] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(2)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4,5)
		}
		CType.keepItems := addToList()
	}
}
[global]

// Access: Editor-in-Chief (role_eindredacteur)
[usergroup = 77] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(2)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	special.show := addToList(tuece_homeslider)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(3,4,5)
			removeItems := removeFromList(2)
		}
		CType.keepItems := addToList(17, 2, list, tuece_homeslider)
	}
}
[global]

// Access: Admin
[adminUser = 1] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(2)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	special.show := addToList(tuece_homeslider)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(3,4,5)
			removeItems := removeFromList(2)
		}
		CType.keepItems := addToList(17, 2, list, tuece_homeslider)
	}
}
[global]


// Column: 3: Left

// Access: Editor (role_recateur)
[usergroup = 82] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(3)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4,5)
		}
		CType.keepItems := addToList()
	}
}
[global]

// Access: Editor-in-Chief (role_eindredacteur)
[usergroup = 77] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(3)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, textpic)
	special.show := addToList(div, tuece_newslatest)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,4,5)
			removeItems := removeFromList(3)
		}
		CType.keepItems := addToList(17, 2, image, text, textpic, div, tuece_newslatest)
	}
}
[global]

// Access: Admin
[adminUser = 1] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(3)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, textpic, bullets, table, html)
	special.show := addToList(div, tuece_newslatest)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,4,5)
			removeItems := removeFromList(3)
		}
		CType.keepItems := addToList(17, 2, image, text, textpic, bullets, table, html, div, tuece_newslatest)
	}
}
[global]


// Column: 4: Middle

// Access: Editor (role_recateur)
[usergroup = 82] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(4)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4,5)
		}
		CType.keepItems := addToList()
	}
}
[global]

// Access: Editor-in-Chief (role_eindredacteur)
[usergroup = 77] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(4)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, textpic)
	special.show := addToList(div, tuece_teaser, tuece_eventlatest)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,5)
			removeItems := removeFromList(4)
		}
		CType.keepItems := addToList(17, 2, image, text, textpic, div, tuece_teaser, tuece_eventlatest)
	}
}
[global]

// Access: Admin
[adminUser = 1] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(4)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, textpic, bullets, table, html)
	special.show := addToList(div, tuece_teaser, tuece_eventlatest)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,5)
			removeItems := removeFromList(4)
		}
		CType.keepItems := addToList(17, 2, image, text, textpic, bullets, table, html, div, tuece_teaser, tuece_eventlatest)
	}
}
[global]


// Column: 5: Right

// Access: Editor (role_recateur)
[usergroup = 82] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(5)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4,5)
		}
		CType.keepItems := addToList()
	}
}
[global]

// Access: Editor-in-Chief (role_eindredacteur)
[usergroup = 77] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(5)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, uploads)
	special.show := addToList(div, tuece_teaser, tuece_promotionboxnarrow, tuece_directlyto, tuece_galleryteaser, tuece_ldapcontact, tuece_mediateaser, tuece_followus)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4)
			removeItems := removeFromList(5)
		}
		CType.keepItems := addToList(17, 2, image, text, textpic, list, uploads, div, tuece_teaser, tuece_promotionboxnarrow, tuece_directlyto, tuece_galleryteaser, tuece_ldapcontact, tuece_mediateaser, tuece_followus)
	}
}
[global]

// Access: Admin
[adminUser = 1] && [userFunc = tx_wwwtuenl_hasBackendLayout(2)] && [userFunc = tx_wwwtuenl_hasColPos(5)] && [userFunc = tx_wwwtuenl_hasDoktype(1)]
mod.wizards.newContentElement.wizardItems {
	common.show := addToList(image, text, uploads, bullets, html)
	special.show := addToList(div, tuece_teaser, tuece_promotionboxnarrow, tuece_directlyto, tuece_contact, tuece_vacancylist, tuece_galleryteaser, tuece_ldapcontact, tuece_mediateaser, tuece_followus)
}
TCEFORM {
	tt_content {
		colPos {
			removeItems := addToList(2,3,4)
			removeItems := removeFromList(5)
		}
		CType.keepItems := addToList(17, 2, image, text, list, uploads, bullets, html, div, tuece_teaser, tuece_promotionboxnarrow, tuece_directlyto, tuece_contact, tuece_vacancylist, tuece_galleryteaser, tuece_ldapcontact, tuece_mediateaser, tuece_followus)
	}
}
[global]