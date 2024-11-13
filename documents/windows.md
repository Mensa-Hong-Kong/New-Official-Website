## Software

-   VSCode
-   Docker
-   HeidiSQL

## Windows Subsystem for Linux (WSL)

### Installation

https://learn.microsoft.com/en-us/windows/wsl/install

1. Open PowerShell as an Administrator
1. `wsl --install -d Debian`

### Packages to Install

-   git
-   curl
-   wget
-   vim
-   bash-completion
-   libpq-dev
-   libfreetype6-dev

### Setup your SSH key for GitHub

https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent

In your WSL terminal, create a public and private key for your GitHub account.

```shell
ssh-keygen -t ed25519 -C "your_email@altamm.com.hk"
```

Copy your _public_ key into your GitHub website profile.

`Settings > SSH and GPG keys > New SSH Key`

Add the following config to `~/.ssh/config`

```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519
```

It's a good idea to use the same SSH agent session across shells.
You can achieve this by adding the following to your `~/.profile`:

```shell
SSH_ENV="$HOME/.ssh/agent-environment"

function start_agent {
    echo "Initialising new SSH agent..."
    /usr/bin/ssh-agent | sed 's/^echo/#echo/' > "${SSH_ENV}"
    echo succeeded
    chmod 600 "${SSH_ENV}"
    . "${SSH_ENV}" > /dev/null
    /usr/bin/ssh-add;
}

# Source SSH settings, if applicable

if [ -f "${SSH_ENV}" ]; then
    . "${SSH_ENV}" > /dev/null
    #ps ${SSH_AGENT_PID} doesn't work under cywgin
    ps -ef | grep ${SSH_AGENT_PID} | grep ssh-agent$ > /dev/null || {
        start_agent;
    }
else
    start_agent;
fi
```

Add your private key to your agent

`ssh-add ~/.ssh/id_ed25519`

### phpenv

https://github.com/phpenv/phpenv

Helps to manage multiple PHP installations.

```shell
git clone git@github.com:phpenv/phpenv.git ~/.phpenv
echo 'export PATH="$HOME/.phpenv/bin:$PATH"' >> ~/.profile
echo 'eval "$(phpenv init -)"' >> ~/.profile
exec $SHELL -l

git clone https://github.com/php-build/php-build $(phpenv root)/plugins/php-build
~/.phpenv/plugins/php-build/install-dependencies.sh
```

#### Install PHP

```shell
PHP_BUILD_CONFIGURE_OPTS="--with-pgsql=/usr/include/postgresql --with-pdo-pgsql=/usr/include/postgresql --with-freetype" phpenv install 8.1.25
phpenv global 8.1.25
```

#### PHP Composer

https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md

Composer is a dependency manager for PHP.

The link above contains a `wget` command that you can use to perform a programatic installation.

Then move `composer.phar` to become globally available:

```shell
sudo mv composer.phar /usr/local/bin/composer
```

### pyenv

https://github.com/pyenv/pyenv

Lets you easily switch between multiple versions of Python.

```shell
git clone https://github.com/pyenv/pyenv.git ~/.pyenv
echo 'export PYENV_ROOT="$HOME/.pyenv"' >> ~/.profile
echo 'command -v pyenv >/dev/null || export PATH="$PYENV_ROOT/bin:$PATH"' >> ~/.profile
echo 'eval "$(pyenv init -)"' >> ~/.profile
exec "$SHELL"

# Install build dependencies
# https://github.com/pyenv/pyenv/wiki#suggested-build-environment
sudo apt update; sudo apt install build-essential libssl-dev zlib1g-dev \
libbz2-dev libreadline-dev libsqlite3-dev curl \
libncursesw5-dev xz-utils tk-dev libxml2-dev libxmlsec1-dev libffi-dev liblzma-dev
```

#### Install Python

Check the version [here](https://github.com/Alta-Multimedia-Limited/alta-hk-legacy/blob/main/ansible/.python-version)

```shell
pyenv install 3.11.4
pyenv global 3.11.4
```

### nvm

https://github.com/nvm-sh/nvm

Allows you to quickly install and use different versions of node via the command line.

```shell
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.5/install.sh | bash
```

Add the following to your `~/.profile`:

```shell
export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" # This loads nvm
```

#### Install NodeJS

```shell
nvm install --lts
```

## VSCode Extensions

-   WSL (ms-vscode-remote.remote-wsl)
-   PHP by DEVSENSE (DEVSENSE.phptools-vscode)
-   HTML Format (mohd-akram.vscode-html-format)
-   HTML CSS Support (ecmel.vscode-html-css)
-   Auto Complete Tag (formulahendry.auto-complete-tag)
-   AutoFileName (JerryHong.autofilename)
-   Bootstrap InteSense (hossaini.bootstrap-intellisense)
-   Bootstrap 5 & Font Awesome Snippets (HansUXdev.bootstrap5-snippets)
-   JavaScript Snippet Pack (akamud.vscode-javascript-snippet-pack)
-   Laravel Extension Pack (onecentlin.laravel-extension-pack)
-   Laravel Goto View (ctf0.laravel-goto-view)
-   Thunder Client (rangav.vscode-thunder-client)
