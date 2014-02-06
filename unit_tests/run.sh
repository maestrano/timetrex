#!/bin/bash
phpunit -d max_execution_time=86400 --configuration config.xml $@
