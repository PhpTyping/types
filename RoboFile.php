<?php

require_once 'vendor/autoload.php';

use Robo\Contract\VerbosityThresholdInterface;
use Robo\Tasks;
use Typing\Type\StringObject;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Class RoboFile.
 *
 * This file has *nix level commands and will not run on doze hosts.
 */
class RoboFile extends Tasks
{
    /**
     * Builds project.
     */
    public function build()
    {
    }

    public function testDev()
    {
        $this->styleCheck(['withFix' => true, 'withReport' => true]);
        $this->testPhpunit();
        $this->testCpd();
        $this->testMagicNumber();
        $this->testMessDetector();
        $this->testStan();
    }

    /**
     * Fixes style to specific standard: Symfony
     */
    public function styleFix()
    {
        $this
            ->taskExec("{$this->getBin('php-cs-fixer')} fix")
            ->run()
            ->stopOnFail()
        ;
        $this
            ->taskExec("{$this->getBin('phpcbf')} --standard=.phpcs.xml")
            ->run()
            ->stopOnFail()
        ;

        $this->say('Don\'t forget to commit your code if it the style fixer fixed any.');
    }

    /**
     * @param array|bool[] $opts
     */
    public function styleCheck(array $opts = ['withFix'=> false, 'withReport' => true])
    {
        $this->_deleteDir('build/phpcs');
        $this->_mkdir('build/phpcs');

        if ($opts['withFix']) {
            $this->styleFix();
        }

        $this
            ->taskExec("{$this->getBin('phpcs')} --config-set installed_paths vendor/escapestudios/symfony2-coding-standard")
            ->run()
            ->stopOnFail()
        ;

        $this
            ->taskExec("{$this->getBin('phpcs')} --standard=.phpcs.xml")
            ->run()
            ->stopOnFail()
        ;

        if ($opts['withReport']) {
            $this
                ->taskExec("{$this->getBin('phpcs')} --standard=.phpcs.xml --report=checkstyle --report-file=build/phpcs/report.xml")
                ->run()
                ->stopOnFail()
            ;
        }
    }

    /**
     * Runs PHPUnit tests, using proper environment variables and config files.
     */
    public function testPhpunit()
    {
        $this->_remove('build/phpunit');
        $this
            ->taskPhpUnit($this->getBin('phpunit'))
            ->env('XDEBUG_MODE', 'coverage')
            ->configFile('phpunit.xml.dist')
            ->run()
            ->stopOnFail()
        ;
    }

    /**
     * Tests Copy Pasta/Code Duplication.
     *
     * @param string $targetDir
     * @param int $maxCopied
     */
    public function testCpd(string $targetDir = 'src', int $maxCopied = 5)
    {
        $buildDir = 'build/php_cpd';
        $this->_remove($buildDir);
        $this->_mkdir($buildDir);
        $this
            ->taskExec(
                "{$this->getBin('phpcpd')} --min-lines={$maxCopied} --log-pmd=./{$buildDir}/log.xml {$targetDir}"
            )
            ->run()
            ->stopOnFail()
        ;
    }

    /**
     * Looks for magic numbers in source code.
     *
     * @param string $targetDir
     */
    public function testMagicNumber(string $targetDir = 'src'): void
    {
        $task = "{$this->getBin('phpmnd')} {$targetDir} --exclude=DataFixtures";
        $result = StringObject::create($this
            ->taskExec($task)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run()
            ->getMessage()
        );

        if ($result->count() > 0 && false === $result->contains('Total of Magic Numbers: 0')) {
            $this->say($result);
            throw new RuntimeException(message: 'Magic Numbers detected.');
        }

        $this->say('[PASS] No magic numbers detected.');
    }

    /**
     * Tests with Mess Detector.
     * Broken in PHP8...
     *
     * @param array|bool[] $opts
     */
    public function testMessDetector(array $opts = ['withReport' => true])
    {
        $this->_deleteDir('build/phpmd');
        $this->_mkdir('build/phpmd');
        $tasks = [
            "{$this->getBin('phpmd')} src text .phpmd.xml --exclude src/Type/Collection.php",
        ];

        if ($opts['withReport']) {
            $tasks[] = "{$this->getBin('phpmd')} src xml .phpmd.xml --reportfile build/phpmd/phpmd-src.xml --exclude src/Type/Collection.php";
        }

        foreach ($tasks as $task) {
            if (null !== $task) {
                $this->taskExec($task)->run()->stopOnFail();
            }
        }
    }

    /**
     * Tests codebase statically with PHP Stan.
     */
    public function testStan()
    {
        $this->taskExec("{$this->getBin('phpstan')}")->run()->stopOnFail();
    }

    /**
     * Tests to see if a squash is required.
     *
     * @param string $branch
     *
     * @throws Exception
     */
    public function testSquash($branch = 'master')
    {
        $process = new Process([
            'git',
            'rev-list',
            '--count',
            'HEAD',
            "^{$branch}"
        ]);
        $process->mustRun();
        $commitCount = trim($process->getOutput());

        if ($commitCount > 1) {
            throw new Exception('Please squash your commits. Maximum allowed is 1.');
        }

        $this->say('[PASS] No squashing required.');
    }

    /**
     * Test if a rebase is needed.
     *
     * @param string $target
     *
     * @throws Exception
     */
    public function testRebase($target = 'develop')
    {
        $branchProcess = new Process([
            'git',
            'rev-parse',
            '--abbrev-ref',
            'HEAD',
        ]);
        $branchProcess->mustRun();
        $branch = trim($branchProcess->getOutput());
        $remotesProcess = new Process(['git', 'remote']);
        $remotesProcess->run();
        $remotes = explode(separator: PHP_EOL, string: $remotesProcess->getOutput(), limit:PHP_INT_MAX);

        $checkoutDevelop = new Process(['git', 'checkout', "{$target}"]);
        $checkoutDevelop->run();

        // Ignore failures that happen because remote times out...
        foreach ($remotes as $remote) {
            try {
                $updateDevelopFromRemoteProcess = new Process(['git', 'fetch', "{$remote}"]);
                $updateDevelopFromRemoteProcess->setIdleTimeout(10)->run();
                $pullDevelopFromRemoteProcess = new Process(['git', 'pull', "{$remote}", "{$target}"]);
                $pullDevelopFromRemoteProcess->setIdleTimeout(10)->run();
            } catch (ProcessTimedOutException) {
                continue;
            }
        }

        $checkoutBranch = new Process(['git', 'checkout', "{$branch}"]);
        $checkoutBranch->run();

        $hash1Process = new Process(['git', 'show-ref', '--heads', '-s', "{$target}"]);
        $hash1Process->run();
        $hash1 = trim($hash1Process->getOutput());

        $hash2Process = new Process(['git', 'merge-base', "{$target}", "{$branch}"]);
        $hash2Process->run();
        $hash2 = trim($hash2Process->getOutput());

        if ($hash1 !== $hash2) {
            throw new Exception(
                sprintf('Please rebase your branch against %s to test against the latest code.', $target)
            );
        }

        $this->say('[PASS] No rebase required.');
    }

    /**
     * @param string $tool
     * @return string
     */
    private function getBin(string $tool): string
    {
        $file = sprintf('%s/bin/%s', dirname(__FILE__), $tool);

        if (false === is_file($file)) {
            throw new RuntimeException("File '{$file}' not found. Run composer install first.");
        }

        return $file;
    }
}
