<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2014 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Tests\Shared;

use PhpOffice\PhpWord\Shared\WordString;

/**
 * Test class for PhpOffice\PhpWord\Shared\String
 *
 * @coversDefaultClass \PhpOffice\PhpWord\Shared\String
 * @runTestsInSeparateProcesses
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Is UTF8
     */
    public function testIsUTF8()
    {
        $this->assertTrue(WordString::isUTF8(''));
        $this->assertTrue(WordString::isUTF8('éééé'));
        $this->assertFalse(WordString::isUTF8(utf8_decode('éééé')));
    }

    /**
     * OOXML to PHP control character
     */
    public function testControlCharacterOOXML2PHP()
    {
        $this->assertEquals('', WordString::controlCharacterOOXML2PHP(''));
        $this->assertEquals(chr(0x08), WordString::controlCharacterOOXML2PHP('_x0008_'));
    }

    /**
     * PHP to OOXML control character
     */
    public function testControlCharacterPHP2OOXML()
    {
        $this->assertEquals('', WordString::controlCharacterPHP2OOXML(''));
        $this->assertEquals('_x0008_', WordString::controlCharacterPHP2OOXML(chr(0x08)));
    }

    /**
     * Test unicode conversion
     */
    public function testToUnicode()
    {
        $this->assertEquals('a', WordString::toUnicode('a'));
        $this->assertEquals('\uc0{\u8364}', WordString::toUnicode('€'));
        $this->assertEquals('\uc0{\u233}', WordString::toUnicode('é'));
    }

    /**
     * Test remove underscore prefix
     */
    public function testRemoveUnderscorePrefix()
    {
        $this->assertEquals('item', WordString::removeUnderscorePrefix('_item'));
    }
}
