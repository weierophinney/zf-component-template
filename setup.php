#!/usr/bin/env php
<?php
/**
 * Setup a new repository.
 *
 * Usage:
 *   ./setup.php [--help|-h|help] <repo-name> <namespace> [<repo-type>]
 *
 * The script will look for `{repo}`, `{org}`, and `{namespace}` strings
 * throughout the repository, replacing them with values based on `<repo-name>`
 * and `<namespace>`. `{org}` defaults to "zendframework" unless `<repo-name>`
 * is of the form `{org}/{repo}`.
 *
 * `<repo-type>` may be one of "component", "apigility", or "expressive",
 * and defaults to "component".
 */

// Prepare arguments
if (isset($argv[1]) && in_array($argv[1], ['-h', '--help', 'help'], true)) {
    help();
}

if (3 > $argc) {
    help(1);
}

$repo = $argv[1];
$ns   = $argv[2];
$type = $argv[3] ?? 'component';

$org  = 'zendframework';
if (false !== strstr($repo, '/')) {
    list($org, $repo) = explode('/', $repo, 2);
}

if (! in_array($type, ['component', 'expressive', 'apigility'], true)) {
    fwrite(STDERR, sprintf("Invalid <repo-type> value%s", str_repeat(PHP_EOL, 2)));
    help(1);
}
$type = 'component' === $type ? 'components' : $type;

// Remove .git directory if present
removeGit(realpath(__DIR__));

// Update all files
updateRepo(realpath(__DIR__), $org, $repo, $ns, $type);

// Finish
finish($repo);

// Functions

function help(int $exit = 0) : void
{
    $stream = $exit === 0 ? STDOUT : STDERR;
    $usage =<<< 'EOH'
Usage:
  ./setup.php [--help|-h|help] <repo-name> <namespace> [<repo-type>]

The script will look for {repo}, {org}, and {namespace} strings
throughout the repository, replacing them with values based on
<repo-name> and <namespace>. {org} defaults to "zendframework"
unless <repo-name> is of the form {org}/{repo}.

<repo-type> may be one of "component", "apigility", or "expressive",
and defaults to "component".

Examples:

  ./setup.sh zend-proof "Zend\Proof"
  ./setup.sh zfcampus/zf-proof "ZF\Proof"

On completion, this script removes itself.

EOH;

    $usage = str_replace("\n", PHP_EOL, $usage);

    fwrite($stream, $usage);
    exit($exit);
}

function removeGit(string $path) : void
{
    if (! is_dir($path . '/.git')) {
        return;
    }

    fwrite(STDOUT, sprintf("- Removing .git directory%s", PHP_EOL));
    removeDirectory($path . '/.git');
}

function removeDirectory(string $path)
{
    $rdi = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($rii as $filename => $fileinfo) {
        if ($fileinfo->isDir()) {
            rmdir($filename);
            continue;
        }
        unlink($filename);
    }
    rmdir($path);
}

function updateRepo(string $path, string $org, string $repo, string $ns, string $type) : void
{
    fwrite(STDOUT, sprintf("- Copying README.md.dist over README.md%s", PHP_EOL));
    unlink($path . '/README.md');
    rename($path . '/README.md.dist', $path . '/README.md');

    $rdi = new RecursiveDirectoryIterator($path);
    $rii = new RecursiveIteratorIterator($rdi);

    foreach ($rii as $file) {
        updateFile($file, $org, $repo, $ns, $type);
    }
}

function updateFile(SplFileInfo $file, string $org, string $repo, string $ns, string $type) : void
{
    if (! $file->isFile()) {
        return;
    }

    if ($file->getRealPath() === realpath(__FILE__)) {
        return;
    }

    // Create the namespace for tests
    $testNs = explode('\\', $ns);
    $testNs[0] .= 'Test';
    $testNs = implode('\\', $testNs);

    // Namespaces used in JSON need more specific escaping.
    $ns = $file->getExtension() === 'json'
        ? str_replace('\\', '\\\\', $ns)
        : $ns;
    $testNs = $file->getExtension() === 'json'
        ? str_replace('\\', '\\\\', $testNs)
        : $testNs;

    $patterns      = ['{org}', '{repo}', '{namespace}', '{namespace-test}', '{type}', '{year}'];
    $substitutions = [$org, $repo, $ns, $testNs, $type, date('Y')];

    $contents = file_get_contents((string) $file);
    $modified = str_replace($patterns, $substitutions, $contents);

    if ($contents === $modified) {
        return;
    }

    file_put_contents((string) $file, $modified);

    fwrite(STDOUT, sprintf("- Updated %s%s", (string) $file, PHP_EOL));
}

function finish(string $repo) : void
{
    $message =<<<'EOM'

Project {repo} is now ready to develop.

Next steps:

- git init .
- git add .
- git commit -m 'Initial creation'

EOM;

    $message = str_replace(['{repo}', "\n"], [$repo, PHP_EOL], $message);

    fwrite(STDOUT, $message);

    unlink(__FILE__);
}
