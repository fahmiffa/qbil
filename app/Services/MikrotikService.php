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
            'timeout' => 30,
        ]);
    }
    public function checkConnection(): bool
    {
        try {
            $query = new Query('/system/identity/print');
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    // -------------------------
    // PPP Profiles (Paket/Package)
    // -------------------------

    public function getPppProfiles(): array
    {
        $query = new Query('/ppp/profile/print');
        return $this->client->query($query)->read();
    }

    public function addPppProfile(string $name, string $rateLimit, ?string $localAddress = null, ?string $remoteAddress = null): void
    {
        $query = (new Query('/ppp/profile/add'))
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit)
            ->equal('only-one', 'yes')
            ->equal('dns-server', '8.8.8.8,8.8.4.4');
        
        if ($localAddress) $query->equal('local-address', $localAddress);
        if ($remoteAddress) $query->equal('remote-address', $remoteAddress);

        $this->client->query($query)->read();
    }

    public function updatePppProfileFull(string $id, string $name, string $rateLimit, ?string $localAddress = null, ?string $remoteAddress = null): void
    {
        $query = (new Query('/ppp/profile/set'))
            ->equal('.id', $id)
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit)
            ->equal('only-one', 'yes')
            ->equal('dns-server', '8.8.8.8,8.8.4.4');

        if ($localAddress) $query->equal('local-address', $localAddress);
        if ($remoteAddress) $query->equal('remote-address', $remoteAddress);

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

    public function addHotspotProfileFull(string $name, string $rateLimit, string $sharedUsers = '1', string $addressPool = 'none', string $sessionTimeout = '8h'): void
    {
        $query = (new Query('/ip/hotspot/user/profile/add'))
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit)
            ->equal('shared-users', $sharedUsers)
            ->equal('address-pool', $addressPool)
            ->equal('session-timeout', $sessionTimeout);

        $this->client->query($query)->read();
    }

    public function updateHotspotProfileFull(string $id, string $name, string $rateLimit, string $sharedUsers = '1', string $addressPool = 'none', string $sessionTimeout = '8h'): void
    {
        $query = (new Query('/ip/hotspot/user/profile/set'))
            ->equal('.id', $id)
            ->equal('name', $name)
            ->equal('rate-limit', $rateLimit)
            ->equal('shared-users', $sharedUsers)
            ->equal('address-pool', $addressPool)
            ->equal('session-timeout', $sessionTimeout);

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

    public function updatePppSecret(string $oldUsername, string $newUsername, string $password, string $profile, string $comment = ''): void
    {
        $query = (new Query('/ppp/secret/print'))->where('name', $oldUsername);
        $secrets = $this->client->query($query)->read();
        if (!empty($secrets)) {
            $setQuery = (new Query('/ppp/secret/set'))
                ->equal('.id', $secrets[0]['.id'])
                ->equal('name', $newUsername)
                ->equal('password', $password)
                ->equal('profile', $profile);
            
            if ($comment) $setQuery->equal('comment', $comment);

            $this->client->query($setQuery)->read();
        } else {
            $this->addPppSecret($newUsername, $password, $profile, $comment);
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

    public function updateHotspotUser(string $oldUsername, string $newUsername, string $password, string $profile, string $comment = ''): void
    {
        $query = (new Query('/ip/hotspot/user/print'))->where('name', $oldUsername);
        $users = $this->client->query($query)->read();
        if (!empty($users)) {
            $setQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $users[0]['.id'])
                ->equal('name', $newUsername)
                ->equal('password', $password)
                ->equal('profile', $profile);
            
            if ($comment) $setQuery->equal('comment', $comment);

            $this->client->query($setQuery)->read();
        } else {
            $this->addHotspotUser($newUsername, $password, $profile, $comment);
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
        
        // 2. Collect Used IPs efficiently
        // Using associative array for O(1) lookup speed
        $usedIps = [];

        // Optimize: Only fetch what's absolutely necessary or use more efficient queries
        // If we have thousands of leases/queues, this is the bottleneck.
        
        // From DHCP Leases
        $leases = $this->client->query(new Query('/ip/dhcp-server/lease/print'))->read();
        foreach ($leases as $l) {
            if (isset($l['address'])) $usedIps[$l['address']] = true;
        }

        // From Simple Queues (Target)
        $queues = $this->client->query(new Query('/queue/simple/print'))->read();
        foreach ($queues as $q) {
            if (isset($q['target'])) {
                $ip = explode('/', $q['target'])[0];
                $usedIps[$ip] = true;
            }
        }

        // From ARP Table (Optional/Limit? Checking ARP can be slow)
        // Let's keep it but be aware it adds to the query count
        $arps = $this->client->query(new Query('/ip/arp/print'))->read();
        foreach ($arps as $a) {
            if (isset($a['address'])) $usedIps[$a['address']] = true;
        }

        // 3. Iterate through ranges to find first free
        foreach ($ranges as $range) {
            if (str_contains($range, '-')) {
                [$start, $end] = explode('-', $range);
                $startLong = ip2long(trim($start));
                $endLong = ip2long(trim($end));

                // Limit the search to prevent infinite/long loops (Max 1000 IPs per range)
                $count = 0;
                for ($i = $startLong; $i <= $endLong; $i++) {
                    $candidate = long2ip($i);
                    if (!isset($usedIps[$candidate])) {
                        return $candidate;
                    }
                    
                    $count++;
                    if ($count > 2000) break; // Break if we scanned too many without success
                }
            } else {
                $candidate = trim($range);
                if (!isset($usedIps[$candidate])) {
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

    public function updateSimpleQueue(string $oldName, string $newName, string $target, string $maxLimit, string $comment = ''): void
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
            
            if ($comment) $setQuery->equal('comment', $comment);

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
                
                if ($comment) $setQuery->equal('comment', $comment);

                $this->client->query($setQuery)->read();
            } else {
                $this->addSimpleQueue($newName, $target, $maxLimit, $comment);
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
    // -------------------------
    // DHCP Server Leases
    // -------------------------

    public function getDhcpServers(): array
    {
        $query = new Query('/ip/dhcp-server/print');
        return $this->client->query($query)->read();
    }

    public function addDhcpLease(string $mac, string $ip, string $server = 'all', string $comment = '', string $rateLimit = ''): void
    {
        $query = (new Query('/ip/dhcp-server/lease/add'))
            ->equal('mac-address', $mac)
            ->equal('address', $ip)
            ->equal('server', $server)
            ->equal('comment', $comment);
        
        if ($rateLimit) {
            $query->equal('rate-limit', $rateLimit);
        }

        $this->client->query($query)->read();   
    }

    public function updateDhcpLeaseByMac(string $oldMac, string $newMac, string $newIp, string $server = 'all', ?string $comment = null, string $rateLimit = ''): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('mac-address', $oldMac);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            $setQuery = (new Query('/ip/dhcp-server/lease/set'))
                ->equal('.id', $leases[0]['.id'])
                ->equal('mac-address', $newMac)
                ->equal('address', $newIp)
                ->equal('server', $server);
            
            if ($comment) $setQuery->equal('comment', $comment);
            if ($rateLimit) $setQuery->equal('rate-limit', $rateLimit);
            
            $a = $this->client->query($setQuery)->read();
        } else {
            $this->addDhcpLease($newMac, $newIp, $server, $comment ?? '', $rateLimit);
        }
    }

    public function removeDhcpLeaseByMac(string $mac): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('mac-address', $mac);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            foreach ($leases as $lease) {
                $delQuery = (new Query('/ip/dhcp-server/lease/remove'))->equal('.id', $lease['.id']);
                $this->client->query($delQuery)->read();
            }
        }
    }

    public function removeDhcpLeaseByIp(string $ip): void
    {
        $query = (new Query('/ip/dhcp-server/lease/print'))->where('address', $ip);
        $leases = $this->client->query($query)->read();

        if (!empty($leases)) {
            foreach ($leases as $lease) {
                $delQuery = (new Query('/ip/dhcp-server/lease/remove'))->equal('.id', $lease['.id']);
                $this->client->query($delQuery)->read();
            }
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

    public function getDhcpLeases(): array
    {
        $query = new Query('/ip/dhcp-server/lease/print');
        return $this->client->query($query)->read();
    }

    // -------------------------
    // Firewall Address List
    // -------------------------

    public function addToAddressList(string $address, string $list, string $comment = ''): void
    {
        // First check if already exists to avoid duplicates
        if ($this->isInAddressList($address, $list)) {
            return;
        }

        $query = (new Query('/ip/firewall/address-list/add'))
            ->equal('address', $address)
            ->equal('list', $list)
            ->equal('comment', $comment);
        $this->client->query($query)->read();
    }

    public function removeFromAddressList(string $address, string $list): void
    {
        $query = (new Query('/ip/firewall/address-list/print'))
            ->where('address', $address)
            ->where('list', $list);
        $items = $this->client->query($query)->read();

        foreach ($items as $item) {
            $delQuery = (new Query('/ip/firewall/address-list/remove'))->equal('.id', $item['.id']);
            $this->client->query($delQuery)->read();
        }
    }

    public function isInAddressList(string $address, string $list): bool
    {
        $query = (new Query('/ip/firewall/address-list/print'))
            ->where('address', $address)
            ->where('list', $list);
        $items = $this->client->query($query)->read();

        return !empty($items);
    }
}
