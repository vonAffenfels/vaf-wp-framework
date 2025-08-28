#!/bin/bash

# Prepare Composer for private repositories with SSH deploy keys
# Usage: Set environment variables like REPOSITORY_NAME="matcher;private_key" and run this script

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verbose mode
VERBOSE=${VERBOSE:-false}

log() {
    if [[ "$VERBOSE" == "true" ]]; then
        echo -e "${GREEN}[INFO]${NC} $1"
    fi
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Check dependencies
command -v jq >/dev/null 2>&1 || error "jq is required but not installed"

# Check if composer.json exists
if [[ ! -f "composer.json" ]]; then
    error "composer.json not found in current directory"
fi

# Create SSH directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Initialize SSH config if it doesn't exist
if [[ ! -f ~/.ssh/config ]]; then
    touch ~/.ssh/config
    chmod 600 ~/.ssh/config
fi

# Counter for unique host aliases
index=0
processed_repos=()

log "Starting repository authentication setup..."

while IFS='=' read -r -d '' var_name var_value; do
    log "Processing $var_name"

    # Split matcher and private key at first semicolon
    IFS=';' ARR=($var_value)
    matcher="${ARR[0]}"
    private_key="${ARR[1]}"

    if [[ -z "$matcher" ]] || [[ -z "$private_key" ]]; then
        warn "Skipping $var_name: invalid format (expected 'matcher;private_key')"
        continue
    fi

    log "Looking for repositories matching: $matcher"

    # Find matching repositories in composer.json
    matching_urls=$(jq -r --arg matcher "$matcher" '.repositories[]? | select(.url | contains($matcher)) | .url' composer.json 2>/dev/null)

    if [[ -z "$matching_urls" ]]; then
        warn "No repositories found matching '$matcher'"
        continue
    fi

    # Process each matching repository URL
    while IFS= read -r repo_url; do
        [[ -z "$repo_url" ]] && continue

        # Skip if we've already processed this repository
        if [[ " ${processed_repos[@]} " =~ " ${repo_url} " ]]; then
            log "Repository $repo_url already processed, skipping"
            continue
        fi

        log "Processing repository: $repo_url"

        # Extract host from git SSH URL (git@host:org/repo.git)
        if [[ "$repo_url" =~ ^git@([^:]+):(.+)$ ]]; then
            host="${BASH_REMATCH[1]}"
            repo_path="${BASH_REMATCH[2]}"
        else
            warn "Skipping $repo_url: not a valid git SSH URL format"
            continue
        fi

        log "Extracted host: $host, repository path: $repo_path"

        # Create unique host alias
        host_alias="repo_host_${index}"
        key_file="$HOME/.ssh/repo_key_${index}"

        log "Creating SSH config for alias: $host_alias"

        # Add SSH config entry
        cat >> ~/.ssh/config << EOF

# Auto-generated for repository: $repo_url
Host $host_alias
    HostName $host
    User git
    IdentityFile $key_file
    IdentitiesOnly yes
    StrictHostKeyChecking accept-new
EOF

        # Write private key to file
        echo "$private_key" > "$key_file"
        chmod 600 "$key_file"

        log "Created private key file: $key_file"

        # Configure git URL rewriting for this specific repository
        rewrite_url="git@${host_alias}:${repo_path}"
        git config --global url."$rewrite_url".insteadOf "$repo_url"

        log "Configured git URL rewriting: $repo_url -> $rewrite_url"

        # Track processed repository
        processed_repos+=("$repo_url")

        ((index++)) || true
    done <<< "$matching_urls"

    # Clear the private key from environment for security
    unset "$var_name"
    log "Cleared $var_name from environment"
done < <(env -0 | grep -z '^REPOSITORY_')

ls -ld ~/.ssh
ls -l ~/.ssh

if [[ $index -eq 0 ]]; then
    warn "No repositories were configured for authentication"
else
    echo -e "${GREEN}Successfully configured authentication for $index repositories${NC}"
fi

log "Repository authentication setup complete"
