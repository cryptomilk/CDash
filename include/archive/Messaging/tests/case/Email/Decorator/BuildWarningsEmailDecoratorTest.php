<?php

use CDash\Config\Config;
use CDash\Messaging\Collection\BuildErrorCollection;
use CDash\Messaging\Email\Decorator\BuildWarningsEmailDecorator;

class BuildWarningsEmailDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testConstructSetsBuildErrorCollection()
    {
        $sut = new BuildWarningsEmailDecorator();
        $this->assertInstanceOf(BuildErrorCollection::class, $sut->getTopicCollection());
    }

    public function testGetBodyTemplate()
    {
        $sut = new BuildWarningsEmailDecorator();
        $expected = "*Warnings*\n%s";
        $actual = $sut->getBodyTemplate();
        $this->assertEquals($expected, $actual);
    }

    public function testGetSubjectTemplate()
    {
        $sut = new BuildWarningsEmailDecorator();
        $expected = '';
        $actual = $sut->getSubjectTemplate();
        $this->assertEquals($expected, $actual);
    }

    public function testGetTemplateTopicItems()
    {
        $sut = new BuildWarningsEmailDecorator();
        $expected = ['one', '2', 'many', 'build', 'errors'];
        $build = $this->getMock('Build', [], [], '', false);
        $build
            ->expects($this->once())
            ->method('GetErrors')
            ->with($this->equalTo(['type' => Build::TYPE_WARN]))
            ->will($this->returnValue($expected));
        $actual = $sut->getTemplateTopicItems($build, '');
        $this->assertEquals($expected, $actual);
    }

    public function testGetItemTemplateValuesWithErrorHavingSourceFile()
    {
        $sut = new BuildWarningsEmailDecorator();
        $srcFile = '/path/to/src/file';
        $srcLine = '1012';
        $hostUrl = 'https://www.tld.com/cdash';
        $buildId = '4545';
        $errText = 'blah, bleh... bleh, bleh, ahem';

        $buildError = $this->getMock('BuildError', [], [], '', false);
        $buildError->SourceFile = $srcFile;
        $buildError->SourceLine = $srcLine;
        $buildError->BuildId = $buildId;
        $buildError->Text = $errText;

        Config::set('CDASH_BASE_URL', $hostUrl);

        $expected = [$srcFile, $srcLine, $hostUrl, $buildId, $errText];
        $actual = $sut->getItemTemplateValues($buildError);
        $this->assertEquals($expected, $actual);
    }

    public function testGetItemTemplateValuesWithNoSourceFile()
    {
        $sut = new BuildWarningsEmailDecorator();
        $errText = 'blah, bleh... bleh, bleh, ahem';
        $postCxt = 'this is the post (bleh) context';

        $buildError = $this->getMock('BuildError', [], [], '', false);
        $buildError->PostContext = $postCxt;
        $buildError->Text = $errText;


        $expected = [$errText, $postCxt];
        $actual = $sut->getItemTemplateValues($buildError);
        $this->assertEquals($expected, $actual);
    }

    public function testGetItemTemplateWithErrorHavingSourceFile()
    {
        $sut = new BuildWarningsEmailDecorator();
        $srcFile = '/path/to/src/file';

        $buildError = $this->getMock('BuildError', [], [], '', false);
        $buildError->SourceFile = $srcFile;

        $expected = "%s line %u (%s/viewBuildError.php?type=1&buildid=%u)\n%s";

        $actual = $sut->getItemTemplate($buildError);
        $this->assertEquals($expected, $actual);
    }

    public function testGetItemTemplateWithNoSourceFile()
    {
        $sut = new BuildWarningsEmailDecorator();
        $buildError = $this->getMock('BuildError', [], [], '', false);

        $expected = "%s\n%s\n";

        $actual = $sut->getItemTemplate($buildError);
        $this->assertEquals($expected, $actual);
    }
}
