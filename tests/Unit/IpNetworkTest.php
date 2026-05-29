<?php

namespace Tests\Unit;

use App\Support\IpNetwork;
use PHPUnit\Framework\TestCase;

class IpNetworkTest extends TestCase
{
    public function test_it_matches_ipv4_cidr_networks(): void
    {
        $this->assertTrue(IpNetwork::contains(['222.2.82.0/24'], '222.2.82.117'));
        $this->assertFalse(IpNetwork::contains(['222.2.82.0/24'], '222.2.83.117'));
    }

    public function test_it_matches_single_ip_addresses(): void
    {
        $this->assertTrue(IpNetwork::contains(['192.168.1.10'], '192.168.1.10'));
        $this->assertFalse(IpNetwork::contains(['192.168.1.10'], '192.168.1.11'));
    }

    public function test_it_parses_network_lists_from_common_separators(): void
    {
        $this->assertSame(
            ['222.2.82.0/24', '192.168.1.10', '10.10.0.0/16'],
            IpNetwork::parseList("222.2.82.0/24\n192.168.1.10, 10.10.0.0/16")
        );
    }

    public function test_it_parses_named_json_network_entries(): void
    {
        $json = IpNetwork::encodeEntries([
            ['name' => 'Router Utama', 'network' => '114.79.18.0/24'],
            ['name' => 'Koneksi Cadangan', 'network' => '36.77.44.7'],
        ]);

        $this->assertSame([
            ['name' => 'Router Utama', 'network' => '114.79.18.0/24'],
            ['name' => 'Koneksi Cadangan', 'network' => '36.77.44.7'],
        ], IpNetwork::parseEntries($json));

        $this->assertSame(['114.79.18.0/24', '36.77.44.7'], IpNetwork::parseList($json));
    }

    public function test_it_rejects_invalid_cidr_prefixes(): void
    {
        $this->assertFalse(IpNetwork::isValid('222.2.82.0/33'));
        $this->assertFalse(IpNetwork::isValid('not-an-ip'));
    }
}
