# Beacon YouTube Scraper Setup

The commands in this readme are all run through a command line, such as 
Terminal on macOS.

## Starting and Stopping the App

To start the app use `cd <path/to/project> ddev start && ddev launch`. To stop
the app, use the `ddev stop` command from the app's directory. 

## Saving Video Data

Video data is saved to your computer only. If you would like to back up the
data saved to your computer you may do so by running the 
`ddev export-db > backup.sql.gz` command from the app's directory. This saves
a `backup.sql.gz` file to the app's directory. To restore run the
`ddev import-db --file=backup.sql.gz` from the app's directory.

## Installation

### Prerequisites

This app needs the following tools to be installed to run the scraper in a
local container:

1. [Xcode Command Line Tools](https://developer.apple.com/xcode/resources/) (macOS only)
2. [Homebrew](http://brew.sh) (macOS only)
3. [DDEV](https://ddev.com/get-started/)
4. [OrbStack](https://orbstack.dev/download) (macOS only, DDEV's recommended provider)

### Initial App Setup

After initial setup you can use the instructions in the "Starting and Stopping
the App section".

1. Install XCode command line tools (macOS only): `xcode-select --install`.
2. Clone the repository: `git clone -b master <url> beacon`.
3. Install DDEV and OrbStack using Homebrew.
   * You do not need to do the "Create a Project" step in the DDEV 
     instructions.
4. Change into the project folder: `cd beacon`.
5. Start the project containers: `ddev start`.
6. Install project dependencies: `ddev composer install`.
7. Create the database: `ddev console doctrine:migrations:migrate`.
8. Open the [site](https://beacon.ddev.site) in your browser: `ddev launch`.

## Updating

1. Change into the project folder: `cd <path/to/project>`.
2. Pull changes from the repository: `git pull`.
3. Start DDEV, if it is not running: `ddev start`.
4. Install updated project dependencies: `ddev composer install`.
5. Apply database updates: `ddev console doctrine:migrations:migrate`.
