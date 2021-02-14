#!/usr/bin/env php
<?php 
/**
 * Release a major, minor or patch update w/ release notes from the CHANGELOG.md file.
 *
 * Usage:
 *
 *  _build/release.php patch
 *  _build/release.php minor
 *  _build/release.php major
 *
 * Release w/o prompt confirmation:
 *
 *  _build/release.php -q patch
 *  _build/release.php --no-interactive minor
 *
 * Release w/o checking for dirty or unpushed changes:
 *
 *  _build/release.php --no-dirty-check minor
 *  _build/release.php --no-unpushed-check patch
 *
 * Run a dry-run test:
 *
 *  _build/release.php --dry-run patch
 *
 * Update the changelog:
 *
 *  _build/release.php --changelog-update patch
 */
namespace WPStaging\Vendor\lucatume\tools;

$root = \dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$changelogFile = $root . '/CHANGELOG.md';
if (!\file_exists($changelogFile)) {
    echo "\33[31mChangelog file (CHANGELOG.md, changelog.md) does not exist.\33[0m\n";
    exit(1);
}
function args()
{
    global $argv;
    $options = \getopt('q', ['not-interactive', 'no-diff-check', 'no-unpushed-check', 'dry-run', 'no-changelog-update'], $optind);
    $map = ['releaseType' => isset($argv[$optind]) ? $argv[$optind] : 'patch', 'notInteractive' => isset($options['q']) || isset($options['not-interactive']), 'noChangelogUpdate' => isset($options['no-changelog-update']), 'checkDiff' => empty($options['no-diff-check']), 'checkUnpushed' => empty($options['no-unpushed-check']), 'dryRun' => isset($options['dry-run'])];
    return static function ($key, $default = null) use($map) {
        return isset($map[$key]) ? $map[$key] : $default;
    };
}
$args = args();
if (!\in_array($args('releaseType'), ['major', 'minor', 'patch'], \true)) {
    echo "\33[31mThe release type has to be one of major, minor or patch.\33[0m\n";
    exit(1);
}
$dryRun = $args('dryRun', \false);
if (!$dryRun) {
    $currentGitBranch = \trim(\shell_exec('git rev-parse --abbrev-ref HEAD'));
    if ($currentGitBranch !== 'master') {
        echo "\33[31mCan release only from master branch.\33[0m\n";
        exit(1);
    }
    echo "Current git branch: \33[32m" . $currentGitBranch . "\33[0m\n";
}
/**
 * Parses the changelog to get the latest notes and the latest, released, version.
 *
 * @param string $changelog The absolute path to the changelog file.
 *
 * @return array<string,mixed> The map of parsed values.
 */
