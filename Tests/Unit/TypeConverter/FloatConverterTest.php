<?php

declare(strict_types=1);

namespace Mediadreams\MdEventmgtFrontend\Tests\Unit\TypeConverter;

/**
 * This file is part of the "Frontend for ext:sf_event_mgt" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2026 Christoph Daecke <typo3@mediadreams.org>
 */

use Mediadreams\MdEventmgtFrontend\TypeConverter\FloatConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FloatConverter::class)]
final class FloatConverterTest extends UnitTestCase
{
    private FloatConverter $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FloatConverter();
    }

    #[Test]
    public function convertFromReturnsNullForNullSource(): void
    {
        self::assertNull($this->subject->convertFrom(null, 'float'));
    }

    #[Test]
    public function convertFromReturnsNullForEmptyStringSource(): void
    {
        self::assertNull($this->subject->convertFrom('', 'float'));
    }

    #[Test]
    public function convertFromConvertsPlainIntegerString(): void
    {
        self::assertSame(123.0, $this->subject->convertFrom('123', 'float'));
    }

    #[Test]
    public function convertFromConvertsGermanCommaNotation(): void
    {
        self::assertSame(12.34, $this->subject->convertFrom('12,34', 'float'));
    }

    #[Test]
    public function convertFromConvertsEnglishPointNotation(): void
    {
        self::assertSame(12.34, $this->subject->convertFrom('12.34', 'float'));
    }

    #[Test]
    public function convertFromConvertsEnglishThousandsNotationWithComma(): void
    {
        // comma before point: "0,000.00" - comma is a thousands separator and gets stripped
        self::assertSame(1234.56, $this->subject->convertFrom('1,234.56', 'float'));
    }

    #[Test]
    public function convertFromConvertsGermanThousandsNotationWithPoint(): void
    {
        // point before comma: "0.000,00" - point is a thousands separator and gets stripped,
        // the comma is the decimal separator and gets converted to a point
        self::assertSame(1234.56, $this->subject->convertFrom('1.234,56', 'float'));
    }

    #[Test]
    public function convertFromReturnsErrorForNonNumericSource(): void
    {
        self::assertInstanceOf(Error::class, $this->subject->convertFrom('not-a-number', 'float'));
    }
}
