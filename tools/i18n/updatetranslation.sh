#!/bin/bash

##$License$##
##
# File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
# http://osc.co.cr
##

# this script is intended to aid with accepting incoming translations (from translators)
# in a messages.po file and merging that file into an existing messages.po.
# It then merges in any changes in the master .pot file and compiles a
# .mo binary file.

# Usage:
#  ./updatestranslation.sh <locale> <path_to_po_file_from_translator>

# Example:
#  cd tools/i18n/
#  ./updatestranslation.sh es_ES /tmp/messages.po.es_ES

# This script is intended to be run from the tools/i18n directory.

LOCALE=$1
NEW_MESSAGES=$2
dir=`pwd`

cd ../../interface/locale/$LOCALE/LC_MESSAGES/
msgmerge $NEW_MESSAGES messages.po > messages.po.new
echo `pwd`
mv messages.po.new messages.po

cd $dir
./mergelocale.sh $LOCALE
