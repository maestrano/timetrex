#!/bin/bash

##$License$##
##
# $Revision: 679 $
# $Id: mergelocale.sh 679 2007-03-08 18:32:16Z ipso $
# $Date: 2007-03-08 10:32:16 -0800 (Thu, 08 Mar 2007) $
#
# File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
# http://osc.co.cr
##

# This script is useful for merging latest changes from the master .pot file into
# a given locale file, and then generating the corresponding .mo binary file.

# This script is intended to be run from the tools/i18n directory

# arg1 should be the locale ID
lc=$1
cd ../../interface/locale
touch $lc/LC_MESSAGES/messages.po && msgmerge --update $lc/LC_MESSAGES/messages.po ./messages.pot && msgfmt -o $lc/LC_MESSAGES/messages.mo $lc/LC_MESSAGES/messages.po
