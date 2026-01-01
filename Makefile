VERSION = "1.0.2"
PACKAGE = plg_content_jouserfields
ZIPFILE = $(PACKAGE)-$(VERSION).zip
UPDATEFILE = $(PACKAGE)-update.xml
ROOT = $(shell pwd)


.PHONY: $(ZIPFILE)

ALL : $(ZIPFILE) fixsha

ZIPIGNORES = -x "fix*.*" -x "Makefile" -x "*.git*" -x "*.svn*" -x "thumbs/*" -x "*.zip"

$(ZIPFILE): 
	@echo "-------------------------------------------------------"
	@echo "Creating zip file for: $*"
	@rm -f $@
	@(cd $(ROOT); zip -r $@ * $(ZIPIGNORES))

fixversions:
	@echo "Updating all install xml files to version $(VERSION)"
	@find . \( -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec  ./fixvd.sh {} $(VERSION) \;

revertversions:
	@echo "Reverting all install xml files"
	@find . \( -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec git checkout {} \;

fixsha:
	@echo "Updating update xml files with checksums"
	@(cd $(ROOT);./fixsha.sh $(ZIPFILE) $(UPDATEFILE))





