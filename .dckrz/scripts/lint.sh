#!/bin/bash

vendor/bin/phpcbf --standard=PSR2 src public
vendor/bin/phpcs --standard=PSR2 src public
