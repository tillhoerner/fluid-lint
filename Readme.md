# Fluid Lint

A syntax checker for Fluid templates in TYPO3 11+

## Installation

```shell
composer req --dev christophlehmann/fluid-lint
```

## Usage

```shell
# Check a single TYPO3 Extension
composer exec typo3 -- fluidlint:check -e site_extension

# Check all TYPO3 Extensions in a directory
composer exec typo3 -- fluidlint:check -b local_packages/
```

Without any options all templates in all non-system extensions are checked. 

With `-vvv` you get more details about the templates.

### All options

```shell
composer exec typo3 -- help fluidlint:check

Description:
  Fluid Lint: Check Fluid syntax

Usage:
  fluidlint:check [options]

Options:
  -b, --base-dir=BASE-DIR                Extensions in given directory
  -e, --extension[=EXTENSION]            Extension key to check
  -p, --path[=PATH]                      File or folder path (if extensionKey is included, path is relative to this extension)
  -r, --regex=REGEX                      Extension key must match regular expression [default: ".*"]
  -x, --file-extensions=FILE-EXTENSIONS  If provided, this CSV list of file extensions are considered Fluid templates [default: "html,xml,txt"]
  -v|vv|vvv, --verbose                   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Limitations

It does not verify the types of ViewHelper arguments since arguments are not used.

## Credits

The command was extracted from [FluidTYPO3/builder](https://github.com/FluidTYPO3/builder) which is the work of [Clause Due](https://github.com/NamelessCoder).
