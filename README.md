# Set-up Environment

Please read documents folder for windows or mac

### Pull the project

### Gen .env

Please read documents folder for ansible

### Install Library

```shell
composer install
npm install
```

## Set-up Local Server

### PHP Server
```shell
php artisan serve
```

### Vite Development Server
```shell
npm run dev
```

## Shutting Down Development Servers

### PHP Server
If you started the PHP server using `php artisan serve`, you can stop it by pressing `Ctrl + C` in the terminal where it's running.

### Vite Development Server
To stop the Vite development server:

1. If running in a separate terminal, press `Ctrl + C` in that terminal.

2. If running as a background process or you're unsure if it's still running, you can use the following command:

   
On macOS/Linux:
```shell
killall node
```

On Windows (in PowerShell):
```shell
taskkill /F /IM node.exe
```

Note: Be cautious with these commands as they will terminate all running Node.js processes.

# Setting up PHP Server and Vite Development Server in PHPStorm

This guide will walk you through the process of setting up both the PHP Server and Vite Development Server in PHPStorm for your Laravel project.

## Prerequisites

- PHPStorm installed
- Laravel project set up
- Node.js and npm installed
- Composer installed

## Setting up PHP Server

1. Open your Laravel project in PHPStorm.
2. Go to `Preferences` (macOS) or `Settings` (Windows/Linux).
3. Navigate to `Languages & Frameworks` > `PHP`.
4. Ensure your PHP language level is set correctly (e.g., PHP 8.1 for Laravel 9).
5. Under `CLI Interpreter`, click the `...` button to add a new interpreter if not already set.
6. Choose your PHP executable (e.g., `/usr/bin/php` on macOS/Linux or `C:\php\php.exe` on Windows).
7. Go to `Languages & Frameworks` > `PHP` > `Servers`.
8. Click the `+` button to add a new server:
    - Name: `Laravel`
    - Host: `localhost`
    - Port: `8000`
    - Check "Use path mappings" and map your project root to `/`.
9. Click `Apply` then `OK` to save the settings.

## Setting up Vite Development Server

1. In PHPStorm, go to `Run` > `Edit Configurations`.
2. Click the `+` button and select `npm` from the list.
3. Set up the configuration:
    - Name: `Vite Dev Server`
    - package.json: Select your project's `package.json` file
    - Command: Choose `run` from the dropdown
    - Scripts: Type `dev` (or your Vite script name from package.json)
4. In the "Before launch" section, click `+` and add `Run External Tool`.
5. In the "External Tools" dialog, click `+` to add a new tool:
    - Name: `Kill Vite Server`
    - Program: `killall` (macOS/Linux) or `taskkill` (Windows)
    - Arguments: `node` (macOS/Linux) or `/F /IM node.exe` (Windows)
    - Working directory: `$ProjectFileDir$`
6. Click `OK` to save the external tool, then `OK` again to save the run configuration.

## Running the Servers

### PHP Server
1. In the top-right corner of PHPStorm, click on the dropdown next to the run button.
2. Select `Edit Configurations`.
3. Click the `+` button and select `PHP Built-in Web Server`.
4. Set up the configuration:
    - Name: `Laravel Server`
    - Host: `localhost`
    - Port: `8000`
    - Document root: Select your project's `public` folder
5. Click `Apply` then `OK`.
6. Select `Laravel Server` from the run configurations dropdown and click the green play button.

### Vite Development Server
1. Select `Vite Dev Server` from the run configurations dropdown.
2. Click the green play button to start the Vite server.

Now both your PHP Server and Vite Development Server should be running simultaneously. You can access your Laravel application at `http://localhost:8000`.

## Stopping the Servers

- To stop either server, click the red square stop button in the Run tool window.
- Alternatively, go to `Run` > `Stop` in the top menu.

Remember to stop both servers when you're done with development to free up system resources.
