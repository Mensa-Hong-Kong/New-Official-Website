## Software

-   VSCode
-   Docker
-   Sourcetree (optional)

### Homebrew

1. install:

```shell
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2. follow terminal output to set shellenv to zprofile

```shell
echo >> /Users/tszyuloveyou/.zprofile
echo 'eval "$(/usr/local/bin/brew shellenv)"' >> /Users/tszyuloveyou/.zprofile
eval "$(/usr/local/bin/brew shellenv)"
```

### NVM

1. install:

```shell
brew install nvm
```

2. add to ~/.zprofile

```shell
export NVM_DIR="$HOME/.nvm"
  [ -s "/usr/local/opt/nvm/nvm.sh" ] && \. "/usr/local/opt/nvm/nvm.sh"  # This loads nvm
  [ -s "/usr/local/opt/nvm/etc/bash_completion.d/nvm" ] && \. "/usr/local/opt/nvm/etc/bash_completion.d/nvm"  # This loads nvm bash_completion
```

### Node and NPM

1. install:

```shell
nvm install --lts 
```

### PyEnv

1. install:

```shell
brew install pyenv
```

2. add to ~/.zprofile

```shell
export PYENV_ROOT="$HOME/.pyenv"
export PATH="$PYENV_ROOT/bin:$PATH"
export PIPENV_PYTHON="$PYENV_ROOT/shims/python"

plugin=(
  pyenv
)

eval "$(pyenv init -)"
eval "$(pyenv virtualenv-init -)"
```

### Python

1. install:

```shell
pyenv install 3.11.4
pyenv global 3.11.4
```

### php 8.1.25

1. first time install:

```shell
brew install php@8.1
brew link php@8.1
```

2. change new php version

```shell
brew unlink php@8.0
brew install php@8.1
brew link php@8.1
```

### Composer

```shell
brew install composer
```

## VSCode Extensions

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
-   Database Client (cweijan.vscode-database-client2)
