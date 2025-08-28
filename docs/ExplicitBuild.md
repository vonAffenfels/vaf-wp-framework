# Explicit Build

This guide covers the vonAffenfels WordPress Framework's explicit build system, which provides control over when and how
the Symfony container cache is created.

## Table of Contents

- [TL;DR](#tldr)
- [Overview](#overview)
- [Container Cache Management](#container-cache-management)
- [Build Process](#build-process)
- [Private Repository Authentication](#private-repository-authentication)
- [GitHub Actions Integration](#github-actions-integration)
- [Technical Details](#technical-details)
- [Examples](#examples)
- [Migration Guide](#migration-guide-from-manual-builds-to-automated-cicd)
- [Troubleshooting](#troubleshooting)

## TL;DR

**Problem**: Framework automatically creates container cache files during development, causing unwanted cache
directories and potential permission issues.

**Solution**: Add `use OnlyCreateCacheExplicitlyOnBuild;` to your Plugin/Theme class to prevent automatic caching. Use
GitHub Actions with `composer build-container` for production builds.

**Quick Setup**:

```php
use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

class MyPlugin extends Plugin {
    use OnlyCreateCacheExplicitlyOnBuild; // Prevents automatic cache creation
}
```

**Result**: Clean development environment + optimized production builds via CI/CD.

## Overview

PHP plugins using the vonAffenfels WordPress Framework internally use a Symfony container. In development environments,
this container is built on-the-fly for every request, which is acceptable for development but not suitable for
production.

In production, a "cached" container is required to speed up execution and because the plugin folder is often not
writable for the plugin to create the cache there. The cached container is created by running `composer build-container`
while the `vendor` folder from `composer install` is present.

## Container Cache Management

### The Problem

By default, the framework automatically creates container cache files during normal bootup. This behavior can be
problematic in development environments where:

- Cache directories are accidentally created
- File permissions may cause issues
- Development workflows are disrupted

### The Solution: OnlyCreateCacheExplicitlyOnBuild Trait

The framework provides an opt-in trait to prevent automatic container cache creation during normal bootup:

```php
use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

class MyPlugin extends Plugin {
    // Very visible opt-in to prevent automatic container caching
    use OnlyCreateCacheExplicitlyOnBuild;
}
```

### How It Works

When you use the `OnlyCreateCacheExplicitlyOnBuild` trait:

1. **Development**: Container is built and compiled but never cached to disk during normal bootup
2. **Explicit Build**: `composer build-container` continues to work for creating production caches
3. **Backward Compatibility**: Default behavior unchanged for existing plugins

## Build Process

### Development vs Production

- **Development**: Use the trait to prevent automatic caching, container built on-the-fly
- **Production**: Use GitHub Actions to run `composer build-container` and commit the result

### The Build Command

```bash
composer build-container
```

This command:

1. Creates a new instance of your plugin/theme class
2. Forces container compilation and caching
3. Generates optimized container files in the `container/` directory

### Integration with CI/CD

The build process is designed to integrate with GitHub Actions for automated container generation when building
releases.

## Private Repository Authentication

### The Challenge

WordPress plugins often have private Composer dependencies installed using VCS repository entries. This makes
`composer install` fail in CI environments because they lack SSH keys to access private repositories.

### Deploy Key Limitations

GitHub only allows adding a public SSH key to one repository, making it impossible to use a single deploy key for
multiple private repositories.

### Automated Solution

We've implemented an automated solution using
the [prepare-composer-for-actions.sh](../examples/prepare-composer-for-actions.sh) script that:

1. **Parses environment variables** in format: `REPOSITORY_NAME="matcher;private_key"`
2. **Automatically detects git hostnames** from repository URLs in composer.json
3. **Creates unique SSH host aliases** for each repository (repo_host_0, repo_host_1, etc.)
4. **Configures git URL rewriting** using `git config url.insteadOf` rules
5. **Supports any git hosting service** (GitHub, GitLab, Bitbucket, self-hosted)

### Environment Variable Format

```bash
REPOSITORY_CONVERSION="vnrag/wp-plugin-conversion;-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAFwAAAAdzc2gtcn
...
-----END OPENSSH PRIVATE KEY-----"
```

### How the Script Works

For each `REPOSITORY_*` environment variable:

1. **Split** the matcher pattern and private key using bash arrays with IFS (handles multiline keys)
2. **Find** matching repositories in composer.json using jq
3. **Extract** hostname from git SSH URLs automatically
4. **Create** SSH config entries with unique aliases
5. **Configure** git URL rewriting: `git@github.com:org/repo` → `git@repo_host_0:org/repo`

### Security Advantages

- **Isolation**: Each deploy key only accesses its specific repository
- **No cross-contamination**: Customer repos can't access company private repositories
- **Automatic cleanup**: Private keys are removed from environment after processing
- **Granular access**: Only the exact repositories needed are accessible

## GitHub Actions Integration

### Complete Workflow Example

See [update-on-tag.yml](../examples/update-on-tag.yml) for a working example with a single private repository dependency
saved in the `REPOSITORY_CONVERSION` github repository secret.

### Step-by-Step Setup

1. **Create deploy keys** for each private repository in your composer.json
2. **Add secrets** to your GitHub repository:
   ```
   REPOSITORY_CONVERSION: "vnrag/wp-plugin-conversion;[PRIVATE_KEY]"
   REPOSITORY_PX_USER: "vnrag/wp-plugin-px-user;[PRIVATE_KEY]"
   ```
3. **Copy the prepare-composer-for-actions.sh script** to your repository root
4. **Create workflow files** in `.github/workflows/`
5. **Test the workflow** by pushing to your repository

## Technical Details

### SSH URL Rewriting Mechanics

The script uses git's `url.insteadOf` configuration to transparently redirect repository access:

- **Original**: `git@github.com:vnrag/wp-plugin-conversion.git`
- **Rewritten**: `git@repo_host_0:vnrag/wp-plugin-conversion.git`
- **SSH config maps** `repo_host_0` back to `github.com` with the correct private key

### Multiline Key Handling

The script properly handles multiline private keys using bash arrays:

```bash
# Instead of: IFS=';' read -r matcher private_key <<< "$var_value"
# Use bash array splitting to handle multiline values properly
IFS=';' read -ra MATCHER_KEY <<< "$var_value"
matcher="${MATCHER_KEY[0]}"
private_key="${MATCHER_KEY[1]}"
```

### Why This Approach?

1. **Security**: Avoided Personal Access Tokens that would expose all user repositories
2. **Maintainability**: Automated the SSH workaround instead of manual configuration
3. **Compatibility**: Works with any git hosting service, not just GitHub
4. **Developer-friendly**: No changes needed to composer.json (still uses SSH URLs)

## Examples

### Plugin Setup

```php
<?php

namespace MyCompany\MyPlugin;

use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

class MyPlugin extends Plugin
{
    // Prevent automatic container cache creation in development
    use OnlyCreateCacheExplicitlyOnBuild;
    
    // Your plugin implementation...
}
```

### Theme Setup

```php
<?php

namespace MyCompany\MyTheme;

use VAF\WP\Framework\Theme;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

class MyTheme extends Theme
{
    // Prevent automatic container cache creation in development
    use OnlyCreateCacheExplicitlyOnBuild;
    
    // Your theme implementation...
}
```

### Local Development

```bash
# Development - no cache created automatically
# Container built on-the-fly for each request

# Production build - create optimized cache
composer build-container
```

### Docker Development

```yaml
# docker-compose.yaml for testing
services:
  test:
    image: debian:stable
    stdin_open: true
    tty: true
    volumes:
      - .:/app
    working_dir: /app
    env_file:
      - env.private
    environment:
      HOME: /root
```

## Migration Guide: From Manual Builds to Automated CI/CD

This section provides a complete step-by-step guide for migrating from the old manual approach (where containers were hopefully built and committed during development) to the new automated CI/CD system.

### Prerequisites

Before starting, ensure you have:
- A plugin/theme using the vonAffenfels WordPress Framework
- Private composer dependencies configured with SSH URLs in composer.json
- Admin access to your private repositories on GitHub/GitLab
- Access to your company's secret management system (1Password, Vault, etc.)

### Step 1: Clear Old Git Hooks (Keep the Package!)

If your project uses `brainmaestro/composer-git-hooks` with a pre-commit hook that attempts to build the container, you need to clear it **without removing the package** to ensure all developers get their hooks cleared automatically.

**Why keep the package?** Removing the dependency would leave lingering git hooks on developers' machines. By keeping it and clearing the hooks, everyone gets automatic cleanup on their next `composer install/update`.

#### Edit composer.json

Find the current hooks configuration (example from existing projects):

```json
{
    "require-dev": {
        "brainmaestro/composer-git-hooks": "^3.0"
    },
    "scripts": {
        "post-install-cmd": "cghooks add --ignore-lock",
        "post-update-cmd": "cghooks update"
    },
    "extra": {
        "hooks": {
            "config": {
                "stop-on-failure": ["pre-commit"]
            },
            "pre-commit": [
                "echo 'Building container...' && pwd && if [ ! -d 'vendor' ] ; then composer install ; fi && composer build-container && git add container/"
            ]
        }
    }
}
```

**Change it to:**

```json
{
    "require-dev": {
        "brainmaestro/composer-git-hooks": "^3.0"
    },
    "scripts": {
        "post-install-cmd": "cghooks add --ignore-lock",
        "post-update-cmd": "cghooks update"
    },
    "extra": {
        "hooks": {
            "pre-commit": []
        }
    }
}
```

#### Update and commit the changes

```bash
# Clear hooks for all developers
composer update brainmaestro/composer-git-hooks

# Commit the change
git add composer.json composer.lock
git commit -m "Clear git pre-commit hooks - migrating to CI/CD builds"
git push
```

**Verify**: After other developers pull and run `composer install`, their pre-commit hooks will be automatically removed.

### Step 2: Update Your Plugin/Theme Class

Add the `OnlyCreateCacheExplicitlyOnBuild` trait to prevent automatic container cache creation:

```php
<?php

namespace MyCompany\MyPlugin;

use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

class MyPlugin extends Plugin
{
    // Prevent automatic container cache creation in development
    use OnlyCreateCacheExplicitlyOnBuild;
    
    // Your existing plugin implementation...
}
```

**Commit this change:**

```bash
git add src/MyPlugin.php  # Your plugin class file
git commit -m "Add OnlyCreateCacheExplicitlyOnBuild trait to prevent automatic caching"
```

### Step 3: Create SSH Deploy Keys for Private Dependencies

For each private repository in your composer.json, you need to create a dedicated SSH deploy key.

#### 3.1 Generate SSH Key Pair

For each private repository dependency:

```bash
# Replace 'my-private-repo' with a descriptive name
ssh-keygen -t ed25519 -f ~/.ssh/deploy-key-my-private-repo -N ""

# This creates:
# ~/.ssh/deploy-key-my-private-repo (private key)
# ~/.ssh/deploy-key-my-private-repo.pub (public key)
```

Alternatively vault offers to create ssh keys directly in their UI.

#### 3.2 Add Public Key to Repository

1. Go to your private repository on GitHub
2. Navigate to **Settings** → **Deploy keys**
3. Click **Add deploy key**
4. Enter a title like "CI/CD Build Access for [Main Plugin Name]"
5. Paste the contents of the `.pub` file:
   ```bash
   cat ~/.ssh/deploy-key-my-private-repo.pub
   ```
6. **Do NOT check** "Allow write access" (read-only is sufficient)
7. Click **Add key**

#### 3.3 Store Private Key in Company Vault

**🔐 CRITICAL SECURITY STEP:**

1. Copy the private key content:
   ```bash
   cat ~/.ssh/deploy-key-my-private-repo
   ```
2. Store it in your company's secret management system (1Password, HashiCorp Vault, etc.)
3. Use a clear naming convention like: "Deploy Key: [Repository Name] - [Main Plugin Name]"
4. Include metadata: repository URL, main plugin/project name, creation date
5. **Delete the local key files after storing in vault:**
   ```bash
   rm ~/.ssh/deploy-key-my-private-repo*
   ```

#### 3.4 Add to GitHub Actions Secrets

1. Go to your main plugin repository on GitHub
2. Navigate to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Name it using the pattern: `REPOSITORY_[DESCRIPTIVE_NAME]`
5. For the value, use the format: `repository-matcher;private-key-content`  
   The repository matcher is the organization/repo path (e.g., `vnrag/wp-plugin-x-auth`), and the private key is the full multiline private key.

**Example:**
- Secret name: `REPOSITORY_X_AUTH`
- Secret value: 
  ```
  vnrag/wp-plugin-x-auth;-----BEGIN OPENSSH PRIVATE KEY-----
  b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAFwAAAAdzc2gtcn
  ...
  -----END OPENSSH PRIVATE KEY-----
  ```

### Step 4: Setup Build Scripts and GitHub Actions

#### 4.1 Add the Composer SSH Script

Copy the authentication script from the framework examples to your repository:

```bash
# Copy the script from the framework examples
cp vendor/vonaffenfels/vaf-wp-framework/examples/prepare-composer-for-actions.sh .

# Make it executable
chmod +x prepare-composer-for-actions.sh

# Commit it
git add prepare-composer-for-actions.sh
git commit -m "Add SSH authentication script for private repositories"
```

#### 4.2 Create GitHub Actions Workflows

Copy and customize the build workflow from the framework examples. Create `.github/workflows/build-container.yml` by copying the example:

```bash
mkdir -p .github/workflows
cp vendor/vonaffenfels/vaf-wp-framework/examples/update-on-tag.yml .github/workflows/build-container.yml
```

Edit `.github/workflows/build-container.yml` to customize it for your project:
- Update the `REPOSITORY_*` environment variables to match your secrets
- Adjust the PHP version if needed
- Modify the workflow name and description

**Note**: The workflow uses `git add -f container/` to force-add container files that are ignored by .gitignore. This is intentional - it keeps development environments clean while allowing CI/CD to commit the built containers for production.

For additional workflows like testing container freshness or running unit tests, you can create similar workflows following the same pattern:
- Copy the build workflow as a starting point
- Modify the trigger events (e.g., `on: [push, pull_request]`)
- Adjust the steps for your specific needs (testing vs building)

The key components that remain consistent across all workflows:
- SSH setup using `bash prepare-composer-for-actions.sh`
- PHP setup and composer installation
- Environment variables for `REPOSITORY_*` secrets

### Step 5: Test and Verify the Setup

#### 5.1 Test Private Repository Access

Push your changes and verify the workflows can access private repositories:

```bash
git push origin main
```

Check the Actions tab in GitHub to see if the container freshness test passes.

#### 5.2 Test Container Build on Tag

Create and push a tag to trigger the build:

```bash
git tag v1.0.0-test
git push origin v1.0.0-test
```

Verify that:
1. The build workflow runs successfully
2. A container is built and committed
3. The commit appears in your repository

#### 5.3 Verify Development Environment

On developer machines, after pulling the changes:

```bash
# Ensure no automatic cache creation during development
# (Container directory should not be created during normal plugin usage)
```

### Step 6: Clean Up and Finalize

#### 6.1 Update .gitignore

Ensure your `.gitignore` includes:

```gitignore
# Ignore all container cache files
# CI/CD will force-add them when building for production
/container/
```

#### 6.2 Remove Old Container Files (if needed)

If you have old, manually-created container files:

```bash
# Remove old containers but keep the directory
rm -rf container/*

# Let CI build and commit the new container
git add -A
git commit -m "Remove manually-built containers, CI will build them"
```

#### 6.3 Optional: Remove composer-git-hooks (Later)

After a few weeks when all developers have pulled the changes, you can optionally remove the git hooks package entirely:

```bash
# After all developers have updated their local repos:
composer remove --dev brainmaestro/composer-git-hooks

# Remove the entire "extra.hooks" section from composer.json
# Remove the cghooks scripts from composer.json
```

### Step 7: Team Documentation

Document the new process for your team:

1. **Development**: No container caching, runs on-the-fly
2. **Production**: Container automatically built and committed on tags
3. **Releases**: Create tags to trigger production builds
4. **Dependencies**: Private repo access handled automatically in CI

### Verification Checklist

- [ ] Old git hooks cleared on all developer machines
- [ ] Plugin class has `OnlyCreateCacheExplicitlyOnBuild` trait
- [ ] Deploy keys created for all private repositories
- [ ] Private keys stored in company vault
- [ ] GitHub Actions secrets configured
- [ ] Build workflows created and tested
- [ ] Container freshness test passes
- [ ] Tag-based builds work correctly
- [ ] Development environment clean (no automatic caching)

## Troubleshooting

### Common Issues

**Problem**: `composer build-container` fails with "Please run composer install"
**Solution**: Ensure `vendor/` directory exists and contains all dependencies

**Problem**: Private repository authentication fails
**Solution**:

- Verify deploy key is added to the repository
- Check that the environment variable format is correct
- Ensure the matcher pattern matches your composer.json repository URL

**Problem**: Container cache not being used in production
**Solution**:

- Verify the `container/` directory is committed to your repository
- Check file permissions on the container cache files
- Ensure the trait is only used in development environments

### Debug Mode

Enable verbose logging in the SSH setup script:

```bash
VERBOSE=true bash prepare-composer-for-actions.sh
```

### Verify SSH Configuration

After running the setup script, check your SSH configuration:

```bash
cat ~/.ssh/config
```

Look for auto-generated entries like:

```
# Auto-generated for repository: git@github.com:vnrag/wp-plugin-conversion.git
Host repo_host_0
    HostName github.com
    User git
    IdentityFile /home/runner/.ssh/repo_key_0
    IdentitiesOnly yes
    StrictHostKeyChecking accept-new
```

### Test Repository Access

Test that private repository access works:

```bash
git ls-remote git@repo_host_0:vnrag/wp-plugin-conversion.git
```

This should successfully list the repository's branches without authentication errors.
