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
