<?php
/**
 * This file is part of the FreeDSx LDAP package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\FreeDSx\Ldap\Exception;

use FreeDSx\Ldap\Exception\UrlParseException;
use PhpSpec\ObjectBehavior;

class UrlParseExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UrlParseException::class);
    }

    function it_should_extend_exception()
    {
        $this->shouldBeAnInstanceOf('\Exception');
    }
}
