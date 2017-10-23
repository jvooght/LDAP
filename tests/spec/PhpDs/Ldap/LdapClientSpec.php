<?php
/**
 * This file is part of the phpDS package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PhpDs\Ldap;

use PhpDs\Ldap\Entry\Entry;
use PhpDs\Ldap\LdapClient;
use PhpDs\Ldap\Operation\LdapResult;
use PhpDs\Ldap\Operation\Request\ExtendedRequest;
use PhpDs\Ldap\Operation\Request\SimpleBindRequest;
use PhpDs\Ldap\Operation\Request\UnbindRequest;
use PhpDs\Ldap\Operation\Response\BindResponse;
use PhpDs\Ldap\Operation\Response\ExtendedResponse;
use PhpDs\Ldap\Operation\Response\SearchResponse;
use PhpDs\Ldap\Operations;
use PhpDs\Ldap\Protocol\ClientProtocolHandler;
use PhpDs\Ldap\Protocol\LdapMessageResponse;
use PhpDs\Ldap\Search\Filters;
use PhpDs\Ldap\Search\Paging;
use PhpDs\Ldap\Search\Vlv;
use PhpSpec\ObjectBehavior;

class LdapClientSpec extends ObjectBehavior
{
    function let(ClientProtocolHandler $handler)
    {
        $handler->getTcpClient()->willReturn(null);
        $this->beConstructedWith(['servers' => ['foo']]);
        $this->setProtocolHandler($handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LdapClient::class);
    }

    function it_should_send_a_search_and_get_entries_back($handler)
    {
        $search = Operations::search(Filters::equal('foo', 'bar'));

        $handler->send($search)->shouldBeCalled()->willReturn(new LdapMessageResponse(1, new SearchResponse(new LdapResult(0, ''), Entry::create('dc=foo,dc=bar'))));

        $this->search($search)->shouldBeLike([Entry::create('dc=foo,dc=bar')]);
    }

    function it_should_bind($handler)
    {
        $response = new LdapMessageResponse(1, new BindResponse(new LdapResult(0, '')));
        $handler->send(new SimpleBindRequest('foo', 'bar', 3))->shouldBeCalled()->willReturn($response);

        $this->bind('foo', 'bar')->shouldBeEqualTo($response);
    }

    function it_should_construct_a_pager_helper()
    {
        $this->paging(Operations::search(Filters::equal('foo', 'bar')))->shouldBeAnInstanceOf(Paging::class);
    }

    function it_should_construct_a_vlv_helper()
    {
        $this->vlv(Operations::search(Filters::equal('foo', 'bar')), 'cn', 100)->shouldBeAnInstanceOf(Vlv::class);
    }

    function it_should_start_tls($handler)
    {
        $handler->send(Operations::extended(ExtendedRequest::OID_START_TLS))->shouldBeCalled()->willReturn(null);

        $this->startTls();
    }

    function it_should_unbind_if_requested($handler)
    {
        $handler->send(new UnbindRequest())->shouldBeCalled()->willReturn(null);

        $this->unbind();
    }

    function it_should_return_a_whoami($handler)
    {
        $handler->send(Operations::extended(ExtendedRequest::OID_WHOAMI))->willReturn(new LdapMessageResponse(1, new ExtendedResponse(new LdapResult(0, ''), null, 'foo')));

        $this->whoami()->shouldBeEqualTo('foo');
    }

    function it_should_get_the_options()
    {
        $this->getOptions()->shouldBeEqualTo([
            'version' => 3,
            'servers' => ['foo'],
            'port' => 389,
            'base_dn' => null,
            'page_size' => 1000,
            'use_ssl' => false,
            'use_tls' => false,
            'ssl_validate_cert' => true,
            'ssl_allow_self_signed' => null,
            'ssl_ca_cert' => null,
            'ssl_peer_name' => null,
            'timeout_connect' => 3,
            'timeout_read' => 15,
            'logger' => null,
        ]);
    }

    function it_should_set_the_options()
    {
        $this->setOptions(['servers' => ['bar', 'foo']]);

        $this->getOptions()->shouldHaveKeyWithValue('servers', ['bar', 'foo']);
    }
}