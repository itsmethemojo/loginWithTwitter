#!/bin/bash

projectPath=$(readlink -f $(dirname $(readlink -f $0))"/..")

ln -s $projectPath"/hooks/pre-commit.sh" .git/hooks/pre-commit
