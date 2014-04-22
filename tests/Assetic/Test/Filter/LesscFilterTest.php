<?php

namespace Assetic\Test\Filter;

use Assetic\Filter\LesscFilter;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;

class LesscFilterTest extends FilterTestCase {

    public function setUp()
    {
        $this->filter = new LesscFilter();
    }

    public function testFilterLoad()
    {
        $asset = new StringAsset('.foo{.bar{width:(1+1);}}');
        $asset->load();

        $this->filter->filterLoad($asset);

        $this->assertEquals(".foo .bar {\n  width: 2;\n}\n", $asset->getContent(), '->filterLoad() parses the content');
    }


    public function testLoadPath()
    {
        $expected = <<<EOF
.foo {
  color: blue;
}
.foo {
  color: red;
}

EOF;

        $this->filter->addLoadPath(__DIR__.'/fixtures/less');

        $asset = new StringAsset('@import "main";');
        $asset->load();

        $this->filter->filterLoad($asset);

        $this->assertEquals($expected, $asset->getContent(), '->filterLoad() adds load paths to include paths');
    }

    public function testSettingLoadPaths()
    {
        $expected = <<<EOF
.foo {
  color: blue;
}
.foo {
  color: red;
}
.bar {
  color: #ff0000;
}

EOF;

        $this->filter->setLoadPaths(array(
            __DIR__.'/fixtures/less',
            __DIR__.'/fixtures/less/import_path',
        ));

        $asset = new StringAsset('@import "main"; @import "_import"; .bar {color: @red}');
        $asset->load();

        $this->filter->filterLoad($asset);

        $this->assertEquals($expected, $asset->getContent(), '->filterLoad() sets load paths to include paths');
    }
}
