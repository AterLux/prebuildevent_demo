# prebuildevent_demo
demonstration of PHP-scripts usage to generate source files

This is a Atmel Studio 7 project for AVR microcontroller.

The project is used to demonstrate usage of PHP scripts, used to generate or update source files.

This project is for ATMega328P AVR-family MCU. It requires an OLED-display with SSD1306 controller and SPI interface enabled.

Connect the display pins to the MCU's outputs as follows:
- display's SCLK input to MCU's SCK output (PB5)
- display's SDAT input to MCU's MOSI output (PB3)
- display's CS# input to MCU's SS output (PB2)
- display's D/C# input to MCU's PB1 output

**When connecting to the SSD1306 display controller, MCU should be 3.3V powered, since SSD1306 is not tolerant to 5V-logic** 

## Usage of php in pre-build events

First, install the php preprocessor. Download the latest version of php5 https://windows.php.net/download#php-5.6 and unzip it into the arbitrary directory. 

Then, update the PATH system variable for it to point to that directory. 
Then restart Atmel Studio for new path settings to take effect.

Rename php.ini-development into php.ini, open it, find [Date] section uncomment "date.timezone = " line, and put a value that fits you: (http://php.net/manual/en/timezones.php).
For example: *Europe/Moscow*. 

In the Atmel Studio project, click "Properties", and choose Build Events tab. 
In the field "Pre-build event command line" you can specify calls to the php-scripts using format: "php -f php_script.php arguments ..."


## [update_version.php](https://github.com/AterLux/prebuildevent_demo/blob/master/scripts/update_version.php)

This script is used to update build version and information about build time each time the project up to build.

The script scans given directories for files, whose names matches the pattern and find out the maximal modification date among them.

Then, if this date is greater than the date of specified .h-file, then .h-file is scanned for macros, defined using *#define* directive followed by the macro name and a numerical value, are processed as follows:
- *BUILD_YEAR*, *BUILD_MONTH*, *BUILD_DAY*, *BUILD_HOUR*, *BUILD_MINUTE*, *BUILD_SECOND* - are being updated to the respective value of the maximal modification date of files matched to pattern in specified directories
- *BUILD_NUMBER* - is being automatically increased by one.

#### Usage:

    php -f update_version.php ["pattern=<pattern>"] <version.h> <dir1> [<dir2> [<dir3>...]]
- \<pattern\> - a regular expression pattern to match filenames. Default is \.(c|cpp|h|inc|asm|s)$
- \<version.h\> - full path to the file with macro defintions.
- \<dir...\> - directories to look for source files.

#### Examle of usage in the *Pre-build event* field

    php -f "$(SolutionDir)scripts\update_version.php" "$(MSBuildProjectDirectory)\build_version.h" "$(MSBuildProjectDirectory)" "$(MSBuildProjectDirectory)\display" "$(MSBuildProjectDirectory)\generated"


## [convert_images.php](https://github.com/AterLux/prebuildevent_demo/blob/master/scripts/convert_images.php)

This scans a specified directory for 24-bit RGB uncompressed *.bmp of size 128x64 and converts it into black&white image data, compatible with a monochrome display, connected to the MCU.
Resulting image data is represented as arrays in the generated source file.

#### Usage:

    php -f convert_images.php <dir_with_bmps> <dest_c_file>
    
#### Examle of usage in the *Pre-build event* field

    php -f "$(SolutionDir)scripts\convert_images.php" "$(SolutionDir)\images" "$(MSBuildProjectDirectory)\generated\images.inc"
