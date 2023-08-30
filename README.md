# Omeka S CLI
Omeka S CLI is a tool developed by [Libnamic](https://libnamic.com/?utm_source=omeka-s-cli-repo) that allows to manage Omeka S installs using a command line interface (CLI). The goal is to help maintain Omeka S and save time by automating daily operations such as module or theme installs and updates.
## About
This tool was created for [Libnamic Hosting](https://hosting.libnamic.com/?utm_source=omeka-s-cli-repo) and is included by default in all Omeka S installs as an extra feature for its customers. Given the benefits experienced with it, it was decided to publish it as open source under GNU AGPLv3 license, so anyone can benefit from it and contribute.
### Libnamic Hosting
Libnamic provides a hosting service specialized in Omeka that offers specific tools and expertise from a team that actually understands the application. Additionally, Libnamic Hosting achieves an average 70% of performance improvement when comparing page generation times with other main competitors. Performance is key with a big repository, and with Libnamic Hosting, a TTFB of 1s can easily be lowered to ~300ms just by switching hosting providers. Libnamic also provides Omeka consultancy and development. See [Libnamic Digital Humanities](https://digitalhumanities.libnamic.com/?utm_source=omeka-s-cli-repo)

## Install
You can just clone the repository and create a symlink in your /usr/bin folder to the main php file, omeka-s-cli.php.
![install-min](https://github.com/Libnamic/omeka-s-cli/assets/1238543/e2339bc6-5e98-4c9f-871a-73b02a60c6b0)

## Usage
The available main commands are:
- backup
- config
- core
- db
- info
- module
- theme
Run them to see what options are availbe. We're working on creating more extensive documentation.

## Examples
### Core update (not for Docker/containerized installs)
![core-update-min](https://github.com/Libnamic/omeka-s-cli/assets/1238543/3a55d835-d1e9-4167-a07a-f827044392c0)
### Module management
![modules-min](https://github.com/Libnamic/omeka-s-cli/assets/1238543/0b695679-fcc9-49a1-9fd0-8068cdabe929)
### Theme management
![themes-min](https://github.com/Libnamic/omeka-s-cli/assets/1238543/ac9ba8f6-375c-419d-bce5-39c1a1fa34b2)

## Disclaimer
This software is experimental and might not work as expected in some cases, especially since this is an initial version of it. There is no warranty and the authors and maintainers will not be liable for any damages or problems. Please keep regular backups of your sites to avoid problems. For example, Libnamic Hosting creates off-site backups of Omeka sites every 4 hours and stores them for 60 days.
