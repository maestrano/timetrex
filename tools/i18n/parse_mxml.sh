#!/bin/sh
grep '_(' $1 | sed -e 's/.*_(/_(/g' -e 's/}.*//g'
