# Fluid Lint

A syntax checker for Fluid templates in TYPO3 11+

## Installation

```shell
composer req --dev christophlehmann/fluid-lint
```

## Usage

```shell
typo3/sysext/core/bin/typo3 fluidlint:check --extension=site_extension
```

The command outputs errors and warnings. When errors occur, then it exits with exit code 1.

When you run the command without options, then all templates in all non-system extensions are checked.

In verbose mode you get more details about the templates.

## Limitations

It does not verify the types of ViewHelper arguments since arguments are not used.

### Options

```shell
  -e, --extension[=EXTENSION]    Extension key to check, if not specified will check all extensions containing Fluid templates
  -p, --path[=PATH]              File or folder path (if extensionKey is included, path is relative to this extension)
  -x, --extensions[=EXTENSIONS]  If provided, this CSV list of file extensions are considered Fluid templates [default: "html,xml,txt"]
  -v|vv|vvv, --verbose           Verbose output
```

## Credits

The command was extracted from [FluidTYPO3/builder](https://github.com/FluidTYPO3/builder) which is the work of [Clause Due](https://github.com/NamelessCoder).
