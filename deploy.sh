#!/usr/bin/env bash
# Simple helper script to deploy the plugin to the WordPress.org SVN repository.
# Adjust paths and run from the plugin root.

set -e

SLUG="gms-coming-soon"
SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="/tmp/${SLUG}-svn"

echo "Checking out SVN repository..."
rm -rf "${SVN_DIR}"
svn checkout "${SVN_URL}" "${SVN_DIR}"

echo "Syncing trunk..."
rsync -av --delete ./ "${SVN_DIR}/trunk" --exclude=".git" --exclude=".github" --exclude="deploy.sh"

cd "${SVN_DIR}"

echo "Adding new files..."
svn add --force trunk/* || true

echo "Checking for changes..."
svn status

echo "If everything looks good, commit with:"
echo "  cd ${SVN_DIR}"
echo "  svn commit -m \"Release new version\""
