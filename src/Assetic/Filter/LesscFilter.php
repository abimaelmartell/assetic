<?php

namespace Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Exception\FilterException;

class LesscFilter extends BaseNodeFilter
{
    private $lesscBin;

    private $nodeBin;

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
