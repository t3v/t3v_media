#!/usr/bin/env sh

# === Variables ===

TYPO3_VERSION="^7.6"

# === Commands ===

# Remove Composer lock file if available
if [ -f composer.lock ]; then
  rm composer.lock
fi

# Install TYPO3 and all other required dependencies
composer require typo3/cms="$TYPO3_VERSION"

# Reset the changes
git checkout composer.json

# Try to keep environment pollution down, EPA loves us
unset TYPO3_VERSION