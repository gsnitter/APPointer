<?php
namespace Sni\ExtendedOutputBundle\Tests\Service;

use Sni\ExtendedOutputBundle\Service;
use PHPUnit\Framework\TestCase;
use Sni\ExtendedOutputBundle\Entity\Viewport;
use Sni\ExtendedOutputBundle\Entity\Window;
use Symfony\Component\Console\Helper\Table;
use Sni\ExtendedOutputBundle\Service\WindowRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Terminal;

class WindowRendererTest extends TestCase
{
    public function testRender()
    {
        $viewPort = new Viewport(5, 5, 6, 3);
        $window = new Window($viewPort);
        $window->setLineNumber(7);
        $window->setCharOffset(10);
        $buffer = $window->getBuffer();

        // Insert some text with colors etc.
        $buffer->getOutput()->writeln('Some text with a single <info>green</info> word.');
        $table = new Table($buffer->getOutput());
        $table->setHeaders(['One', 'Two'])
            ->setRows([[1, 2]]);
        $table->render();
        $buffer->getOutput()->writeln('<bg=yellow;options=bold>Some bold text with yellow background.</>');

        // $STREAM = $this->buffer->getOutput()->getStream();
        // rewind($stream);
        // passthru('clear; tput sc; tput cup 10 0; cat /tmp/debug.txt; tput rc');

        $projector = $this->getMockBuilder(ProjectorInterface::class)
            ->setMethods(['getLines'])
            ->getMock();
        $projector
            ->expects($getLinesSpy = $this->once())
            ->method('getLines')
            ->will($this->returnValue(['line 1', 'line 2', 'end   ']));

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'has', 'set', 'initialized', 'getParameter', 'hasParameter', 'setParameter'])
            ->getMock();
        $container
            ->expects($containerGetSpy = $this->once())
            ->method('get')
            ->will($this->returnValue($projector));

        $terminal = $this->getMockBuilder(Terminal::class)
            ->disableOriginalConstructor()
            ->setMethods(['tputCup'])
            ->getMock();
        $terminal
            ->expects($tputSpy = $this->exactly(3))
            ->method('tputCup')
            ;

        $renderer = new WindowRenderer($container, $terminal);
        $renderer->render($window);

        $this->assertSame(
            ((($containerGetSpy->getInvocations())[0])->getParameters())[0],
            'extended.output.text.projector.wrap'
        );

        $this->assertSame(3, count($tputSpy->getInvocations()));
        $firstCallParams = (($tputSpy->getInvocations())[0])->getParameters();
        $secondCallParams = (($tputSpy->getInvocations())[1])->getParameters();
        $thirdCallParams = (($tputSpy->getInvocations())[2])->getParameters();

        // The text projector gave line, and we tput this to the terminal. 
        $this->assertSame([5, 5, 'line 1'], $firstCallParams);
        $this->assertSame([5, 6, 'line 2'], $secondCallParams);
        $this->assertSame([5, 7, 'end   '], $thirdCallParams);

        $getLinesParameters = (($getLinesSpy->getInvocations())[0])->getParameters();
        $this->assertSame([
            $window->getBuffer(),
            $window->getViewPort(),
            7,
            10
        ], $getLinesParameters);
    }
}
