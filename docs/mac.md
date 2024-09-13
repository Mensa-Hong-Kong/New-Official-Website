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
(echo; echo 'eval "$(/opt/homebrew/bin/brew shellenv)"') >> ~/.zprofile
eval "$(/opt/homebrew/bin/brew shellenv)"
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

### PhpEnv

1. install:

```shell
git clone git@github.com:phpenv/phpenv.git ~/.phpenv
git clone https://github.com/php-build/php-build $(phpenv root)/plugins/php-build
```

2. add to ~/.zprofile

```shell
export PATH="$HOME/.phpenv/bin:$PATH"
eval "$(phpenv init -)"

export LDFLAGS="-L/usr/local/opt/jpeg/lib"
export CPPFLAGS="-I/usr/local/opt/jpeg/include"
export PATH="/usr/local/opt/jpeg/bin:$PATH"
export PKG_CONFIG_PATH="/usr/local/opt/jpeg/lib/pkgconfig"
```

### Packages to Install for php 8.1.25

-   bzip2
-   libpng
-   libjpeg
-   libiconv
-   icu4c
-   oniguruma
-   tidy-html5
-   libzip

commend:

```shell
brew install {package}
```

### php 8.1.25

1. install:

```shell
PHP_BUILD_CONFIGURE_OPTS="--with-bz2=$(brew --prefix bzip2) --with-iconv=$(brew --prefix libiconv)" phpenv install 8.1.25
phpenv global 8.1.25
```

### php 8.1.25

1. install:

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
