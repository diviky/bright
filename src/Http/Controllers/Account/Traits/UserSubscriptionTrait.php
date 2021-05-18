<?php

namespace Diviky\Bright\Http\Controllers\Account\Traits;

trait UserSubscriptionTrait
{
    public function isUserSubscribed($channel, $user_id = null)
    {
        $user_id = $user_id ?: user('id');

        return (object) $this->db->table('user_subscriptions')
            ->where('user_id', $user_id)
            ->where('channel', $channel)
            ->value('status');
    }

    public function pluckUserSubscriptions($user_id = null)
    {
        $user_id = $user_id ?: user('id');

        return (object) $this->db->table('user_subscriptions')
            ->where('user_id', $user_id)
            ->pluck('status', 'channel')
            ->toArray();
    }

    public function addUserSubscription($channel, $status, $user_id = null)
    {
        $user_id = $user_id ?: user('id');

        return $this->db->table('user_subscriptions')
            ->updateOrInsert(
                ['user_id' => $user_id, 'channel' => $channel],
                ['status' => $status]
            );
    }

    public function getUserSubscriptions($user_id = null)
    {
        $user_id = $user_id ?: user('id');

        return $this->db->table('user_subscriptions')
            ->where('user_id', $user_id)
            ->get();
    }

    public function getSubscriptionChannels()
    {
        return $this->db->table('subscription_channels')
            ->where('status', 1)
            ->get()
            ->keyBy('name');
    }

    public function getUserSubscriptionChannels($user_id = null)
    {
        $user_id = $user_id ?: user('id');

        return $this->db->table('subscription_channels as c')
            ->leftJoin('user_subscriptions as s', function ($join) use ($user_id) {
                $join->on('s.channel', 'c.name')
                    ->where('s.user_id', $user_id);
            })
            ->where('c.status', 1)
            ->select(['s.id', 'c.name as channel', 's.status', 'c.title'])
            ->get();
    }
}
