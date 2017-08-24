# zf-component-template

This repository provides a template for use when creating a new Zend Framework
component, be it for ZF, Expressive, or Apigility.

## Usage

Clone the repo:

```console
$ git clone https://github.com/weierophinney/zf-component-template <new project name>`
```

Enter the new repo directory: 

```console
$ cd <new project name>
```

Run the setup script. This requires a component name (which can include the
organization, if it will not live under the zendframework organization), the
top-level namespace for the component (which should be provided within quotation
marks, e.g., `"Zend\Proof"`), and, optionally, the component type (one of
"component", "expressive", or "apigility"):

```console
$ ./setup.php zend-proof "Zend\Proof"
```

The script does the following:

- Replaces the strings `{org}`, `{repo}`, `{year}`, `{namespace}`,
  `{namespace-test}`, and `{type}` with values provided from the command line,
  or derived from them or the environment.
- Copies the `README.md.dist` file over this one.
- Removes the `.git/` subdirectory.
- Removes itself.
- Provides a list of `git` commands for you to run to get started developing the
  new component.

Once done, you can start developing your component.
