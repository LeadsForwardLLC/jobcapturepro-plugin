#!/usr/bin/env bash
set -e

# Usage: bash scripts/release.sh <version>
# Example: bash scripts/release.sh 1.0.8

VERSION="$1"

if [ -z "$VERSION" ]; then
    echo "Error: version argument required."
    echo "Usage: bash scripts/release.sh <version>"
    exit 1
fi

# Validate version format
if ! echo "$VERSION" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+$'; then
    echo "Error: version must be in x.y.z format (e.g. 1.0.8)"
    exit 1
fi

BRANCH="release/$VERSION"

# Must start from dev
echo "Switching to dev and pulling latest..."
git checkout dev
git pull origin dev

# Ensure working tree is clean
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Error: working tree is not clean. Commit or stash changes before releasing."
    exit 1
fi

# Build dist files
echo "Building dist files..."
npm run build

# Create release branch
echo "Creating branch $BRANCH..."
git checkout -b "$BRANCH"

# Bump version in jobcapturepro.php (plugin header + constant)
sed -i "s/Version:           .*/Version:           $VERSION/" jobcapturepro.php
sed -i "s/define('JOBCAPTUREPRO_VERSION', '.*')/define('JOBCAPTUREPRO_VERSION', '$VERSION')/" jobcapturepro.php

# Bump version in README.TXT
sed -i "s/^Stable tag: .*/Stable tag: $VERSION/" README.TXT
sed -i "s/^version: .*/version: $VERSION/" README.TXT

echo "Version bumped to $VERSION"

# Force-add dist/ (ignored in .gitignore)
git add -f dist/

# Remove everything from git index, then re-add only prod files
echo "Stripping dev files — keeping only prod files..."
git rm -r --cached . -q

# Re-add only the files that belong in the release
PROD_FILES=(
    LICENSE.TXT
    README.TXT
    index.php
    jobcapturepro.php
    uninstall.php
    admin/class-jobcapturepro-admin.php
    admin/index.php
    includes/class-jobcapturepro-api.php
    includes/class-jobcapturepro-loader.php
    includes/class-jobcapturepro-templates.php
    includes/class-jobcapturepro.php
    includes/class-template-loader.php
    includes/index.php
    public/class-jobcapturepro-shortcodes.php
    public/index.php
    templates/checkin-card.php
    templates/checkins-slider.php
    templates/company-info.php
    templates/company-stats.php
    templates/cta-section.php
    templates/image-gallery.php
    templates/powered-by-footer.php
    templates/single-checkin.php
)

for f in "${PROD_FILES[@]}"; do
    if [ -e "$f" ]; then
        git add "$f"
    else
        echo "Warning: $f not found, skipping"
    fi
done

# Add all dist files
git add -f dist/

# Commit
git commit -m "Release $VERSION - only the prod files kept"

echo ""
echo "Done! Release branch '$BRANCH' is ready."
echo "Review the changes, then push with:"
echo "  git push -u origin $BRANCH"
