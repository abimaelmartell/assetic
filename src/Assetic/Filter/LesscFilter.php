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

    private $parserOptions;

    public function __construct($lesscBin = '/usr/local/bin/lessc', $nodeBin = null)
    {
        $this->lesscBin = $lesscBin;
        $this->nodeBin = $nodeBin;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $command = $this->nodeBin ? array($this->nodeBin, $this->lesscBin) : array($this->lesscBin);

        // temporal io
        $input = tempnam(sys_get_temp_dir(), 'assetic_lessc');
        file_put_contents($input, $asset->getContent());

        $process = $this->createProcessBuilder($command)
            ->add('--include-path='. $asset->getSourceDirectory())
            ->add($input)
            ->getProcess();

        $code = $process->run();
        
        if ($code != 0) {
            throw FilterException::fromProcess($process)->setInput($asset->getContent());
        }

        $asset->setContent($process->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
