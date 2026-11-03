<?php

namespace CarlBennett\Tools\Tests\Libraries\Utility;

use \CarlBennett\Tools\Libraries\Utility\HTTPForm;
use \PHPUnit\Framework\TestCase;

class HTTPFormTest extends TestCase
{
    public function testGetConvertsNumericStrings(): void
    {
        $form = new HTTPForm(['i' => '42', 'f' => '1.5', 's' => 'text', 'a' => ['x']]);
        $this->assertSame(42, $form->get('i'));
        $this->assertSame(1.5, $form->get('f'));
        $this->assertSame('text', $form->get('s'));
        $this->assertSame(['x'], $form->get('a'));
    }

    public function testGetDefault(): void
    {
        $form = new HTTPForm();
        $this->assertNull($form->get('missing'));
        $this->assertSame('d', $form->get('missing', 'd'));
    }

    public function testSetDeleteAndRemove(): void
    {
        $form = new HTTPForm(['a' => 1]);
        $form->set('b', 2);
        $this->assertSame(['a' => 1, 'b' => 2], $form->getAll());
        $form->delete('a');
        $form->remove('b');
        $form->delete('missing');
        $this->assertSame([], $form->getAll());
    }
}
