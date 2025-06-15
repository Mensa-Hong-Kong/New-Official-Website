## Software

-   VSCode
-   Docker
-   Sourcetree (optional)

### Homebrew

1. Install:

```shell
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2. Follow terminal output to set shellenv to zprofile

```shell
touch ~/.zprofile
echo >> ~/.zprofile
echo 'eval $(/opt/homebrew/bin/brew shellenv)' >> ~/.zprofile
eval "$(/usr/local/bin/brew shellenv)"
```

### NVM

1. Install:

```shell
brew install nvm
```

2. Manually add these lines to `~/.zprofile`

```shell
export NVM_DIR="$HOME/.nvm"
  [ -s "/usr/local/opt/nvm/nvm.sh" ] && \. "/usr/local/opt/nvm/nvm.sh"  # This loads nvm
  [ -s "/usr/local/opt/nvm/etc/bash_completion.d/nvm" ] && \. "/usr/local/opt/nvm/etc/bash_completion.d/nvm"  # This loads nvm bash_completion
```

### Node and NPM

1. Install:

```shell
nvm install --lts
```

### PyEnv

1. Install:

```shell
brew install pyenv
```

2. Manually add these lines to `~/.zprofile`

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

1. Install:

```shell
pyenv install 3.11.4
pyenv global 3.11.4
```

### php 8.2

1. First time install:

```shell
brew install php@8.2
brew link php@8.2
```

2. If you have installed php 8.1 before, you need to unlink it first and change new php version:

```shell
brew unlink php@8.1
brew install php@8.2
brew link php@8.2
```

### Composer

1. Install:

```shell
brew install composer
```

## VSCode Extensions

-   PHP by DEVSENSE (DEVSENSE.phptools-vscode)
-   Auto Complete Tag (formulahendry.auto-complete-tag)
-   Auto File Name (JerryHong.autofilename)
-   Bootstrap 5 & Font Awesome Snippets (HansUXdev.bootstrap5-snippets)
-   Bootstrap IntelliSense (hossaini.bootstrap-intellisense)
-   Code Spell Checker (streetsidesoftware.code-spell-checker)
-   HTML CSS Support (ecmel.vscode-html-css)
-   HTML Format (mohd-akram.vscode-html-format)
-   JavaScript Snippet Pack (akamud.vscode-javascript-snippet-pack)
-   Laravel & PHP Essentials (TechieCouch.laravel-php-essentials)
-   PHP by DEVSENSE (DEVSENSE.phptools-vscode)
-   Svelte Auto Import (pivaszbs.svelte-autoimport)
-   Svelte for VS Code (svelte.svelte-vscode)
-   Svelte Intellisense (ardenivanov.svelte-intellisense)
-   Svelte Snippets (JakobKruse.svelte-kit-snippets)
-   svelte-format (melihaltintas.svelte-format)
-   Thunder Client (rangav.vscode-thunder-client)
-   Tailwind CSS IntelliSense (bradlc.vscode-tailwindcss)

### Setup Inertia.js from Laravel & PHP Essentials

add config to {project root}/vscode/settings.js

```
"inertia": {
    "pages": "resources/js/Pages/**/*.svelte",
    "defaultExtension": ".svelte",
}
```