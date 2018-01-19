#!/bin/bash

projectPath=$(readlink -f $(dirname $(readlink -f $0))"/..")

ln -s $projectPath"/hooks/codesniffer.sh" .git/hooks/pre-commit
