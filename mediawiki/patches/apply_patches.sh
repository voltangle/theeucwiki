#!/bin/bash
# Applies patches. Simple as that.

find $MW_HOME/patches/$1/ -name '*.patch' -print0 | xargs -0 patch -p1