function changelog($changelog)
{
    $notes = '';
    $latestVersion = '';
    $f = \fopen($changelog, 'rb');
    $read = \false;
    while ($line = \fgets($f)) {
        if (\preg_match('/^## \\[unreleased]/', $line)) {
            $read = \true;
            continue;
        }
        if (\preg_match('/^## \\[(?<version>\\d+\\.\\d\\.\\d+)]/', $line, $m)) {
            $latestVersion = $m['version'];
            break;
        }
        if ($read === \true) {
            $notes .= $line;
        }
    }
    \fclose($f);
    return ['notes' => \trim($notes), 'latestVersion' => $latestVersion];
}
function updateChangelog($changelogFile, $version, callable $args, $date = null)
{
    $date = $date === null ? \date('Y-m-d') : $date;
    $changelogVersionLine = \sprintf("\n\n## [%s] %s;", $version, $date);
    $changelogFileName = \basename($changelogFile);
    $currentContents = \file_get_contents($changelogFile);
    $entryLine = '## [unreleased] Unreleased';
    if (\strpos($currentContents, $entryLine) === \false) {
        $message = 'Unreleased entry line not found; does the changelog file contain an entry like "' . $entryLine . '"?';
        echo "\33[31m{$message}\33[0m\n";
        exit(1);
    }
    $changelogContents = \str_replace($entryLine, $entryLine . $changelogVersionLine, $currentContents);
    $changelogContents = \preg_replace_callback('/\\[(?:[Uu])nreleased]:\\s+(?<repo>.*)\\/(?<previous_version>\\d+\\.\\d+\\.\\d+)...(HEAD|head)/ium', static function (array $matches) use($version) {
        return \sprintf('[%1$s]: %2$s/%3$s...%1$s' . \PHP_EOL . '[unreleased]: %2$s/%1$s...HEAD', $version, $matches['repo'], $matches['previous_version']);
    }, $changelogContents);
    echo "Changelog updates:\n\n---\n";
    echo \substr($changelogContents, 0, 1024);
    echo "\n\n[...]\n\n";
    echo \substr($changelogContents, \strlen($changelogContents) - 512);
    echo "---\n\n";
    if (!$args('dryRun', \false) && ($args('notInteractive', \false) || confirm("Would you like to proceed?"))) {
        \file_put_contents($changelogFile, $changelogContents);
        \passthru('git commit -m "doc(' . $changelogFileName . '.md) update to version ' . $version . '" -- ' . $changelogFile);
    }
}
$changelog = changelog($changelogFile);
$releaseType = $args('releaseType', 'patch');
switch ($releaseType) {
    case 'major':
        $releaseVersion = \preg_replace_callback('/(?<target>\\d+)\\.\\d\\.\\d+/', static function ($m) {
            return ++$m['target'] . '.0.0';
        }, $changelog['latestVersion']);
        break;
    case 'minor':
        $releaseVersion = \preg_replace_callback('/(?<major>\\d+)\\.(?<target>\\d)\\.\\d+/', static function ($m) {
            return $m['major'] . '.' . ++$m['target'] . '.0';
        }, $changelog['latestVersion']);
        break;
    case 'patch':
        $releaseVersion = \preg_replace_callback('/(?<major>\\d+)\\.(?<minor>\\d)\\.(?<target>\\d+)/', static function ($m) {
            return $m['major'] . '.' . $m['minor'] . '.' . ++$m['target'];
        }, $changelog['latestVersion']);
        break;
}
$releaseNotesHeader = "{$releaseVersion}\n\n";
$fullReleaseNotes = $releaseNotesHeader . $changelog['notes'];
echo "Latest release: \33[32m" . $changelog['latestVersion'] . "\33[0m\n";
echo "Release type: \33[32m" . $releaseType . "\33[0m\n";
echo "Next release: \33[32m" . $releaseVersion . "\33[0m\n";
echo "Release notes:\n\n---\n" . $fullReleaseNotes . "\n---\n";
echo "\n\n";
if (!$args('noChangelogUpdate', \false)) {
    updateChangelog($changelogFile, $releaseVersion, $args);
}
if ($args('checkDiff', \true) && !$dryRun) {
    $gitDirty = \trim(\shell_exec('git diff HEAD'));
    if (!empty($gitDirty)) {
        echo "\33[31mYou have uncommited work.\33[0m\n";
        exit(1);
    }
}
function confirm($question)
{
    $question = "\n{$question} ";
    return \preg_match('/y/i', \readline($question));
}
if ($args('checkUnpushed', \true) && !$dryRun) {
    $gitDiff = \trim(\shell_exec('git log origin/master..HEAD'));
    if (!empty($gitDiff)) {
        echo "\33[31mYou have unpushed changes.\33[0m\n";
        if (confirm('Would you like to push them now?')) {
            \passthru('git push');
        } else {
            exit(1);
        }
    }
}
\file_put_contents($root . '/.rel', $fullReleaseNotes);
$releaseCommand = 'hub release create -F .rel ' . $releaseVersion;
echo "Releasing with command: \33[32m" . $releaseCommand . "\33[0m\n\n";
if ($dryRun || $args('notInteractive', \false) || confirm('Do you want to proceed?')) {
    if (!$dryRun) {
        \passthru($releaseCommand);
    }
} else {
    echo "Canceling\n";
}
\unlink($root . '/.rel');
