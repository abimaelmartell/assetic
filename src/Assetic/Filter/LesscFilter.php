<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;

/**
 * Filter for the `lessc` compiler
 *
 * @link https://github.com/less/less.js/
 * @author Abimael Martell <me@abimaelmartell.com>
 */
class LesscFilter extends BaseNodeFilter
{
    private $lesscBin;

    private $nodeBin;

    private $compress = false;

    private $cleanCss = false;

    private $loadPaths = array();

    /**
     * @param string $lesscBin Path to the lessc bin
     * @param string $nodeBin optional Path to the node bin
     */
    public function __construct($lesscBin = '/usr/local/bin/lessc', $nodeBin = null)
    {
        $this->lesscBin = $lesscBin;
        $this->nodeBin = $nodeBin;
    }

    /**
     * Load the filter into the asset
     * @param AssetInterface $asset
     */
    public function filterLoad(AssetInterface $asset)
    {
        $command = $this->nodeBin ? array($this->nodeBin, $this->lesscBin) : array($this->lesscBin);

        // create temporal io
        $input = tempnam(sys_get_temp_dir(), 'assetic_lessc');
        file_put_contents($input, $asset->getContent());

        // extract asset's relative path
        if ($dir = $asset->getSourceDirectory()) {
            $this->addLoadPath($dir);
        }

        $pb = $this->createProcessBuilder($command);

        if ($this->compress) {
            $pb->add('--compress');
        }

        if ($this->cleanCss) {
            $pb->add('--clean-css');
        }

        if (!empty($this->loadPaths)) {
            $pb->add('--include-path=' . join($this->loadPaths, ':'));
        }

        $pb->add($input);

        $process = $pb->getProcess();

        $code = $process->run();
        
        if ($code != 0) {
            throw FilterException::fromProcess($process)->setInput($asset->getContent());
        }

        $asset->setContent($process->getOutput());
    }

    /**
     * Compress output using clean-css
     * @param bool $cleanCss
     */
    public function setCleanCss($cleanCss = true)
    {
        $this->cleanCss = $cleanCss;
    }

    /**
     * Compress output by removing some whitespaces
     * @param bool $compress
     */
    public function setCompress($compress = true)
    {
        $this->compress = $compress;
    }

    /**
     * Add a path to the lessc includes path
     * @param string $loadPath
     */
    public function addLoadPath($loadPath)
    {
        $this->loadPaths[] = $loadPath;
    }

    /**
     * Set the lessc includes path
     * @param array $loadPaths
     */
    public function setLoadPaths(array $loadPaths)
    {
        $this->loadPaths = $loadPaths;
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
