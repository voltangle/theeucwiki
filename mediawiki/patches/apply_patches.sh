#!/bin/bash
# Applies patches. Simple as that.

set -x;
set -e;

find $MW_HOME/patches/$1/ -name '*.patch' -print0 | xargs -t -0 -L 1 -I {} sh -c 'patch -p1 < {}'

