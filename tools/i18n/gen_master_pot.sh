#!/bin/bash

##$License$##
##
# File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
# http://osc.co.cr
##

# This script generates the master .pot file containing english strings
# for translation.
#
# These strings are parsed from:
#   * smarty templates between {t}some text{/t} blocks
#   * PHP Files containing TTi18n::gettext("some text") function calls.

# We could also parse from static DB text, but so far see no need for
# doing so.

DEPTH=../..
LOCALE_ROOT=$DEPTH/interface/locale
POT_FILENAME=messages.pot

#---- Ensure pot file exists ----
touch $LOCALE_ROOT/$POT_FILENAME

#---- Extract strings from templates ----
echo "Parsing templates..."
TMP_FILE=/tmp/gen_master_pot_tmp.txt
find $DEPTH/templates -name "*.tpl" | grep -v "\.git" | xargs -i php tsmarty2c.php \{\} | xgettext --from-code=UTF-8 --no-wrap --language=C --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -

#---- Extract strings from PHP Files ----
# Note that we want to extract from TTi18n::gettext() calls.
# xgettext ignores the "TTi18n::" bit and sees the gettext(). So it works.
echo "Parsing PHP Files..."
find $DEPTH/ -name "*.php" | grep -v templates_c | grep -v "\.git" | xargs cat | xgettext --from-code=UTF-8 --no-wrap --keyword=getText --join-existing --language=PHP --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -

#FLEX_LOCALE_ROOT=$DEPTH/flex/src
#if [ -d $FLEX_LOCALE_ROOT ]; then
#    echo "Parsing FLEX files from: $FLEX_LOCALE_ROOT"
#    find $FLEX_LOCALE_ROOT/ -regextype posix-egrep -regex ".*/.*.as" | grep -v "\.git" | xargs cat | xgettext --from-code=UTF-8 --no-wrap --keyword=getText --join-existing --language=PHP --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -
#    find $FLEX_LOCALE_ROOT/ -regextype posix-egrep -regex ".*/.*.mxml" -exec $LOCALE_ROOT/../../tools/i18n/parse_mxml.sh {} \; | xgettext --from-code=UTF-8 --no-wrap --keyword=getText --join-existing --language=PHP --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -
#fi

echo "Parsing JS Files..."
#find $DEPTH/ -name "*.js" | grep -v "\.git" | xargs cat | sed 's/<br>/ /g' | sed 's/\n/ /g' | xgettext --from-code=UTF-8 --no-wrap --keyword=_ --join-existing --language=Javascript --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -
find $DEPTH/ -name "*.js" | grep -v "\.git" | xargs cat | xgettext --from-code=UTF-8 --no-wrap --keyword=_ --join-existing --language=Javascript --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -

#---- Extract strings from DB Tables with static strings ----
###  Not necessary for TimeTrex at this time ###


#---- Done ----
echo "Done!  POT File is in " $LOCALE_ROOT/$POT_FILENAME
