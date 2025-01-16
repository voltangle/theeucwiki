#!/bin/bash
# Applies patches. Simple as that.

patch -p1 < patches/$1/*.patch
