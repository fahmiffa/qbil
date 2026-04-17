<?php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected Client $client;
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->client = new Client([
            'host'    => $router->host,
            'user'    => $router->username,
            'pass'    => $router->password,
            'port'    => (int) $router->port,
            'timeout' => 5,
        ]);
    }

    // -------------------------
    // PPP Profiles (Paket/Package)
    // -------------------------

    public function getPppProfiles(): array
    {
        $query = new Query('/ppp/profile/print');
        return $this->client->query($query)->read();
    }

    public function addPppProfile(string $name, string $rateLimit): void
    {
        $query = (new Query('/ppp/profile/add'))
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit);
        $this->client->query($query)->read();
    }

    public function updatePppProfile(string $id, string $name, string $rateLimit): void
    {
        $query = (new Query('/ppp/profile/set'))
            ->equal('.id', $id)
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit);
        $this->client->query($query)->read();
    }

    public function removePppProfile(string $name): void
    {
        $query = (new Query('/ppp/profile/print'))->where('name', $name);
        $profiles = $this->client->query($query)->read();
        if (!empty($profiles)) {
            $delQuery = (new Query('/ppp/profile/remove'))->equal('.id', $profiles[0]['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    // -------------------------
    // Hotspot Profiles (Paket)
    // -------------------------

    public function getHotspotProfiles(): array
    {
        $query = new Query('/ip/hotspot/user/profile/print');
        return $this->client->query($query)->read();
    }

    public function addHotspotProfile(string $name, string $rateLimit = '', string $sharedUsers = '1', string $sessionTimeout = ''): void
    {
        $query = (new Query('/ip/hotspot/user/profile/add'))
            ->equal('name', $name)
            ->equal('shared-users', $sharedUsers);
        if ($rateLimit !== '') {
            $query->equal('rate-limit', $rateLimit);
        }
        if ($sessionTimeout !== '') {
            $query->equal('session-timeout', $sessionTimeout);
        }
        $this->client->query($query)->read();
    }

    public function updateHotspotProfile(string $id, string $name, string $rateLimit = '', string $sharedUsers = '1', string $sessionTimeout = ''): void
    {
        $query = (new Query('/ip/hotspot/user/profile/set'))
            ->equal('.id', $id)
            ->equal('name', $name)
            ->equal('shared-users', $sharedUsers);
        if ($rateLimit !== '') {
            $query->equal('rate-limit', $rateLimit);
        }
        if ($sessionTimeout !== '') {
            $query->equal('session-timeout', $sessionTimeout);
        }
        $this->client->query($query)->read();
    }

    public function removeHotspotProfile(string $id): void
    {
        $query = (new Query('/ip/hotspot/user/profile/remove'))->equal('.id', $id);
        $this->client->query($query)->read();
    }

    public function removePppProfileById(string $id): void
    {
        $query = (new Query('/ppp/profile/remove'))->equal('.id', $id);
        $this->client->query($query)->read();
    }

    // -------------------------
    // PPP Secrets (Customer PPPoE)
    // -------------------------

    public function addPppSecret(string $username, string $password, string $profile, string $comment = ''): void
    {
        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('service', 'pppoe')
            ->equal('profile', $profile)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function updatePppSecret(string $oldUsername, string $newUsername, string $password, string $profile): void
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $oldUsername);
        $secrets = $this->client->query($query)->read();
        if (!empty($secrets)) {
            $setQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('name', $newUsername)
                ->equal('password', $password)
                ->equal('profile', $profile);
            $this->client->query($setQuery)->read();
        } else {
            $this->addPppSecret($newUsername, $password, $profile);
        }
    }

    public function disablePppSecret(string $username): void
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $username);
        $secrets = $this->client->query($query)->read();
        if (!empty($secrets)) {
            $setQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('disabled', 'yes');
            $this->client->query($setQuery)->read();
        }
    }

    public function enablePppSecret(string $username): void
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $username);
        $secrets = $this->client->query($query)->read();
        if (!empty($secrets)) {
            $setQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('disabled', 'no');
            $this->client->query($setQuery)->read();
        }
    }

    public function removePppSecret(string $username): void
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $username);
        $secrets = $this->client->query($query)->read();
        if (!empty($secrets)) {
            $delQuery = (new Query('/ppp/secret/remove'))->equal('.id', $secrets[0]['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    // -------------------------
    // Hotspot Users (Customer Hotspot)
    // -------------------------

    public function addHotspotUser(string $username, string $password, string $profile, string $comment = ''): void
    {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $profile)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function updateHotspotUser(string $oldUsername, string $newUsername, string $password, string $profile): void
    {
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $oldUsername);
        $users = $this->client->query($query)->read();
        if (!empty($users)) {
            $setQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $users[0]['.id'])
                ->equal('name', $newUsername)
                ->equal('password', $password)
                ->equal('profile', $profile);
            $this->client->query($setQuery)->read();
        } else {
            $this->addHotspotUser($newUsername, $password, $profile);
        }
    }

    public function disableHotspotUser(string $username): void
    {
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
        $users = $this->client->query($query)->read();
        if (!empty($users)) {
            $setQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $users[0]['.id'])
                ->equal('disabled', 'yes');
            $this->client->query($setQuery)->read();
        }
    }

    public function enableHotspotUser(string $username): void
    {
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
        $users = $this->client->query($query)->read();
        if (!empty($users)) {
            $setQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $users[0]['.id'])
                ->equal('disabled', 'no');
            $this->client->query($setQuery)->read();
        }
    }

    public function removeHotspotUser(string $username): void
    {
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
        $users = $this->client->query($query)->read();
        if (!empty($users)) {
            $delQuery = (new Query('/ip/hotspot/user/remove'))->equal('.id', $users[0]['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    // -------------------------
    // Interfaces (Live from Mikrotik)
    // -------------------------

    public function getInterfaces(): array
    {
        $query = new Query('/interface/print');
        return $this->client->query($query)->read();
    }

    public function setInterfaceDisabled(string $id, bool $disabled): void
    {
        $cmd = $disabled ? '/interface/disable' : '/interface/enable';
        $query = (new Query($cmd))->equal('.id', $id);
        $this->client->query($query)->read();
    }

    public function setInterfaceComment(string $id, string $comment): void
    {
        $query = (new Query('/interface/set'))
            ->equal('.id', $id)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function addVlan(string $name, int $vlanId, string $parent, string $comment = ''): void
    {
        $query = (new Query('/interface/vlan/add'))
            ->equal('name', $name)
            ->equal('vlan-id', (string)$vlanId)
            ->equal('interface', $parent)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function addBridge(string $name, string $comment = ''): void
    {
        $query = (new Query('/interface/bridge/add'))
            ->equal('name', $name)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function removeInterface(string $id, string $type): void
    {
        $path = match ($type) {
            'vlan'   => '/interface/vlan/remove',
            'bridge' => '/interface/bridge/remove',
            default  => '/interface/vlan/remove',
        };
        $query = (new Query($path))->equal('.id', $id);
        $this->client->query($query)->read();
    }

    // -------------------------
    // IP Pool (Live from Mikrotik)
    // -------------------------

    public function getIpPools(): array
    {
        $query = new Query('/ip/pool/print');
        return $this->client->query($query)->read();
    }

    public function addIpPool(string $name, string $ranges, string $nextpool = 'none'): void
    {
        $query = (new Query('/ip/pool/add'))
            ->equal('name', $name)
            ->equal('ranges', $ranges);
        if ($nextpool !== 'none') {
            $query->equal('next-pool', $nextpool);
        }
        $this->client->query($query)->read();
    }

    public function updateIpPool(string $id, string $name, string $ranges, string $nextpool = 'none'): void
    {
        $query = (new Query('/ip/pool/set'))
            ->equal('.id', $id)
            ->equal('name', $name)
            ->equal('ranges', $ranges);
        if ($nextpool !== 'none') {
            $query->equal('next-pool', $nextpool);
        } else {
            $query->equal('next-pool', '');
        }
        $this->client->query($query)->read();
    }

    public function findAvailableIpInPool(string $poolName): ?string
    {
        // 1. Get Pool Details
        $query = (new Query('/ip/pool/print'))->where('name', $poolName);
        $pools = $this->client->query($query)->read();
        if (empty($pools)) return null;

        $ranges = explode(',', $pools[0]['ranges']);
        
        // 2. Collect Used IPs from various sources in Mikrotik
        $usedIps = [];

        // From DHCP Leases
        $leases = $this->client->query(new Query('/ip/dhcp-server/lease/print'))->read();
        foreach ($leases as $l) {
            if (isset($l['address'])) $usedIps[] = $l['address'];
        }

        // From Simple Queues (Target)
        $queues = $this->client->query(new Query('/queue/simple/print'))->read();
        foreach ($queues as $q) {
            if (isset($q['target'])) {
                // Remove CIDR if present
                $ip = explode('/', $q['target'])[0];
                $usedIps[] = $ip;
            }
        }

        // From ARP Table
        $arps = $this->client->query(new Query('/ip/arp/print'))->read();
        foreach ($arps as $a) {
            if (isset($a['address'])) $usedIps[] = $a['address'];
        }

        $usedIps = array_unique($usedIps);

        // 3. Iterate through ranges to find first free
        foreach ($ranges as $range) {
            if (str_contains($range, '-')) {
                [$start, $end] = explode('-', $range);
                $startLong = ip2long(trim($start));
                $endLong = ip2long(trim($end));

                for ($i = $startLong; $i <= $endLong; $i++) {
                    $candidate = long2ip($i);
                    if (!in_array($candidate, $usedIps)) {
                        return $candidate;
                    }
                }
            } else {
                // Single IP or CIDR (not handled deeply here, but single IP check)
                $candidate = trim($range);
                if (!in_array($candidate, $usedIps)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function removeIpPool(string $id): void
    {
        $query = (new Query('/ip/pool/remove'))->equal('.id', $id);
        $this->client->query($query)->read();
    }

    // -------------------------
    // Simple Queue (Bandwidth Management)
    // -------------------------

    public function getSimpleQueues(): array
    {
        $query = new Query('/queue/simple/print');
        return $this->client->query($query)->read();
    }

    public function addSimpleQueue(string $name, string $target, string $maxLimit, string $comment = ''): void
    {
        $query = (new Query('/queue/simple/add'))
            ->equal('name', $name)
            ->equal('target', $target)
            ->equal('max-limit', $maxLimit)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function updateSimpleQueue(string $oldName, string $newName, string $target, string $maxLimit): void
    {
        // 1. Try search by Name first (identifier in app)
        $query = (new Query('/queue/simple/print'))->where('name', $oldName);
        $queues = $this->client->query($query)->read();

        // 2. If not found, try search by Target IP (in case IP is shared or identifier shifted)
        if (empty($queues)) {
            // Append /32 if not present for exact match in Mikrotik target
            $searchTarget = str_contains($target, '/') ? $target : $target . '/32';
            $query = (new Query('/queue/simple/print'))->where('target', $searchTarget);
            $queues = $this->client->query($query)->read();
        }

        if (!empty($queues)) {
            $setQuery = (new Query('/queue/simple/set'))
                ->equal('.id', $queues[0]['.id'])
                ->equal('name', $newName)
                ->equal('target', $target)
                ->equal('max-limit', $maxLimit);
            $this->client->query($setQuery)->read();
        } else {
            // Check if name exists before adding to avoid "already has such name" error
            $query = (new Query('/queue/simple/print'))->where('name', $newName);
            $existsByName = $this->client->query($query)->read();
            
            if (!empty($existsByName)) {
                $setQuery = (new Query('/queue/simple/set'))
                    ->equal('.id', $existsByName[0]['.id'])
                    ->equal('target', $target)
                    ->equal('max-limit', $maxLimit);
                $this->client->query($setQuery)->read();
            } else {
                $this->addSimpleQueue($newName, $target, $maxLimit);
            }
        }
    }

    public function disableSimpleQueue(string $name): void
    {
        $query = (new Query('/queue/simple/print'))->where('name', $name);
        $queues = $this->client->query($query)->read();
        if (!empty($queues)) {
            $setQuery = (new Query('/queue/simple/set'))
                ->equal('.id', $queues[0]['.id'])
                ->equal('disabled', 'yes');
            $this->client->query($setQuery)->read();
        }
    }

    public function enableSimpleQueue(string $name): void
    {
        $query = (new Query('/queue/simple/print'))->where('name', $name);
        $queues = $this->client->query($query)->read();
        if (!empty($queues)) {
            $setQuery = (new Query('/queue/simple/set'))
                ->equal('.id', $queues[0]['.id'])
                ->equal('disabled', 'no');
            $this->client->query($setQuery)->read();
        }
    }

    public function removeSimpleQueue(string $name): void
    {
        $query = (new Query('/queue/simple/print'))->where('name', $name);
        $queues = $this->client->query($query)->read();
        if (!empty($queues)) {
            $delQuery = (new Query('/queue/simple/remove'))->equal('.id', $queues[0]['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    // -------------------------
    // DHCP Server Leases
    // -------------------------

    public function addDhcpLease(string $mac, string $ip, string $comment = ''): void
    {
        $query = (new Query('/ip/dhcp-server/lease/add'))
            ->equal('mac-address', $mac)
            ->equal('address', $ip)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function updateDhcpLeaseByMac(string $oldMac, string $newMac, string $newIp): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('mac-address', $oldMac);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            $setQuery = (new Query('/ip/dhcp-server/lease/set'))
                ->equal('.id', $leases[0]['.id'])
                ->equal('mac-address', $newMac)
                ->equal('address', $newIp);
            $this->client->query($setQuery)->read();
        } else {
            $this->addDhcpLease($newMac, $newIp);
        }
    }

    public function removeDhcpLeaseByMac(string $mac): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('mac-address', $mac);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            $delQuery = (new Query('/ip/dhcp-server/lease/remove'))->equal('.id', $leases[0]['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    public function setDhcpLeaseStateByMac(string $mac, bool $disabled): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('mac-address', $mac);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            $setQuery = (new Query('/ip/dhcp-server/lease/set'))
                ->equal('.id', $leases[0]['.id'])
                ->equal('disabled', $disabled ? 'yes' : 'no');
            $this->client->query($setQuery)->read();
        }
    }
}
